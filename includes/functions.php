<?php
// FILE PATH: /includes/functions.php

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatCurrency(float $amount, string $currency): string {
    $symbols = ['SAR' => 'SR ', 'USD' => '$', 'BDT' => '৳'];
    $symbol = $symbols[$currency] ?? '';
    if ($currency === 'BDT') {
        $n = (int)$amount;
        $s = (string)$n;
        if (strlen($s) <= 3) return $symbol . $s;
        $last3 = substr($s, -3);
        $rest  = substr($s, 0, -3);
        $rest  = preg_replace('/(\d+?)(?=(\d{2})+(?!\d))/', '$1,', $rest);
        return $symbol . $rest . ',' . $last3;
    }
    return $symbol . number_format($amount, 0, '.', ',');
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
    if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > SESSION_TIMEOUT) {
        session_unset(); session_destroy();
        header('Location: ' . BASE_URL . '/admin/index.php?timeout=1');
        exit;
    }
    $_SESSION['admin_last_activity'] = time();
}

function isUserLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function rateLimit(string $key, int $maxAttempts = 5, int $window = 3600): bool {
    $cacheFile = sys_get_temp_dir() . '/rl_' . md5($key) . '.json';
    $data = ['attempts' => 0, 'window_start' => time()];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ((time() - $data['window_start']) > $window) {
            $data = ['attempts' => 0, 'window_start' => time()];
        }
    }
    if ($data['attempts'] >= $maxAttempts) return false;
    $data['attempts']++;
    file_put_contents($cacheFile, json_encode($data));
    return true;
}

// Companion to rateLimit() — call with the SAME $key/$window right after
// rateLimit() returns false, to tell the caller how many seconds are left
// before the window resets. Kept separate so rateLimit()'s existing
// callers (booking/contact/email/register/login) are untouched.
function rateLimitRemainingSeconds(string $key, int $window): int {
    $cacheFile = sys_get_temp_dir() . '/rl_' . md5($key) . '.json';
    if (!file_exists($cacheFile)) return 0;
    $data = json_decode(file_get_contents($cacheFile), true);
    $elapsed = time() - ($data['window_start'] ?? time());
    return max(0, $window - $elapsed);
}

function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function getExchangeRates(): array {
    $fallback = ['SAR' => 1, 'USD' => 0.27, 'BDT' => 32.5, 'last_updated' => date('Y-m-d H:i:s')];
    if (defined('EXCHANGE_CACHE_FILE') && file_exists(EXCHANGE_CACHE_FILE)) {
        $cached = json_decode(file_get_contents(EXCHANGE_CACHE_FILE), true);
        if ($cached && (time() - strtotime($cached['last_updated'])) < EXCHANGE_CACHE_DURATION) {
            return $cached;
        }
    }
    return $fallback;
}

// ── Dynamic Site Settings ────────────────────────────────────
function getSetting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            require_once BASE_PATH . '/data/server/db_connection.php';
            $db = getDB();
            $rows = $db->query("SELECT setting_key, setting_val FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $cache[$r['setting_key']] = $r['setting_val'];
        } catch (Exception $e) {
            // DB not ready yet — return defaults silently
        }
    }
    return $cache[$key] ?? $default;
}

function getAllSettings(): array {
    try {
        require_once BASE_PATH . '/data/server/db_connection.php';
        $db = getDB();
        $rows = $db->query("SELECT * FROM site_settings ORDER BY group_name, id")->fetchAll(PDO::FETCH_ASSOC);
        $grouped = [];
        foreach ($rows as $r) $grouped[$r['group_name']][] = $r;
        return $grouped;
    } catch (Exception $e) {
        return [];
    }
}

// ── Booking status → badge color group ───────────────────────
// Shared across booking-preview.php, user-dashboard.php, and
// admin/dashboard.php so all three group the 16-value status lifecycle
// the same way: rejections/cancellations red, confirmations/completion
// green, everything in between (submitted, contacted, any "...
// Processing" stage, on hold, payment pending) yellow.
function bookingStatusBadgeClass(string $status): string {
    if (str_contains($status, 'Rejected') || str_contains($status, 'Cancelled')) {
        return 'bg-red-500/10 text-red-400 border border-red-500/20';
    }
    if (str_contains($status, 'Confirmed') || str_contains($status, 'Completed')
        || str_contains($status, 'Approved') || str_contains($status, 'Received')) {
        return 'bg-secondary/10 text-secondary border border-secondary/20';
    }
    return 'bg-yellow-400/10 text-yellow-400 border border-yellow-400/20';
}

// Solid-color dot version of the same logic — used next to a <select> where
// applying the badge classes directly to the <select> made its native
// dropdown-options render with poor contrast (yellow text on a near-white
// browser-native background).
function bookingStatusDotClass(string $status): string {
    if (str_contains($status, 'Rejected') || str_contains($status, 'Cancelled')) return 'bg-red-400';
    if (str_contains($status, 'Confirmed') || str_contains($status, 'Completed')
        || str_contains($status, 'Approved') || str_contains($status, 'Received')) return 'bg-secondary';
    return 'bg-yellow-400';
}

// ── Admin sidebar / page access permissions — single source of truth ──
// Used by BOTH includes/sidebar.php (which menu links show) and every
// admin/*.php page (whether the page's own content renders or gets
// replaced with a "You are {role}!" block) — one array drives both, so
// they can never drift out of sync. Add a new role or change what an
// existing role can reach here only; nothing else needs editing.
function getSidebarPermissions(): array {
    return [
        'super-admin' => null, // null = every tab allowed
        'admin'       => ['dashboard','packages','service-levels','visa-types','flights','hotels','transport','vehicle-types','room-board-types','ziarah','moyallem','meals','users','change-password'],
        'guest'       => ['change-password'],
    ];
}

// $tab is the bare filename without .php, e.g. 'dashboard', 'settings'.
function canAccessTab(string $tab): bool {
    $permissions = getSidebarPermissions();
    $role = $_SESSION['role_alias'] ?? null;
    if (!array_key_exists($role, $permissions)) return true; // unknown/legacy session — fail open, same as before this change
    $allowed = $permissions[$role];
    return $allowed === null || in_array($tab, $allowed, true);
}