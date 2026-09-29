<?php
// FILE PATH: /admin/migrate-transport.php
// One-time migration: transport_routes + transport_prices  ->  transport_routes_v2
// Old tables are left untouched (nothing is dropped/deleted).
// Safe to re-run: skips routes whose slug already exists in transport_routes_v2.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';
requireAdmin();

function slugify(string $from, array $stops): string {
    $parts = array_merge([$from], $stops);
    $parts = array_map(fn($p) => preg_replace('/[^A-Za-z0-9]+/', '', $p), $parts);
    return implode('-', $parts);
}

$log = [];
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_migration') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $log[] = ['type' => 'error', 'msg' => 'Invalid CSRF token.'];
    } else {
        try {
            $oldRoutes = $db->query("SELECT * FROM transport_routes ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
            $insStmt = $db->prepare("INSERT INTO transport_routes_v2
                (uuid, sys_id, slug, from_loc, to_json, vehicle_info, is_active, sort_order)
                VALUES (?,?,?,?,?,?,?,?)");
            $checkStmt = $db->prepare("SELECT id FROM transport_routes_v2 WHERE slug = ?");

            foreach ($oldRoutes as $r) {
                $slug = slugify($r['from_loc'], [$r['to_loc']]);
                $checkStmt->execute([$slug]);
                if ($checkStmt->fetch()) {
                    $log[] = ['type' => 'skip', 'msg' => "Skipped '{$r['route_name']}' — slug '{$slug}' already migrated."];
                    continue;
                }

                $priceStmt = $db->prepare("SELECT vehicle_type, price_sar FROM transport_prices WHERE route_id = ?");
                $priceStmt->execute([$r['id']]);
                $vehicleInfo = [];
                foreach ($priceStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                    if ((float)$p['price_sar'] > 0) {
                        $vehicleInfo[] = ['vehicle_type' => $p['vehicle_type'], 'price_sar' => (float)$p['price_sar']];
                    }
                }

                $uuid   = generateUUID();
                $sysId  = 'RT-' . strtoupper(substr(md5($slug . $uuid), 0, 6));
                $toJson = json_encode([$r['to_loc']], JSON_UNESCAPED_UNICODE);
                $vJson  = json_encode($vehicleInfo, JSON_UNESCAPED_UNICODE);

                $insStmt->execute([
                    $uuid, $sysId, $slug, $r['from_loc'], $toJson, $vJson,
                    (int)$r['is_active'], (int)$r['sort_order']
                ]);
                $log[] = ['type' => 'ok', 'msg' => "Migrated '{$r['route_name']}' -> slug '{$slug}' (" . count($vehicleInfo) . " vehicle prices)."];
            }
            if (!$oldRoutes) $log[] = ['type' => 'skip', 'msg' => 'No routes found in the old transport_routes table.'];
        } catch (Exception $e) {
            $log[] = ['type' => 'error', 'msg' => 'Migration failed: ' . $e->getMessage()];
        }
    }
}

$csrf = csrfToken();
$existingCount = (int)$db->query("SELECT COUNT(*) FROM transport_routes_v2")->fetchColumn();
$oldCount = (int)$db->query("SELECT COUNT(*) FROM transport_routes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Migrate Transport | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625'}}}}</script>
<style>body{background:#111625}</style>
</head>
<body class="text-white font-sans min-h-screen p-8">
<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-bold mb-2">Transport Data Migration</h1>
  <p class="text-white/40 text-sm mb-6">Copies routes from the old <code>transport_routes</code>/<code>transport_prices</code> tables into the new multi-stop <code>transport_routes_v2</code> format. Nothing is deleted — safe to re-run.</p>

  <div class="bg-white/5 border border-white/10 rounded-xl p-5 mb-6 text-sm space-y-1">
    <p>Old table (<code>transport_routes</code>): <span class="font-bold text-secondary"><?= $oldCount ?></span> routes</p>
    <p>New table (<code>transport_routes_v2</code>): <span class="font-bold text-secondary"><?= $existingCount ?></span> routes</p>
  </div>

  <?php if ($log): ?>
  <div class="bg-white/5 border border-white/10 rounded-xl p-5 mb-6 text-sm space-y-2 max-h-96 overflow-auto">
    <?php foreach ($log as $l): ?>
      <p class="<?= $l['type']==='ok'?'text-secondary':($l['type']==='error'?'text-red-400':'text-white/40') ?>">
        <?= $l['type']==='ok'?'✓':($l['type']==='error'?'✗':'—') ?> <?= htmlspecialchars($l['msg']) ?>
      </p>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="action" value="run_migration">
    <button type="submit" class="bg-secondary hover:bg-emerald text-primary font-bold px-6 py-3 rounded-xl">Run Migration</button>
    <a href="<?= BASE_URL ?>/admin/transport.php" class="ml-3 text-white/40 hover:text-white text-sm">Go to new Transport admin →</a>
  </form>
</div>
</body></html>
