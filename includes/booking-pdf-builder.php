<?php
// FILE PATH: /includes/booking-pdf-builder.php
// Builds a print-ready A4 PDF for one bookings row. Same visual design
// as includes/pdf-builder.php (custom_builds' PDF), but reads the
// bookings table's own columns and — unlike custom_builds, which never
// gets a discount — adds a strike-through original total + discounted
// final total when bookings.discount is set.
// Returns the relative path (under /storage/bookings/pdf) to the saved
// file, or null on failure.
require_once dirname(__DIR__) . '/lib/SimplePdf.php';

function buildBookingRecordPdf(array $booking): ?string
{
    $pdf = new SimplePdf();
    $pdf->addPage();

    $green = [80, 188, 129];   // secondary
    $dark  = [26, 32, 57];     // primary
    $gray  = [120, 120, 130];
    $light = [235, 237, 242];
    $red   = [220, 80, 80];

    $siteName = getSetting('site_name', 'TravHub');
    $margin = 20;
    $right = SimplePdf::PAGE_W_MM - $margin;

    $customer   = json_decode($booking['customer_infos']  ?? '{}', true) ?: [];
    $persons    = json_decode($booking['persons']         ?? '{}', true) ?: [];
    $flightInfo = json_decode($booking['flight_infos']    ?? '{}', true) ?: [];
    $days       = json_decode($booking['days']            ?? '{}', true) ?: [];
    $hotelInfos = json_decode($booking['hotel_infos']     ?? '{}', true) ?: [];
    $transport  = json_decode($booking['transport_infos'] ?? '{}', true) ?: [];
    $moyallem   = json_decode($booking['moyallem_infos']  ?? '{}', true) ?: [];
    $ziarah     = json_decode($booking['ziarah_infos']    ?? '{}', true) ?: [];
    $totals     = json_decode($booking['total_prices']    ?? '{}', true) ?: [];
    $finalTotals= json_decode($booking['final_prices']    ?? '{}', true) ?: [];
    $discount   = json_decode($booking['discount']        ?? 'null', true);
    $snap       = json_decode($booking['data_json']       ?? '{}', true) ?: [];
    $choices    = array_filter(explode(',', $booking['choices'] ?? ''));

    // ── Header band ──
    $pdf->rect(0, 0, SimplePdf::PAGE_W_MM, 32, $dark);
    $pdf->setColor(255, 255, 255);
    $pdf->setFont('B', 20);
    $pdf->text($margin, 18, $siteName);
    $pdf->setFont('', 9);
    $pdf->text($margin, 25, 'Umrah Journey — Booking Confirmation');
    $pdf->setFont('B', 10);
    $pdf->textRight($right, 18, 'Ref: ' . ($booking['sys_id'] ?? '—'));
    $pdf->setFont('', 8);
    $pdf->textRight($right, 24, 'Generated ' . date('d M Y, h:i A'));
    $pdf->setColor(0, 0, 0);

    $y = 42;

    // ── Traveler block ──
    $pdf->setFont('B', 13);
    $pdf->setColor(...$dark);
    $pdf->text($margin, $y, 'Traveler Information');
    $pdf->setColor(0,0,0);
    $y += 3;
    $pdf->line($margin, $y, $right, $y, 0.4, $green);
    $y += 8;

    $pdf->setFont('', 10);
    $travelerLines = [
        ['Name', $customer['name']  ?? '—'],
        ['Phone', $customer['phone'] ?? '—'],
        ['Email', $customer['email'] ?? '—'],
        ['Travelers', sprintf('%d Adult(s)%s%s',
            (int)($persons['adults'] ?? 1),
            !empty($persons['children']) ? ', ' . (int)$persons['children'] . ' Child(ren)' : '',
            !empty($persons['infants']) ? ', ' . (int)$persons['infants'] . ' Infant(s)' : ''
        )],
    ];
    foreach ($travelerLines as [$label, $val]) {
        $pdf->setColor(...$gray);
        $pdf->text($margin, $y, $label . ':');
        $pdf->setColor(0,0,0);
        $pdf->text($margin + 35, $y, (string)$val);
        $y += 6.5;
    }

    $y += 6;

    // ── Journey details block ──
    $pdf->setFont('B', 13);
    $pdf->setColor(...$dark);
    $pdf->text($margin, $y, 'Journey Details');
    $pdf->setColor(0,0,0);
    $y += 3;
    $pdf->line($margin, $y, $right, $y, 0.4, $green);
    $y += 8;

    $visaName    = $snap['visa']['type'] ?? ($snap['visa']['visa_type'] ?? '—');
    $serviceName = $snap['trip']['service_level'] ?? ($snap['serviceLevel'] ?? '—');

    $rows = [
        ['Visa', in_array('visa', $choices) ? $visaName : 'Not selected'],
        ['Package Level', $serviceName ?: '—'],
    ];
    if (in_array('flight', $choices)) {
        $rows[] = ['Flight', $flightInfo['connection_type'] ?? '—'];
    }
    $rows[] = ['Duration', ((int)($days['total'] ?? 0)) . ' Days'];
    $rows[] = ['Stay Split', "Makkah {$days['makkah']}n + Madinah {$days['madinah']}n"];
    if (in_array('hotel', $choices)) {
        $rows[] = ['Hotel Category', $hotelInfos['category'] ?? '—'];
    }

    if (in_array('transport', $choices) && !empty($transport['legs'])) {
        $legLabels = array_map(fn($l) => ($l['vehicle_name'] ?? '') . ' — ' . ($l['route_name'] ?? ''), $transport['legs']);
        $rows[] = ['Transport', implode('; ', array_filter($legLabels))];
    } else {
        $rows[] = ['Transport', 'Not selected'];
    }

    $moyallemNames = '—';
    if (!empty($moyallem['services']) && is_array($moyallem['services'])) {
        $names = array_filter(array_map(fn($m) => $m['service_name'] ?? '', $moyallem['services']));
        if ($names) $moyallemNames = implode(', ', $names);
    }
    $rows[] = ['Moyallem', $moyallemNames];

    $ziarahNames = '—';
    if (!empty($ziarah['selections']) && is_array($ziarah['selections'])) {
        $names = array_filter(array_map(fn($z) => $z['name'] ?? '', $ziarah['selections']));
        if ($names) $ziarahNames = implode(', ', $names);
    }
    $rows[] = ['Ziarah', $ziarahNames];

    $pdf->setFont('', 10);
    foreach ($rows as [$label, $val]) {
        $pdf->setColor(...$gray);
        $pdf->text($margin, $y, $label . ':');
        $pdf->setColor(0,0,0);
        $y = $pdf->multiText($margin + 40, $y, $right - ($margin + 40), (string)$val, 5.5);
        $y += 1.5;
    }

    $y += 6;

    // ── Price band — shows the original total; when a discount is set,
    // also shows the discount and the final (discounted) total, unlike
    // custom_builds' PDF, which never carries a discount. ──
    $sar = (float) ($totals['sar'][0] ?? 0);
    $bdt = (float) ($totals['bdt'][0] ?? 0);
    $usd = (float) ($totals['usd'][0] ?? 0);
    $hasDiscount = !empty($discount) && !empty($finalTotals);

    $boxH = $hasDiscount ? 48 : 26;
    $pdf->rect($margin, $y, $right - $margin, $boxH, $light);
    $pdf->setColor(...$gray);
    $pdf->setFont('', 9);
    $pdf->text($margin + 6, $y + 7, $hasDiscount ? 'ORIGINAL TOTAL' : 'TOTAL');
    $pdf->setFont($hasDiscount ? '' : 'B', $hasDiscount ? 10 : 15);
    $pdf->setColor(...($hasDiscount ? $gray : $dark));
    $origLine = 'SR ' . number_format($sar, 0) . '   |   BDT ' . number_format($bdt, 0) . '   |   USD ' . number_format($usd, 2);
    $pdf->text($margin + 6, $y + ($hasDiscount ? 13 : 16), $origLine);

    if ($hasDiscount) {
        $discCurrency = strtoupper($discount['currency'] ?? 'BDT');
        $discLabel = $discount['type'] === 'percentage'
            ? $discount['value'] . '% off (on ' . $discCurrency . ')'
            : number_format($discount['value'], 0) . ' ' . $discCurrency . ' off';
        $pdf->setFont('', 9);
        $pdf->setColor(...$red);
        $pdf->text($margin + 6, $y + 21, 'DISCOUNT: ' . $discLabel);

        $fSar = (float) ($finalTotals['sar'][0] ?? 0);
        $fBdt = (float) ($finalTotals['bdt'][0] ?? 0);
        $fUsd = (float) ($finalTotals['usd'][0] ?? 0);
        $pdf->setFont('', 9);
        $pdf->setColor(...$gray);
        $pdf->text($margin + 6, $y + 29, 'FINAL TOTAL');
        $pdf->setFont('B', 15);
        $pdf->setColor(...$dark);
        $pdf->text($margin + 6, $y + 37, 'SR ' . number_format($fSar, 0) . '   |   BDT ' . number_format($fBdt, 0) . '   |   USD ' . number_format($fUsd, 2));
    }

    $pdf->setFont('', 8);
    $pdf->setColor(...$gray);
    $pdf->textRight($right - 6, $y + ($hasDiscount ? 44 : ($boxH - 4)), 'Subject to final confirmation by our consultants');
    $pdf->setColor(0,0,0);
    $y += $boxH + 10;

    // ── Footer note ──
    $pdf->setFont('I', 8.5);
    $pdf->setColor(...$gray);
    $y = $pdf->multiText($margin, $y, $right - $margin,
        'This is your confirmed Umrah booking based on your selections. Our team will contact you shortly to finalize details and payment.',
        5);
    $pdf->setColor(0,0,0);

    // Bottom brand strip
    $pdf->line($margin, 280, $right, 280, 0.3, $light);
    $pdf->setFont('', 8);
    $pdf->setColor(...$gray);
    $pdf->text($margin, 286, $siteName . ' — ' . getSetting('site_url', ''));
    $pdf->textRight($right, 286, getSetting('whatsapp_number', ''));

    // ── Save ──
    $dir = dirname(__DIR__) . '/storage/bookings/pdf';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $booking['sys_id']) . '.pdf';
    $fullPath = $dir . '/' . $filename;
    if (!$pdf->save($fullPath)) return null;

    return 'storage/bookings/pdf/' . $filename;
}