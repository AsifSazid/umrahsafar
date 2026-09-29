<?php
// FILE PATH: /api/password-validation.php
// Used by the register form to validate a password as the user types.
// Rule: minimum 6 characters — no uppercase/number/special-character
// requirement (confirmed).
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

$password = $_POST['password'] ?? $_GET['password'] ?? '';

if (strlen($password) < 6) {
    jsonResponse(false, 'Password must be at least 6 characters.', ['valid' => false]);
}

jsonResponse(true, 'Password is valid.', ['valid' => true]);