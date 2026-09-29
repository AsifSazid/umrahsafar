<?php
// FILE PATH: /admin/migrate-moyallem.php
// One-time migration: moyallem_services (flat price_sar)  ->  moyallem_services_v2 (General/Expert/VIP category_price)
// Old table is left untouched (nothing is dropped/deleted).
// The old flat price becomes the "General" category price; Expert/VIP
// start at 0 SAR for the admin to fill in manually afterward.
// Safe to re-run: skips services whose sys_id already exists in moyallem_services_v2.
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
require_once dirname(__DIR__) . '/data/server/uuid_generator.php';
requireAdmin();

$log = [];
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_migration') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $log[] = ['type' => 'error', 'msg' => 'Invalid CSRF token.'];
    } else {
        try {
            $oldServices = $db->query("SELECT * FROM moyallem_services ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
            $insStmt = $db->prepare("INSERT INTO moyallem_services_v2
                (uuid, sys_id, name, description, icon, category_price, is_active, sort_order)
                VALUES (?,?,?,?,?,?,?,?)");
            $checkStmt = $db->prepare("SELECT id FROM moyallem_services_v2 WHERE name = ?");

            foreach ($oldServices as $svc) {
                $checkStmt->execute([$svc['name']]);
                if ($checkStmt->fetch()) {
                    $log[] = ['type' => 'skip', 'msg' => "Skipped '{$svc['name']}' — a service with this name already exists in the new table."];
                    continue;
                }

                $uuid  = generateUUID();
                $sysId = 'MY-' . strtoupper(substr(md5($svc['name'] . $uuid), 0, 6));
                $categoryPrice = [
                    ['category' => 'General', 'price_sar' => (float)$svc['price_sar']],
                    ['category' => 'Expert',  'price_sar' => 0],
                    ['category' => 'VIP',     'price_sar' => 0],
                ];
                $cpJson = json_encode($categoryPrice, JSON_UNESCAPED_UNICODE);

                $insStmt->execute([
                    $uuid, $sysId, $svc['name'], $svc['description'], $svc['icon'] ?: 'star',
                    $cpJson, (int)$svc['is_active'], (int)$svc['sort_order']
                ]);
                $log[] = ['type' => 'ok', 'msg' => "Migrated '{$svc['name']}' — old price SR " . number_format((float)$svc['price_sar'],0) . " set as 'General'. Set Expert/VIP prices manually."];
            }
            if (!$oldServices) $log[] = ['type' => 'skip', 'msg' => 'No services found in the old moyallem_services table.'];
        } catch (Exception $e) {
            $log[] = ['type' => 'error', 'msg' => 'Migration failed: ' . $e->getMessage()];
        }
    }
}

$csrf = csrfToken();
$existingCount = (int)$db->query("SELECT COUNT(*) FROM moyallem_services_v2")->fetchColumn();
$oldCount = (int)$db->query("SELECT COUNT(*) FROM moyallem_services")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Migrate Moyallem | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625'}}}}</script>
<style>body{background:#111625}</style>
</head>
<body class="text-white font-sans min-h-screen p-8">
<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-bold mb-2">Moyallem Data Migration</h1>
  <p class="text-white/40 text-sm mb-6">Copies services from the old flat-price <code>moyallem_services</code> table into the new category-priced <code>moyallem_services_v2</code> format (General/Expert/VIP). The old price becomes the "General" price — Expert and VIP start at 0 SAR and need to be set manually afterward. Nothing is deleted — safe to re-run.</p>

  <div class="bg-white/5 border border-white/10 rounded-xl p-5 mb-6 text-sm space-y-1">
    <p>Old table (<code>moyallem_services</code>): <span class="font-bold text-secondary"><?= $oldCount ?></span> services</p>
    <p>New table (<code>moyallem_services_v2</code>): <span class="font-bold text-secondary"><?= $existingCount ?></span> services</p>
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
    <a href="<?= BASE_URL ?>/admin/moyallem.php" class="ml-3 text-white/40 hover:text-white text-sm">Go to new Moyallem admin →</a>
  </form>
</div>
</body></html>