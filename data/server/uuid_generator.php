<?php
// Kept for anywhere a pure-random UUID (not tied to a sys_id) is still
// wanted — generateSysIdAndUuid() below is the one used for every table
// with a sys_id/uuid pair now.
function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// ── sys_id — short, human-readable, sequential, table-prefixed IDs ──
// Format: US{MONTH}-{YY}-{SHORT}-{SERIAL}, e.g. "US9-26-USR-00001".
// "US" = Umrah Safar, fixed literal on every sys_id.
// MONTH is 1-9 for Jan-Sep, then A/B/C for Oct/Nov/Dec. SERIAL is a
// 5-digit base-36 counter (0-9 then A-Z) that resets every month (and
// therefore every year) — one place to register every table's
// 3-character short code; add a new table here once, then call
// generateSysIdAndUuid($pdo, 'table_name') anywhere that table's rows
// are created.
function _sysIdRegistry(): array {
    return [
        'hotels'               => 'HTL',
        'room_types'           => 'RMT',
        'room_board_types'      => 'RBT',
        'room_prices'           => 'RMP',
        'meals'                => 'MLL',
        'moyallem_services'     => 'MYL',
        'transport_routes'      => 'TRS',
        'vehicle_types'         => 'VHT',
        'ziarah'                => 'ZIR',
        'flights'               => 'FLT',
        'visa_types'            => 'VST',
        'service_levels'        => 'SVC',
        'custom_builds'         => 'CBS',
        'bookings'              => 'BKS',
        'packages'              => 'PKG',
        'system_roles'          => 'SYR',
        'users'                 => 'USR',
    ];
}

// Jan-Sep → '1'-'9', Oct/Nov/Dec → 'A'/'B'/'C'
function _sysIdMonthCode(): string {
    $m = (int) date('n');
    return $m <= 9 ? (string) $m : chr(ord('A') + ($m - 10));
}

function _sysIdBase36(int $n, int $width = 5): string {
    return str_pad(strtoupper(base_convert((string) $n, 10, 36)), $width, '0', STR_PAD_LEFT);
}
function _sysIdBase36Max(int $width = 5): int {
    return (int) base_convert(str_repeat('Z', $width), 36, 10); // 5 digits → 60,466,175
}

/**
 * Generate the next sys_id AND its derived uuid for $table, sharing a
 * single counter increment — e.g. for 'users' in Sep 2026:
 *   sys_id → "US9-26-USR-00001"
 *   uuid   → "xxxxxusp-xxxx-9usr-xxxx-26xxxxx00001"
 * (uuid embeds the fixed literal "usp" in the first group, plus the
 * same month/table-short-code/year/serial as the sys_id elsewhere —
 * deliberately NOT a standard/opaque UUID; see the note in the
 * conversation this was requested in. Random hex fragments still fill
 * every position not fixed by the sys_id.)
 * Returns ['sys_id' => ..., 'uuid' => ...].
 */
function generateSysIdAndUuid(PDO $pdo, string $table): array {
    $registry = _sysIdRegistry();
    $short = $registry[$table] ?? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $table), 0, 3)) ?: 'GEN';
    $short = str_pad(substr($short, 0, 3), 3, 'X'); // guarantee exactly 3 chars
    $yy    = date('y');
    $month = _sysIdMonthCode();

    // Atomic increment in one statement — no separate read-then-write
    // race between two requests generating a sys_id for the same
    // table/year/month at once (InnoDB serializes this UPDATE per row).
    $pdo->prepare("
        INSERT INTO sys_id_counters (tag, year, month, counter)
        VALUES (?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE counter = counter + 1
    ")->execute([$table, $yy, $month]);

    $row = $pdo->prepare("SELECT counter FROM sys_id_counters WHERE tag = ? AND year = ? AND month = ?");
    $row->execute([$table, $yy, $month]);
    $counter = (int) $row->fetchColumn();

    if ($counter > _sysIdBase36Max()) {
        // ~60.4 million sys_ids for one table in one month — treated as
        // a hard stop rather than an auto-rollover, since month already
        // resets the counter naturally; this should never happen in
        // practice at this project's scale.
        throw new Exception("generateSysIdAndUuid: '{$table}' exhausted its counter for {$month}/{$yy}.");
    }

    $serial = _sysIdBase36($counter, 5);
    $sysId  = "US{$month}-{$yy}-{$short}-{$serial}";

    $shortLower = strtolower($short);
    $hex = fn(int $n) => bin2hex(random_bytes($n)); // n bytes → 2n lowercase hex chars

    $uuid = sprintf(
        '%s%s-%s-%s%s-%s-%s%s%s',
        substr($hex(3), 0, 5), 'usp',                     // 8 chars: 5 random + fixed literal "usp"
        $hex(2),                                          // 4 chars: random
        strtolower($month), $shortLower,                  // 4 chars: month + table short-code
        $hex(2),                                          // 4 chars: random
        $yy, substr($hex(3), 0, 5), strtolower($serial)   // 12 chars: YY + 5 random + 5 serial (lowercase here)
    );

    return ['sys_id' => $sysId, 'uuid' => $uuid];
}