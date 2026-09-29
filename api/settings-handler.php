<?php
// FILE PATH: /api/settings-handler.php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed.');
if (!verifyCsrf($_POST['csrf_token'] ?? '')) jsonResponse(false, 'Invalid security token.');

$action = sanitize($_POST['action'] ?? '');

try {
    $db = getDB();

    if ($action === 'update_settings') {
        $allowed = ['whatsapp_number','admin_email','bkash_number','bkash_type',
                    'bank_name','bank_account','bank_holder','exchange_api_key',
                    'site_name','site_url'];
        $updated = 0;
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $db->prepare("UPDATE site_settings SET setting_val = :v WHERE setting_key = :k")
                   ->execute([':v' => $val, ':k' => $key]);
                $updated++;
            }
        }
        jsonResponse(true, "$updated settings updated successfully.");
    }

    jsonResponse(false, 'Unknown action.');
} catch (Exception $e) {
    error_log('Settings error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to update settings.');
}
