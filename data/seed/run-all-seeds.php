<?php
// FILE PATH: /data/seed/run-all-seeds.php
//
// Master runner — executes every seed script in the correct dependency
// order in ONE command, instead of running each data/seed/*.php file
// by hand.
//
// Order (per role-user-system-setup.pdf + each script's own header
// comment):
//   1. system-roles.php      → seeds the 4 default roles (must exist
//                               before super-admin.php)
//   2. super-admin.php       → seeds the first Super Admin user
//                               (needs the 'super-admin' role from #1)
//   3. service-levels.php    → independent catalog
//   4. visa-types.php        → independent catalog
//   5. flights.php           → independent catalog
//   6. vehicle-types.php     → independent catalog (needed by #7)
//   7. transport-routes.php  → needs vehicle_types.sys_id from #6
//   8. meals.php             → independent catalog
//   9. moyallem-services.php → independent catalog
//  10. hotels.php            → FIRST in the hotel chain (needed by #11)
//  11. room-types.php        → needs hotels.sys_id from #10
//  12. room-board-types.php  → global board-type catalog (needed by #13)
//  13. room-prices.php       → needs room_types (#11) AND
//                               room_board_types (#12) to already exist
//  14. ziarah.php            → independent catalog
//
// Each script is run as its own PHP CLI process (via `include` in a
// fresh scope is unsafe here — several scripts reuse the same
// variable names like $db, $ids, $now, so they are kept isolated by
// shelling out to `php` for each one, exactly as if you ran them by
// hand one after another).
//
// Usage:
//   php data/seed/run-all-seeds.php
//
// Every individual script already skips rows that already exist, so
// this whole runner is safe to re-run.

$seedDir = __DIR__;

// PHP_BINARY can point to php-fpm instead of the CLI binary on some
// hosts (cPanel/aaPanel setups running PHP-FPM), which breaks any
// attempt to shell out to it. Try a list of common CLI paths/aliases
// and use the first one that actually reports as "Zend Engine" CLI.
function findPhpCli(): string {
    $candidates = [];

    // PHP_BINARY itself, only if it's not obviously an fpm binary
    if (defined('PHP_BINARY') && stripos(PHP_BINARY, 'fpm') === false) {
        $candidates[] = PHP_BINARY;
    }

    // Try to find a CLI binary next to the fpm one, e.g.
    // /www/server/php/83/bin/php next to /www/server/php/83/sbin/php-fpm
    if (defined('PHP_BINARY')) {
        $dir = dirname(PHP_BINARY);
        $candidates[] = $dir . '/php';
        $candidates[] = dirname($dir) . '/bin/php';
    }

    $candidates[] = 'php'; // rely on PATH
    $candidates[] = '/usr/bin/php';
    $candidates[] = '/usr/local/bin/php';

    foreach ($candidates as $bin) {
        $out = [];
        $code = 0;
        @exec(escapeshellarg($bin) . ' -v 2>&1', $out, $code);
        $joined = implode("\n", $out);
        if ($code === 0 && stripos($joined, 'PHP') !== false && stripos($joined, 'fpm') === false) {
            return $bin;
        }
    }

    // Give up and just return 'php' — will fail loudly below if wrong
    return 'php';
}

$phpBin = findPhpCli();
echo "Using PHP CLI binary: {$phpBin}\n";

$sequence = [
    'system-roles.php',
    'super-admin.php',
    'service-levels.php',
    'visa-types.php',
    'flights.php',
    'vehicle-types.php',
    'transport-routes.php',
    'meals.php',
    'moyallem-services.php',
    'hotels.php',
    'room-types.php',
    'room-board-types.php',
    'room-prices.php',
    'ziarah.php',
];

$failed = [];

foreach ($sequence as $i => $file) {
    $path = $seedDir . DIRECTORY_SEPARATOR . $file;
    $step = $i + 1;
    $total = count($sequence);

    echo "\n========================================\n";
    echo "[{$step}/{$total}] Running {$file}\n";
    echo "========================================\n";

    if (!file_exists($path)) {
        echo "SKIPPED — file not found: {$path}\n";
        $failed[] = $file;
        continue;
    }

    $cmd = escapeshellarg($phpBin) . ' ' . escapeshellarg($path) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);
    echo implode("\n", $output) . "\n";

    if ($exitCode !== 0) {
        echo "\n✗ {$file} exited with code {$exitCode} — stopping here.\n";
        $failed[] = $file;
        break; // stop the chain — later steps likely depend on this one
    }
}

echo "\n========================================\n";
if (empty($failed)) {
    echo "✓ All seed scripts completed.\n";
} else {
    echo "✗ Stopped due to a problem with: " . implode(', ', $failed) . "\n";
    echo "Fix the issue above, then re-run this script — completed\n";
    echo "steps are safe to repeat (each script skips existing rows).\n";
}
echo "========================================\n";