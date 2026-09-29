<?php
// FILE PATH: /data/seed/transport-routes.php
// Run this ONCE, right after data/table-sql/transport-routes.sql,
// and AFTER data/seed/vehicle-types.php (vehicle names below are
// resolved to vehicle_types.sys_id here — vehicle types must exist
// first). Uses generateSysIdAndUuid() so sys_id/uuid come from the
// real generator, same pattern as every other table in this project.
//
// Route + price data below is carried over from the old v1
// transport_routes/transport_prices seed data.

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__) . '/server/db_connection.php';
require_once dirname(__DIR__) . '/server/uuid_generator.php';

function slugify(string $origin, array $stops): string {
    $parts = array_merge([$origin], $stops);
    $parts = array_filter(array_map(fn($p) => preg_replace('/[^A-Za-z0-9]+/', '', $p), $parts));
    return implode('-', $parts);
}

$routes = [
    ['origin' => 'Jeddah Airport', 'destinations' => ['Makkah'],
        'prices' => ['Car' => 250, 'HiAce' => 450, 'Coaster' => 700, 'Bus' => 1200, 'H1' => 350, 'GMC/JMC' => 500, 'Staria' => 600, 'Train' => 120]],
    ['origin' => 'Jeddah Airport', 'destinations' => ['Madinah'],
        'prices' => ['Car' => 400, 'HiAce' => 700, 'Coaster' => 1100, 'Bus' => 2000, 'H1' => 550, 'GMC/JMC' => 800, 'Staria' => 950, 'Train' => 200]],
    ['origin' => 'Makkah', 'destinations' => ['Madinah'],
        'prices' => ['Car' => 350, 'HiAce' => 600, 'Coaster' => 950, 'Bus' => 1700, 'H1' => 480, 'GMC/JMC' => 700, 'Staria' => 820, 'Train' => 160]],
    ['origin' => 'Madinah', 'destinations' => ['Jeddah Airport'],
        'prices' => ['Car' => 400, 'HiAce' => 700, 'Coaster' => 1100, 'Bus' => 2000, 'H1' => 550, 'GMC/JMC' => 800, 'Staria' => 950, 'Train' => 200]],
    ['origin' => 'Makkah', 'destinations' => ['Jeddah Airport'],
        'prices' => ['Car' => 250, 'HiAce' => 450, 'Coaster' => 700, 'Bus' => 1200, 'H1' => 350, 'GMC/JMC' => 500, 'Staria' => 600, 'Train' => 120]],
    ['origin' => 'Makkah', 'destinations' => ['Makkah (Ziyarah)'],
        'prices' => ['Car' => 300, 'HiAce' => 500, 'Coaster' => 800, 'Bus' => 1400, 'H1' => 420, 'GMC/JMC' => 600, 'Staria' => 700]],
    ['origin' => 'Madinah', 'destinations' => ['Madinah (Ziyarah)'],
        'prices' => ['Car' => 280, 'HiAce' => 480, 'Coaster' => 750, 'Bus' => 1300, 'H1' => 400, 'GMC/JMC' => 570, 'Staria' => 670]],
];

try {
    $db = getDB();

    // Look up every vehicle type's sys_id once, by name.
    $vehicleSysIds = [];
    foreach ($db->query("SELECT sys_id, name FROM vehicle_types") as $v) {
        $vehicleSysIds[$v['name']] = $v['sys_id'];
    }

    foreach ($routes as $route) {
        $slug = slugify($route['origin'], $route['destinations']);

        $exists = $db->prepare("SELECT id FROM transport_routes WHERE slug = ?");
        $exists->execute([$slug]);
        if ($exists->fetch()) {
            echo "Route '{$slug}' already exists — skipped.\n";
            continue;
        }

        $vehicleOptions = [];
        foreach ($route['prices'] as $vehicleName => $price) {
            if (!isset($vehicleSysIds[$vehicleName])) {
                echo "  Warning: vehicle type '{$vehicleName}' not found — skipping that price for '{$slug}'.\n";
                continue;
            }
            $vehicleOptions[] = ['vehicle_type_sys_id' => $vehicleSysIds[$vehicleName], 'price' => $price];
        }

        $ids = generateSysIdAndUuid($db, 'transport_routes');
        $now = date('Y-m-d H:i:s');
        $metadata = json_encode(['created_at' => $now, 'created_by' => 'system', 'updated_at' => $now, 'updated_by' => 'system']);

        $stmt = $db->prepare("INSERT INTO transport_routes (sys_id, uuid, slug, origin, destinations, vehicle_options, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ids['sys_id'],
            $ids['uuid'],
            $slug,
            $route['origin'],
            json_encode($route['destinations'], JSON_UNESCAPED_UNICODE),
            json_encode($vehicleOptions, JSON_UNESCAPED_UNICODE),
            $metadata,
        ]);

        echo "Route '{$slug}' created — sys_id: {$ids['sys_id']}\n";
    }
} catch (Exception $e) {
    die("Failed: " . $e->getMessage() . "\n");
}