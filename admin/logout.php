<?php
require_once dirname(__DIR__) . '/includes/config.php';
session_unset();
session_destroy();
header('Location: ' . BASE_URL . '/admin/index.php');
exit;
