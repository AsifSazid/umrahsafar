<?php
// FILE PATH: /includes/pdf-builder.php
// Builds a print-ready A4 PDF for one custom_builds row.
// Returns the relative path (under /storage/custom-builds/pdf) to the saved file, or null on failure.
//
// Design/layout is unchanged from the original version — only the field
// access below was rewritten, to read the new structured JSON columns
// (customer_infos, persons, visa_infos, hotel_infos, transport_infos,
// moyallem_infos, total_prices) plus data_json for readable labels that
// aren't stored as plain text in a structured column (e.g. the visa
// type's name, service level name — those columns hold sys_id
// references, not display names).
require_once dirname(__DIR__) . '/lib/SimplePdf.php';

function buildBookingPdf(array $build): ?string
{
    $pdf = new SimplePdf();
    $pdf->addPage();

    $green = [80, 188, 129];   // secondary
    $dark  = [26, 32, 57];     // primary
    $gray  = [120, 120, 130];
    $light = [235, 237, 242];

    $siteName = getSetting('site_name', 'TravHub');
    $margin = 20;
    $right = SimplePdf::PAGE_W_MM - $margin;

    // ── Decode the structured columns + the raw snapshot (data_json) —
    // the snapshot fills in readable labels (visa/service-level names,
    // hotel category, etc.) that the structured columns store as sys_id
    // references rather than plain text. ──
    $customer   = json_decode($build['customer_infos']  ?? '{}', true) ?: [];
    $persons    = json_decode($build['persons']         ?? '{}', true) ?: [];
    $visaInfos  = json_decode($build['visa_infos']       ?? '{}', true) ?: [];
    $flightInfo = json_decode($build['flight_infos']     ?? '{}', true) ?: [];
    $days       = json_decode($build['days']             ?? '{}', true) ?: [];
    $hotelInfos = json_decode($build['hotel_infos']      ?? '{}', true) ?: [];
    $transport  = json_decode($build['transport_infos']  ?? '{}', true) ?: [];
    $moyallem   = json_decode($build['moyallem_infos']   ?? '{}', true) ?: [];
    $ziarah     = json_decode($build['ziarah_infos']     ?? '{}', true) ?: [];
    $totals     = json_decode($build['total_prices']     ?? '{}', true) ?: [];
    $snap       = json_decode($build['data_json']        ?? '{}', true) ?: [];

    $choices = array_filter(explode(',', $build['choices'] ?? ''));

    // ── Header band ──
    $pdf->rect(0, 0, SimplePdf::PAGE_W_MM, 32, $dark);
    $pdf->setColor(255, 255, 255);
    $pdf->setFont('B', 20);
    $pdf->text($margin, 18, $siteName);
    $pdf->setFont('', 9);
    $pdf->text($margin, 25, 'Umrah Journey — Booking Preview');
    $pdf->setFont('B', 10);
    $pdf->textRight($right, 18, 'Ref: ' . ($build['sys_id'] ?? '—'));
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

    // Readable names (visa type, service level) live in the raw snapshot —
    // the structured columns only store their sys_id references.
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

    // ── Total price band — now shows all three currencies (SAR/BDT/USD),
    // same box style as before, just three lines of amount instead of one. ──
    $sar = (float) ($totals['sar'][0] ?? 0);
    $bdt = (float) ($totals['bdt'][0] ?? 0);
    $usd = (float) ($totals['usd'][0] ?? 0);

    $boxH = 26;
    $pdf->rect($margin, $y, $right - $margin, $boxH, $light);
    $pdf->setColor(...$gray);
    $pdf->setFont('', 9);
    $pdf->text($margin + 6, $y + 7, 'ESTIMATED TOTAL');
    $pdf->setColor(...$dark);
    $pdf->setFont('B', 15);
    $pdf->text($margin + 6, $y + 16, 'SR ' . number_format($sar, 0) . '   |   BDT ' . number_format($bdt, 0) . '   |   USD ' . number_format($usd, 2));
    $pdf->setFont('', 8);
    $pdf->setColor(...$gray);
    $pdf->textRight($right - 6, $y + 22, 'Subject to final confirmation by our consultants');
    $pdf->setColor(0,0,0);
    $y += $boxH + 10;

    // ── Footer note ──
    $pdf->setFont('I', 8.5);
    $pdf->setColor(...$gray);
    $y = $pdf->multiText($margin, $y, $right - $margin,
        'This is a preview of your custom Umrah journey based on your selections. Prices are estimates and may be adjusted after review. Our team will contact you shortly to confirm final details and payment.',
        5);
    $pdf->setColor(0,0,0);

    // Bottom brand strip
    $pdf->line($margin, 280, $right, 280, 0.3, $light);
    $pdf->setFont('', 8);
    $pdf->setColor(...$gray);
    $pdf->text($margin, 286, $siteName . ' — ' . getSetting('site_url', ''));
    $pdf->textRight($right, 286, getSetting('whatsapp_number', ''));

    // ── Save ──
    $dir = dirname(__DIR__) . '/storage/custom-builds/pdf';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $build['sys_id']) . '.pdf';
    $fullPath = $dir . '/' . $filename;
    if (!$pdf->save($fullPath)) return null;

    return 'storage/custom-builds/pdf/' . $filename; // relative path, stored in DB + used to build public URL
}