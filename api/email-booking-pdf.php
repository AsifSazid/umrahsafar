<?php
// FILE PATH: /api/email-booking-pdf.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed.');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!rateLimit("email_pdf_{$ip}", 5, 3600))
    jsonResponse(false, 'Too many requests. Please try again later.');

$ref   = sanitize($_POST['build_ref'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$ref) jsonResponse(false, 'Missing booking reference.');
if (!$email) jsonResponse(false, 'Please enter a valid email address.');

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT build_ref, pdf_path, customer_name FROM custom_builds WHERE build_ref = ?");
    $stmt->execute([$ref]);
    $build = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$build)              jsonResponse(false, 'Booking not found.');
    if (!$build['pdf_path'])  jsonResponse(false, 'PDF is not ready yet — please try again shortly.');

    $pdfFullPath = dirname(__DIR__) . '/' . $build['pdf_path'];
    if (!is_file($pdfFullPath)) jsonResponse(false, 'PDF file is missing on the server.');

    $siteName = getSetting('site_name', 'TravHub');
    $subject  = "[{$siteName}] Your Umrah Booking Preview — {$build['build_ref']}";
    $bodyText = "Assalamu Alaikum " . ($build['customer_name'] ?: '') . ",\r\n\r\n"
              . "Please find attached your Umrah journey booking preview (Ref: {$build['build_ref']}).\r\n\r\n"
              . "Our team will contact you shortly to confirm final details.\r\n\r\n"
              . "— {$siteName}";

    // Build a simple MIME message with a PDF attachment — no external mail library needed.
    $boundary = md5(uniqid((string)mt_rand(), true));
    $fileContent = chunk_split(base64_encode(file_get_contents($pdfFullPath)));
    $filename = basename($pdfFullPath);

    $headers  = "From: {$siteName} <noreply@" . preg_replace('#^https?://#', '', rtrim(getSetting('site_url','travhub.com.bd'),'/')) . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $message  = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $bodyText . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: application/pdf; name=\"{$filename}\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
    $message .= $fileContent . "\r\n";
    $message .= "--{$boundary}--";

    $sent = @mail($email, $subject, $message, $headers);

    if ($sent) {
        jsonResponse(true, 'PDF sent to your email.');
    } else {
        jsonResponse(false, 'Could not send the email right now. Please try WhatsApp instead.');
    }
} catch (Exception $e) {
    error_log('Email PDF error: ' . $e->getMessage());
    jsonResponse(false, 'Something went wrong. Please try again.');
}
