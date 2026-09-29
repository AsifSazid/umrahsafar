<?php
// FILE PATH: /admin/_media-block.php
// Shared column-wise media renderer (Images | Uploaded Videos | From
// YouTube), each column showing up to 6 items in a 3x2 grid with a
// "See More" button if there are more. All media for one group (a
// hotel, or one room type) is also emitted as a single combined JS
// array (image+video+youtube together, in one sequence) so clicking
// ANY thumbnail opens the shared slider (#slider-modal, defined once
// in hotel-view.php) already positioned on that item, and the visitor
// can then arrow through every other file in the group regardless of
// type.
// Not a standalone page — included by hotel-view.php, which also
// defines youtubeEmbedId() before including this file, and provides
// the shared slider modal + its JS once per page.

function renderMediaBlock(string $title, array $item, string $groupKey): void {
    $imageUrls   = $item['image_urls'] ?: [];
    $images      = $item['images'] ?: [];
    $videos      = $item['videos'] ?: [];
    $youtubeUrls = $item['youtube_urls'] ?: [];
    $youtubeIds  = array_values(array_filter(array_map('youtubeEmbedId', $youtubeUrls)));

    // One combined, ordered list — image entries first, then videos, then
    // YouTube — this exact order is what the slider arrows step through.
    $combined = [];
    foreach ($imageUrls as $u) $combined[] = ['type' => 'image', 'src' => $u];
    foreach ($images as $p)    $combined[] = ['type' => 'image', 'src' => BASE_URL . '/' . $p];
    foreach ($videos as $p)    $combined[] = ['type' => 'video', 'src' => BASE_URL . '/' . $p];
    foreach ($youtubeIds as $vid) $combined[] = ['type' => 'youtube', 'id' => $vid];

    if (!$combined) return;

    // Split back out with each item's index into $combined preserved, so
    // thumbnails know exactly which slider position to open.
    $imageEntries = $videoEntries = $youtubeEntries = [];
    foreach ($combined as $idx => $c) {
        if ($c['type'] === 'image') $imageEntries[] = ['idx' => $idx, 'src' => $c['src']];
        elseif ($c['type'] === 'video') $videoEntries[] = ['idx' => $idx, 'src' => $c['src']];
        elseif ($c['type'] === 'youtube') $youtubeEntries[] = ['idx' => $idx, 'id' => $c['id']];
    }

    $jsVar = 'mediaGroup_' . preg_replace('/[^A-Za-z0-9_]/', '_', $groupKey);
    ?>
    <script>var <?= $jsVar ?> = <?= json_encode($combined, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
    <div class="border-t border-white/10 pt-4 mt-4">
        <p class="text-[11px] text-white/30 uppercase tracking-wider mb-3"><?= htmlspecialchars($title) ?></p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <?php if ($imageEntries): ?>
            <div>
                <p class="text-xs font-semibold text-white/60 mb-2">Images</p>
                <div class="grid grid-cols-3 gap-2">
                    <?php foreach (array_slice($imageEntries, 0, 6) as $e): ?>
                    <img src="<?= htmlspecialchars($e['src']) ?>" onclick="openSlider(<?= $jsVar ?>, <?= $e['idx'] ?>)" class="thumb w-full aspect-square rounded-lg object-cover bg-dark border border-white/10" loading="lazy" onerror="this.style.opacity=0.2" referrerpolicy="no-referrer">
                    <?php endforeach; ?>
                </div>
                <?php if (count($imageEntries) > 6): ?>
                <button type="button" onclick='openSeeMore(<?= $jsVar ?>)' class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg mt-2">See More (<?= count($imageEntries) ?>)</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($videoEntries): ?>
            <div>
                <p class="text-xs font-semibold text-white/60 mb-2">Uploaded Videos</p>
                <div class="grid grid-cols-3 gap-2">
                    <?php foreach (array_slice($videoEntries, 0, 6) as $e): ?>
                    <div onclick="openSlider(<?= $jsVar ?>, <?= $e['idx'] ?>)" class="thumb w-full aspect-square rounded-lg bg-dark border border-white/10 relative overflow-hidden">
                        <video src="<?= htmlspecialchars($e['src']) ?>#t=0.5" preload="metadata" muted class="w-full h-full object-cover pointer-events-none"></video>
                        <div class="absolute inset-0 flex items-center justify-center bg-black/20"><i data-lucide="play-circle" class="w-6 h-6 text-white drop-shadow"></i></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($videoEntries) > 6): ?>
                <button type="button" onclick='openSeeMore(<?= $jsVar ?>)' class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg mt-2">See More (<?= count($videoEntries) ?>)</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($youtubeEntries): ?>
            <div>
                <p class="text-xs font-semibold text-white/60 mb-2">From YouTube</p>
                <div class="grid grid-cols-3 gap-2">
                    <?php foreach (array_slice($youtubeEntries, 0, 6) as $e): ?>
                    <img src="https://img.youtube.com/vi/<?= htmlspecialchars($e['id']) ?>/hqdefault.jpg" onclick="openSlider(<?= $jsVar ?>, <?= $e['idx'] ?>)" class="thumb w-full aspect-square rounded-lg object-cover bg-dark border border-white/10" loading="lazy">
                    <?php endforeach; ?>
                </div>
                <?php if (count($youtubeEntries) > 6): ?>
                <button type="button" onclick='openSeeMore(<?= $jsVar ?>)' class="text-xs bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg mt-2">See More (<?= count($youtubeEntries) ?>)</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php
}