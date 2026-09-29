<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdmin();
require_once dirname(__DIR__) . '/data/server/db_connection.php';

$type   = sanitize($_GET['type'] ?? 'bookings');
$status = sanitize($_GET['status'] ?? 'all');

$db = getDB();

if ($type === 'bookings') {
    $where  = $status !== 'all' ? "WHERE status = :s" : "";
    $params = $status !== 'all' ? [':s' => $status] : [];
    $stmt   = $db->prepare("SELECT booking_ref, customer_name, customer_email, customer_phone, package_name, departure_date, adults, children, infants, makkah_nights, madinah_nights, hotel_category, transport_type, visa_type, total_price_bdt, total_price_sar, status, created_at FROM bookings $where ORDER BY created_at DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="travhub-bookings-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Booking Ref','Name','Email','Phone','Package','Departure','Adults','Children','Infants','Makkah Nights','Madinah Nights','Hotel','Transport','Visa','Price BDT','Price SAR','Status','Submitted']);
    foreach ($rows as $row) fputcsv($out, array_values($row));
    fclose($out);
    exit;
}
