<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrf($token)) {
    jsonResponse(false, 'Invalid security token. Please refresh the page.');
}

// Rate limiting — 5 per hour per IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!rateLimit("contact_{$ip}", 5, 3600)) {
    jsonResponse(false, 'Too many submissions. Please try again later.');
}

// Validate inputs
$name    = sanitize($_POST['name']    ?? '');
$email   = sanitize($_POST['email']   ?? '');
$phone   = sanitize($_POST['phone']   ?? '');
$subject = sanitize($_POST['subject'] ?? 'General Inquiry');
$message = sanitize($_POST['message'] ?? '');

$errors = [];
if (strlen($name) < 2)              $errors[] = 'Please enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if (strlen($phone) < 7)             $errors[] = 'Please enter a valid phone number.';
if (strlen($message) < 10)          $errors[] = 'Message must be at least 10 characters.';

if ($errors) {
    jsonResponse(false, implode(' ', $errors));
}

// Save to database
try {
    $db  = getDB();
    $sql = "INSERT INTO contact_inquiries (name, email, phone, subject, message, ip_address, created_at)
            VALUES (:name, :email, :phone, :subject, :message, :ip, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':phone'   => $phone,
        ':subject' => $subject,
        ':message' => $message,
        ':ip'      => $ip
    ]);
} catch (Exception $e) {
    // Log error silently — don't block submission
    error_log('Contact DB error: ' . $e->getMessage());
}

// Send email notification
$emailBody = "
New Contact Inquiry — TravHub

Name:    {$name}
Email:   {$email}
Phone:   {$phone}
Subject: {$subject}

Message:
{$message}

---
Submitted: " . date('Y-m-d H:i:s') . "
IP: {$ip}
";

$headers = "From: TravHub Website <noreply@travhub.com.bd>\r\n";
$headers .= "Reply-To: {$email}\r\n";
@mail(ADMIN_EMAIL, "[TravHub] New Inquiry: {$subject}", $emailBody, $headers);

// WhatsApp deep link (for admin notification — logged, not sent programmatically here)
$waMessage = urlencode("New inquiry from {$name} ({$phone}): {$subject}");

jsonResponse(true, 'Thank you! We will get back to you within 24 hours.', [
    'whatsapp_url' => "https://wa.me/" . ltrim(WHATSAPP_NUMBER, '+') . "?text={$waMessage}"
]);
