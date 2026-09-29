<?php
// FILE PATH: /data/server/username_generator.php
// Generates lowercase, sequential usernames:
//   usp{e|c}{YY}{SERIAL}
//   usp = Umrah Safar Portal (fixed literal)
//   e/c = 'e' for employee/office/backend users, 'c' for clients
//   YY  = 2-digit year
//   SERIAL = decimal counter, 1-999999, min 5-digit zero-padded
//            (00001, 00002, ... 99999, 100000, ... 999999)
// Counter is scoped per (type, year) — employee and client numbering
// are independent, and both reset every year.
//
// Examples:
//   generateUsername($pdo, 'super-admin') → "uspe2600001"
//   generateUsername($pdo, 'admin')       → "uspe2600002"
//   generateUsername($pdo, 'client')      → "uspc2600001"

/**
 * Any role except 'client' is treated as an employee/office/backend
 * user ('e') — matches the same boundary admin/index.php already uses
 * (role_alias != 'client' can log in there), so this doesn't duplicate
 * a second, possibly-inconsistent definition of "who counts as staff".
 */
function _usernameTypeForRole(string $roleAlias): string {
    return $roleAlias === 'client' ? 'c' : 'e';
}

/**
 * Generate the next sequential username for the given role. Always
 * lowercase. $roleAlias decides the 'e'/'c' segment — pass whatever
 * role the account is being created with (e.g. from system_roles).
 */
function generateUsername(PDO $pdo, string $roleAlias): string {
    $type = _usernameTypeForRole($roleAlias);
    $yy   = date('y');

    // Atomic increment in one statement — no separate read-then-write
    // race between two requests generating a username for the same
    // type/year at once (InnoDB serializes this UPDATE per row).
    $pdo->prepare("
        INSERT INTO username_counters (type, year, counter)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE counter = counter + 1
    ")->execute([$type, $yy]);

    $row = $pdo->prepare("SELECT counter FROM username_counters WHERE type = ? AND year = ?");
    $row->execute([$type, $yy]);
    $counter = (int) $row->fetchColumn();

    if ($counter > 999999) {
        throw new Exception("generateUsername: counter exhausted for type '{$type}' year {$yy} (max 999999).");
    }

    // 5-digit minimum, grows to 6 digits naturally past 99999 — str_pad
    // never truncates, it only pads shorter numbers up to width 5.
    $serial = str_pad((string) $counter, 5, '0', STR_PAD_LEFT);

    return strtolower("usp{$type}{$yy}{$serial}");
}