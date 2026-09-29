<?php
// FILE PATH: /admin/hotel-create.php
// Create OR edit a hotel, together with all its room types, on one page.
// Edit mode: ?hotel=<uuid>
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/data/server/db_connection.php';
requireAdmin();
$csrf = csrfToken();

// Formats a room type's {"unit":"sqr-m","value":300} size into a readable
// label like "300 m²".
function formatRoomSize(?array $size): string {
    if (!$size || !isset($size['value'])) return '—';
    $labels = ['sqr-m' => 'm²', 'sqr-cm' => 'cm²', 'sqr-ft' => 'ft²', 'sqr-in' => 'in²'];
    $unit = $labels[$size['unit'] ?? ''] ?? ($size['unit'] ?? '');
    return $size['value'] . ' ' . $unit;
}

$hotelUuid = trim($_GET['hotel'] ?? '');
$isEdit = $hotelUuid !== '';
$hotel = null;
$roomTypes = [];

try {
    $db = getDB();
    if ($isEdit) {
        $stmt = $db->prepare("SELECT * FROM hotels WHERE uuid = ?");
        $stmt->execute([$hotelUuid]);
        $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hotel) { header('Location: ' . BASE_URL . '/admin/hotels.php'); exit; }
        foreach (['checkin_schedule','distance_info','age_ranges','image_urls','images','videos','youtube_urls'] as $f) {
            $hotel[$f] = json_decode($hotel[$f] ?? 'null', true);
        }

        $rtStmt = $db->prepare("SELECT * FROM room_types WHERE hotel_sys_id = ? ORDER BY sort_order, id");
        $rtStmt->execute([$hotel['sys_id']]);
        $roomTypes = $rtStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($roomTypes as &$rt) {
            foreach (['person_capacity','bed_config','amenities','add_on','view_info','size','image_urls','images','videos','youtube_urls'] as $f) {
                $rt[$f] = json_decode($rt[$f] ?? 'null', true);
            }
        }
        unset($rt);
    }
} catch (Exception $e) {
    header('Location: ' . BASE_URL . '/admin/hotels.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $isEdit ? 'Edit' : 'New' ?> Hotel | TravHub Admin</title>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Poppins','sans-serif']},colors:{primary:'#1A2039',secondary:'#50BC81',navy:'#1E2648',dark:'#111625',emerald:'#3AAB71',teal:'#02CCFE'}}}}</script>
<style>
body{background:#111625}
.field{ width:100%; background:#111625; border:1px solid rgba(255,255,255,0.1); border-radius:0.75rem; padding:0.625rem 1rem; font-size:0.875rem; color:#fff; }
.field:focus{ outline:none; border-color:#50BC81; }
</style>
</head>
<body class="text-white font-sans min-h-screen">
<div class="flex min-h-screen">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="flex-1 p-4 lg:p-8 overflow-auto">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?= BASE_URL ?>/admin/hotels.php" class="text-white/40 hover:text-white"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
    <h1 class="text-2xl font-bold"><?= $isEdit ? 'Edit Hotel' : 'New Hotel' ?></h1>
  </div>
  <div id="toast" class="hidden fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm"></div>

  <!-- ══════════ HOTEL FORM ══════════ -->
  <form id="hotel-form" class="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4 mb-6">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="action" value="<?= $isEdit ? 'update_hotel' : 'create_hotel' ?>">
    <input type="hidden" name="sys_id" value="<?= $isEdit ? htmlspecialchars($hotel['sys_id']) : '' ?>">
    <input type="hidden" name="image_urls" value="[]">
    <input type="hidden" name="youtube_urls" value="[]">

    <h2 class="font-bold text-lg mb-1">Hotel Information</h2>

    <div class="grid grid-cols-3 gap-4">
      <div><label class="text-xs text-white/40 block mb-1.5">City</label>
        <select name="city" class="field">
          <?php foreach (['Makkah','Madinah','Jeddah'] as $c): ?>
          <option value="<?= $c ?>" <?= ($isEdit && $hotel['city']===$c)?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-span-2"><label class="text-xs text-white/40 block mb-1.5">Hotel Name</label>
        <input type="text" name="name" required class="field" value="<?= $isEdit ? htmlspecialchars($hotel['name']) : '' ?>"></div>
    </div>

    <div class="grid grid-cols-3 gap-4">
      <div><label class="text-xs text-white/40 block mb-1.5">Star Rating</label>
        <select name="star_rating" class="field">
          <?php for ($s=1;$s<=5;$s++): ?><option value="<?= $s ?>" <?= ($isEdit && (int)$hotel['star_rating']===$s)?'selected':'' ?>><?= $s ?> Star</option><?php endfor; ?>
        </select></div>
      <div><label class="text-xs text-white/40 block mb-1.5">Check-in Time</label>
        <input type="time" name="checkin_time" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['checkin_schedule']['in'] ?? '14:00') : '14:00' ?>"></div>
      <div><label class="text-xs text-white/40 block mb-1.5">Check-out Time</label>
        <input type="time" name="checkout_time" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['checkin_schedule']['out'] ?? '12:00') : '12:00' ?>"></div>
    </div>

    <div><label class="text-xs text-white/40 block mb-1.5">Description</label>
      <textarea name="description" rows="3" class="field resize-none"><?= $isEdit ? htmlspecialchars($hotel['description']) : '' ?></textarea></div>

    <div class="grid grid-cols-2 gap-4">
      <div><label class="text-xs text-white/40 block mb-1.5">Address</label><input type="text" name="address" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['address']) : '' ?>"></div>
      <div><label class="text-xs text-white/40 block mb-1.5">Email</label><input type="email" name="email" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['email']) : '' ?>"></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="text-xs text-white/40 block mb-1.5">Phone</label><input type="text" name="phone" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['phone']) : '' ?>"></div>
      <div><label class="text-xs text-white/40 block mb-1.5">Sort Order</label><input type="number" name="sort_order" class="field" value="<?= $isEdit ? (int)$hotel['sort_order'] : 0 ?>"></div>
    </div>

    <!-- Distance -->
    <div class="border-t border-white/10 pt-4">
      <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Distance</p>
      <div class="grid grid-cols-4 gap-4 mb-3">
        <div id="landmark-wrap">
          <label class="text-xs text-white/40 block mb-1.5" id="landmark-label">Landmark</label>
          <input type="text" name="distance_landmark" id="distance-landmark" class="field" readonly value="<?= $isEdit ? htmlspecialchars($hotel['distance_info']['landmark'] ?? '') : 'Masjid al-Haram' ?>">
        </div>
        <div id="gate-no-wrap">
          <label class="text-xs text-white/40 block mb-1.5">Gate No.</label>
          <input type="text" name="distance_gate_no" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['distance_info']['gate_no'] ?? '') : '' ?>">
        </div>
        <div><label class="text-xs text-white/40 block mb-1.5">Distance Value</label>
          <input type="number" step="0.01" name="distance_value" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['distance_info']['value'] ?? 0) : 0 ?>"></div>
        <div><label class="text-xs text-white/40 block mb-1.5">Unit</label>
          <select name="distance_unit" class="field">
            <option value="m" <?= ($isEdit && ($hotel['distance_info']['unit'] ?? '')==='m')?'selected':'' ?>>Meters (m)</option>
            <option value="km" <?= ($isEdit && ($hotel['distance_info']['unit'] ?? '')==='km')?'selected':'' ?>>Kilometers (km)</option>
          </select></div>
      </div>
      <div class="w-1/2 grid grid-cols-2 gap-4">
        <div>
          <label class="flex items-center gap-2 text-sm cursor-pointer mb-1.5">
            <input type="checkbox" name="walking_enabled" value="1" id="walking-enabled" class="w-4 h-4 accent-secondary" <?= (!empty($hotel['distance_info']['walking']['enabled']))?'checked':'' ?>>
            Walking distance available
          </label>
        </div>
        <div id="walking-minutes-wrap" class="<?= (!empty($hotel['distance_info']['walking']['enabled']))?'':'hidden' ?>">
          <label class="text-xs text-white/40 block mb-1.5">Walking Minutes</label>
          <input type="number" min="1" name="walking_minutes" class="field" value="<?= $isEdit ? htmlspecialchars($hotel['distance_info']['walking']['minutes'] ?? 1) : 1 ?>">
        </div>
      </div>
    </div>

    <!-- Age ranges -->
    <div class="border-t border-white/10 pt-4">
      <p class="text-xs text-white/40 uppercase tracking-wider mb-3">Child / Infant Age Ranges</p>
      <div class="grid grid-cols-4 gap-3">
        <div><label class="text-[11px] text-white/30 block mb-1">Infant Min</label><input type="number" name="infant_age_min" id="infant-age-min" class="field" value="0" readonly></div>
        <div><label class="text-[11px] text-white/30 block mb-1">Infant Max</label><input type="number" min="0" name="infant_age_max" id="infant-age-max" class="field" value="<?= $isEdit ? (int)($hotel['age_ranges']['infant']['max']??1) : 1 ?>" oninput="syncChildMin()"></div>
        <div><label class="text-[11px] text-white/30 block mb-1">Child Min</label><input type="number" name="child_age_min" id="child-age-min" class="field" readonly value="<?= $isEdit ? (int)($hotel['age_ranges']['child']['min']??2) : 2 ?>"></div>
        <div><label class="text-[11px] text-white/30 block mb-1">Child Max</label><input type="number" min="0" name="child_age_max" class="field" value="<?= $isEdit ? (int)($hotel['age_ranges']['child']['max']??11) : 11 ?>"></div>
      </div>
    </div>

    <!-- External image/youtube URLs -->
    <div class="border-t border-white/10 pt-4">
      <p class="text-xs text-white/40 uppercase tracking-wider mb-2">External Image URLs (Facebook/Google etc.)</p>
      <div id="hotel-image-urls" class="space-y-2 mb-2"></div>
      <button type="button" onclick="addUrlRow('hotel-image-urls', 'image_urls')" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg">+ Image URL</button>
    </div>
    <div>
      <p class="text-xs text-white/40 uppercase tracking-wider mb-2 mt-3">YouTube Video URLs</p>
      <div id="hotel-youtube-urls" class="space-y-2 mb-2"></div>
      <button type="button" onclick="addUrlRow('hotel-youtube-urls', 'youtube_urls', '#hotel-form', 'youtube')" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg">+ YouTube URL</button>
    </div>

    <!-- Uploaded media — always visible. For a brand-new hotel (no sys_id
         yet), picked files are held locally and auto-uploaded right after
         the hotel is created (mirrors admin/vehicle-types.php's pattern). -->
    <div class="border-t border-white/10 pt-4">
      <p class="text-xs text-white/40 uppercase tracking-wider mb-2">Images (JPG/PNG, any number)</p>
      <div id="hotel-image-gallery" class="flex flex-wrap gap-2 mb-2"></div>
      <input type="file" id="hotel-image-input" accept="image/jpeg,image/png" multiple class="text-xs text-white/50 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-white/5 file:text-white/70 file:text-xs">
    </div>
    <div class="border-t border-white/10 pt-4">
      <p class="text-xs text-white/40 uppercase tracking-wider mb-2">Videos (MP4/WebM, any number)</p>
      <div id="hotel-video-gallery" class="flex flex-wrap gap-2 mb-2"></div>
      <input type="file" id="hotel-video-input" accept="video/mp4,video/webm" multiple class="text-xs text-white/50 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-white/5 file:text-white/70 file:text-xs">
    </div>
    <?php if (!$isEdit): ?><p class="text-[11px] text-white/25">Picked files upload automatically right after you create the hotel below.</p><?php endif; ?>

    <button type="submit" id="hotel-btn" class="bg-secondary hover:bg-emerald text-primary font-bold px-6 py-3 rounded-xl text-sm flex items-center gap-2">
      <i data-lucide="save" class="w-4 h-4"></i> <?= $isEdit ? 'Save Hotel Info' : 'Create Hotel' ?>
    </button>
  </form>

  <!-- ══════════ ROOM TYPES ══════════ -->
  <?php if ($isEdit): ?>
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg">Room Types</h2>
    <button onclick="openRoomModal()" class="bg-secondary hover:bg-emerald text-primary font-bold px-4 py-2.5 rounded-xl flex items-center gap-2 text-sm"><i data-lucide="plus" class="w-4 h-4"></i> New Room Type</button>
  </div>
  <div id="room-types-list" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
    <?php foreach ($roomTypes as $rt): ?>
    <div class="bg-white/5 border border-white/10 rounded-2xl p-4" data-sys-id="<?= htmlspecialchars($rt['sys_id']) ?>">
      <div class="flex items-start justify-between gap-2">
        <div>
          <span class="font-semibold text-sm"><?= htmlspecialchars($rt['name']) ?></span>
          <?php if(!$rt['is_active']): ?> <span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-0.5 rounded-full align-middle">Inactive</span><?php endif; ?>
          <p class="text-[11px] text-white/30 font-mono"><?= htmlspecialchars($rt['sys_id']) ?></p>
          <p class="text-xs text-white/40 mt-1"><?= (int)($rt['person_capacity']['adults']??0) ?> adults, <?= (int)($rt['person_capacity']['children']??0) ?> children · <?= htmlspecialchars(formatRoomSize($rt['size'])) ?></p>
        </div>
        <div class="flex gap-1.5 shrink-0">
          <button onclick='editRoomType(<?= json_encode($rt, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' class="px-2.5 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
          <button onclick="toggleRoomType('<?= htmlspecialchars($rt['sys_id'],ENT_QUOTES) ?>')" class="px-2.5 py-1.5 text-xs bg-white/5 hover:bg-white/10 rounded-lg" title="<?= $rt['is_active']?'Deactivate':'Activate' ?>"><i data-lucide="<?= $rt['is_active']?'eye-off':'eye' ?>" class="w-3.5 h-3.5"></i></button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$roomTypes): ?><p class="text-sm text-white/30 col-span-2">No room types yet — add one above.</p><?php endif; ?>
  </div>
  <?php else: ?>
  <p class="text-sm text-white/30 bg-white/5 border border-white/10 rounded-2xl p-6">Save the hotel first — room types can be added once the hotel is created.</p>
  <?php endif; ?>
</main>
</div>

<!-- ══════════ ROOM TYPE MODAL ══════════ -->
<div id="room-modal-overlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm items-start justify-center p-4 overflow-auto" style="display:none;">
  <div class="bg-navy border border-white/10 rounded-2xl w-full max-w-2xl my-6">
    <div class="flex items-center justify-between p-6 border-b border-white/10">
      <h2 class="font-bold text-lg" id="room-modal-title">New Room Type</h2>
      <button onclick="closeRoomModal()" class="text-white/40 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form id="room-form" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create_room_type">
      <input type="hidden" name="sys_id" value="">
      <input type="hidden" name="hotel_sys_id" value="<?= $isEdit ? htmlspecialchars($hotel['sys_id']) : '' ?>">
      <input type="hidden" name="bed_config" value="[]">
      <input type="hidden" name="amenities" value="[]">
      <input type="hidden" name="image_urls" value="[]">
      <input type="hidden" name="youtube_urls" value="[]">

      <div><label class="text-xs text-white/40 block mb-1.5">Name</label><input type="text" name="name" required class="field"></div>
      <div><label class="text-xs text-white/40 block mb-1.5">Description</label><textarea name="description" rows="2" class="field resize-none"></textarea></div>

      <div class="grid grid-cols-4 gap-3">
        <div><label class="text-xs text-white/40 block mb-1.5">Max Adults</label><input type="number" min="0" name="capacity_adults" value="2" class="field"></div>
        <div><label class="text-xs text-white/40 block mb-1.5" id="max-children-label">Max Children</label><input type="number" min="0" name="capacity_children" value="0" class="field"></div>
        <div><label class="text-xs text-white/40 block mb-1.5">Size</label><input type="number" step="0.01" min="0" name="size_value" value="0" class="field"></div>
        <div><label class="text-xs text-white/40 block mb-1.5">Unit</label>
          <select name="size_unit" class="field">
            <option value="sqr-m">m² (square meter)</option>
            <option value="sqr-cm">cm² (square centimeter)</option>
            <option value="sqr-ft">ft² (square foot)</option>
            <option value="sqr-in">in² (square inch)</option>
          </select>
        </div>
      </div>

      <!-- Bed config -->
      <div class="border-t border-white/10 pt-3">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-white/40 uppercase tracking-wider">Bed Configuration</p>
          <button type="button" onclick="addBedRow()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg flex items-center gap-1"><i data-lucide="plus" class="w-3 h-3"></i> Add Bed Type</button>
        </div>
        <table class="w-full text-sm border border-white/10 rounded-xl overflow-hidden">
          <thead>
            <tr class="bg-white/10 text-white/50 text-[11px] uppercase tracking-wider">
              <th class="text-left px-3 py-2">Type</th>
              <th class="text-left px-3 py-2 w-24">Quantity</th>
              <th class="text-left px-3 py-2 w-24">Capacity</th>
              <th class="text-left px-3 py-2 w-16">Action</th>
            </tr>
          </thead>
          <tbody id="bed-config-rows"></tbody>
        </table>
        <p class="text-[10px] text-white/25 mt-1">Total bed capacity shouldn't exceed Max Adults + Max Children above.</p>
      </div>

      <!-- Amenities -->
      <div class="border-t border-white/10 pt-3">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-white/40 uppercase tracking-wider">Amenities</p>
          <button type="button" onclick="addAmenityRow()" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg flex items-center gap-1"><i data-lucide="plus" class="w-3 h-3"></i> Amenity</button>
        </div>
        <div id="amenity-rows" class="space-y-2"></div>
      </div>

      <!-- View (Haram/Nawabi/generic, label depends on the hotel's city) -->
      <div class="border-t border-white/10 pt-3">
        <label class="flex items-center gap-2 text-sm cursor-pointer mb-2">
          <input type="checkbox" name="view_enabled" id="room-view-enabled" value="1" class="w-4 h-4 accent-secondary" onchange="document.getElementById('room-view-desc-wrap').classList.toggle('hidden', !this.checked)">
          <span id="room-view-label">View available</span>
        </label>
        <div id="room-view-desc-wrap" class="hidden">
          <label class="text-xs text-white/40 block mb-1.5">Describe the view</label>
          <textarea name="view_description" rows="2" class="field resize-none" placeholder="e.g. Full frontal view of Masjid al-Haram from a private balcony."></textarea>
        </div>
      </div>

      <!-- Add-on -->
      <div class="border-t border-white/10 pt-3">
        <label class="flex items-center gap-2 text-sm cursor-pointer mb-2">
          <input type="checkbox" name="add_on_bed" id="room-addon-bed" value="1" class="w-4 h-4 accent-secondary" onchange="document.getElementById('room-addon-charge-wrap').classList.toggle('hidden', !this.checked)">
          Extra bed add-on available
        </label>
        <div id="room-addon-charge-wrap" class="hidden">
          <label class="text-xs text-white/40 block mb-1.5">Extra Bed Charge (SAR)</label>
          <input type="number" step="0.01" name="add_on_charge" value="0" class="field w-40">
        </div>
      </div>

      <!-- External URLs -->
      <div class="border-t border-white/10 pt-3">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-2">External Image URLs</p>
        <div id="room-image-urls" class="space-y-2 mb-2"></div>
        <button type="button" onclick="addUrlRow('room-image-urls', 'image_urls', '#room-form')" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg">+ Image URL</button>
      </div>
      <div>
        <p class="text-xs text-white/40 uppercase tracking-wider mb-2 mt-3">YouTube Video URLs</p>
        <div id="room-youtube-urls" class="space-y-2 mb-2"></div>
        <button type="button" onclick="addUrlRow('room-youtube-urls', 'youtube_urls', '#room-form', 'youtube')" class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg">+ YouTube URL</button>
      </div>

      <!-- Uploaded media — always visible. New (unsaved) room type: files are
           held locally and auto-uploaded right after the room type is created. -->
      <div id="room-media-section" class="border-t border-white/10 pt-3">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-2">Images (JPG/PNG, any number)</p>
        <div id="room-image-gallery" class="flex flex-wrap gap-2 mb-2"></div>
        <input type="file" id="room-image-input" accept="image/jpeg,image/png" multiple class="text-xs text-white/50 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-white/5 file:text-white/70 file:text-xs">
        <p class="text-xs text-white/40 uppercase tracking-wider mb-2 mt-3">Videos (MP4/WebM, any number)</p>
        <div id="room-video-gallery" class="flex flex-wrap gap-2 mb-2"></div>
        <input type="file" id="room-video-input" accept="video/mp4,video/webm" multiple class="text-xs text-white/50 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-white/5 file:text-white/70 file:text-xs">
      </div>

      <div class="flex gap-3 pt-2">
        <button type="button" onclick="closeRoomModal()" class="flex-1 bg-white/5 hover:bg-white/10 font-bold py-3 rounded-xl text-sm">Cancel</button>
        <button type="submit" id="room-btn" class="flex-1 bg-secondary hover:bg-emerald text-primary font-bold py-3 rounded-xl text-sm">Save Room Type</button>
      </div>
    </form>
  </div>
</div>

<script>
lucide.createIcons();
const csrf = '<?= htmlspecialchars($csrf) ?>';
const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
const hotelSysId = <?= json_encode($isEdit ? $hotel['sys_id'] : '') ?>;
const hotelCity = <?= json_encode($isEdit ? $hotel['city'] : 'Makkah') ?>;

// ── City → landmark label/value auto behavior (mirrors admin/hotels.php's original pattern) ──
function syncLandmark() {
  const city = document.querySelector('[name="city"]').value;
  const label = document.getElementById('landmark-label');
  const input = document.getElementById('distance-landmark');
  const gateWrap = document.getElementById('gate-no-wrap');
  if (city === 'Makkah') { label.textContent = 'Landmark'; input.value = 'Masjid al-Haram'; input.readOnly = true; gateWrap.style.display = ''; }
  else if (city === 'Madinah') { label.textContent = 'Landmark'; input.value = 'Masjid an-Nabawi'; input.readOnly = true; gateWrap.style.display = ''; }
  else { label.textContent = 'Landmark (type your own)'; input.readOnly = false; gateWrap.style.display = 'none';
    if (['Masjid al-Haram','Masjid an-Nabawi'].includes(input.value)) input.value = ''; }
}
document.querySelector('[name="city"]')?.addEventListener('change', syncLandmark);
<?php if (!$isEdit): ?>syncLandmark();<?php endif; ?>

document.getElementById('walking-enabled')?.addEventListener('change', function() {
  document.getElementById('walking-minutes-wrap').classList.toggle('hidden', !this.checked);
});

// ── Infant Max -> Child Min auto-calc (Child Min is always Infant Max + 1, readonly) ──
function syncChildMin() {
  const infantMax = parseInt(document.getElementById('infant-age-max').value) || 0;
  document.getElementById('child-age-min').value = infantMax + 1;
}
syncChildMin();

// ── Generic repeatable URL-row helper (used for image_urls / youtube_urls on both forms) ──
// Extract a YouTube video ID from any common URL shape (mirrors the
// server-side youtubeEmbedId() in admin/hotel-view.php).
function youtubeIdFromUrl(url) {
  const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/);
  return m ? m[1] : null;
}

function addUrlRow(containerId, hiddenName, formSelector = '#hotel-form', previewType = 'image', value = '') {
  const container = document.getElementById(containerId);
  const div = document.createElement('div');
  div.className = 'flex gap-2 items-center';
  div.innerHTML = `
    <div class="w-10 h-10 rounded-lg bg-dark border border-white/10 shrink-0 overflow-hidden url-preview"></div>
    <input type="url" placeholder="https://..." value="${value.replace(/"/g,'&quot;')}" class="url-input field" oninput="syncUrlRows('${containerId}','${hiddenName}','${formSelector}'); updateUrlPreview(this, '${previewType}')">
    <button type="button" onclick="this.parentElement.remove(); syncUrlRows('${containerId}','${hiddenName}','${formSelector}')" class="px-3 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-xl shrink-0"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  container.appendChild(div);
  lucide.createIcons();
  if (value) updateUrlPreview(div.querySelector('.url-input'), previewType);
}
function updateUrlPreview(input, previewType) {
  const preview = input.previousElementSibling;
  const url = input.value.trim();
  if (!url) { preview.innerHTML = ''; return; }
  if (previewType === 'youtube') {
    const vid = youtubeIdFromUrl(url);
    preview.innerHTML = vid ? `<img src="https://img.youtube.com/vi/${vid}/default.jpg" class="w-full h-full object-cover">` : '';
  } else {
    preview.innerHTML = `<img src="${url}" class="w-full h-full object-cover" onerror="this.style.display='none'">`;
  }
}
function syncUrlRows(containerId, hiddenName, formSelector = '#hotel-form') {
  const urls = [...document.getElementById(containerId).querySelectorAll('.url-input')].map(i => i.value.trim()).filter(Boolean);
  document.querySelector(`${formSelector} [name="${hiddenName}"]`).value = JSON.stringify(urls);
}

// ── Pending media (for brand-new hotel/room-type — no sys_id yet) ──
let pendingHotelImageFiles = [], pendingHotelImagePreviews = [];
let pendingHotelVideoFiles = [], pendingHotelVideoPreviews = [];
let pendingRoomImageFiles = [], pendingRoomImagePreviews = [];
let pendingRoomVideoFiles = [], pendingRoomVideoPreviews = [];

// ── Hotel form submit ──
document.getElementById('hotel-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('hotel-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`, {method:'POST', body:new FormData(e.target)});
  const data = await res.json();

  if (data.success && !isEdit && data.sys_id) {
    // Brand-new hotel just got its sys_id — upload any pending images/videos now.
    if (pendingHotelImageFiles.length) { btn.textContent = 'Uploading images...'; await uploadMedia('upload_hotel_image', data.sys_id, pendingHotelImageFiles); }
    if (pendingHotelVideoFiles.length) { btn.textContent = 'Uploading videos...'; await uploadMedia('upload_hotel_video', data.sys_id, pendingHotelVideoFiles); }
    showToast(data.message, data.success);
    const uuidRes = await fetch(`${window.BASE_URL}/api/hotels.php`);
    const uuidData = await uuidRes.json();
    const created = (uuidData.hotels || []).find(h => h.sys_id === data.sys_id);
    if (created) { window.location.href = `${window.BASE_URL}/admin/hotel-create.php?hotel=${created.uuid}`; return; }
  }

  showToast(data.message, data.success);
  btn.disabled = false; btn.innerHTML = '<i data-lucide="save" class="w-4 h-4 inline mr-1"></i>' + (isEdit ? 'Save Hotel Info' : 'Create Hotel'); lucide.createIcons();
  if (data.success && isEdit) setTimeout(() => location.reload(), 700);
});

// ══════════ ROOM TYPE MODAL LOGIC ══════════
function getBedRows() {
  return [...document.querySelectorAll('#bed-config-rows > tr')].map(row => ({
    bed_type: row.querySelector('.bed-type').value.trim(),
    quantity: parseInt(row.querySelector('.bed-qty').value) || 0,
    capacity_per_bed: parseInt(row.querySelector('.bed-cap').value) || 0
  })).filter(b => b.bed_type);
}
function syncBedRows() { document.querySelector('#room-form [name="bed_config"]').value = JSON.stringify(getBedRows()); }
function addBedRow(type='', qty=1, cap=1) {
  const tr = document.createElement('tr');
  tr.className = 'border-t border-white/5';
  tr.innerHTML = `
    <td class="px-3 py-2"><input type="text" class="bed-type field" placeholder="e.g. King" value="${type.replace(/"/g,'&quot;')}" oninput="syncBedRows()"></td>
    <td class="px-3 py-2"><input type="number" min="1" class="bed-qty field" placeholder="Qty" value="${qty}" oninput="syncBedRows()"></td>
    <td class="px-3 py-2"><input type="number" min="1" class="bed-cap field" placeholder="Cap/bed" value="${cap}" oninput="syncBedRows()"></td>
    <td class="px-3 py-2"><button type="button" onclick="this.closest('tr').remove(); syncBedRows();" class="p-2 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button></td>`;
  document.getElementById('bed-config-rows').appendChild(tr);
  lucide.createIcons();
  syncBedRows();
}

function getAmenityRows() {
  return [...document.querySelectorAll('#amenity-rows > div')].map(row => row.querySelector('.amenity-input').value.trim()).filter(Boolean);
}
function syncAmenityRows() { document.querySelector('#room-form [name="amenities"]').value = JSON.stringify(getAmenityRows()); }
function addAmenityRow(val='') {
  const div = document.createElement('div');
  div.className = 'flex gap-2';
  div.innerHTML = `
    <input type="text" class="amenity-input field" placeholder="e.g. Free WiFi" value="${val.replace(/"/g,'&quot;')}" oninput="syncAmenityRows()">
    <button type="button" onclick="this.parentElement.remove(); syncAmenityRows();" class="px-3 bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-xl"><i data-lucide="x" class="w-4 h-4"></i></button>`;
  document.getElementById('amenity-rows').appendChild(div);
  lucide.createIcons();
  syncAmenityRows();
}

let currentRoomSysId = '';
const hotelChildAgeRange = <?= json_encode($isEdit ? ($hotel['age_ranges']['child'] ?? ['min'=>2,'max'=>11]) : ['min'=>2,'max'=>11]) ?>;

function openRoomModal(rt = null) {
  const form = document.getElementById('room-form');
  form.reset();
  document.getElementById('bed-config-rows').innerHTML = '';
  document.getElementById('amenity-rows').innerHTML = '';
  document.getElementById('room-image-urls').innerHTML = '';
  document.getElementById('room-youtube-urls').innerHTML = '';
  document.getElementById('room-modal-title').textContent = rt ? 'Edit Room Type' : 'New Room Type';
  document.getElementById('max-children-label').textContent = `Max Children (age ${hotelChildAgeRange.min}-${hotelChildAgeRange.max})`;
  const viewLabel = hotelCity === 'Makkah' ? 'Haram View available' : hotelCity === 'Madinah' ? 'Nawabi View available' : 'View available';
  document.getElementById('room-view-label').textContent = viewLabel;
  form.elements['action'].value = rt ? 'update_room_type' : 'create_room_type';
  form.elements['sys_id'].value = rt ? (rt.sys_id || '') : '';
  form.elements['hotel_sys_id'].value = hotelSysId;
  currentRoomSysId = rt ? rt.sys_id : '';
  pendingRoomImagePreviews.forEach(u => URL.revokeObjectURL(u));
  pendingRoomVideoPreviews.forEach(u => URL.revokeObjectURL(u));
  pendingRoomImageFiles = []; pendingRoomVideoFiles = [];
  pendingRoomImagePreviews = []; pendingRoomVideoPreviews = [];

  if (rt) {
    form.elements['name'].value = rt.name || '';
    form.elements['description'].value = rt.description || '';
    form.elements['capacity_adults'].value = (rt.person_capacity && rt.person_capacity.adults) || 0;
    form.elements['capacity_children'].value = (rt.person_capacity && rt.person_capacity.children) || 0;
    form.elements['size_value'].value = (rt.size && rt.size.value) || 0;
    form.elements['size_unit'].value = (rt.size && rt.size.unit) || 'sqr-m';
    (rt.bed_config || []).forEach(b => addBedRow(b.bed_type, b.quantity, b.capacity_per_bed));
    (rt.amenities || []).forEach(a => addAmenityRow(a));
    document.getElementById('room-addon-bed').checked = !!(rt.add_on && Number(rt.add_on.bed));
    document.getElementById('room-addon-charge-wrap').classList.toggle('hidden', !(rt.add_on && Number(rt.add_on.bed)));
    form.elements['add_on_charge'].value = (rt.add_on && rt.add_on.charge) || 0;
    document.getElementById('room-view-enabled').checked = !!(rt.view_info && Number(rt.view_info.enabled));
    document.getElementById('room-view-desc-wrap').classList.toggle('hidden', !(rt.view_info && Number(rt.view_info.enabled)));
    form.elements['view_description'].value = (rt.view_info && rt.view_info.description) || '';
    (rt.image_urls || []).forEach(u => addUrlRow('room-image-urls','image_urls','#room-form','image', u));
    (rt.youtube_urls || []).forEach(u => addUrlRow('room-youtube-urls','youtube_urls','#room-form','youtube', u));
    syncUrlRows('room-image-urls','image_urls','#room-form');
    syncUrlRows('room-youtube-urls','youtube_urls','#room-form');
    currentRoomSavedImages = rt.images || [];
    currentRoomSavedVideos = rt.videos || [];
    renderRoomGallery('images', currentRoomSavedImages);
    renderRoomGallery('videos', currentRoomSavedVideos);
  } else {
    addBedRow();
    addAmenityRow();
    document.getElementById('room-addon-charge-wrap').classList.add('hidden');
    document.getElementById('room-view-desc-wrap').classList.add('hidden');
    currentRoomSavedImages = [];
    currentRoomSavedVideos = [];
    renderRoomGallery('images', []);
    renderRoomGallery('videos', []);
  }
  document.getElementById('room-modal-overlay').style.display = 'flex';
}
function editRoomType(rt) { openRoomModal(rt); }
async function toggleRoomType(sysId) {
  const fd = new FormData(); fd.append('csrf_token',csrf); fd.append('action','toggle_room_type'); fd.append('sys_id',sysId);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`,{method:'POST',body:fd});
  const data = await res.json(); showToast(data.message, data.success);
  if (data.success) setTimeout(()=>location.reload(), 700);
}
function closeRoomModal() { document.getElementById('room-modal-overlay').style.display = 'none'; }


document.getElementById('room-form').addEventListener('submit', async e => {
  e.preventDefault();
  syncBedRows(); syncAmenityRows();
  const btn = document.getElementById('room-btn');
  btn.disabled = true; btn.textContent = 'Saving...';
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`, {method:'POST', body:new FormData(e.target)});
  const data = await res.json();

  const newRoomSysId = data.sys_id || currentRoomSysId;
  if (data.success && newRoomSysId) {
    if (pendingRoomImageFiles.length) { btn.textContent = 'Uploading images...'; await uploadMedia('upload_room_type_image', newRoomSysId, pendingRoomImageFiles); }
    if (pendingRoomVideoFiles.length) { btn.textContent = 'Uploading videos...'; await uploadMedia('upload_room_type_video', newRoomSysId, pendingRoomVideoFiles); }
  }

  showToast(data.message, data.success);
  btn.disabled = false; btn.textContent = 'Save Room Type';
  if (data.success) { closeRoomModal(); setTimeout(() => location.reload(), 700); }
});

// ── Media galleries (hotel + room type) — shows saved files plus any
//    locally-pending ones (dashed border, "Pending" badge, removable). ──
function renderHotelGallery(kind, paths) {
  const gallery = document.getElementById(`hotel-${kind.slice(0,-1)}-gallery`);
  if (!gallery) return;
  const isImg = kind === 'images';
  const pendingPreviews = isImg ? pendingHotelImagePreviews : pendingHotelVideoPreviews;
  const saved = paths.map(p => `
    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-dark group">
      ${isImg ? `<img src="${window.BASE_URL}/${p}" class="w-full h-full object-cover">` : `<video src="${window.BASE_URL}/${p}" class="w-full h-full object-cover"></video>`}
      <button type="button" onclick="deleteHotelMedia('${kind}','${p.replace(/'/g,"\\'")}')" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center"><i data-lucide="trash-2" class="w-4 h-4 text-red-400"></i></button>
    </div>`).join('');
  const pending = pendingPreviews.map((url, i) => `
    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-dark group border-2 border-dashed border-secondary/50">
      ${isImg ? `<img src="${url}" class="w-full h-full object-cover opacity-70">` : `<video src="${url}" class="w-full h-full object-cover opacity-70"></video>`}
      <button type="button" onclick="removePendingHotelFile('${kind}',${i})" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center"><i data-lucide="x" class="w-4 h-4 text-white"></i></button>
      <span class="absolute bottom-0 inset-x-0 bg-secondary/80 text-primary text-[8px] text-center font-bold py-0.5">Pending</span>
    </div>`).join('');
  gallery.innerHTML = saved + pending;
  lucide.createIcons();
}
function renderRoomGallery(kind, paths) {
  const gallery = document.getElementById(`room-${kind.slice(0,-1)}-gallery`);
  if (!gallery) return;
  const isImg = kind === 'images';
  const pendingPreviews = isImg ? pendingRoomImagePreviews : pendingRoomVideoPreviews;
  const saved = paths.map(p => `
    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-dark group">
      ${isImg ? `<img src="${window.BASE_URL}/${p}" class="w-full h-full object-cover">` : `<video src="${window.BASE_URL}/${p}" class="w-full h-full object-cover"></video>`}
      <button type="button" onclick="deleteRoomMedia('${kind}','${p.replace(/'/g,"\\'")}')" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center"><i data-lucide="trash-2" class="w-4 h-4 text-red-400"></i></button>
    </div>`).join('');
  const pending = pendingPreviews.map((url, i) => `
    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-dark group border-2 border-dashed border-secondary/50">
      ${isImg ? `<img src="${url}" class="w-full h-full object-cover opacity-70">` : `<video src="${url}" class="w-full h-full object-cover opacity-70"></video>`}
      <button type="button" onclick="removePendingRoomFile('${kind}',${i})" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center"><i data-lucide="x" class="w-4 h-4 text-white"></i></button>
      <span class="absolute bottom-0 inset-x-0 bg-secondary/80 text-primary text-[8px] text-center font-bold py-0.5">Pending</span>
    </div>`).join('');
  gallery.innerHTML = saved + pending;
  lucide.createIcons();
}

function removePendingHotelFile(kind, index) {
  if (kind === 'images') { URL.revokeObjectURL(pendingHotelImagePreviews[index]); pendingHotelImageFiles.splice(index,1); pendingHotelImagePreviews.splice(index,1); renderHotelGallery('images', currentHotelSavedImages); }
  else { URL.revokeObjectURL(pendingHotelVideoPreviews[index]); pendingHotelVideoFiles.splice(index,1); pendingHotelVideoPreviews.splice(index,1); renderHotelGallery('videos', currentHotelSavedVideos); }
}
function removePendingRoomFile(kind, index) {
  if (kind === 'images') { URL.revokeObjectURL(pendingRoomImagePreviews[index]); pendingRoomImageFiles.splice(index,1); pendingRoomImagePreviews.splice(index,1); renderRoomGallery('images', currentRoomSavedImages); }
  else { URL.revokeObjectURL(pendingRoomVideoPreviews[index]); pendingRoomVideoFiles.splice(index,1); pendingRoomVideoPreviews.splice(index,1); renderRoomGallery('videos', currentRoomSavedVideos); }
}

let currentHotelSavedImages = <?= json_encode($isEdit ? ($hotel['images'] ?: []) : []) ?>;
let currentHotelSavedVideos = <?= json_encode($isEdit ? ($hotel['videos'] ?: []) : []) ?>;
let currentRoomSavedImages = [];
let currentRoomSavedVideos = [];

async function uploadMedia(action, sysId, files) {
  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fd.append('action', action);
  fd.append('sys_id', sysId);
  for (const f of files) fd.append('files[]', f);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`, {method:'POST', body:fd});
  return res.json();
}

document.getElementById('hotel-image-input')?.addEventListener('change', async function() {
  if (!this.files.length) return;
  if (hotelSysId) {
    const data = await uploadMedia('upload_hotel_image', hotelSysId, this.files);
    showToast(data.message, data.success);
    if (data.images) { currentHotelSavedImages = data.images; renderHotelGallery('images', currentHotelSavedImages); }
  } else {
    for (const f of this.files) { pendingHotelImageFiles.push(f); pendingHotelImagePreviews.push(URL.createObjectURL(f)); }
    renderHotelGallery('images', currentHotelSavedImages);
  }
  this.value = '';
});
document.getElementById('hotel-video-input')?.addEventListener('change', async function() {
  if (!this.files.length) return;
  if (hotelSysId) {
    const data = await uploadMedia('upload_hotel_video', hotelSysId, this.files);
    showToast(data.message, data.success);
    if (data.videos) { currentHotelSavedVideos = data.videos; renderHotelGallery('videos', currentHotelSavedVideos); }
  } else {
    for (const f of this.files) { pendingHotelVideoFiles.push(f); pendingHotelVideoPreviews.push(URL.createObjectURL(f)); }
    renderHotelGallery('videos', currentHotelSavedVideos);
  }
  this.value = '';
});
async function deleteHotelMedia(kind, path) {
  if (!confirm('Remove this file?')) return;
  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fd.append('action', kind === 'images' ? 'delete_hotel_image' : 'delete_hotel_video');
  fd.append('sys_id', hotelSysId);
  fd.append('path', path);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  if (data.success) {
    if (kind === 'images') currentHotelSavedImages = data.images || [];
    else currentHotelSavedVideos = data.videos || [];
    renderHotelGallery(kind, data[kind] || []);
  }
}

document.getElementById('room-image-input')?.addEventListener('change', async function() {
  if (!this.files.length) return;
  if (currentRoomSysId) {
    const data = await uploadMedia('upload_room_type_image', currentRoomSysId, this.files);
    showToast(data.message, data.success);
    if (data.images) { currentRoomSavedImages = data.images; renderRoomGallery('images', currentRoomSavedImages); }
  } else {
    for (const f of this.files) { pendingRoomImageFiles.push(f); pendingRoomImagePreviews.push(URL.createObjectURL(f)); }
    renderRoomGallery('images', currentRoomSavedImages);
  }
  this.value = '';
});
document.getElementById('room-video-input')?.addEventListener('change', async function() {
  if (!this.files.length) return;
  if (currentRoomSysId) {
    const data = await uploadMedia('upload_room_type_video', currentRoomSysId, this.files);
    showToast(data.message, data.success);
    if (data.videos) { currentRoomSavedVideos = data.videos; renderRoomGallery('videos', currentRoomSavedVideos); }
  } else {
    for (const f of this.files) { pendingRoomVideoFiles.push(f); pendingRoomVideoPreviews.push(URL.createObjectURL(f)); }
    renderRoomGallery('videos', currentRoomSavedVideos);
  }
  this.value = '';
});
async function deleteRoomMedia(kind, path) {
  if (!confirm('Remove this file?')) return;
  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fd.append('action', kind === 'images' ? 'delete_room_type_image' : 'delete_room_type_video');
  fd.append('sys_id', currentRoomSysId);
  fd.append('path', path);
  const res = await fetch(`${window.BASE_URL}/api/hotels.php`, {method:'POST', body:fd});
  const data = await res.json();
  showToast(data.message, data.success);
  if (data.success) {
    if (kind === 'images') currentRoomSavedImages = data.images || [];
    else currentRoomSavedVideos = data.videos || [];
    renderRoomGallery(kind, data[kind] || []);
  }
}

<?php if ($isEdit): ?>
renderHotelGallery('images', <?= json_encode($hotel['images'] ?: []) ?>);
renderHotelGallery('videos', <?= json_encode($hotel['videos'] ?: []) ?>);
<?php foreach ($hotel['image_urls'] ?: [] as $u): ?>
addUrlRow('hotel-image-urls','image_urls','#hotel-form','image', <?= json_encode($u) ?>);
<?php endforeach; ?>
<?php foreach ($hotel['youtube_urls'] ?: [] as $u): ?>
addUrlRow('hotel-youtube-urls','youtube_urls','#hotel-form','youtube', <?= json_encode($u) ?>);
<?php endforeach; ?>
syncUrlRows('hotel-image-urls','image_urls');
syncUrlRows('hotel-youtube-urls','youtube_urls');
<?php endif; ?>

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed top-5 right-5 z-50 font-bold px-5 py-3 rounded-xl shadow-2xl text-sm ${ok?'bg-secondary text-primary':'bg-red-500 text-white'}`;
  t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),3000);
}
</script>
</body></html>