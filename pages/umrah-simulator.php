<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#16130E">
<meta name="description" content="Umrah Safar - TravHub Global Limited এর Umrah শেখার সিমুলেটর">
<link rel="manifest" href="manifest.json">
<title>Umrah Safar | TravHub Global Limited</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Hind+Siliguri:wght@400;500;600;700&family=Tiro+Bangla&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<canvas id="c" aria-label="Umrah Simulator 3D view"></canvas>

<!-- Watermark -->
<div class="watermark" aria-hidden="true">Umrah Safar &nbsp;|&nbsp; TravHub Global Limited</div>

<!-- HUD -->
<div id="hud" hidden>
  <header class="hizam">
    <div class="hizam-in">
      <div class="brand" id="brand">
        Umrah Safar
        <small>TravHub Global Limited</small>
      </div>
      <ol class="steps" id="steps"></ol>
      <div class="hizam-tools">
        <button class="icon-btn" id="toolsBtn" title="Tools">☰</button>
        <button class="icon-btn" id="helpBtn">?</button>
        <div class="langs" role="group">
          <button data-lang="en" aria-pressed="true">EN</button>
          <button data-lang="bn" aria-pressed="false">বাং</button>
        </div>
      </div>
    </div>
  </header>

  <section class="objective" id="objective" aria-live="polite" hidden>
    <span class="obj-k" id="objK"></span>
    <p class="obj-t" id="objT"></p>
    <p class="obj-h" id="objH" hidden></p>
  </section>

  <div class="counter" id="counter" hidden>
    <svg viewBox="0 0 100 100" id="ring" aria-hidden="true"></svg>
    <div class="c-num" id="cNum"></div>
    <div class="c-lab" id="cLab"></div>
    <div class="c-km" id="cKm"></div>
  </div>

  <div class="toast" id="toast" role="status"></div>

  <div class="bottom">
    <div class="dua-strip" id="duaStrip" hidden></div>
    <button class="near" id="near" hidden></button>
  </div>

  <div class="controls" id="controls" hidden>
    <button class="ctl act" id="actBtn" hidden></button>
    <div class="ctl-row">
      <button class="ctl" id="autoBtn" aria-pressed="false"></button>
      <button class="ctl" id="runBtn"></button>
    </div>
  </div>

  <div class="mapbox" id="mapBox" hidden>
    <canvas id="map" role="img" aria-label="Map"></canvas>
    <div class="map-gate" id="mapGate" hidden></div>
    <div class="map-dist" id="mapDist"></div>
  </div>

  <div class="joy-hint" id="joyHint" hidden></div>
  <div class="joy" id="joy" hidden><div class="knob" id="knob"></div></div>
  <div class="pray" id="pray" hidden></div>
</div>

<!-- Card Modal -->
<div class="modal" id="modal" hidden>
  <div class="card" id="card" role="dialog" aria-modal="true" aria-labelledby="cardTitle">
    <p class="card-k" id="cardK"></p>
    <h2 id="cardTitle"></h2>
    <div class="progress-bar" id="progBar" hidden>
      <div class="progress-fill" id="progFill"></div>
    </div>
    <div class="card-b" id="cardBody"></div>
    <div class="card-btns" id="cardBtns"></div>
  </div>
</div>

<!-- Side Panel -->
<div class="side-panel" id="sidePanel" hidden>
  <div class="side-inner">
    <div class="side-head">
      <h3 id="sidePanelTitle">Tools</h3>
      <button class="side-close" id="sideClose">✕</button>
    </div>
    <div class="side-tabs" id="sideTabs" role="tablist"></div>
    <div class="side-body" id="sideBody"></div>
  </div>
</div>

<div class="fade" id="fade"></div>
<div class="loading" id="loading"><p id="loadMsg">Preparing Masjid al-Haram</p></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="data.js?t=<?php echo time(); ?>"></script>
<script src="world.js?t=<?php echo time(); ?>"></script>
<script src="tools.js?t=<?php echo time(); ?>"></script>
<script src="game.js?t=<?php echo time(); ?>"></script>
</body>
</html>