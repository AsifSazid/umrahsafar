<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Umrah Simulator</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Hind+Siliguri:wght@400;500;600;700&family=Tiro+Bangla&display=swap" rel="stylesheet">
<style>
:root{
  box-sizing:border-box;
  padding-top:env(safe-area-inset-top,0px);
  padding-bottom:env(safe-area-inset-bottom,0px);
  --kiswa:#16130E; --gold:#C9A54C; --gold-hi:#E7CB82; --gold-lo:#8C6D27;
  --ihram:#FBFAF7; --dome:#1F6A47; --carpet:#8E1C22;
  --panel:rgba(251,250,247,.95); --panel-solid:#FBFAF7; --ink:#16130E; --ink-2:#4B463C;
  --rule:rgba(22,19,14,.14); --veil:rgba(22,19,14,.42); --dua-bg:rgba(201,165,76,.10);
  --hint:#1F6A47; --kick:#8C6D27;
  --shadow:0 12px 32px rgba(22,19,14,.22);
  --f-ui:"Hind Siliguri", system-ui, -apple-system, "Segoe UI", sans-serif;
  --f-disp:"Tiro Bangla", "Hind Siliguri", Georgia, serif;
  --f-ar:"Amiri", "Noto Naskh Arabic", "Traditional Arabic", serif;
  color-scheme:light dark;
}
@media (prefers-color-scheme:dark){
  :root:not([data-theme="light"]){
    --panel:rgba(27,24,18,.94); --panel-solid:#1B1812; --ink:#F2EEE3; --ink-2:#BDB6A6;
    --rule:rgba(242,238,227,.16); --veil:rgba(0,0,0,.52); --dua-bg:rgba(201,165,76,.12);
    --hint:#7CCFA2; --kick:#E7CB82; --shadow:0 12px 32px rgba(0,0,0,.45);
  }
}
:root[data-theme="dark"]{
  --panel:rgba(27,24,18,.94); --panel-solid:#1B1812; --ink:#F2EEE3; --ink-2:#BDB6A6;
  --rule:rgba(242,238,227,.16); --veil:rgba(0,0,0,.52); --dua-bg:rgba(201,165,76,.12);
  --hint:#7CCFA2; --kick:#E7CB82; --shadow:0 12px 32px rgba(0,0,0,.45);
}
*,*::before,*::after{box-sizing:inherit}
[hidden]{display:none !important}
html{height:100%;scroll-padding-top:env(safe-area-inset-top,0px)}
body{height:100%;margin:0;overflow:hidden;background:#E9E2D2;font-family:var(--f-ui);color:var(--ink);-webkit-font-smoothing:antialiased;-webkit-tap-highlight-color:transparent}
button{font:inherit}
#c{position:fixed;inset:0;width:100%;height:100%;display:block;touch-action:none;outline:none}

/* The hizam: the gold-embroidered band of the kiswa, used as the steps bar */
.hizam{position:fixed;top:0;left:0;right:0;z-index:5;padding-top:env(safe-area-inset-top,0px);
  background:repeating-linear-gradient(135deg,rgba(201,165,76,.07) 0 2px,transparent 2px 13px),var(--kiswa);
  border-bottom:3px double var(--gold);color:var(--gold-hi)}
.hizam-in{display:flex;align-items:center;gap:10px;min-height:52px;
  padding-left:max(14px,env(safe-area-inset-left,0px));padding-right:max(10px,env(safe-area-inset-right,0px))}
.brand{font-family:var(--f-disp);font-size:1.2rem;color:var(--gold-hi);flex:none;padding-right:4px}
.steps{list-style:none;margin:0;padding:0;display:flex;gap:2px;overflow-x:auto;scrollbar-width:none;flex:1;min-width:0}
.steps::-webkit-scrollbar{display:none}
.steps button{all:unset;cursor:pointer;display:flex;align-items:center;gap:7px;padding:7px 10px;border-radius:3px;color:rgba(231,203,130,.6);font-size:.88rem;white-space:nowrap;line-height:1}
.steps .n{font-family:var(--f-disp);font-size:.8rem;width:21px;height:21px;display:grid;place-items:center;border:1px solid currentColor;border-radius:50%;flex:none}
.steps li.done button{color:var(--gold)}
.steps li.done .n{background:rgba(201,165,76,.2)}
.steps li.cur button{background:var(--gold);color:var(--kiswa)}
.steps li.cur .n{border-color:var(--kiswa)}
.steps button:focus-visible,.langs button:focus-visible,.icon-btn:focus-visible{outline:2px solid var(--gold-hi);outline-offset:2px}
.langs{display:flex;border:1px solid rgba(201,165,76,.55);border-radius:999px;overflow:hidden;flex:none}
.langs button{all:unset;cursor:pointer;padding:6px 11px;font-size:.84rem;color:var(--gold-hi);line-height:1.1}
.langs button[aria-pressed="true"]{background:var(--gold);color:var(--kiswa)}
.icon-btn{all:unset;cursor:pointer;flex:none;width:30px;height:30px;border-radius:50%;border:1px solid rgba(201,165,76,.55);display:grid;place-items:center;color:var(--gold-hi);font-family:var(--f-disp);font-size:1rem}

.objective{position:fixed;z-index:4;left:50%;transform:translateX(-50%);top:calc(env(safe-area-inset-top,0px) + 68px);
  width:min(560px,calc(100% - 24px));background:var(--panel);color:var(--ink);border-radius:4px;box-shadow:var(--shadow);
  padding:9px 14px 10px 16px;border-left:4px solid var(--gold);pointer-events:none;-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px)}
.obj-k{font-size:.76rem;color:var(--ink-2);display:block}
.obj-t{margin:0;font-size:1.04rem;font-weight:600;line-height:1.35}
.obj-h{margin:3px 0 0;font-size:.9rem;color:var(--hint);font-weight:600;line-height:1.35}

.counter{position:fixed;z-index:4;right:max(12px,env(safe-area-inset-right,0px));top:calc(env(safe-area-inset-top,0px) + 150px);
  width:108px;text-align:center;background:var(--panel);border-radius:54px 54px 10px 10px;padding:9px 8px 10px;box-shadow:var(--shadow);pointer-events:none}
.counter svg{width:90px;height:90px;display:block;margin:0 auto}
.counter .trk{fill:none;stroke:var(--rule);stroke-width:7;stroke-linecap:round}
.counter .fil{fill:none;stroke:var(--gold);stroke-width:7;stroke-linecap:round}
.c-num{position:absolute;top:9px;left:0;right:0;height:90px;display:grid;place-items:center;font-family:var(--f-disp);font-size:1.45rem;color:var(--ink)}
.c-lab{font-size:.8rem;color:var(--ink-2);margin-top:3px;line-height:1.2}

.toast{position:fixed;z-index:6;left:50%;top:33%;transform:translate(-50%,-50%) scale(.96);opacity:0;transition:opacity .22s,transform .22s;
  background:var(--kiswa);color:var(--ihram);padding:10px 18px;border-radius:4px;font-weight:600;max-width:min(470px,calc(100% - 32px));
  text-align:center;pointer-events:none;border:1px solid var(--gold);line-height:1.4}
.toast.show{opacity:1;transform:translate(-50%,-50%) scale(1)}
.toast.warn{background:var(--carpet);border-color:#f2b8ae}
.toast.ok{background:var(--dome);border-color:#9fd8b8}
.toast .ar{font-family:var(--f-ar);font-size:1.55rem;display:block;line-height:1.55;font-weight:400}

.bottom{position:fixed;z-index:4;left:50%;transform:translateX(-50%);bottom:calc(env(safe-area-inset-bottom,0px) + 18px);
  display:flex;flex-direction:column;align-items:center;gap:8px;width:min(500px,calc(100% - 440px));min-width:280px;pointer-events:none}
.dua-strip{width:100%;background:var(--panel);border-radius:4px;padding:7px 14px 8px;text-align:center;box-shadow:var(--shadow);border-top:2px solid var(--gold)}
.dua-strip .k{font-size:.78rem;color:var(--ink-2);display:block}
.dua-strip .ar{font-family:var(--f-ar);font-size:1.3rem;line-height:1.65;direction:rtl;display:block}
.dua-strip .tr{font-size:.86rem;color:var(--ink-2);display:block;line-height:1.35}
.near{all:unset;pointer-events:auto;cursor:pointer;background:var(--kiswa);color:var(--ihram);border:1px solid var(--gold);border-radius:22px;
  padding:7px 16px 8px;display:flex;flex-direction:column;align-items:center;line-height:1.25;box-shadow:var(--shadow);max-width:100%;text-align:center}
.near b{font-weight:600;font-size:.95rem;color:var(--gold-hi)}
.near span{font-size:.76rem;opacity:.8}
.near:focus-visible{outline:2px solid var(--gold-hi);outline-offset:3px}

.controls{position:fixed;z-index:4;right:max(14px,env(safe-area-inset-right,0px));bottom:calc(env(safe-area-inset-bottom,0px) + 16px);
  display:flex;flex-direction:column;align-items:flex-end;gap:10px}
.ctl-row{display:flex;gap:8px}
.ctl{all:unset;cursor:pointer;min-height:44px;padding:0 15px;border-radius:22px;background:var(--panel);color:var(--ink);font-weight:600;font-size:.9rem;
  display:inline-flex;align-items:center;box-shadow:var(--shadow);border:1px solid var(--rule);user-select:none;-webkit-user-select:none;touch-action:none}
.ctl[aria-pressed="true"]{background:var(--dome);color:#fff;border-color:var(--dome)}
.ctl.held{background:var(--kiswa);color:var(--gold-hi);border-color:var(--gold)}
.ctl.nudge{animation:nudge 1.3s ease-in-out infinite}
@keyframes nudge{50%{box-shadow:0 0 0 6px rgba(201,165,76,.5),var(--shadow)}}
.act{min-height:58px;padding:0 26px;border-radius:29px;background:var(--gold);color:var(--kiswa);font-size:1.05rem;font-weight:700;border:2px solid var(--gold-hi);
  box-shadow:0 8px 24px rgba(140,109,39,.45);animation:actIn .25s ease-out}
@keyframes actIn{from{transform:scale(.85);opacity:0}}
.ctl:focus-visible{outline:2px solid var(--gold);outline-offset:3px}

.joy{position:fixed;z-index:3;width:124px;height:124px;margin:-62px 0 0 -62px;border-radius:50%;border:2px solid rgba(251,250,247,.75);background:rgba(22,19,14,.2);pointer-events:none}
.knob{position:absolute;left:50%;top:50%;width:54px;height:54px;margin:-27px;border-radius:50%;background:rgba(251,250,247,.92);box-shadow:var(--shadow)}
.joy-hint{position:fixed;z-index:3;left:max(22px,env(safe-area-inset-left,0px));bottom:calc(env(safe-area-inset-bottom,0px) + 26px);width:108px;height:108px;
  border-radius:50%;border:2px dashed rgba(251,250,247,.8);display:grid;place-items:center;color:#fff;font-size:.82rem;text-shadow:0 1px 3px rgba(0,0,0,.7);pointer-events:none;background:rgba(22,19,14,.12)}

.pray{position:fixed;z-index:5;left:50%;bottom:calc(env(safe-area-inset-bottom,0px) + 110px);transform:translateX(-50%);background:var(--kiswa);color:var(--ihram);
  border:1px solid var(--gold);border-radius:4px;padding:9px 20px 10px;text-align:center;display:flex;flex-direction:column;line-height:1.3;pointer-events:none}
.pray span{font-size:.8rem;color:var(--gold-hi)}
.pray b{font-size:1.1rem;font-weight:600}

.modal{position:fixed;inset:0;z-index:10;display:grid;place-items:center;
  padding:calc(env(safe-area-inset-top,0px) + 14px) 14px calc(env(safe-area-inset-bottom,0px) + 14px);background:var(--veil)}
.card{width:min(560px,100%);max-height:100%;overflow:auto;background:var(--panel-solid);color:var(--ink);border-radius:6px;box-shadow:var(--shadow);
  padding:20px 22px 18px;border-top:8px solid var(--kiswa);position:relative;overscroll-behavior:contain}
.card::before{content:"";position:absolute;left:0;right:0;top:0;height:2px;background:var(--gold)}
.card-k{margin:4px 0 2px;font-size:.84rem;color:var(--kick)}
.card h2{font-family:var(--f-disp);font-weight:400;font-size:1.7rem;line-height:1.22;margin:0 0 12px}
.card-b p{margin:0 0 12px;line-height:1.62;font-size:1rem}
.card-b .note{font-size:.93rem;color:var(--ink-2);border-left:2px solid var(--gold);padding-left:11px}
.card-b .lead{font-size:1.08rem;color:var(--ink-2);margin-bottom:16px}
.dua{margin:4px 0 14px;padding:14px 14px 12px;background:var(--dua-bg);border-radius:4px;text-align:center;transition:background .3s}
.dua.said{animation:said .6s ease-out}
@keyframes said{0%{box-shadow:0 0 0 0 rgba(201,165,76,.7)}100%{box-shadow:0 0 0 14px rgba(201,165,76,0)}}
.dua .ar{font-family:var(--f-ar);font-size:1.75rem;line-height:1.9;margin:0 0 6px;direction:rtl}
.dua .tr{font-size:.98rem;margin:0 0 5px;color:var(--ink);line-height:1.5}
.dua .mean{font-size:.92rem;color:var(--ink-2);margin:0;line-height:1.5}
.rules{list-style:none;margin:0 0 14px;padding:0}
.rules li{position:relative;padding:7px 0 7px 22px;border-bottom:1px solid var(--rule);line-height:1.45}
.rules li::before{content:"";position:absolute;left:3px;top:15px;width:8px;height:8px;background:var(--gold);transform:rotate(45deg)}
.card-btns{display:flex;flex-wrap:wrap;gap:10px;justify-content:flex-end;margin-top:14px}
.btn{all:unset;cursor:pointer;padding:11px 19px;border-radius:24px;font-weight:600;border:1px solid var(--rule);color:var(--ink);text-align:center;line-height:1.3}
.btn.primary{background:var(--kiswa);color:var(--gold-hi);border-color:var(--kiswa)}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]) .btn.primary{background:var(--gold);color:var(--kiswa);border-color:var(--gold)}}
:root[data-theme="dark"] .btn.primary{background:var(--gold);color:var(--kiswa);border-color:var(--gold)}
.btn:focus-visible{outline:2px solid var(--gold);outline-offset:3px}
.btn:disabled{opacity:.4;cursor:default}

/* Start card */
.card.intro h2{font-size:clamp(2.1rem,7vw,2.9rem);margin-bottom:8px;line-height:1.1}
.seg{display:inline-flex;border:1px solid var(--rule);border-radius:999px;overflow:hidden;margin:0 0 16px}
.seg button{all:unset;cursor:pointer;padding:7px 16px;font-size:.92rem}
.seg button[aria-pressed="true"]{background:var(--kiswa);color:var(--gold-hi)}
.seg button:focus-visible{outline:2px solid var(--gold);outline-offset:-2px}
.q{font-weight:600;margin:0 0 8px !important}
.choice{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.opt{all:unset;cursor:pointer;border:1px solid var(--rule);border-radius:6px;padding:12px 14px;display:flex;flex-direction:column;gap:3px}
.opt-t{font-family:var(--f-disp);font-size:1.25rem}
.opt-n{font-size:.84rem;color:var(--ink-2);line-height:1.35}
.opt[aria-pressed="true"]{border-color:var(--gold);box-shadow:inset 0 0 0 1px var(--gold);background:var(--dua-bg)}
.opt:focus-visible{outline:2px solid var(--gold);outline-offset:2px}
.fine{font-size:.86rem !important;color:var(--ink-2)}

.fade{position:fixed;inset:0;z-index:20;background:var(--kiswa);opacity:0;pointer-events:none;transition:opacity .4s}
.fade.on{opacity:1}
.loading{position:fixed;inset:0;z-index:30;background:var(--kiswa);display:grid;place-items:center;color:var(--gold-hi);font-family:var(--f-disp);font-size:1.2rem;text-align:center;padding:24px}
.loading p{margin:0;border-top:3px double var(--gold);border-bottom:3px double var(--gold);padding:12px 6px}

body.bn .obj-t,body.bn .card-b p{line-height:1.7}

.mapbox{position:fixed;z-index:4;left:max(14px,env(safe-area-inset-left,0px));bottom:calc(env(safe-area-inset-bottom,0px) + 16px);width:160px;pointer-events:none;text-align:center}
.mapbox canvas{display:block;width:160px;height:160px;border-radius:50%;box-shadow:var(--shadow);border:3px solid var(--kiswa);outline:1px solid var(--gold);outline-offset:-1px;background:#e9e3d4}
.map-gate{display:inline-block;margin-top:7px;background:var(--kiswa);color:var(--gold-hi);border:1px solid var(--gold);border-radius:14px;padding:3px 11px 4px;font-size:.82rem;font-weight:600;line-height:1.25;max-width:100%}
.map-dist{margin-top:5px;display:flex;flex-direction:column;align-items:center;gap:3px}
.map-dist span{background:var(--panel);color:var(--ink);border:1px solid var(--rule);border-radius:12px;padding:2px 9px;font-size:.78rem;font-weight:600;box-shadow:var(--shadow);white-space:nowrap}
.map-dist span.go{background:var(--dome);color:#fff;border-color:var(--dome)}
.c-km{font-size:.78rem;font-weight:600;color:var(--hint);margin-top:2px}
.choice.list{grid-template-columns:1fr}
.choice.list .opt{padding:10px 14px}
.map-gate b{font-family:var(--f-disp);font-weight:400;font-size:1rem;margin-left:3px}
@media (max-width:760px),(pointer:coarse){
  .mapbox{bottom:auto;top:calc(env(safe-area-inset-top,0px) + 176px);left:max(10px,env(safe-area-inset-left,0px));width:112px}
  .mapbox canvas{width:112px;height:112px}
  .map-gate{font-size:.74rem;padding:2px 8px 3px}
  .map-dist span{font-size:.7rem;padding:2px 7px}
}


@media (max-width:760px){
  .bottom{width:calc(100% - 24px);min-width:0;bottom:calc(env(safe-area-inset-bottom,0px) + 150px)}
}
@media (max-width:640px){
  .brand{display:none}
  .steps button{padding:7px 8px;font-size:.84rem}
  .objective{top:calc(env(safe-area-inset-top,0px) + 62px)}
  .obj-t{font-size:.97rem}
  .counter{width:84px;border-radius:42px 42px 9px 9px;top:calc(env(safe-area-inset-top,0px) + 176px);padding:7px 6px 8px}
  .counter svg{width:70px;height:70px}
  .c-num{height:70px;top:7px;font-size:1.15rem}
  .c-lab{font-size:.72rem}
  .card{padding:18px 17px 16px}
  .card h2{font-size:1.45rem}
  .dua .ar{font-size:1.5rem}
  .choice{grid-template-columns:1fr}
  .pray{bottom:calc(env(safe-area-inset-bottom,0px) + 190px)}
}
@media (prefers-reduced-motion:reduce){
  .toast,.fade{transition:none}
  .ctl.nudge{animation:none;box-shadow:0 0 0 3px var(--gold)}
  .act,.dua.said{animation:none}
}
</style>
</head>
<body>
<canvas id="c" aria-label="3D view of the simulation"></canvas>

<div id="hud" hidden>
  <header class="hizam">
    <div class="hizam-in">
      <div class="brand" id="brand">Umrah</div>
      <ol class="steps" id="steps"></ol>
      <button class="icon-btn" id="helpBtn" aria-label="Controls">?</button>
      <div class="langs" role="group" aria-label="Language">
        <button data-lang="en" aria-pressed="true">EN</button><button data-lang="bn" aria-pressed="false">বাংলা</button>
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
  <div class="mapbox" id="mapBox" hidden><canvas id="map" role="img" aria-label="Map"></canvas><div class="map-gate" id="mapGate" hidden></div><div class="map-dist" id="mapDist"></div></div>
  <div class="joy-hint" id="joyHint" hidden></div>
  <div class="joy" id="joy" hidden><div class="knob" id="knob"></div></div>
  <div class="pray" id="pray" hidden></div>
</div>

<div class="modal" id="modal" hidden>
  <div class="card" id="card" role="dialog" aria-modal="true" aria-labelledby="cardTitle">
    <p class="card-k" id="cardK"></p>
    <h2 id="cardTitle"></h2>
    <div class="card-b" id="cardBody"></div>
    <div class="card-btns" id="cardBtns"></div>
  </div>
</div>
<div class="fade" id="fade"></div>
<div class="loading" id="loading"><p id="loadMsg">Preparing Masjid al-Haram</p></div>

<script>
    // ১. রাইট ক্লিক সম্পূর্ণ বন্ধ করা
    document.addEventListener('contextmenu', event => event.preventDefault());

    // ২. ডেভলপার শর্টকাটগুলো (F12, Ctrl+Shift+I ইত্যাদি) ব্লক করা
    document.onkeydown = function(e) {
        if (e.keyCode == 123) { return false; } // F12
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) { return false; }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) { return false; }
        if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) { return false; }
        if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) { return false; } // View Source
    };

    // ৩. IVAC-এর মতো অ্যান্টি-ডেবাগার লুপ (ব্রাউজার ফ্রিজ করার জন্য)
    (function() {
        function createDebuggerLoop() {
            function debuggerFunction() {
                // কেউ ইন্সপেক্ট খুললেই এই 'debugger' কোডটি রান করবে এবং সাইট পজ (Pause) হয়ে যাবে
                debugger; 
                setTimeout(debuggerFunction, 100);
            }
            try {
                debuggerFunction();
            } catch (err) {}
        }
        // প্রতি ১০০ মিলি-সেকেন্ড পর পর চেক করবে ইন্সপেক্ট খোলা হয়েছে কিনা
        setInterval(createDebuggerLoop, 100);
    })();
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function(){
'use strict';

/* ================= Text (English / Bangla) ================= */
const STR = {
en: {
 brand:`Umrah`, title:`Umrah Simulator`,
 sub:`Walk the real route from your hotel to the Kaaba, then perform every step of Umrah inside Masjid al-Haram.`,
 langLabel:`Language`, performAs:`Who is performing Umrah?`, man:`Man`, woman:`Woman`,
 manNote:`Includes Idtiba, Raml and jogging between the green lights`,
 womanNote:`Walks at a normal pace throughout`,
 begin:`Begin with Ihram`,
 disclaimer:`This is a learning aid. Some rulings differ between madhabs, so follow your scholar or group guide on the details.`,
 ctrlTitle:`Controls`,
 ctrlDesk:`Move with W A S D or the arrow keys. Hold Shift, or the Walk briskly button, for Raml. Press E for the gold action button. Drag to look around and scroll to zoom.`,
 ctrlTouch:`Put your left thumb anywhere on the left side of the screen to move. Drag on the right side to look around, and pinch to zoom. Hold Walk briskly for Raml.`,
 ctrlAuto:`Auto-walk takes you along the correct route, including the direction of Tawaf.`,
 steps_ihram:`Ihram`, steps_tawaf:`Tawaf`, steps_maqam:`Maqam`, steps_zamzam:`Zamzam`, steps_sai:`Sa'i`, steps_halq:`Halq`, steps_gate:`Gate`,
 stepsLabel:`Steps of Umrah`, now:`Now`, move:`Move`,
 auto:`Auto-walk`, run:`Walk briskly`, help:`Controls`,
 cont:`Continue`, close:`Close`, learn:`Tap to read about it`, nearby:`You are near`,
 c_tawaf:`Tawaf round`, c_sai:`Sa'i lap`, place_safa:`Safa`, place_marwa:`Marwa`,
 obj_tawaf_start:`Walk to the Black Stone corner. Follow the gold arrow.`,
 obj_on_line:`You're on the starting line. Face the Black Stone and begin Tawaf.`,
 obj_tawaf:`Round {n} of 7. Keep the Kaaba on your left.`,
 obj_maqam:`Go behind Maqam Ibrahim and pray 2 rakats.`,
 obj_zamzam:`Go to the Zamzam station and drink.`,
 obj_to_sai:`Walk to Bab as-Safa to begin Sa'i.`,
 obj_sai_safa:`You're on Safa. Face the Kaaba and begin Sa'i.`,
 obj_sai:`Lap {n} of 7. Walk to {dest}.`,
 obj_free:`Walk freely. Use the steps bar to practise any part again.`,
 act_begin_tawaf:`Begin Tawaf`, act_istilam:`Make Istilam`, act_pray:`Pray 2 rakats`, act_drink:`Drink Zamzam`, act_gate:`Go to Sa'i`,
 act_safa:`Recite on Safa`, act_dhikr_safa:`Dhikr on Safa`, act_dhikr_marwa:`Dhikr on Marwa`,
 act_enter:`Enter with right foot`, act_salam:`Give salam`, act_exit:`Exit with left foot`,
 t_round:`Round {n} done. Make Istilam toward the Black Stone.`,
 t_wrong:`Wrong way. Tawaf goes anticlockwise, with the Kaaba on your left.`,
 t_tawaf_done:`Tawaf complete. Alhamdulillah.`,
 t_idtiba:`Idtiba: keep your right shoulder uncovered for all 7 rounds.`,
 t_cover:`Cover your right shoulder again before you pray.`,
 t_lap:`Lap {n} done. You reached {dest}.`,
 t_sai_done:`Sa'i complete: 7 laps, finishing on Marwa.`,
 t_prayed:`Prayer complete.`,
 hint_raml:`Raml: walk briskly with short steps in rounds 1 to 3.`,
 hint_green_m:`Green lights: men jog lightly between them.`,
 hint_green_f:`Green lights: women keep walking at a normal pace.`,
 hint_yamani:`Between Rukn Yamani and the Black Stone, recite`,
 hint_green_dua:`Between the green lights, many recite`,
 pr_rak1:`First rakat`, pr_rak2:`Second rakat`, pr_qiyam:`Standing (Qiyam)`, pr_ruku:`Bowing (Ruku)`, pr_sujood:`Prostration (Sujood)`,
 pr_jalsa:`Sitting between the two sujood`, pr_tash:`Tashahhud`, pr_salam:`Salam to the right and left`,
 c_ih1_t:`Before the Miqat: put on Ihram`,
 c_ih1_m:`Clip your nails, groom, and take a ghusl. Wear two unstitched white sheets: the izar around your waist and the rida over your shoulders, with sandals that leave the top of the foot bare. If it isn't a disliked prayer time, pray 2 rakats for Ihram.`,
 c_ih1_f:`Clip your nails, groom, and take a ghusl. Wear your normal modest clothes in any colour, and keep your face and hands uncovered while in Ihram. If it isn't a disliked prayer time, pray 2 rakats for Ihram. A woman on her period can still enter Ihram, but waits until she is pure before Tawaf.`,
 c_flight:`Flying from Dhaka? Put on Ihram before boarding or before the Miqat. The crew announces when the plane is approaching it. Don't wait until Jeddah.`,
 c_ih2_t:`At the Miqat: make your intention`,
 c_ih2_a:`Make the intention for Umrah in your heart and say:`,
 c_ih2_b:`Then begin the Talbiyah, men aloud and women quietly. Keep reciting it until you start Tawaf.`,
 c_talb_btn:`Recite Talbiyah ({n}/3)`,
 c_ih3_t:`While in Ihram, avoid`,
 c_ih3_list:[`Perfume, and scented soap or oil`,`Cutting hair or nails`,`Marital relations`,`Arguing, fighting and bad language`,`Hunting`],
 c_ih3_m:`Stitched clothing, and covering your head`,
 c_ih3_f:`Niqab and gloves`,
 c_wudu:`You need wudu for Tawaf, so renew it before you enter the mosque.`,
 c_arrive:`Arrive in Makkah`,
 c_enter_k:`Masjid al-Haram, Makkah`, c_enter_t:`Entering Masjid al-Haram`,
 c_enter_a:`Step in with your right foot first and recite:`,
 c_enter_b:`When you first see the Kaaba, pause and make heartfelt dua.`,
 c_step_in:`Step inside`,
 c_tb_t:`Begin Tawaf`,
 c_tb_a:`Make the intention for Tawaf in your heart and stop the Talbiyah. Face the Black Stone, turn your palms toward it, and say:`,
 c_tb_b:`Kiss or touch the stone only if you can reach it without pushing anyone. Now turn so the Kaaba is on your left and walk. Make Istilam each time you pass this line.`,
 c_tb_m:`Men walk briskly (Raml) in the first 3 rounds.`,
 c_tb_go:`Start walking`,
 c_mq_t:`Two rakats behind Maqam Ibrahim`,
 c_mq_a:`Recite this verse, then pray 2 rakats behind Maqam Ibrahim. If it's crowded, anywhere in the mosque is fine.`,
 c_mq_b:`It is sunnah to recite Surah al-Kafirun in the first rakat and Surah al-Ikhlas in the second.`,
 c_start_prayer:`Start the prayer`,
 c_zz_t:`Drink Zamzam`,
 c_zz_a:`Face the qibla, say Bismillah, and drink in three sips. This is a good time to ask Allah for what you need:`,
 c_done:`Done`,
 c_sf_k:`The Mas'a`, c_sf_t:`On Safa`,
 c_sf_a:`At the start of Sa'i only, recite:`,
 c_sf_b:`Face the Kaaba, raise your hands as in dua, and say:`,
 c_sf_c:`Then walk to Marwa. Safa to Marwa is one lap, so after 7 laps you finish on Marwa.`,
 c_sf_go:`Begin Sa'i`,
 c_dk_t:`On {dest}`,
 c_dk_a:`Face the Kaaba, raise your hands, and repeat this dhikr along with your own duas.`,
 c_hq_t:`Halq or Taqsir: leaving Ihram`,
 c_hq_m:`To finish Umrah, shave your head (Halq), which is more virtuous, or shorten the hair from all over your head (Taqsir).`,
 c_hq_f:`To finish Umrah, cut about a fingertip's length, roughly 2 cm, from the ends of your hair. Your mahram or another woman can do this for you in private.`,
 c_hq_shave:`Halq: shave`, c_hq_trim:`Taqsir: trim`, c_hq_cut:`Trim my hair`,
 c_ud_t:`Alhamdulillah, your Umrah is complete`,
 c_ud_a:`The restrictions of Ihram are now lifted. May Allah accept it from you.`,
 c_ud_b:`Use Walk freely to explore the gates and routes again, or the steps bar at the top to repeat any part.`, c_restart:`Start again`,
 c_free:`Walk freely`,
 gateNo:`Gate {n}`, yourGate:`Your gate`, mapLabel:`Map`,
 c_rt_k:`Arriving in Makkah`, c_rt_t:`Where are you walking from?`,
 c_rt_a:`These are the main walking routes into Masjid al-Haram. Each ends at an official entrance to the Tawaf area, and the distances shown are real walking distances.`,
 c_rt_note:`On the day, gates can close when the mosque is full, and they change during Ramadan crowd control. Follow the green "open" signs and the guards' directions.`,
 c_rt_go:`Start walking`,
 obj_plaza:`Cross the plaza to {gate}, Gate {n}.`,
 obj_gate:`You're at {gate}, Gate {n}. Enter with your right foot.`,
 obj_inside:`Follow the Mataf signs through the halls to the Kaaba.`,
 obj_to_safa:`Follow the Safa signs through the colonnade to the Mas'a.`,
 obj_safa_turn:`You're in the Mas'a. Turn right and walk up to Safa.`,
 t_firstsight:`Your first sight of the Kaaba. Pause and make heartfelt dua.`,
 c_gate_note:`This is Gate {n}, {gate}. Note the number: it's how you find your way back to your hotel after Umrah.`,
 c_hq_exit:`After Sa'i, most pilgrims leave from the Marwa end through Al-Marwah Gate. Barbers are outside the mosque near the Marwa exit.`,
 rtA_t:`Ibrahim Al Khalil Road (Misfalah)`, rtA_n:`South-west. Hotels and shops such as Emaar Al Khalil and Makkah Hotel. About 0.8 km to King Abdulaziz Gate, Gate 1, a 10 to 15 minute walk.`,
 rtB_t:`Ajyad Street`, rtB_n:`South-east. Hotels such as Al Safwah Royale Orchid, Elaf Ajyad and Makarem Ajyad. About 0.7 km to King Abdulaziz Gate, Gate 1, an 8 to 12 minute walk.`,
 rtC_t:`Jabal Omar`, rtC_n:`West. Hotel towers such as Hyatt Regency, Jumeirah, Address and Hilton Suites. About 0.5 km to King Fahd Gate, Gate 79, a 5 to 10 minute walk.`,
 rtD_t:`Jarwal`, rtD_n:`North-west. Hotels such as Anjum, Al Kiswah Towers and Emaar Andalusia. About 1 km to Umrah Gate, Gate 62.`,
 rtE_t:`Masjid al-Jinn (Al-Hajun)`, rtE_n:`North-east, past Jannat al-Mu'alla. About 0.9 km to Al-Fath Gate, Gate 45.`,
 obj_road_A:`Walk north along Ibrahim Al Khalil Road. The Clock Tower is ahead on your right.`,
 obj_road_B:`Walk north along Ajyad Street. The Clock Tower is on your left.`,
 obj_road_C:`Walk east through Jabal Omar toward the mosque. The Clock Tower is ahead on your right.`,
 obj_road_D:`Walk south-east from Jarwal toward the mosque. The Clock Tower is ahead, slightly to the right.`,
 obj_road_E:`Walk south from Masjid al-Jinn along Masjid al-Haram Road. The Clock Tower is straight ahead, beyond the mosque.`,
 view_1:`Coming out of Gate 1, you face the Clock Tower. Ibrahim Al Khalil Road runs along its right-hand side and Ajyad Street along its left.`,
 view_79:`Coming out of Gate 79, the Jabal Omar towers are straight ahead and the Clock Tower is on your left.`,
 view_62:`Coming out of Gate 62, the road to Jarwal is straight ahead, the Jabal Omar towers are on your left, and the Clock Tower is behind you on the left.`,
 view_45:`Coming out of Gate 45, Masjid al-Haram Road towards Al-Ma'la and Masjid al-Jinn is ahead on your right, and the Clock Tower is behind you.`,
 view_100:`Coming out of Gate 100, the King Abdullah expansion is around you, the Jarwal road is ahead on your left, and the Clock Tower is behind you.`,
 c_land_tip:`The Clock Tower can be seen from almost everywhere and always marks the south side. Use it to get your bearings.`,
 c_way:`Your way back: you came in by Gate {n}, {gate}. Sa'i ends at the Marwa end on the north-east side, so walk round the outside of the mosque to Gate {n}.`,
 unit_m:`m`, unit_km:`km`, d_walked:`Walked {d}`, d_gate:`Gate {n}: {d}`, d_mataf:`Mataf: {d}`, d_of:`{d} of {t}`,
 loading:`Preparing Masjid al-Haram`,
 loadFail:`The 3D engine didn't load. Check your internet connection, then reload the page.`
},
bn: {
 brand:`উমরাহ`, title:`উমরাহ সিমুলেটর`,
 sub:`হোটেল থেকে কাবা পর্যন্ত বাস্তব পথ ধরে হেঁটে যান, তারপর মসজিদুল হারামের ভেতরে উমরাহর প্রতিটি ধাপ পালন করুন।`,
 langLabel:`ভাষা`, performAs:`কে উমরাহ করছেন?`, man:`পুরুষ`, woman:`নারী`,
 manNote:`ইযতিবা, রমল এবং সবুজ বাতির মাঝে হালকা দৌড়সহ`,
 womanNote:`পুরো সময় স্বাভাবিক গতিতে হাঁটা`,
 begin:`ইহরাম দিয়ে শুরু করুন`,
 disclaimer:`এটি একটি শেখার সহায়ক। মাযহাবভেদে কিছু মাসআলায় পার্থক্য আছে, তাই বিস্তারিত বিষয়ে আপনার আলেম বা গ্রুপ গাইডের নির্দেশনা মেনে চলুন।`,
 ctrlTitle:`নিয়ন্ত্রণ`,
 ctrlDesk:`W A S D বা অ্যারো কি দিয়ে চলুন। রমলের জন্য Shift অথবা "দ্রুত হাঁটা" বোতাম চেপে ধরুন। সোনালি কাজের বোতামের জন্য E চাপুন। চারপাশ দেখতে ড্র্যাগ করুন, জুম করতে স্ক্রল করুন।`,
 ctrlTouch:`চলার জন্য স্ক্রিনের বাম পাশের যেকোনো জায়গায় বাম আঙুল রাখুন। চারপাশ দেখতে ডান পাশে টানুন, জুম করতে দুই আঙুলে চিমটি দিন। রমলের জন্য "দ্রুত হাঁটা" চেপে ধরুন।`,
 ctrlAuto:`অটো-হাঁটা আপনাকে সঠিক পথে নিয়ে যাবে, তাওয়াফের দিকসহ।`,
 steps_ihram:`ইহরাম`, steps_tawaf:`তাওয়াফ`, steps_maqam:`মাকাম`, steps_zamzam:`জমজম`, steps_sai:`সাঈ`, steps_halq:`হলক`, steps_gate:`গেট`,
 stepsLabel:`উমরাহর ধাপ`, now:`এখন`, move:`চলুন`,
 auto:`অটো-হাঁটা`, run:`দ্রুত হাঁটা`, help:`নিয়ন্ত্রণ`,
 cont:`এগিয়ে যান`, close:`বন্ধ করুন`, learn:`বিস্তারিত পড়তে চাপুন`, nearby:`আপনি এর কাছে আছেন`,
 c_tawaf:`তাওয়াফের চক্কর`, c_sai:`সাঈর চক্কর`, place_safa:`সাফা`, place_marwa:`মারওয়া`,
 obj_tawaf_start:`হাজরে আসওয়াদের কোণে যান। সোনালি তীর অনুসরণ করুন।`,
 obj_on_line:`আপনি শুরুর লাইনে আছেন। হাজরে আসওয়াদের দিকে মুখ করে তাওয়াফ শুরু করুন।`,
 obj_tawaf:`৭ চক্করের মধ্যে {n} নম্বর। কাবাকে বাম পাশে রাখুন।`,
 obj_maqam:`মাকামে ইবরাহিমের পেছনে গিয়ে দুই রাকাত নামাজ পড়ুন।`,
 obj_zamzam:`জমজমের স্থানে গিয়ে পান করুন।`,
 obj_to_sai:`সাঈ শুরু করতে বাবুস সাফার দিকে হাঁটুন।`,
 obj_sai_safa:`আপনি সাফায় আছেন। কাবার দিকে মুখ করে সাঈ শুরু করুন।`,
 obj_sai:`৭ চক্করের মধ্যে {n} নম্বর। {dest}র দিকে হাঁটুন।`,
 obj_free:`নিজের মতো ঘুরে দেখুন। যেকোনো অংশ আবার অনুশীলন করতে ওপরের ধাপগুলো ব্যবহার করুন।`,
 act_begin_tawaf:`তাওয়াফ শুরু করুন`, act_istilam:`ইস্তিলাম করুন`, act_pray:`দুই রাকাত নামাজ`, act_drink:`জমজম পান করুন`, act_gate:`সাঈতে যান`,
 act_safa:`সাফায় পড়ুন`, act_dhikr_safa:`সাফায় জিকির`, act_dhikr_marwa:`মারওয়ায় জিকির`,
 act_enter:`ডান পা দিয়ে প্রবেশ`, act_salam:`সালাম দিন`, act_exit:`বাম পা দিয়ে বের হন`,
 t_round:`{n} নম্বর চক্কর শেষ। হাজরে আসওয়াদের দিকে ইস্তিলাম করুন।`,
 t_wrong:`ভুল দিক। তাওয়াফ ঘড়ির কাঁটার উল্টো দিকে, কাবা থাকবে বাম পাশে।`,
 t_tawaf_done:`তাওয়াফ সম্পন্ন। আলহামদুলিল্লাহ।`,
 t_idtiba:`ইযতিবা: সাত চক্করেই ডান কাঁধ খোলা রাখুন।`,
 t_cover:`নামাজের আগে ডান কাঁধ আবার ঢেকে নিন।`,
 t_lap:`{n} নম্বর চক্কর শেষ। আপনি {dest}য় পৌঁছেছেন।`,
 t_sai_done:`সাঈ সম্পন্ন: ৭ চক্কর, মারওয়ায় শেষ।`,
 t_prayed:`নামাজ সম্পন্ন।`,
 hint_raml:`রমল: ১ থেকে ৩ নম্বর চক্করে ছোট ছোট কদমে দ্রুত হাঁটুন।`,
 hint_green_m:`সবুজ বাতি: পুরুষরা এর মাঝে হালকা দৌড়ান।`,
 hint_green_f:`সবুজ বাতি: নারীরা স্বাভাবিক গতিতেই হাঁটবেন।`,
 hint_yamani:`রুকনে ইয়ামানি ও হাজরে আসওয়াদের মাঝে পড়ুন`,
 hint_green_dua:`সবুজ বাতির মাঝে অনেকে পড়েন`,
 pr_rak1:`প্রথম রাকাত`, pr_rak2:`দ্বিতীয় রাকাত`, pr_qiyam:`দাঁড়ানো (কিয়াম)`, pr_ruku:`রুকু`, pr_sujood:`সিজদা`,
 pr_jalsa:`দুই সিজদার মাঝে বসা`, pr_tash:`তাশাহহুদ`, pr_salam:`ডানে ও বামে সালাম`,
 c_ih1_t:`মিকাতের আগে: ইহরাম পরুন`,
 c_ih1_m:`নখ কাটুন, পরিচ্ছন্ন হয়ে গোসল করুন। দুটি সেলাইবিহীন সাদা চাদর পরুন: কোমরে ইজার আর কাঁধে রিদা, এবং এমন স্যান্ডেল যাতে পায়ের ওপরের অংশ খোলা থাকে। মাকরুহ সময় না হলে ইহরামের দুই রাকাত নামাজ পড়ুন।`,
 c_ih1_f:`নখ কাটুন, পরিচ্ছন্ন হয়ে গোসল করুন। যেকোনো রঙের স্বাভাবিক শালীন পোশাক পরুন, আর ইহরাম অবস্থায় মুখ ও হাত খোলা রাখুন। মাকরুহ সময় না হলে ইহরামের দুই রাকাত নামাজ পড়ুন। মাসিক চলাকালেও ইহরাম বাঁধা যায়, তবে পবিত্র হওয়া পর্যন্ত তাওয়াফের জন্য অপেক্ষা করতে হবে।`,
 c_flight:`ঢাকা থেকে বিমানে যাচ্ছেন? বিমানে ওঠার আগে বা মিকাতের আগেই ইহরাম পরে নিন। বিমান মিকাতের কাছে এলে ক্রু ঘোষণা দেন। জেদ্দা পৌঁছানো পর্যন্ত অপেক্ষা করবেন না।`,
 c_ih2_t:`মিকাতে: নিয়ত করুন`,
 c_ih2_a:`মনে মনে উমরাহর নিয়ত করুন এবং বলুন:`,
 c_ih2_b:`তারপর তালবিয়া পড়া শুরু করুন; পুরুষরা উচ্চস্বরে, নারীরা নিচু স্বরে। তাওয়াফ শুরু করা পর্যন্ত পড়তে থাকুন।`,
 c_talb_btn:`তালবিয়া পড়ুন ({n}/৩)`,
 c_ih3_t:`ইহরাম অবস্থায় যা থেকে বিরত থাকবেন`,
 c_ih3_list:[`সুগন্ধি, সুগন্ধযুক্ত সাবান বা তেল`,`চুল বা নখ কাটা`,`স্বামী-স্ত্রীর মিলন`,`ঝগড়া-বিবাদ ও খারাপ কথা`,`শিকার করা`],
 c_ih3_m:`সেলাই করা পোশাক পরা এবং মাথা ঢাকা`,
 c_ih3_f:`নিকাব ও হাতমোজা পরা`,
 c_wudu:`তাওয়াফের জন্য অজু লাগবে, তাই মসজিদে প্রবেশের আগে অজু করে নিন।`,
 c_arrive:`মক্কায় পৌঁছান`,
 c_enter_k:`মসজিদুল হারাম, মক্কা`, c_enter_t:`মসজিদুল হারামে প্রবেশ`,
 c_enter_a:`ডান পা দিয়ে প্রবেশ করুন এবং পড়ুন:`,
 c_enter_b:`প্রথমবার কাবা দেখে একটু থামুন এবং আন্তরিকভাবে দোয়া করুন।`,
 c_step_in:`ভেতরে প্রবেশ করুন`,
 c_tb_t:`তাওয়াফ শুরু`,
 c_tb_a:`মনে মনে তাওয়াফের নিয়ত করুন এবং তালবিয়া বন্ধ করুন। হাজরে আসওয়াদের দিকে মুখ করে হাতের তালু এর দিকে ফিরিয়ে বলুন:`,
 c_tb_b:`কাউকে ধাক্কা না দিয়ে পৌঁছানো গেলেই কেবল পাথরে চুমু দিন বা স্পর্শ করুন। এবার কাবাকে বাম পাশে রেখে হাঁটুন। প্রতিবার এই লাইন পার হওয়ার সময় ইস্তিলাম করুন।`,
 c_tb_m:`পুরুষরা প্রথম ৩ চক্করে দ্রুত হাঁটবেন (রমল)।`,
 c_tb_go:`হাঁটা শুরু করুন`,
 c_mq_t:`মাকামে ইবরাহিমের পেছনে দুই রাকাত`,
 c_mq_a:`এই আয়াতটি পড়ুন, তারপর মাকামে ইবরাহিমের পেছনে দুই রাকাত নামাজ পড়ুন। ভিড় বেশি হলে মসজিদের যেকোনো জায়গায় পড়া যায়।`,
 c_mq_b:`প্রথম রাকাতে সূরা কাফিরুন এবং দ্বিতীয় রাকাতে সূরা ইখলাস পড়া সুন্নত।`,
 c_start_prayer:`নামাজ শুরু করুন`,
 c_zz_t:`জমজম পান`,
 c_zz_a:`কিবলামুখী হয়ে বিসমিল্লাহ বলে তিন শ্বাসে পান করুন। এ সময় আল্লাহর কাছে নিজের প্রয়োজনের কথা চাওয়া উত্তম:`,
 c_done:`সম্পন্ন`,
 c_sf_k:`মাসআ`, c_sf_t:`সাফায়`,
 c_sf_a:`শুধু সাঈর শুরুতে পড়ুন:`,
 c_sf_b:`কাবার দিকে মুখ করে দোয়ার মতো হাত তুলে বলুন:`,
 c_sf_c:`তারপর মারওয়ার দিকে হাঁটুন। সাফা থেকে মারওয়া এক চক্কর, তাই ৭ চক্কর শেষে আপনি মারওয়ায় থাকবেন।`,
 c_sf_go:`সাঈ শুরু করুন`,
 c_dk_t:`{dest}য়`,
 c_dk_a:`কাবার দিকে মুখ করে হাত তুলুন এবং এই জিকিরের সঙ্গে নিজের দোয়াগুলো করুন।`,
 c_hq_t:`হলক বা কসর: ইহরাম থেকে মুক্ত হওয়া`,
 c_hq_m:`উমরাহ শেষ করতে মাথা মুণ্ডন করুন (হলক), যা বেশি ফজিলতপূর্ণ, অথবা পুরো মাথা থেকে চুল ছোট করুন (কসর)।`,
 c_hq_f:`উমরাহ শেষ করতে চুলের আগা থেকে আঙুলের এক কর পরিমাণ, প্রায় ২ সেমি, কেটে ফেলুন। আপনার মাহরাম বা অন্য কোনো নারী আড়ালে এটি করে দিতে পারেন।`,
 c_hq_shave:`হলক: মুণ্ডন`, c_hq_trim:`কসর: ছোট করা`, c_hq_cut:`চুল ছোট করুন`,
 c_ud_t:`আলহামদুলিল্লাহ, আপনার উমরাহ সম্পন্ন হয়েছে`,
 c_ud_a:`এখন ইহরামের সব বিধিনিষেধ শেষ। আল্লাহ আপনার উমরাহ কবুল করুন।`,
 c_ud_b:`গেট ও পথগুলো আবার ঘুরে দেখতে "নিজের মতো ঘুরে দেখুন" বেছে নিন, অথবা যেকোনো অংশ আবার করতে ওপরের ধাপগুলো ব্যবহার করুন।`, c_restart:`আবার শুরু করুন`,
 c_free:`নিজের মতো ঘুরে দেখুন`,
 gateNo:`গেট নম্বর {n}`, yourGate:`আপনার গেট`, mapLabel:`মানচিত্র`,
 c_rt_k:`মক্কায় পৌঁছে`, c_rt_t:`কোথা থেকে হেঁটে আসবেন?`,
 c_rt_a:`মসজিদুল হারামে হেঁটে যাওয়ার প্রধান পথগুলো এখানে। প্রতিটি পথ শেষ হয়েছে তাওয়াফ এলাকার একটি অফিসিয়াল প্রবেশপথে, আর দূরত্বগুলো বাস্তব হাঁটার দূরত্ব।`,
 c_rt_note:`বাস্তবে মসজিদ পূর্ণ হলে গেট বন্ধ হতে পারে, আর রমজানে ভিড় নিয়ন্ত্রণের জন্য গেট বদলায়। সবুজ "খোলা" চিহ্ন ও নিরাপত্তাকর্মীদের নির্দেশনা মেনে চলুন।`,
 c_rt_go:`হাঁটা শুরু করুন`,
 obj_plaza:`চত্বর পেরিয়ে {gate}-এ যান, গেট নম্বর {n}।`,
 obj_gate:`আপনি {gate}-এ আছেন, গেট নম্বর {n}। ডান পা দিয়ে প্রবেশ করুন।`,
 obj_inside:`হলঘরের ভেতর দিয়ে "মাতাফ" চিহ্ন ধরে কাবার দিকে যান।`,
 obj_to_safa:`"সাফা" চিহ্ন ধরে স্তম্ভসারি পেরিয়ে মাসআর দিকে যান।`,
 obj_safa_turn:`আপনি মাসআয় পৌঁছেছেন। ডানে ঘুরে সাফার দিকে যান।`,
 t_firstsight:`এই প্রথম কাবা দেখছেন। একটু থেমে আন্তরিকভাবে দোয়া করুন।`,
 c_gate_note:`এটি গেট নম্বর {n}, {gate}। নম্বরটি মনে রাখুন; উমরাহ শেষে হোটেলে ফেরার পথ খুঁজতে এটিই কাজে লাগবে।`,
 c_hq_exit:`সাঈর পর বেশিরভাগ মানুষ মারওয়ার দিক থেকে বাবুল মারওয়া দিয়ে বের হন। মারওয়ার বের হওয়ার পথের কাছে মসজিদের বাইরে নাপিতের দোকান আছে।`,
 rtA_t:`ইবরাহিম খলিল রোড (মিসফালাহ)`, rtA_n:`দক্ষিণ-পশ্চিম। ইমার আল খলিল ও মক্কা হোটেলের মতো হোটেল আর দোকান। বাদশাহ আবদুল আজিজ গেট (গেট ১) পর্যন্ত প্রায় ০.৮ কিমি, ১০ থেকে ১৫ মিনিটের হাঁটা।`,
 rtB_t:`আজিয়াদ স্ট্রিট`, rtB_n:`দক্ষিণ-পূর্ব। আল সাফওয়া রয়্যাল অর্কিড, ইলাফ আজিয়াদ ও মাকারেম আজিয়াদের মতো হোটেল। বাদশাহ আবদুল আজিজ গেট (গেট ১) পর্যন্ত প্রায় ০.৭ কিমি, ৮ থেকে ১২ মিনিটের হাঁটা।`,
 rtC_t:`জাবালে ওমর`, rtC_n:`পশ্চিম। হায়াত রিজেন্সি, জুমেইরা, অ্যাড্রেস ও হিলটন স্যুটসের মতো হোটেল টাওয়ার। বাদশাহ ফাহাদ গেট (গেট ৭৯) পর্যন্ত প্রায় ০.৫ কিমি, ৫ থেকে ১০ মিনিটের হাঁটা।`,
 rtD_t:`জারওয়াল`, rtD_n:`উত্তর-পশ্চিম। আনজুম, আল কিসওয়াহ টাওয়ার্স ও ইমার আন্দালুসিয়ার মতো হোটেল। বাবুল উমরাহ (গেট ৬২) পর্যন্ত প্রায় ১ কিমি।`,
 rtE_t:`মসজিদে জিন (আল-হাজুন)`, rtE_n:`উত্তর-পূর্ব, জান্নাতুল মুআল্লার পাশ দিয়ে। বাবুল ফাতহ (গেট ৪৫) পর্যন্ত প্রায় ০.৯ কিমি।`,
 obj_road_A:`ইবরাহিম খলিল রোড ধরে উত্তরে হাঁটুন। ক্লক টাওয়ার সামনে ডান দিকে।`,
 obj_road_B:`আজিয়াদ স্ট্রিট ধরে উত্তরে হাঁটুন। ক্লক টাওয়ার আপনার বাম দিকে।`,
 obj_road_C:`জাবালে ওমরের ভেতর দিয়ে পূর্বে মসজিদের দিকে হাঁটুন। ক্লক টাওয়ার সামনে ডান দিকে।`,
 obj_road_D:`জারওয়াল থেকে দক্ষিণ-পূর্বে মসজিদের দিকে হাঁটুন। ক্লক টাওয়ার সামনে, একটু ডান দিকে।`,
 obj_road_E:`মসজিদে জিন থেকে মসজিদুল হারাম রোড ধরে দক্ষিণে হাঁটুন। ক্লক টাওয়ার সোজা সামনে, মসজিদের ওপারে।`,
 view_1:`গেট ১ দিয়ে বের হলে সামনে ক্লক টাওয়ার। এর ডান পাশ দিয়ে ইবরাহিম খলিল রোড, বাম পাশ দিয়ে আজিয়াদ স্ট্রিট।`,
 view_79:`গেট ৭৯ দিয়ে বের হলে সোজা সামনে জাবালে ওমরের টাওয়ারগুলো, আর বাম দিকে ক্লক টাওয়ার।`,
 view_62:`গেট ৬২ দিয়ে বের হলে সোজা সামনে জারওয়ালের রাস্তা, বাম দিকে জাবালে ওমরের টাওয়ার, আর ক্লক টাওয়ার পেছনে বাম দিকে।`,
 view_45:`গেট ৪৫ দিয়ে বের হলে সামনে ডান দিকে মুআল্লা ও মসজিদে জিনের দিকে যাওয়ার মসজিদুল হারাম রোড, আর ক্লক টাওয়ার পেছনে।`,
 view_100:`গেট ১০০ দিয়ে বের হলে চারপাশে বাদশাহ আবদুল্লাহ সম্প্রসারণ, সামনে বাম দিকে জারওয়ালের রাস্তা, আর ক্লক টাওয়ার পেছনে।`,
 c_land_tip:`ক্লক টাওয়ার প্রায় সব জায়গা থেকে দেখা যায় এবং সবসময় দক্ষিণ দিক বোঝায়। দিক ঠিক করতে এটি ব্যবহার করুন।`,
 c_way:`ফেরার পথ: আপনি গেট নম্বর {n}, {gate} দিয়ে ঢুকেছিলেন। সাঈ শেষ হয় উত্তর-পূর্ব দিকের মারওয়া প্রান্তে, তাই মসজিদের বাইরে দিয়ে ঘুরে গেট নম্বর {n}-এ যান।`,
 unit_m:`মি`, unit_km:`কিমি`, d_walked:`হাঁটা হয়েছে {d}`, d_gate:`গেট {n}: {d}`, d_mataf:`মাতাফ: {d}`, d_of:`{t}-এর মধ্যে {d}`,
 loading:`মসজিদুল হারাম প্রস্তুত হচ্ছে`,
 loadFail:`3D ইঞ্জিন লোড হয়নি। ইন্টারনেট সংযোগ দেখে পেজটি আবার লোড করুন।`
}};

/* ================= Duas: Arabic, transliteration, meaning ================= */
const DUA = {
 niyyah:{ar:`اللَّهُمَّ إِنِّي أُرِيدُ الْعُمْرَةَ فَيَسِّرْهَا لِي وَتَقَبَّلْهَا مِنِّي`,
  tr:{en:`Allāhumma innī urīdul-ʿumrata fa-yassirhā lī wa taqabbalhā minnī.`, bn:`আল্লাহুম্মা ইন্নি উরিদুল উমরাতা ফাইয়াসসিরহা লি ওয়া তাকাব্বালহা মিন্নি।`},
  m:{en:`O Allah, I intend to perform Umrah, so make it easy for me and accept it from me.`, bn:`হে আল্লাহ, আমি উমরাহর নিয়ত করছি; আপনি তা আমার জন্য সহজ করে দিন এবং কবুল করুন।`}},
 talbiyah:{ar:`لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ، لَبَّيْكَ لَا شَرِيكَ لَكَ لَبَّيْكَ، إِنَّ الْحَمْدَ وَالنِّعْمَةَ لَكَ وَالْمُلْكَ، لَا شَرِيكَ لَكَ`,
  tr:{en:`Labbayk Allāhumma labbayk, labbayka lā sharīka laka labbayk, innal-ḥamda wan-niʿmata laka wal-mulk, lā sharīka lak.`, bn:`লাব্বাইকা আল্লাহুম্মা লাব্বাইক, লাব্বাইকা লা শারিকা লাকা লাব্বাইক, ইন্নাল হামদা ওয়ান নি'মাতা লাকা ওয়াল মুলক, লা শারিকা লাক।`},
  m:{en:`Here I am, O Allah, here I am. Here I am, You have no partner, here I am. All praise, all blessings and all sovereignty are Yours. You have no partner.`, bn:`আমি হাজির, হে আল্লাহ, আমি হাজির। আমি হাজির, আপনার কোনো শরিক নেই, আমি হাজির। নিশ্চয়ই সকল প্রশংসা, নিয়ামত ও রাজত্ব আপনারই। আপনার কোনো শরিক নেই।`}},
 enter:{ar:`بِسْمِ اللَّهِ، وَالصَّلَاةُ وَالسَّلَامُ عَلَىٰ رَسُولِ اللَّهِ، اللَّهُمَّ افْتَحْ لِي أَبْوَابَ رَحْمَتِكَ`,
  tr:{en:`Bismillāh, waṣ-ṣalātu was-salāmu ʿalā Rasūlillāh. Allāhummaftaḥ lī abwāba raḥmatik.`, bn:`বিসমিল্লাহ, ওয়াস সালাতু ওয়াস সালামু আলা রাসূলিল্লাহ। আল্লাহুম্মাফ তাহলি আবওয়াবা রাহমাতিক।`},
  m:{en:`In the name of Allah, and peace and blessings be upon the Messenger of Allah. O Allah, open for me the doors of Your mercy.`, bn:`আল্লাহর নামে, এবং রাসূলুল্লাহর ওপর দরুদ ও সালাম। হে আল্লাহ, আমার জন্য আপনার রহমতের দরজাগুলো খুলে দিন।`}},
 istilam:{ar:`بِسْمِ اللَّهِ، اللَّهُ أَكْبَرُ`,
  tr:{en:`Bismillāhi, Allāhu akbar.`, bn:`বিসমিল্লাহি আল্লাহু আকবার।`},
  m:{en:`In the name of Allah. Allah is the Greatest.`, bn:`আল্লাহর নামে, আল্লাহ সর্বশ্রেষ্ঠ।`}},
 rabbana:{ar:`رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ`,
  tr:{en:`Rabbanā ātinā fid-dunyā ḥasanatan wa fil-ākhirati ḥasanatan wa qinā ʿadhāban-nār.`, bn:`রাব্বানা আতিনা ফিদ্দুনইয়া হাসানাতাও ওয়া ফিল আখিরাতি হাসানাতাও ওয়া কিনা আযাবান নার।`},
  m:{en:`Our Lord, give us good in this world and good in the Hereafter, and protect us from the punishment of the Fire. (2:201)`, bn:`হে আমাদের রব, আমাদের দুনিয়াতে কল্যাণ দিন, আখিরাতেও কল্যাণ দিন এবং জাহান্নামের আযাব থেকে রক্ষা করুন। (২:২০১)`}},
 maqam:{ar:`وَاتَّخِذُوا مِنْ مَقَامِ إِبْرَاهِيمَ مُصَلًّى`,
  tr:{en:`Wattakhidhū mim-maqāmi Ibrāhīma muṣallā.`, bn:`ওয়াত্তাখিযু মিম মাকামি ইবরাহিমা মুসাল্লা।`},
  m:{en:`And take the standing place of Ibrahim as a place of prayer. (2:125)`, bn:`আর তোমরা মাকামে ইবরাহিমকে নামাজের স্থান হিসেবে গ্রহণ করো। (২:১২৫)`}},
 zamzam:{ar:`اللَّهُمَّ إِنِّي أَسْأَلُكَ عِلْمًا نَافِعًا، وَرِزْقًا وَاسِعًا، وَشِفَاءً مِنْ كُلِّ دَاءٍ`,
  tr:{en:`Allāhumma innī as'aluka ʿilman nāfiʿā, wa rizqan wāsiʿā, wa shifā'an min kulli dā'.`, bn:`আল্লাহুম্মা ইন্নি আসআলুকা ইলমান নাফিআ, ওয়া রিযকান ওয়াসিআ, ওয়া শিফাআম মিন কুল্লি দা।`},
  m:{en:`O Allah, I ask You for beneficial knowledge, plentiful provision, and healing from every illness.`, bn:`হে আল্লাহ, আমি আপনার কাছে উপকারী জ্ঞান, প্রশস্ত রিজিক এবং সব রোগ থেকে আরোগ্য চাই।`}},
 safa:{ar:`إِنَّ الصَّفَا وَالْمَرْوَةَ مِنْ شَعَائِرِ اللَّهِ ۝ أَبْدَأُ بِمَا بَدَأَ اللَّهُ بِهِ`,
  tr:{en:`Innaṣ-ṣafā wal-marwata min shaʿā'irillāh. Abda'u bimā bada'allāhu bih.`, bn:`ইন্নাস সাফা ওয়াল মারওয়াতা মিন শাআইরিল্লাহ। আবদাউ বিমা বাদাআল্লাহু বিহি।`},
  m:{en:`Indeed, Safa and Marwa are among the symbols of Allah. (2:158) I begin with what Allah began with.`, bn:`নিশ্চয়ই সাফা ও মারওয়া আল্লাহর নিদর্শনসমূহের অন্তর্ভুক্ত। (২:১৫৮) আল্লাহ যা দিয়ে শুরু করেছেন, আমিও তা দিয়ে শুরু করছি।`}},
 dhikr:{ar:`اللَّهُ أَكْبَرُ، اللَّهُ أَكْبَرُ، اللَّهُ أَكْبَرُ، لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ`,
  tr:{en:`Allāhu akbar, Allāhu akbar, Allāhu akbar. Lā ilāha illallāhu waḥdahū lā sharīka lah, lahul-mulku wa lahul-ḥamd, wa huwa ʿalā kulli shay'in qadīr.`, bn:`আল্লাহু আকবার, আল্লাহু আকবার, আল্লাহু আকবার। লা ইলাহা ইল্লাল্লাহু ওয়াহদাহু লা শারিকা লাহু, লাহুল মুলকু ওয়া লাহুল হামদু, ওয়া হুয়া আলা কুল্লি শাইয়িন কাদির।`},
  m:{en:`Allah is the Greatest (three times). There is no god but Allah alone, with no partner. His is the dominion and His is the praise, and He has power over all things.`, bn:`আল্লাহ সর্বশ্রেষ্ঠ (তিনবার)। আল্লাহ ছাড়া কোনো ইলাহ নেই, তিনি এক, তাঁর কোনো শরিক নেই। রাজত্ব ও প্রশংসা তাঁরই, এবং তিনি সবকিছুর ওপর ক্ষমতাবান।`}},
 green:{ar:`رَبِّ اغْفِرْ وَارْحَمْ، إِنَّكَ أَنْتَ الْأَعَزُّ الْأَكْرَمُ`,
  tr:{en:`Rabbighfir warḥam, innaka antal-aʿazzul-akram.`, bn:`রাব্বিগফির ওয়ারহাম, ইন্নাকা আনতাল আআযযুল আকরাম।`},
  m:{en:`My Lord, forgive and have mercy. You are the Most Mighty, the Most Generous.`, bn:`হে আমার রব, ক্ষমা করুন ও দয়া করুন। নিশ্চয়ই আপনি সর্বাধিক পরাক্রমশালী, সর্বাধিক মহানুভব।`}},
 exit:{ar:`اللَّهُمَّ إِنِّي أَسْأَلُكَ مِنْ فَضْلِكَ`,
  tr:{en:`Allāhumma innī as'aluka min faḍlik.`, bn:`আল্লাহুম্মা ইন্নি আসআলুকা মিন ফাদলিক।`},
  m:{en:`O Allah, I ask You of Your bounty.`, bn:`হে আল্লাহ, আমি আপনার অনুগ্রহ প্রার্থনা করি।`}}
};

/* ================= Real gate numbers and names ================= */
const GATES = {
 1:{ar:'باب الملك عبدالعزيز', n:{en:`King Abdulaziz Gate`, bn:`বাদশাহ আবদুল আজিজ গেট`}},
 45:{ar:'باب الفتح', n:{en:`Al-Fath Gate`, bn:`বাবুল ফাতহ`}},
 62:{ar:'باب العمرة', n:{en:`Umrah Gate`, bn:`বাবুল উমরাহ`}},
 79:{ar:'باب الملك فهد', n:{en:`King Fahd Gate`, bn:`বাদশাহ ফাহাদ গেট`}},
 100:{ar:'باب الملك عبدالله', n:{en:`King Abdullah Gate`, bn:`বাদশাহ আবদুল্লাহ গেট`}}
};

/* ================= Places you can walk up to ================= */
const INFO = {
 blackStone:{n:{en:`Hajar al-Aswad (the Black Stone)`, bn:`হাজরে আসওয়াদ`}, d:'istilam',
  x:{en:`Each round of Tawaf starts and ends at this corner. In the crowd you usually can't reach it, so face it, turn your palms toward it and say "Bismillahi Allahu akbar". This is Istilam.`,
     bn:`তাওয়াফের প্রতিটি চক্কর এই কোণ থেকে শুরু হয়ে এখানেই শেষ হয়। ভিড়ের কারণে সাধারণত কাছে যাওয়া যায় না, তাই এর দিকে মুখ করে হাতের তালু দিয়ে ইশারা করে বলুন "বিসমিল্লাহি আল্লাহু আকবার"। এটিই ইস্তিলাম।`}},
 multazam:{n:{en:`Al-Multazam`, bn:`মুলতাযাম`},
  x:{en:`The wall between the Black Stone and the door of the Kaaba. Make heartfelt dua here if you can reach it without pushing.`,
     bn:`হাজরে আসওয়াদ ও কাবার দরজার মাঝের দেয়াল। কাউকে ধাক্কা না দিয়ে পৌঁছানো গেলে এখানে আন্তরিকভাবে দোয়া করুন।`}},
 door:{n:{en:`Door of the Kaaba`, bn:`কাবার দরজা`},
  x:{en:`The door is on the north-east wall, raised well above the ground. Pilgrims don't enter the Kaaba during Umrah; Tawaf is performed around it.`,
     bn:`দরজাটি উত্তর-পূর্ব দেয়ালে, মাটি থেকে বেশ উঁচুতে। উমরাহর সময় কাবার ভেতরে প্রবেশ করা হয় না; তাওয়াফ করা হয় এর চারপাশে।`}},
 hijr:{n:{en:`Hijr Ismail (Hatim)`, bn:`হাতিম (হিজরে ইসমাইল)`},
  x:{en:`The area inside this semicircular wall is part of the Kaaba, so Tawaf must go around the outside of it. The golden spout above it is the Mizab ar-Rahmah.`,
     bn:`এই অর্ধবৃত্তাকার দেয়ালের ভেতরের অংশ কাবারই অংশ, তাই তাওয়াফ অবশ্যই এর বাইরে দিয়ে করতে হবে। ওপরের সোনালি নালাটি মিযাবে রহমত।`}},
 yamani:{n:{en:`Rukn Yamani (the Yemeni Corner)`, bn:`রুকনে ইয়ামানি`}, d:'rabbana',
  x:{en:`Touch it with your right hand if you easily can, without kissing it. If you can't reach it, just walk on; no gesture is needed. From here to the Black Stone, recite:`,
     bn:`সহজে সম্ভব হলে ডান হাতে স্পর্শ করুন, চুমু দেবেন না। পৌঁছাতে না পারলে ইশারা ছাড়াই এগিয়ে যান। এখান থেকে হাজরে আসওয়াদ পর্যন্ত পড়ুন:`}},
 maqam:{n:{en:`Maqam Ibrahim`, bn:`মাকামে ইবরাহিম`},
  x:{en:`The stone Prophet Ibrahim (AS) stood on while building the Kaaba, kept inside this gold and glass enclosure. Pray 2 rakats behind it after Tawaf.`,
     bn:`কাবা নির্মাণের সময় হযরত ইবরাহিম (আ.) যে পাথরে দাঁড়িয়েছিলেন, সেটি এই সোনালি-কাচের আবরণের ভেতরে রাখা আছে। তাওয়াফের পর এর পেছনে দুই রাকাত নামাজ পড়ুন।`}},
 zamzam:{n:{en:`Zamzam water`, bn:`জমজমের পানি`},
  x:{en:`Zamzam is available chilled and unchilled throughout the mosque. Drink it facing the qibla.`,
     bn:`পুরো মসজিদজুড়ে ঠান্ডা ও সাধারণ, দুই ধরনের জমজম পাওয়া যায়। কিবলামুখী হয়ে পান করুন।`}},
 safa:{n:{en:`Mount Safa`, bn:`সাফা পাহাড়`},
  x:{en:`Sa'i begins here. Hajar (AS) ran between Safa and Marwa searching for water for her son Ismail (AS), and Allah brought forth Zamzam.`,
     bn:`সাঈ এখান থেকে শুরু হয়। হযরত হাজেরা (আ.) শিশুপুত্র ইসমাইল (আ.)-এর জন্য পানির খোঁজে সাফা ও মারওয়ার মাঝে দৌড়েছিলেন, আর আল্লাহ জমজম প্রবাহিত করেন।`}},
 marwa:{n:{en:`Mount Marwa`, bn:`মারওয়া পাহাড়`},
  x:{en:`Sa'i ends here, at the end of the 7th lap.`, bn:`সপ্তম চক্কর শেষে সাঈ এখানে শেষ হয়।`}},
 green:{n:{en:`The green lights`, bn:`সবুজ বাতি`}, d:'green',
  x:{en:`They mark the low valley where Hajar (AS) ran. Men jog lightly between them; women walk normally. Many recite:`,
     bn:`সেই নিচু উপত্যকা চিহ্নিত করে যেখানে হাজেরা (আ.) দৌড়েছিলেন। পুরুষরা এর মাঝে হালকা দৌড়ান; নারীরা স্বাভাবিকভাবে হাঁটেন। অনেকে পড়েন:`}},
 safaWay:{n:{en:`Way to Safa`, bn:`সাফার পথ`},
  x:{en:`After Tawaf, follow the signs for Al-Safa. On the ground floor the Mas'a begins just beyond the colonnade on the Black Stone side.`,
     bn:`তাওয়াফের পর "আস-সাফা" চিহ্ন অনুসরণ করুন। নিচতলায় হাজরে আসওয়াদের দিকের স্তম্ভসারি পেরোলেই মাসআ শুরু।`}},
 gate1:{n:{en:`King Abdulaziz Gate (Gate 1)`, bn:`বাদশাহ আবদুল আজিজ গেট (গেট ১)`},
  x:{en:`One of the five main gates, each marked by two minarets. It faces the Clock Tower on the south side and leads straight to the Tawaf area.`,
     bn:`পাঁচটি প্রধান গেটের একটি; প্রতিটি প্রধান গেটের ওপর দুটি মিনার থাকে। দক্ষিণ দিকে ক্লক টাওয়ারের মুখোমুখি এই গেট দিয়ে সরাসরি তাওয়াফ এলাকায় যাওয়া যায়।`}},
 gate45:{n:{en:`Al-Fath Gate (Gate 45)`, bn:`বাবুল ফাতহ (গেট ৪৫)`},
  x:{en:`A main gate on the north side, and one of the official entrances to the Tawaf area for Umrah pilgrims.`,
     bn:`উত্তর দিকের একটি প্রধান গেট, উমরাহ পালনকারীদের জন্য তাওয়াফ এলাকার অফিসিয়াল প্রবেশপথগুলোর একটি।`}},
 gate62:{n:{en:`Umrah Gate (Gate 62)`, bn:`বাবুল উমরাহ (গেট ৬২)`},
  x:{en:`A main gate on the north-west side, and an official entrance to the Tawaf area.`,
     bn:`উত্তর-পশ্চিম দিকের একটি প্রধান গেট এবং তাওয়াফ এলাকার একটি অফিসিয়াল প্রবেশপথ।`}},
 gate79:{n:{en:`King Fahd Gate (Gate 79)`, bn:`বাদশাহ ফাহাদ গেট (গেট ৭৯)`},
  x:{en:`The main gate on the west side, and one of the most used entrances to the Tawaf area.`,
     bn:`পশ্চিম দিকের প্রধান গেট, তাওয়াফ এলাকায় যাওয়ার সবচেয়ে বেশি ব্যবহৃত প্রবেশপথগুলোর একটি।`}},
 gate100:{n:{en:`King Abdullah Gate (Gate 100)`, bn:`বাদশাহ আবদুল্লাহ গেট (গেট ১০০)`},
  x:{en:`One of the five main gates, on the north side in the King Abdullah expansion, and an official entrance to the Tawaf area.`,
     bn:`পাঁচটি প্রধান গেটের একটি, উত্তর দিকে বাদশাহ আবদুল্লাহ সম্প্রসারণে, এবং তাওয়াফ এলাকার একটি অফিসিয়াল প্রবেশপথ।`}},
 clockTower:{n:{en:`Abraj Al Bait (the Clock Tower)`, bn:`আবরাজ আল বাইত (ক্লক টাওয়ার)`},
  x:{en:`The tallest landmark in Makkah, directly south of the mosque and facing King Abdulaziz Gate (Gate 1). Its towers hold hotels including the Fairmont Makkah Clock Royal Tower, Raffles Makkah Palace, Swissôtel, Pullman ZamZam, Mövenpick Hajar Tower and Al Marwa Rayhaan. You can see it from almost anywhere, and it always marks the south side.`,
     bn:`মক্কার সবচেয়ে উঁচু নিদর্শন, মসজিদের ঠিক দক্ষিণে, বাদশাহ আবদুল আজিজ গেটের (গেট ১) মুখোমুখি। এর টাওয়ারগুলোতে ফেয়ারমন্ট মক্কা ক্লক রয়্যাল টাওয়ার, র‍্যাফলস মক্কা প্যালেস, সুইসোটেল, পুলম্যান জমজম, মোভেনপিক হাজার টাওয়ার ও আল মারওয়া রায়হানসহ অনেক হোটেল আছে। প্রায় সব জায়গা থেকে এটি দেখা যায় এবং সবসময় দক্ষিণ দিক বোঝায়।`}},
 ajyad:{n:{en:`Ajyad`, bn:`আজিয়াদ`},
  x:{en:`One of Makkah's oldest pilgrim districts, south-east of the mosque near the Safa side. Hotels here include Al Safwah Royale Orchid, Elaf Ajyad and Makarem Ajyad.`,
     bn:`মক্কার পুরোনো হাজিপাড়াগুলোর একটি, মসজিদের দক্ষিণ-পূর্বে সাফার দিকে। এখানে আল সাফওয়া রয়্যাল অর্কিড, ইলাফ আজিয়াদ ও মাকারেম আজিয়াদের মতো হোটেল আছে।`}},
 jabalOmar:{n:{en:`Jabal Omar`, bn:`জাবালে ওমর`},
  x:{en:`A district of modern hotel towers directly west of the mosque, including Hyatt Regency, Jumeirah, Address and Hilton Suites Jabal Omar. Guests here usually use King Fahd Gate (Gate 79).`,
     bn:`মসজিদের ঠিক পশ্চিমে আধুনিক হোটেল টাওয়ারের এলাকা; হায়াত রিজেন্সি, জুমেইরা, অ্যাড্রেস ও হিলটন স্যুটস জাবালে ওমর এখানে। এখানকার অতিথিরা সাধারণত বাদশাহ ফাহাদ গেট (গেট ৭৯) ব্যবহার করেন।`}},
 jarwal:{n:{en:`Jarwal`, bn:`জারওয়াল`},
  x:{en:`A hotel district north-west of the mosque, about 1 km from the Kaaba, with hotels such as Anjum, Al Kiswah Towers and Emaar Andalusia.`,
     bn:`মসজিদের উত্তর-পশ্চিমে হোটেল এলাকা, কাবা থেকে প্রায় ১ কিমি; আনজুম, আল কিসওয়াহ টাওয়ার্স ও ইমার আন্দালুসিয়ার মতো হোটেল এখানে।`}},
 abuQubais:{n:{en:`Jabal Abu Qubais`, bn:`জাবালে আবু কুবাইস`},
  x:{en:`The mountain just east of Safa, topped by a royal palace. If you can see it, you are on the east (Safa) side of the mosque.`,
     bn:`সাফার ঠিক পূর্বের পাহাড়, যার চূড়ায় একটি রাজপ্রাসাদ। এটি দেখা গেলে বুঝবেন আপনি মসজিদের পূর্ব (সাফা) দিকে আছেন।`}},
 expansion:{n:{en:`King Abdullah Expansion`, bn:`বাদশাহ আবদুল্লাহ সম্প্রসারণ`},
  x:{en:`The large northern extension of the mosque. King Abdullah Gate (Gate 100) leads into it.`,
     bn:`মসজিদের উত্তর দিকের বড় সম্প্রসারিত অংশ। বাদশাহ আবদুল্লাহ গেট (গেট ১০০) দিয়ে এখানে ঢোকা যায়।`}},
 ibrahimKhalil:{n:{en:`Ibrahim Al Khalil Road`, bn:`ইবরাহিম খলিল রোড`},
  x:{en:`One of the busiest pilgrim streets, lined with hotels, shops and restaurants, and home to many South Asian groups. It runs north to the plaza in front of King Abdulaziz Gate.`,
     bn:`হাজিদের সবচেয়ে ব্যস্ত রাস্তাগুলোর একটি; হোটেল, দোকান ও রেস্তোরাঁয় ভরা, আর দক্ষিণ এশিয়ার অনেক গ্রুপ এখানেই থাকে। রাস্তাটি উত্তরে বাদশাহ আবদুল আজিজ গেটের সামনের চত্বর পর্যন্ত গেছে।`}},
 masjidJinn:{n:{en:`Masjid al-Jinn`, bn:`মসজিদে জিন`},
  x:{en:`A historic mosque in the Al-Hajun area, about 900 m north of the Haram and east of Jannat al-Mu'alla. It marks where a group of jinn listened to the Prophet ﷺ recite the Qur'an.`,
     bn:`হাজুন এলাকার একটি ঐতিহাসিক মসজিদ, হারাম থেকে প্রায় ৯০০ মিটার উত্তরে, জান্নাতুল মুআল্লার পূর্ব পাশে। এখানে একদল জিন নবীজির ﷺ কুরআন তিলাওয়াত শুনেছিল।`}},
 mualla:{n:{en:`Jannat al-Mu'alla`, bn:`জান্নাতুল মুআল্লা`},
  x:{en:`Makkah's historic cemetery, where Khadijah (RA), the Prophet's ﷺ wife, is buried.`,
     bn:`মক্কার ঐতিহাসিক কবরস্থান, যেখানে নবীজির ﷺ স্ত্রী খাদিজা (রা.) শায়িত আছেন।`}},
 safaGate:{n:{en:`Al-Safa Gate (Gate 13)`, bn:`বাবুস সাফা (গেট ১৩)`},
  x:{en:`An exit from the Mas'a at the Safa end, leading outside the mosque.`,
     bn:`সাফা প্রান্তে মাসআ থেকে মসজিদের বাইরে যাওয়ার একটি পথ।`}},
 marwahGate:{n:{en:`Al-Marwah Gate`, bn:`বাবুল মারওয়া`},
  x:{en:`The exit at the Marwa end, used by most pilgrims after finishing Sa'i.`,
     bn:`মারওয়া প্রান্তের বের হওয়ার পথ; সাঈ শেষে বেশিরভাগ মানুষ এটি দিয়ে বের হন।`}}
};

/* ================= Helpers ================= */
const $ = s => document.querySelector(s);
const TAU = Math.PI * 2;
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
const lerp = (a, b, k) => a + (b - a) * k;
const wrapPi = a => { while (a > Math.PI) a -= TAU; while (a < -Math.PI) a += TAU; return a; };
const lerpAngle = (a, b, k) => a + wrapPi(b - a) * k;
function rng(seed){ return function(){ seed |= 0; seed = seed + 0x6D2B79F5 | 0; let t = Math.imul(seed ^ seed >>> 15, 1 | seed); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }
const pick = (r, arr) => arr[Math.floor(r() * arr.length)];
const store = {
  get(k){ try { return localStorage.getItem('umrahsim:' + k); } catch(e){ return null; } },
  set(k, v){ try { localStorage.setItem('umrahsim:' + k, v); } catch(e){} }
};
const DEBUG = /[?&]debug\b/.test(location.search);

let lang = store.get('lang') === 'bn' ? 'bn' : 'en';
const bnDigits = s => String(s).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
const num = n => lang === 'bn' ? bnDigits(n) : String(n);
function t(k, vars){
  let s = STR[lang][k]; if (s === undefined) s = STR.en[k]; if (s === undefined) return k;
  if (Array.isArray(s)) return s;
  if (vars) for (const v in vars) s = s.split('{' + v + '}').join(typeof vars[v] === 'number' ? num(vars[v]) : vars[v]);
  return s;
}
const esc = s => String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
function duaHTML(key){
  const d = DUA[key];
  return `<div class="dua" data-dua="${key}"><p class="ar" lang="ar" dir="rtl">${d.ar}</p><p class="tr">${esc(d.tr[lang])}</p><p class="mean">${esc(d.m[lang])}</p></div>`;
}

const loadMsg = $('#loadMsg');
loadMsg.textContent = t('loading');
if (!window.THREE) { loadMsg.textContent = t('loadFail'); return; }

/* ================= Renderer ================= */
const isTouch = matchMedia('(pointer: coarse)').matches;
const canvas = $('#c');
let renderer;
try {
  renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'high-performance' });
} catch (e) { loadMsg.textContent = t('loadFail'); return; }
renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isTouch ? 1.5 : 1.75));
const world = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1400);
world.add(new THREE.HemisphereLight(0xfff6e6, 0xb5a78e, 0.7));
const sun = new THREE.DirectionalLight(0xffffff, 0.62);
sun.position.set(-60, 120, 45); world.add(sun);
const MAXANISO = Math.min(8, renderer.capabilities.getMaxAnisotropy());

function resize(){
  const w = window.innerWidth, h = window.innerHeight;
  renderer.setSize(w, h, false); camera.aspect = w / h; camera.updateProjectionMatrix();
}
window.addEventListener('resize', resize);

/* ================= Canvas textures ================= */
function ctex(w, h, draw, rx = 1, ry = 1){
  const c = document.createElement('canvas'); c.width = w; c.height = h;
  draw(c.getContext('2d'), w, h);
  const tx = new THREE.CanvasTexture(c);
  tx.wrapS = tx.wrapT = THREE.RepeatWrapping; tx.repeat.set(rx, ry); tx.anisotropy = MAXANISO;
  return tx;
}
function drawMarble(g, w, h, seed, base, grout){
  const r = rng(seed); g.fillStyle = base; g.fillRect(0, 0, w, h);
  for (let i = 0; i < 24; i++){
    g.strokeStyle = `rgba(135,140,145,${0.04 + r() * 0.08})`; g.lineWidth = 0.6 + r() * 1.6;
    g.beginPath(); const x = r() * w, y = r() * h; g.moveTo(x, y);
    g.bezierCurveTo(x + (r() - .5) * w, y + (r() - .5) * h, x + (r() - .5) * w, y + (r() - .5) * h, x + (r() - .5) * w * 1.2, y + (r() - .5) * h * 1.2);
    g.stroke();
  }
  g.strokeStyle = grout; g.lineWidth = 3; g.strokeRect(1.5, 1.5, w - 3, h - 3);
}
function archPath(g, x0, yb, x1, ya){
  const mid = (x0 + x1) / 2, ys = ya + (x1 - x0) * 0.5;
  g.beginPath(); g.moveTo(x0, yb); g.lineTo(x0, ys);
  g.quadraticCurveTo(x0, ya + (ys - ya) * 0.12, mid, ya);
  g.quadraticCurveTo(x1, ya + (ys - ya) * 0.12, x1, ys);
  g.lineTo(x1, yb); g.closePath();
}
function windowWall(o){
  return ctex(256, 256, (g, w, h) => {
    g.fillStyle = o.base; g.fillRect(0, 0, w, h);
    g.fillStyle = o.trim; g.fillRect(0, 0, w, 9); g.fillRect(0, h - 7, w, 7);
    g.fillStyle = o.pil || o.trim; g.fillRect(0, 0, 12, h); g.fillRect(w - 12, 0, 12, h);
    g.fillStyle = o.glass; archPath(g, w * .27, h * .88, w * .73, h * .16); g.fill();
    g.strokeStyle = o.trim; g.lineWidth = 6; g.stroke();
    g.fillStyle = o.trim; g.fillRect(w / 2 - 2, h * .42, 4, h * .46); g.fillRect(w * .27, h * .62, w * .46, 3);
  }, o.rx || 1, o.ry || 1);
}
function archBand(n){
  return ctex(256, 128, (g, w, h) => {
    g.fillStyle = '#ddd0b6'; g.fillRect(0, 0, w, h);
    g.fillStyle = '#c7b48f'; g.fillRect(0, 0, w, 12); g.fillRect(0, 20, w, 3);
    g.globalCompositeOperation = 'destination-out';
    archPath(g, 26, h + 2, w - 26, h * .3); g.fill();
    g.globalCompositeOperation = 'source-over';
    g.strokeStyle = '#b39d74'; g.lineWidth = 5; archPath(g, 26, h + 2, w - 26, h * .3); g.stroke();
  }, n, 1);
}
const kiswaTex = ctex(1024, 1024, (g, w, h) => {
  g.fillStyle = '#0d0c0e'; g.fillRect(0, 0, w, h);
  g.strokeStyle = 'rgba(255,255,255,0.05)'; g.lineWidth = 2;
  for (let y = 0; y < h + 48; y += 48) for (let x = ((y / 48) % 2) * 24; x < w + 48; x += 48){
    g.beginPath(); g.moveTo(x - 14, y); g.lineTo(x, y - 20); g.lineTo(x + 14, y); g.lineTo(x, y + 20); g.closePath(); g.stroke();
  }
  const y0 = h * .26, bh = h * .09;
  g.fillStyle = '#0d0c0e'; g.fillRect(0, y0, w, bh);
  g.fillStyle = '#d4ad55'; g.fillRect(0, y0, w, 7); g.fillRect(0, y0 + bh - 7, w, 7);
  const r = rng(7); g.strokeStyle = '#dab45f'; g.fillStyle = '#dab45f'; g.lineCap = 'round'; g.lineWidth = 5;
  let x = 12;
  while (x < w - 50){
    const k = r();
    if (k < .35){ g.beginPath(); g.moveTo(x, y0 + bh - 16); g.lineTo(x, y0 + 16 + r() * 22); g.stroke(); x += 15; }
    else if (k < .7){ g.beginPath(); g.ellipse(x + 15, y0 + bh - 24, 16, 8 + r() * 7, 0, 0, Math.PI); g.stroke(); x += 34; }
    else { g.beginPath(); g.moveTo(x, y0 + bh - 18); g.quadraticCurveTo(x + 20, y0 + bh * .3, x + 40, y0 + bh - 18); g.stroke();
      g.beginPath(); g.arc(x + 20, y0 + 22, 4, 0, TAU); g.fill(); x += 46; }
  }
  for (let i = 0; i < 4; i++){
    const px = w * (.07 + i * .24), py = h * .4, pw = w * .14, ph = h * .085;
    g.strokeStyle = '#c9a24a'; g.lineWidth = 4; g.strokeRect(px, py, pw, ph);
    g.lineWidth = 3; g.beginPath(); g.ellipse(px + pw / 2, py + ph / 2, pw * .32, ph * .28, 0, 0, TAU); g.stroke();
  }
});
const doorTex = ctex(128, 256, (g, w, h) => {
  const gr = g.createLinearGradient(0, 0, w, 0);
  gr.addColorStop(0, '#9e7a2c'); gr.addColorStop(.5, '#e6c672'); gr.addColorStop(1, '#9e7a2c');
  g.fillStyle = gr; g.fillRect(0, 0, w, h);
  g.strokeStyle = '#6f531b'; g.lineWidth = 4; g.strokeRect(6, 6, w - 12, h - 12);
  g.beginPath(); g.moveTo(w / 2, 6); g.lineTo(w / 2, h - 6); g.stroke();
  for (let y = 26; y < h - 30; y += 46){ g.strokeRect(14, y, w / 2 - 22, 32); g.strokeRect(w / 2 + 8, y, w / 2 - 22, 32); }
});
function skyTex(top, mid, bottom){
  return ctex(4, 512, (g, w, h) => {
    const gr = g.createLinearGradient(0, 0, 0, h);
    gr.addColorStop(0, top); gr.addColorStop(.58, mid); gr.addColorStop(1, bottom);
    g.fillStyle = gr; g.fillRect(0, 0, w, h);
  });
}
function hotelTex(base, win){
  return ctex(128, 256, (g, w, h) => {
    g.fillStyle = base; g.fillRect(0, 0, w, h); g.fillStyle = win;
    for (let y = 10; y < h; y += 22) for (let x = 9; x < w; x += 20) g.fillRect(x, y, 11, 13);
  }, 2, 5);
}
function roundRect(g, x, y, w, h, r){
  g.beginPath(); g.moveTo(x + r, y); g.lineTo(x + w - r, y); g.quadraticCurveTo(x + w, y, x + w, y + r);
  g.lineTo(x + w, y + h - r); g.quadraticCurveTo(x + w, y + h, x + w - r, y + h); g.lineTo(x + r, y + h);
  g.quadraticCurveTo(x, y + h, x, y + h - r); g.lineTo(x, y + r); g.quadraticCurveTo(x, y, x + r, y); g.closePath();
}
function signSprite(ar, en, width){
  const c = document.createElement('canvas'); c.width = 1024; c.height = 300; const g = c.getContext('2d');
  roundRect(g, 8, 8, 1008, 284, 24); g.fillStyle = 'rgba(15,74,52,0.94)'; g.fill();
  g.lineWidth = 8; g.strokeStyle = '#d4b35e'; g.stroke();
  g.fillStyle = '#ffffff'; g.textAlign = 'center'; g.textBaseline = 'middle';
  g.font = '700 118px Amiri, "Noto Naskh Arabic", serif'; g.fillText(ar, 512, 108);
  g.font = '600 74px "Hind Siliguri", system-ui, sans-serif'; g.fillText(en, 512, 224);
  const tx = new THREE.CanvasTexture(c); tx.anisotropy = MAXANISO;
  const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tx, depthWrite: false, fog: false }));
  sp.scale.set(width, width * 300 / 1024, 1);
  return sp;
}

/* ================= Materials & mesh helpers ================= */
const lam = (color, extra) => new THREE.MeshLambertMaterial(Object.assign({ color }, extra || {}));
const MAT = {
  gold: new THREE.MeshPhongMaterial({ color: 0xc9a24a, specular: 0xfff0b0, shininess: 70, emissive: 0x2b1e06 }),
  silver: new THREE.MeshPhongMaterial({ color: 0xcfd4d8, specular: 0xffffff, shininess: 90 }),
  stone: lam(0xe3d7c1), stoneDark: lam(0xcdbd9e), marble: lam(0xf1f1ee)
};
function add(parent, geo, mat, x = 0, y = 0, z = 0){
  const m = new THREE.Mesh(geo, mat); m.position.set(x, y, z); parent.add(m); return m;
}
function crescent(parent, y, s = 1){
  const c = add(parent, new THREE.TorusGeometry(0.55 * s, 0.11 * s, 8, 20, Math.PI * 1.35), MAT.gold, 0, y, 0);
  c.rotation.z = Math.PI * 0.82; return c;
}
function minaret(h, s = 1, mat){
  const g = new THREE.Group(); const m = mat || MAT.stone;
  add(g, new THREE.BoxGeometry(4.2 * s, 8, 4.2 * s), MAT.stoneDark, 0, 4, 0);
  add(g, new THREE.CylinderGeometry(1.5 * s, 1.75 * s, h * .5, 8), m, 0, 8 + h * .25, 0);
  add(g, new THREE.CylinderGeometry(2.4 * s, 2.0 * s, .9, 8), MAT.stoneDark, 0, 8 + h * .5, 0);
  add(g, new THREE.CylinderGeometry(1.25 * s, 1.4 * s, h * .25, 8), m, 0, 8 + h * .625, 0);
  add(g, new THREE.CylinderGeometry(1.9 * s, 1.6 * s, .8, 8), MAT.stoneDark, 0, 8 + h * .75, 0);
  add(g, new THREE.CylinderGeometry(1.0 * s, 1.1 * s, h * .13, 8), m, 0, 8 + h * .815, 0);
  add(g, new THREE.ConeGeometry(1.2 * s, 5 * s, 8), MAT.gold, 0, 8 + h * .88 + 2.5 * s, 0);
  crescent(g, 8 + h * .88 + 5.9 * s, s);
  return g;
}

/* ================= Geometry of the Haram (metres-ish units) ================= */
const KAABA_ROT = Math.PI * 0.75, KC = Math.cos(KAABA_ROT), KS = Math.sin(KAABA_ROT);
const KW = 11.5, KD = 10.5, KH = 13, KB = 0.4;
const kw = (lx, lz) => ({ x: lx * KC + lz * KS, z: -lx * KS + lz * KC });   // kaaba-local to world
const kl = (x, z) => ({ x: x * KC - z * KS, z: x * KS + z * KC });           // world to kaaba-local
const polar = (r, a) => ({ x: r * Math.cos(a), z: -r * Math.sin(a) });       // a grows anticlockwise from east
const angleOf = (x, z) => Math.atan2(-z, x);
const BS = kw(-KW / 2, KD / 2), A0 = angleOf(BS.x, BS.z);                   // Black Stone corner
const YM = kw(-KW / 2, -KD / 2), AY = angleOf(YM.x, YM.z);                  // Yemeni corner
const MAQAM = kw(-1, KD / 2 + 10.5), PRAY_SPOT = kw(-1, KD / 2 + 13.6);
const ZAMZAM = polar(31.2, 0.30), ZAMZAM_WP = polar(28.4, 0.30);
const HIJR_A = 8.3, HIJR_B = 6.7, PR = 0.45;
const CROWD_C = kw(2.5, 0);

function pushBox(p, b, r){
  const cx = clamp(p.x, b.x0, b.x1), cz = clamp(p.z, b.z0, b.z1);
  const dx = p.x - cx, dz = p.z - cz, d2 = dx * dx + dz * dz;
  if (d2 >= r * r) return;
  if (d2 > 1e-9){ const d = Math.sqrt(d2); p.x = cx + dx / d * r; p.z = cz + dz / d * r; return; }
  const l = p.x - b.x0, rr = b.x1 - p.x, tt = p.z - b.z0, bb = b.z1 - p.z, m = Math.min(l, rr, tt, bb);
  if (m === l) p.x = b.x0 - r; else if (m === rr) p.x = b.x1 + r; else if (m === tt) p.z = b.z0 - r; else p.z = b.z1 + r;
}
function pushSeg(p, s, r){
  const vx = s.x2 - s.x1, vz = s.z2 - s.z1, L2 = vx * vx + vz * vz;
  const u = clamp(((p.x - s.x1) * vx + (p.z - s.z1) * vz) / L2, 0, 1), cx = s.x1 + vx * u, cz = s.z1 + vz * u;
  const dx = p.x - cx, dz = p.z - cz, d = Math.hypot(dx, dz), m = s.t / 2 + r;
  if (d < m && d > 1e-6){ p.x = cx + dx / d * m; p.z = cz + dz / d * m; }
}
function pushCircle(p, c, r){
  const dx = p.x - c.x, dz = p.z - c.z, d = Math.hypot(dx, dz), m = c.r + r;
  if (d < m && d > 1e-6){ p.x = c.x + dx / d * m; p.z = c.z + dz / d * m; }
}

/* ================= Crowd ================= */
const MALE_IHRAM = ['#f6f5f0', '#f1efe8', '#ebe8de', '#f8f8f6'];
const MALE_THOBE = ['#f4f2ec', '#ebe6da', '#d9d4c8', '#b9b3a6', '#e9e2cf', '#7d8792', '#f6f6f2'];
const FEMALE = ['#1d1b20', '#26232b', '#2f2a2a', '#3a3f4a', '#55483b', '#ece8de', '#1f2430'];
const SKIN = ['#c99a76', '#a87656', '#7a5236', '#e0b896', '#8d5e3c', '#b98462'];
const HIJAB = ['#1b1b1f', '#f2efe8', '#2c2f3a', '#3d2f2f', '#e9e4da'];
function makeCrowd(n, seed, maleRobes){
  const bodyGeo = new THREE.CylinderGeometry(0.26, 0.36, 1.32, 8); bodyGeo.translate(0, 0.66, 0);
  const headGeo = new THREE.SphereGeometry(0.19, 10, 8); headGeo.translate(0, 1.5, 0);
  const bodies = new THREE.InstancedMesh(bodyGeo, lam(0xffffff), n);
  const heads = new THREE.InstancedMesh(headGeo, lam(0xffffff), n);
  bodies.instanceMatrix.setUsage(THREE.DynamicDrawUsage); heads.instanceMatrix.setUsage(THREE.DynamicDrawUsage);
  bodies.frustumCulled = heads.frustumCulled = false;
  const r = rng(seed), col = new THREE.Color(), agents = [];
  for (let i = 0; i < n; i++){
    const female = r() < 0.4;
    bodies.setColorAt(i, col.set(female ? pick(r, FEMALE) : pick(r, maleRobes)));
    heads.setColorAt(i, col.set(female ? pick(r, HIJAB) : pick(r, SKIN)));
    agents.push({ female, ph: r() * TAU, r1: r(), r2: r(), r3: r() });
  }
  bodies.instanceColor.needsUpdate = true; heads.instanceColor.needsUpdate = true;
  const m = new THREE.Matrix4();
  return {
    bodies, heads, agents,
    set(i, x, z, yaw, y = 0, sy = 1){
      m.makeRotationY(yaw); const e = m.elements;
      if (sy !== 1){ e[4] *= sy; e[5] *= sy; e[6] *= sy; }
      e[12] = x; e[13] = y; e[14] = z;
      bodies.setMatrixAt(i, m); heads.setMatrixAt(i, m);
    },
    commit(){ bodies.instanceMatrix.needsUpdate = true; heads.instanceMatrix.needsUpdate = true; }
  };
}
// Keep crowd members from walking through the player
function dodge(x, z, out){
  if (S.mode === 'follow'){
    const dx = x - P.pos.x, dz = z - P.pos.z, d2 = dx * dx + dz * dz;
    if (d2 < 0.8 && d2 > 1e-6){ const d = Math.sqrt(d2), k = (0.9 - d) / d; x += dx * k; z += dz * k; }
  }
  out.x = x; out.z = z; return out;
}
const _o = { x: 0, z: 0 };

/* ================= World layout (Kaaba at the origin, north = -z, east = +x) ================= */
const R_IN = 44, R_OUT = 60, HALL_H = 10, BLDG_H = 24, PLAZA_R = 95, IN_LINTEL = 7.5, OUT_LINTEL = 8.6;
const A_G1 = -Math.PI / 2, A_G45 = 1.5, A_G100 = 1.95, A_G62 = 2.42, A_G79 = Math.PI, A_SAFA = -0.5;
const GATE_ANG = { 1: A_G1, 45: A_G45, 100: A_G100, 62: A_G62, 79: A_G79 };
const MAIN_GATES = [1, 45, 100, 62, 79];
const OPENINGS = MAIN_GATES.map(n => ({ a: GATE_ANG[n], hw: 3.4 })).concat([{ a: A_SAFA, hw: 3.3 }]);
function openingAt(R, a, extra = 0){ for (const o of OPENINGS) if (Math.abs(wrapPi(a - o.a)) * R < o.hw + extra) return o; return null; }
// The Mas'a: a long hall east of the mosque, Safa at the south end and Marwa at the north end
const MX0 = 62.6, MX1 = 77.4, MZS = 45, MZN = -53, MZ_SAFA = 38, MZ_MARWA = -46, GZ0 = 8, GZ1 = 25;
const SAFA_PT = { x: 70, z: 41 }, MARWA_PT = { x: 70, z: -49 };
// Passage from the colonnade on the Black Stone side out to the Safa end of the Mas'a
const PDIR = { x: Math.cos(A_SAFA), z: -Math.sin(A_SAFA) }, PPERP = { x: -PDIR.z, z: PDIR.x }, PB = polar(R_OUT, A_SAFA);
function passageWall(side){
  const b = { x: PB.x + PPERP.x * 3.3 * side, z: PB.z + PPERP.z * 3.3 * side }, tt = (MX0 - 1 - b.x) / PDIR.x;
  return { x1: b.x, z1: b.z, x2: b.x + PDIR.x * tt, z2: b.z + PDIR.z * tt, t: 0.8, h: HALL_H };
}
const PW = [passageWall(-1), passageWall(1)];
const ZW0 = Math.min(PW[0].z2, PW[1].z2) + 0.4, ZW1 = Math.max(PW[0].z2, PW[1].z2) - 0.4;

// Real approach routes, each ending at an official entrance to the Tawaf area. km = real walking distance (approximate).
const faceIn = a => Math.atan2(-Math.cos(a), Math.sin(a));
const ROADS = [
  { k: 'A', x1: -30, z1: 214, x2: -30, z2: 78, hw: 7 },
  { k: 'B', x1: 55, z1: 214, x2: 55, z2: 72, hw: 7 },
  { k: 'C', x1: -212, z1: 1, x2: -80, z2: 1, hw: 7 },
  { k: 'D', ...(() => { const p = polar(212, A_G62), q = polar(84, A_G62); return { x1: p.x, z1: p.z, x2: q.x, z2: q.z }; })(), hw: 7 },
  { k: 'E', x1: 60, z1: -230, x2: 60, z2: -66, hw: 7 }
];
const ROUTES = {
  A: { no: 1, km: 0.8, start: { x: -30, z: 205, yaw: Math.PI }, out: [{ x: -30, z: 95 }, { x: -8, z: 74 }, polar(R_OUT + 3, A_G1)] },
  B: { no: 1, km: 0.7, start: { x: 55, z: 205, yaw: Math.PI }, out: [{ x: 55, z: 88 }, { x: 14, z: 72 }, polar(R_OUT + 3, A_G1)] },
  C: { no: 79, km: 0.5, start: { x: -203, z: 1, yaw: Math.PI / 2 }, out: [{ x: -92, z: 1 }, polar(R_OUT + 3, A_G79)] },
  D: { no: 62, km: 1.0, start: { ...polar(203, A_G62), yaw: faceIn(A_G62) }, out: [polar(92, A_G62), polar(R_OUT + 3, A_G62)] },
  E: { no: 45, km: 0.9, start: { x: 61, z: -214, yaw: 0 }, out: [{ x: 60, z: -82 }, { x: 30, z: -70 }, polar(R_OUT + 3, A_G45)] }
};
const ROUTE_KEYS = ['A', 'B', 'C', 'D', 'E'];
for (const k of ROUTE_KEYS){
  const R = ROUTES[k], pts = [R.start, ...R.out]; let u = 0;
  for (let i = 1; i < pts.length; i++) u += Math.hypot(pts[i].x - pts[i - 1].x, pts[i].z - pts[i - 1].z);
  R.units = u; R.scale = R.km * 1000 / u;                 // metres per world unit on this street
  const a = GATE_ANG[R.no]; R.inn = [polar(52, a), polar(40.5, a), polar(34, a)];
}
const SAFA_ROUTE = [polar(35.5, A_SAFA), polar(41, A_SAFA), polar(52, A_SAFA), polar(66, A_SAFA), { x: 66.5, z: 34.5 }, { x: 70, z: 40.5 }];
function segDist(x, z, s){
  const vx = s.x2 - s.x1, vz = s.z2 - s.z1, u = clamp(((x - s.x1) * vx + (z - s.z1) * vz) / (vx * vx + vz * vz), 0, 1);
  return Math.hypot(x - (s.x1 + vx * u), z - (s.z1 + vz * u));
}
function walkable(x, z){
  if (x * x + z * z < PLAZA_R * PLAZA_R) return true;
  for (const r of ROADS) if (segDist(x, z, r) < r.hw) return true;
  return false;
}
function pushRing(p, R, t){
  const r = Math.hypot(p.x, p.z), m = t / 2 + PR;
  if (Math.abs(r - R) >= m || r < 1e-6) return;
  if (openingAt(R, angleOf(p.x, p.z))) return;
  const k = (r < R ? R - m : R + m) / r; p.x *= k; p.z *= k;
}
const arDigits = n => String(n).replace(/\d/g, d => '٠١٢٣٤٥٦٧٨٩'[d]);
function gateSign(no){
  const c = document.createElement('canvas'); c.width = 1024; c.height = 340; const g = c.getContext('2d');
  roundRect(g, 8, 8, 1008, 324, 26); g.fillStyle = 'rgba(15,74,52,0.95)'; g.fill(); g.lineWidth = 8; g.strokeStyle = '#d4b35e'; g.stroke();
  g.beginPath(); g.arc(172, 170, 130, 0, TAU); g.fillStyle = '#fbfaf6'; g.fill(); g.lineWidth = 10; g.strokeStyle = '#d4b35e'; g.stroke();
  g.fillStyle = '#0f4a34'; g.textAlign = 'center'; g.textBaseline = 'middle';
  g.font = `700 ${String(no).length > 1 ? 128 : 160}px "Hind Siliguri", system-ui, sans-serif`; g.fillText(String(no), 172, 150);
  g.font = '700 58px Amiri, serif'; g.fillText(arDigits(no), 172, 248);
  g.fillStyle = '#ffffff'; g.font = '700 100px Amiri, "Noto Naskh Arabic", serif'; g.fillText(GATES[no].ar, 655, 115);
  g.font = '600 62px "Hind Siliguri", system-ui, sans-serif'; g.fillText(GATES[no].n.en, 655, 238);
  const tx = new THREE.CanvasTexture(c); tx.anisotropy = MAXANISO;
  const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tx, depthWrite: false, fog: false })); sp.scale.set(12, 12 * 340 / 1024, 1);
  return sp;
}

function buildWorld(){
  const G = new THREE.Group();
  const sc = { name: 'world', group: G, boxes: [], segs: [], circles: [], infos: [], cboxes: [] };
  sc.bg = skyTex('#79a5c9', '#cfe1ea', '#f3e7d0'); sc.fog = new THREE.Fog(0xefe3cc, 220, 780);
  const dummy = new THREE.Object3D(), mm = new THREE.Matrix4();
  const unitBox = new THREE.BoxGeometry(1, 1, 1); unitBox.translate(0, 0.5, 0);

  /* ---- Ground: city paving, marble plazas, the two streets ---- */
  const gTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 5, '#d9d1c1', 'rgba(110,100,85,.25)'), 260, 260);
  add(G, new THREE.PlaneGeometry(1600, 1600).rotateX(-Math.PI / 2), lam(0xd8d2c6, { map: gTex }));
  const pTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 7, '#f2f0ea', 'rgba(140,140,135,.28)'), 50, 50);
  add(G, new THREE.CircleGeometry(PLAZA_R, 128).rotateX(-Math.PI / 2), lam(0xd3d0c9, { map: pTex }), 0, 0.01, 0);
  const rTex = ctex(128, 128, (g, w, h) => { g.fillStyle = '#bdb3a2'; g.fillRect(0, 0, w, h); g.strokeStyle = 'rgba(90,80,70,.25)'; g.lineWidth = 2; for (let i = 0; i <= w; i += 32){ g.beginPath(); g.moveTo(i, 0); g.lineTo(i, h); g.stroke(); g.beginPath(); g.moveTo(0, i); g.lineTo(w, i); g.stroke(); } });
  for (const r of ROADS){
    const dx = r.x2 - r.x1, dz = r.z2 - r.z1, L = Math.hypot(dx, dz), tx = rTex.clone(); tx.needsUpdate = true; tx.repeat.set(4, Math.round(L / 3.5));
    add(G, new THREE.PlaneGeometry(r.hw * 2, L).rotateX(-Math.PI / 2), lam(0xffffff, { map: tx }), (r.x1 + r.x2) / 2, 0.012, (r.z1 + r.z2) / 2).rotation.y = Math.atan2(dx, dz);
  }
  const mTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 9, '#f5f6f4', 'rgba(140,150,155,.26)'), 26, 26);
  add(G, new THREE.CircleGeometry(R_IN, 128).rotateX(-Math.PI / 2), lam(0xe4e6e2, { map: mTex }), 0, 0.02, 0);

  /* ---- The Kaaba (own frame; +z face is the north-east wall with the door) ---- */
  const K = new THREE.Group(); K.rotation.y = KAABA_ROT; G.add(K);
  add(K, new THREE.BoxGeometry(KW + 1.0, KB, KD + 1.0), lam(0xd9d9d3), 0, KB / 2, 0);
  const kSide = lam(0xffffff, { map: kiswaTex }), kTop = lam(0x141210);
  add(K, new THREE.BoxGeometry(KW, KH, KD), [kSide, kSide, kTop, kTop, kSide, kSide], 0, KB + KH / 2, 0);
  add(K, new THREE.BoxGeometry(2.0, 3.3, 0.22), new THREE.MeshPhongMaterial({ map: doorTex, specular: 0x775522, shininess: 40 }), -3.0, KB + 2.2 + 1.65, KD / 2 + 0.09);
  const bs = new THREE.Group(); bs.position.set(-KW / 2 - 0.07, KB + 1.15, KD / 2 + 0.07); bs.rotation.y = -Math.PI / 4; K.add(bs);
  add(bs, new THREE.TorusGeometry(0.34, 0.09, 10, 32), MAT.silver).scale.set(0.85, 1.15, 1);
  add(bs, new THREE.CircleGeometry(0.33, 24), lam(0x2a201b), 0, 0, -0.02).scale.set(0.85, 1.15, 1);
  add(K, new THREE.BoxGeometry(1.8, 0.3, 0.5), MAT.gold, KW / 2 + 0.9, KB + KH - 0.5, 0);
  const hijrMat = lam(0xf2f2ef), capMat = lam(0xd6cfc0);
  { const N = 28, p0 = -Math.PI / 2 + 0.16, p1 = Math.PI / 2 - 0.16; let prev = null;
    for (let i = 0; i <= N; i++){
      const ph = p0 + (p1 - p0) * i / N, pt = { x: KW / 2 + HIJR_A * Math.cos(ph), z: HIJR_B * Math.sin(ph) };
      if (prev){
        const dx = pt.x - prev.x, dz = pt.z - prev.z, len = Math.hypot(dx, dz), ry = -Math.atan2(dz, dx);
        add(K, new THREE.BoxGeometry(len + 0.06, 1.3, 0.85), hijrMat, (pt.x + prev.x) / 2, 0.65, (pt.z + prev.z) / 2).rotation.y = ry;
        add(K, new THREE.BoxGeometry(len + 0.06, 0.12, 0.95), capMat, (pt.x + prev.x) / 2, 1.36, (pt.z + prev.z) / 2).rotation.y = ry;
      }
      prev = pt;
    } }
  // Tawaf starting line and the green lamp on the colonnade that marks it
  const lineLen = 35.5 - 8.6, rc = 8.6 + lineLen / 2;
  add(G, new THREE.PlaneGeometry(lineLen, 0.22).rotateX(-Math.PI / 2), new THREE.MeshBasicMaterial({ color: 0x7a5a3a, transparent: true, opacity: .42, depthWrite: false }), rc * Math.cos(A0), 0.04, -rc * Math.sin(A0)).rotation.y = A0;
  const lamp = polar(37.0, A0); add(G, new THREE.BoxGeometry(0.6, 0.6, 0.6), new THREE.MeshBasicMaterial({ color: 0x3ee28a }), lamp.x, 4.0, lamp.z);
  // Maqam Ibrahim
  const mq = new THREE.Group(); mq.position.set(MAQAM.x, 0, MAQAM.z); G.add(mq);
  add(mq, new THREE.CylinderGeometry(1.0, 1.15, 0.4, 8), lam(0xdedad0), 0, 0.2, 0);
  add(mq, new THREE.BoxGeometry(0.55, 0.55, 0.55), lam(0x9d8a6e), 0, 0.68, 0);
  add(mq, new THREE.CylinderGeometry(0.72, 0.72, 1.9, 8, 1, true), new THREE.MeshPhongMaterial({ color: 0xd8b65a, specular: 0xffe9a8, shininess: 60, transparent: true, opacity: .5, side: THREE.DoubleSide, depthWrite: false }), 0, 1.35, 0);
  add(mq, new THREE.CylinderGeometry(0.8, 0.8, 0.12, 8), MAT.gold, 0, 0.42, 0);
  add(mq, new THREE.CylinderGeometry(0.8, 0.8, 0.12, 8), MAT.gold, 0, 2.3, 0);
  add(mq, new THREE.SphereGeometry(0.75, 16, 8, 0, TAU, 0, Math.PI / 2), MAT.gold, 0, 2.35, 0);
  add(mq, new THREE.ConeGeometry(0.1, 0.6, 8), MAT.gold, 0, 3.35, 0);
  sc.circles.push({ x: MAQAM.x, z: MAQAM.z, r: 1.15 });
  // Zamzam station
  const zz = new THREE.Group(); zz.position.set(ZAMZAM.x, 0, ZAMZAM.z); zz.rotation.y = 0.30 + Math.PI / 2; G.add(zz);
  add(zz, new THREE.BoxGeometry(5.2, 0.9, 1.1), lam(0xd9d4c8), 0, 0.45, 0);
  for (const k of [-1.8, -0.6, 0.6, 1.8]){ add(zz, new THREE.CylinderGeometry(0.32, 0.32, 1.0, 16), MAT.silver, k, 1.4, 0); add(zz, new THREE.BoxGeometry(0.12, 0.1, 0.22), MAT.silver, k, 1.05, 0.4); }
  const zs = signSprite('زمزم', 'Zamzam', 4.2); zs.position.set(0, 3.3, 0); zz.add(zs);
  const tg = { x: -Math.sin(0.30), z: -Math.cos(0.30) };
  sc.circles.push({ x: ZAMZAM.x + tg.x * 1.4, z: ZAMZAM.z + tg.z * 1.4, r: 1.3 }, { x: ZAMZAM.x - tg.x * 1.4, z: ZAMZAM.z - tg.z * 1.4, r: 1.3 });

  /* ---- Ottoman colonnade around the mataf ---- */
  const NC = 72, RC = 37.4, colGeo = new THREE.CylinderGeometry(0.4, 0.46, 4.4, 10); colGeo.translate(0, 2.2, 0);
  const colTh = []; for (let i = 0; i < NC; i++){ const th = i / NC * TAU; if (!openingAt(RC, th - Math.PI / 2, 0.9)) colTh.push(th); }
  const cols = new THREE.InstancedMesh(colGeo, lam(0xefe8da), colTh.length);
  colTh.forEach((th, i) => { const x = RC * Math.sin(th), z = RC * Math.cos(th); mm.makeTranslation(x, 0, z); cols.setMatrixAt(i, mm); sc.circles.push({ x, z, r: 0.46, cam: true }); });
  G.add(cols);
  add(G, new THREE.CylinderGeometry(RC, RC, 3.0, NC * 2, 1, true), lam(0xffffff, { map: archBand(NC), alphaTest: .5, side: THREE.DoubleSide }), 0, 5.6, 0);
  add(G, new THREE.RingGeometry(RC - 0.5, R_IN, 144, 1).rotateX(-Math.PI / 2), lam(0xd9ccb3, { side: THREE.DoubleSide }), 0, 7.1, 0);
  add(G, new THREE.CylinderGeometry(RC - 0.5, RC - 0.5, 0.6, 144, 1, true), lam(0xcdbd9e, { side: THREE.DoubleSide }), 0, 7.4, 0);
  const domes = new THREE.InstancedMesh(new THREE.SphereGeometry(1.5, 14, 8, 0, TAU, 0, Math.PI / 2), lam(0xd6c8ad), NC / 2);
  for (let i = 0; i < NC / 2; i++){ const th = (i + .5) / (NC / 2) * TAU; mm.makeTranslation(40.7 * Math.sin(th), 7.1, 40.7 * Math.cos(th)); domes.setMatrixAt(i, mm); }
  G.add(domes);

  /* ---- Mosque halls between the colonnade and the outer facade, with real gate openings ---- */
  function ringWall(R, t, h, segLen, mat, lintelY, lintelMat){
    const ops = OPENINGS.slice().sort((p, q) => p.a - q.a), parts = [];
    ops.forEach((o, k) => {
      const nx = ops[(k + 1) % ops.length];
      const e1 = o.a + o.hw / R; let e2 = nx.a - nx.hw / R; if (e2 <= e1) e2 += TAU;
      const span = e2 - e1, n = Math.max(1, Math.round(span * R / segLen));
      for (let j = 0; j < n; j++) parts.push({ a: e1 + (j + .5) / n * span, len: span * R / n });
      const p = polar(R, o.a);
      add(G, new THREE.BoxGeometry(o.hw * 2 + 0.3, h - lintelY, t + 0.1), lintelMat, p.x, (h + lintelY) / 2, p.z).rotation.y = o.a + Math.PI / 2;
      for (const s of [-1, 1]){ const q = polar(R, o.a + s * o.hw / R); sc.circles.push({ x: q.x, z: q.z, r: t / 2 + 0.1 }); }
    });
    const im = new THREE.InstancedMesh(unitBox, mat, parts.length);
    parts.forEach((pt, i) => { const p = polar(R, pt.a); dummy.position.set(p.x, 0, p.z); dummy.rotation.set(0, pt.a + Math.PI / 2, 0); dummy.scale.set(pt.len + 0.08, h, t); dummy.updateMatrix(); im.setMatrixAt(i, dummy.matrix); });
    G.add(im);
  }
  ringWall(R_IN, 1.2, HALL_H, 3.2, lam(0xffffff, { map: windowWall({ base: '#e4d8c1', trim: '#c4b08b', glass: '#6a6253' }) }), IN_LINTEL, lam(0xd9ccb3));
  ringWall(R_OUT, 1.6, BLDG_H, 4.2, lam(0xffffff, { map: windowWall({ base: '#ebe2cf', trim: '#c7b38c', glass: '#58544b', pil: '#d9ccb1', ry: 3 }) }), OUT_LINTEL, lam(0xd9ccb3));
  add(G, new THREE.RingGeometry(R_IN - 0.6, R_OUT + 0.8, 160).rotateX(-Math.PI / 2), lam(0xe9e1cf, { side: THREE.DoubleSide }), 0, HALL_H, 0);
  add(G, new THREE.CylinderGeometry(47, 47, BLDG_H - HALL_H, 160, 1, true), lam(0xffffff, { map: windowWall({ base: '#e2d6bf', trim: '#c9b792', glass: '#4f4c46', rx: 60, ry: 3 }), side: THREE.DoubleSide }), 0, (BLDG_H + HALL_H) / 2, 0);
  add(G, new THREE.RingGeometry(46.8, R_OUT + 0.8, 160).rotateX(-Math.PI / 2), lam(0xd9cdb5, { side: THREE.DoubleSide }), 0, BLDG_H, 0);
  // Carpeted prayer areas in the halls, leaving marble walkways in front of each opening
  const carpet = ctex(256, 256, (g, w, h) => { g.fillStyle = '#8a1d24'; g.fillRect(0, 0, w, h); g.fillStyle = '#a63a3a'; g.fillRect(0, 0, w, 7); g.strokeStyle = 'rgba(240,206,150,.4)'; g.lineWidth = 4; archPath(g, w * .2, h - 14, w * .8, 26); g.stroke(); }, 14, 14);
  { const ops = OPENINGS.slice().sort((p, q) => p.a - q.a);
    ops.forEach((o, k) => { const nx = ops[(k + 1) % ops.length]; const e1 = o.a + (o.hw + 3) / 52; let e2 = nx.a - (nx.hw + 3) / 52; if (e2 <= e1) e2 += TAU;
      add(G, new THREE.RingGeometry(46, 58, 40, 1, e1, e2 - e1).rotateX(-Math.PI / 2), lam(0xffffff, { map: carpet }), 0, 0.025, 0); }); }
  // Hall columns
  { const pts = []; for (const r of [49.5, 54.5]) for (let i = 0; i < 40; i++){ const a = (i + (r > 50 ? .5 : 0)) / 40 * TAU; if (!openingAt(r, a, 2.4)) pts.push(polar(r, a)); }
    const hg = new THREE.CylinderGeometry(0.5, 0.55, HALL_H, 12); hg.translate(0, HALL_H / 2, 0);
    const hc = new THREE.InstancedMesh(hg, lam(0xf3efe6), pts.length);
    pts.forEach((p, i) => { mm.makeTranslation(p.x, 0, p.z); hc.setMatrixAt(i, mm); sc.circles.push({ x: p.x, z: p.z, r: 0.55, cam: true }); });
    G.add(hc); }
  // Gate portals with their numbers, and the two minarets over each main gate
  function portal(no){
    const a = GATE_ANG[no], p = polar(R_OUT, a), g = new THREE.Group(), th = Math.atan2(p.x, p.z), hw = 3.4;
    g.position.set(p.x, 0, p.z); g.rotation.y = th; G.add(g);
    const tan = { x: Math.cos(th), z: -Math.sin(th) }, out = { x: Math.sin(th), z: Math.cos(th) };
    for (const s of [-1, 1]){
      add(g, new THREE.BoxGeometry(2.6, 15, 3.2), MAT.stoneDark, s * (hw + 1.3), 7.5, 1.0);
      sc.circles.push({ x: p.x + tan.x * s * (hw + 1.3) + out.x * 1.0, z: p.z + tan.z * s * (hw + 1.3) + out.z * 1.0, r: 1.7 });
      const m = minaret(72, 1.05), q = polar(R_OUT + 7, a + s * 0.17); m.position.set(q.x, 0, q.z); G.add(m);
      sc.boxes.push({ x0: q.x - 2.3, x1: q.x + 2.3, z0: q.z - 2.3, z1: q.z + 2.3 }); sc.cboxes.push({ x0: q.x - 2.3, x1: q.x + 2.3, z0: q.z - 2.3, z1: q.z + 2.3, h: 90 });
    }
    add(g, new THREE.BoxGeometry(hw * 2 + 5.2, 3.6, 3.2), MAT.stoneDark, 0, 13.2, 1.0);
    add(g, new THREE.BoxGeometry(hw * 2 + 0.2, 0.5, 0.4), MAT.gold, 0, OUT_LINTEL - 0.2, 2.2);
    const sgn = gateSign(no); sgn.scale.set(10, 10 * 340 / 1024, 1); sgn.position.set(0, 11.2, 3.4); g.add(sgn);
    const ms = signSprite('المطاف', 'Mataf', 4); const mp = polar(R_IN + 1.7, a); ms.position.set(mp.x, 8.7, mp.z); G.add(ms);
  }
  MAIN_GATES.forEach(portal);
  // Signs along the way to Safa
  { const s1 = signSprite('الصفا', 'Safa', 3.6), q1 = polar(36.0, A_SAFA); s1.position.set(q1.x, 4.6, q1.z); G.add(s1);
    const s2 = signSprite('الصفا', 'Safa', 4), q2 = polar(R_OUT - 1.8, A_SAFA); s2.position.set(q2.x, 7.6, q2.z); G.add(s2);
    const s3 = signSprite('المسعى', "Mas'a", 4); s3.position.set(60.4, 7.2, (ZW0 + ZW1) / 2); G.add(s3); }
  // Passage walls to the Mas'a
  PW.forEach(w => { const dx = w.x2 - w.x1, dz = w.z2 - w.z1, L = Math.hypot(dx, dz);
    add(G, new THREE.BoxGeometry(L, HALL_H, w.t), MAT.stone, (w.x1 + w.x2) / 2, HALL_H / 2, (w.z1 + w.z2) / 2).rotation.y = -Math.atan2(dz, dx); sc.segs.push(w); });

  /* ---- The Mas'a ---- */
  const masaWall = windowWall({ base: '#ece4d4', trim: '#c9b48a', glass: '#b9ad94' });
  function mbox(x0, x1, z0, z1, h, y0 = 0, collide = true){
    const len = Math.max(x1 - x0, z1 - z0), tx = masaWall.clone(); tx.needsUpdate = true; tx.repeat.set(Math.max(1, Math.round(len / 7)), Math.max(1, Math.round((h - y0) / 6)));
    add(G, new THREE.BoxGeometry(x1 - x0, h - y0, z1 - z0), lam(0xffffff, { map: tx }), (x0 + x1) / 2, (h + y0) / 2, (z0 + z1) / 2);
    if (collide) sc.boxes.push({ x0, x1, z0, z1 }); sc.cboxes.push({ x0, x1, z0, z1, h, y0 });
  }
  mbox(61.6, MX0, -66, ZW0, 18); mbox(61.6, MX0, ZW1, 58, 18); mbox(61.6, MX0, ZW0, ZW1, 18, 9, false);
  mbox(MX1, 78.4, -66, 58, 18); mbox(61.6, 78.4, 57, 58, 18); mbox(61.6, 78.4, -66, -65, 18);
  const fTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 13, '#f3f3f0', 'rgba(140,150,155,.3)'), 4, 30);
  add(G, new THREE.PlaneGeometry(MX1 - MX0, 122).rotateX(-Math.PI / 2), lam(0xe6e6e2, { map: fTex }), 70, 0.03, -4);
  add(G, new THREE.PlaneGeometry(MX1 - MX0, 122).rotateX(Math.PI / 2), lam(0xf3ecdf), 70, 11.3, -4);
  add(G, new THREE.PlaneGeometry(16.8, 124).rotateX(-Math.PI / 2), lam(0xd9cdb5), 70, 18.02, -4);
  { const lg = new THREE.BoxGeometry(2.2, 0.12, 3.2), white = [], green = [];
    for (let z = -62; z <= 54; z += 5) for (const x of [65.5, 70, 74.5]) ((z > GZ0 && z < GZ1) ? green : white).push([x, z]);
    const mkL = (list, mat) => { const im = new THREE.InstancedMesh(lg, mat, list.length); list.forEach(([x, z], i) => { mm.makeTranslation(x, 11.2, z); im.setMatrixAt(i, mm); }); G.add(im); };
    mkL(white, new THREE.MeshBasicMaterial({ color: 0xfff7e6 })); mkL(green, new THREE.MeshBasicMaterial({ color: 0x2fe07c }));
    const cg = new THREE.BoxGeometry(1.1, 11.3, 1.1); cg.translate(0, 5.65, 0);
    const cpos = []; for (let z = -60; z <= 52; z += 7) for (const x of [66.5, 73.5]) cpos.push([x, z]);
    const ci = new THREE.InstancedMesh(cg, lam(0xece5d6), cpos.length);
    cpos.forEach(([x, z], i) => { mm.makeTranslation(x, 0, z); ci.setMatrixAt(i, mm); sc.circles.push({ x, z, r: 0.75, cam: true }); });
    G.add(ci);
    const band = new THREE.BoxGeometry(1.25, 0.5, 1.25), gm = new THREE.MeshBasicMaterial({ color: 0x2fe07c });
    cpos.filter(([, z]) => z > GZ0 && z < GZ1).forEach(([x, z]) => add(G, band, gm, x, 3.2, z)); }
  const rock = (z, sx, sy, sz, seed) => {
    const geo = new THREE.IcosahedronGeometry(1, 2), pa = geo.attributes.position, r = rng(seed), v = new THREE.Vector3();
    for (let i = 0; i < pa.count; i++){ v.fromBufferAttribute(pa, i); v.multiplyScalar(0.78 + r() * 0.4); pa.setXYZ(i, v.x, v.y, v.z); }
    geo.computeVertexNormals(); add(G, geo, new THREE.MeshPhongMaterial({ color: 0x9c846a, flatShading: true, shininess: 5 }), 70, 0, z).scale.set(sx, sy, sz);
  };
  rock(51.5, 6.5, 3.6, 5.5, 4); rock(-59.5, 6, 3, 5, 8);
  const glass = new THREE.MeshBasicMaterial({ color: 0xcfe3e6, transparent: true, opacity: .28, depthWrite: false });
  for (const z of [45.55, -53.55]){ add(G, new THREE.BoxGeometry(MX1 - MX0, 1.1, 0.12), glass, 70, 0.55, z); sc.boxes.push({ x0: MX0, x1: MX1, z0: z - 0.15, z1: z + 0.15 }); }
  [[signSprite('الصفا', 'Safa', 6), 70, 8.6, 43.5], [signSprite('المروة', 'Marwa', 6), 70, 8.6, -51.5],
   [signSprite('القبلة', 'Qibla', 3.4), 63.4, 5.2, 40], [signSprite('القبلة', 'Qibla', 3.4), 63.4, 5.2, -48],
   [signSprite('باب الصفا', 'Al-Safa Gate 13', 4.6), 76.2, 7.4, 34], [signSprite('باب المروة', 'Al-Marwah Gate', 4.6), 76.2, 7.4, -42],
   [signSprite('باب الصفا', 'Al-Safa Gate 13', 6), 80, 9, 34], [signSprite('باب المروة', 'Al-Marwah Gate', 6), 80, 9, -42]]
   .forEach(([s, x, y, z]) => { s.position.set(x, y, z); G.add(s); });
  const doorT = ctex(128, 256, (g, w, h) => { g.fillStyle = '#efe6d3'; g.fillRect(0, 0, w, h); g.fillStyle = '#c9a24a'; archPath(g, 8, h - 4, w - 8, 26); g.fill(); g.fillStyle = '#3a2f22'; archPath(g, 22, h - 4, w - 22, 50); g.fill(); });
  for (const z of [34, -42]){ add(G, new THREE.PlaneGeometry(3.2, 5.2), lam(0xffffff, { map: doorT }), MX1 - 0.04, 2.6, z).rotation.y = -Math.PI / 2;
    add(G, new THREE.PlaneGeometry(3.2, 5.2), lam(0xffffff, { map: doorT }), 78.44, 2.6, z).rotation.y = Math.PI / 2; }

  /* ---- The city around the Haram: real districts and landmarks ---- */
  const hTex = [hotelTex('#d8cdb8', '#7c7466'), hotelTex('#c9c2b5', '#5e6670'), hotelTex('#e3dccd', '#8a7d68')];
  const glassTex = ctex(128, 256, (g, w, h) => { g.fillStyle = '#9fb3bf'; g.fillRect(0, 0, w, h); g.fillStyle = '#6f8796'; for (let y = 6; y < h; y += 16) g.fillRect(0, y, w, 7); g.fillStyle = '#e6e0d2'; g.fillRect(0, 0, 6, h); g.fillRect(w - 6, 0, 6, h); }, 2, 6);
  const bld = (x0, x1, z0, z1, h, ti, mat) => { add(G, new THREE.BoxGeometry(x1 - x0, h, z1 - z0), mat || lam(0xffffff, { map: hTex[ti % 3] }), (x0 + x1) / 2, h / 2, (z0 + z1) / 2); sc.cboxes.push({ x0, x1, z0, z1, h }); };
  const glassM = lam(0xffffff, { map: glassTex });
  const sign = (ar, en, w, x, y, z) => { const sp = signSprite(ar, en, w); sp.position.set(x, y, z); G.add(sp); return sp; };
  const hr = rng(21);
  // South: Abraj Al Bait with the Clock Tower, facing King Abdulaziz Gate
  bld(-16, 44, 112, 192, 30, 0); bld(-12, 8, 120, 140, 70, 1); bld(24, 42, 165, 188, 80, 2); bld(-14, 4, 160, 186, 64, 2); bld(26, 42, 118, 138, 58, 1);
  const clockTex = ctex(256, 256, (g, w, h) => {
    g.fillStyle = '#e9e2d0'; g.fillRect(0, 0, w, h); g.beginPath(); g.arc(w / 2, h / 2, 116, 0, TAU); g.fillStyle = '#f8f6ef'; g.fill(); g.lineWidth = 12; g.strokeStyle = '#1f6a47'; g.stroke();
    g.strokeStyle = '#16130e'; g.lineWidth = 6; for (let i = 0; i < 12; i++){ const a = i / 12 * TAU; g.beginPath(); g.moveTo(w / 2 + Math.cos(a) * 92, h / 2 + Math.sin(a) * 92); g.lineTo(w / 2 + Math.cos(a) * 106, h / 2 + Math.sin(a) * 106); g.stroke(); }
    g.lineWidth = 7; g.beginPath(); g.moveTo(w / 2, h / 2); g.lineTo(w / 2 + 40, h / 2 - 48); g.moveTo(w / 2, h / 2); g.lineTo(w / 2 - 8, h / 2 - 86); g.stroke();
  });
  const ct = new THREE.Group(); ct.position.set(14, 30, 152); G.add(ct);
  add(ct, new THREE.BoxGeometry(26, 100, 26), lam(0xffffff, { map: hTex[0] }), 0, 50, 0);
  add(ct, new THREE.BoxGeometry(20, 26, 20), lam(0xe9e2d0), 0, 113, 0);
  const cf = lam(0xffffff, { map: clockTex });
  for (let i = 0; i < 4; i++) add(ct, new THREE.PlaneGeometry(15, 15), cf, 10.1 * Math.sin(i * Math.PI / 2), 113, 10.1 * Math.cos(i * Math.PI / 2)).rotation.y = i * Math.PI / 2;
  add(ct, new THREE.CylinderGeometry(6, 8.5, 10, 8), lam(0xe9e2d0), 0, 131, 0);
  add(ct, new THREE.ConeGeometry(3, 32, 8), MAT.gold, 0, 152, 0); crescent(ct, 170, 3);
  sign('أبراج البيت', 'Abraj Al Bait (Clock Tower)', 14, 14, 37, 110.5);
  // South-west: Ibrahim Al Khalil Road from Misfalah, Jabal Omar towers on its west side
  for (let z = 84; z < 230; z += 26){ bld(-80 + hr() * 10, -42, z, z + 20, 34 + hr() * 50, 0, hr() < .5 ? glassM : null); bld(-130, -88, z - 6, z + 18, 40 + hr() * 50, Math.floor(hr() * 3)); }
  for (let z = 196; z < 232; z += 18) bld(-19, -2, z, z + 15, 26 + hr() * 30, Math.floor(hr() * 3));
  sign('المسفلة', 'Misfalah', 5, -30, 6.5, 214);
  // South-east: Ajyad Street, Al Safwah Royale Orchid at its plaza end
  bld(64, 88, 74, 92, 74, 1); sign('الصفوة رويال أوركيد', 'Al Safwah Royale Orchid', 8, 63, 20, 83);
  for (let z = 96; z < 206; z += 24) bld(65, 65 + 14 + hr() * 10, z, z + 19, 30 + hr() * 34, Math.floor(hr() * 3));
  // Shops at street level on Ibrahim Al Khalil Road and Ajyad Street
  const shopM = [0xb5513e, 0x2f6c48, 0x2d5d8a, 0xc79a3c, 0x7a4c7e].map(c => lam(c)), awnM = lam(0xf3ece0);
  const shops = (xw, xe, z0, z1, skipE) => { for (let z = z0; z < z1; z += 7.5) for (const [x, s2] of [[xw, 1], [xe, -1]]){
    if (skipE && skipE(x, z)) continue; const k = Math.floor(hr() * 5);
    add(G, new THREE.BoxGeometry(1.2, 4.2, 6.6), shopM[k], x, 2.1, z + 3.5);
    add(G, new THREE.BoxGeometry(1.6, 0.15, 6.8), awnM, x + s2 * 0.9, 3.9, z + 3.5).rotation.z = s2 * 0.25; } };
  shops(-38.6, -21.4, 98, 205, (x, z) => x > -30 && z > 108 && z < 194);
  shops(46.4, 63.6, 96, 205, (x, z) => x < 55 && z > 108 && z < 194);
  // West: Jabal Omar, modern hotel towers on the walk to King Fahd Gate
  for (let x = -200; x < -96; x += 22){ bld(x, x + 17, -44 + hr() * 6, -11, 60 + hr() * 55, 0, glassM); bld(x + 2, x + 18, 13, 44 - hr() * 6, 55 + hr() * 55, 0, hr() < .6 ? glassM : null); }
  sign('جبل عمر', 'Jabal Omar', 9, -150, 9, 1); sign('جبل عمر', 'Jabal Omar', 9, -98, 9, 1);
  // North-west: Jarwal, mid-rise hotels on the walk to Umrah Gate
  { const a = A_G62, d = { x: Math.cos(a), z: -Math.sin(a) }, n = { x: -d.z, z: d.x };
    for (let r = 104; r < 206; r += 21) for (const s2 of [-1, 1]){
      const off = 17 + hr() * 5, c = { x: d.x * r + n.x * off * s2, z: d.z * r + n.z * off * s2 }, h = 26 + hr() * 30;
      add(G, new THREE.BoxGeometry(16, h, 15), lam(0xffffff, { map: hTex[Math.floor(hr() * 3)] }), c.x, h / 2, c.z).rotation.y = Math.atan2(d.x, d.z);
    }
    const s1 = polar(170, a), s3 = polar(112, a); sign('جرول', 'Jarwal', 6, s1.x, 7.5, s1.z); sign('جرول', 'Jarwal', 6, s3.x, 7.5, s3.z); }
  // North: the King Abdullah expansion behind Gate 100
  add(G, new THREE.BoxGeometry(70, 32, 50), lam(0xffffff, { map: windowWall({ base: '#ebe2cf', trim: '#c7b38c', glass: '#58544b', rx: 10, ry: 3 }) }), -45, 16, -125); sc.cboxes.push({ x0: -80, x1: -10, z0: -150, z1: -100, h: 32 });
  for (const x of [-62, -28]){ const m = minaret(80, 1.1); m.position.set(x, 0, -97); G.add(m); }
  sign('توسعة الملك عبدالله', 'King Abdullah Expansion', 12, -45, 40, -99);
  // North-east: Masjid al-Haram Road to Masjid al-Jinn, past Jannat al-Mu'alla
  for (let z = -206; z < -84; z += 24) bld(70, 84 + hr() * 12, z, z + 19, 28 + hr() * 40, Math.floor(hr() * 3));
  bld(20, 49, -104, -86, 30, 1);
  const cWall = lam(0xd8c9a8), grav = lam(0xb9a98c);
  add(G, new THREE.PlaneGeometry(46, 95).rotateX(-Math.PI / 2), grav, 25, 0.02, -157.5);
  for (const [x0, x1, z0, z1] of [[2, 48, -205.6, -204.4], [2, 48, -110.6, -109.4], [1.4, 2.6, -205, -110], [47.4, 48.6, -205, -110]])
    add(G, new THREE.BoxGeometry(x1 - x0, 2.4, z1 - z0), cWall, (x0 + x1) / 2, 1.2, (z0 + z1) / 2);
  sign('مقبرة المعلاة', "Jannat al-Mu'alla", 6, 50.5, 5.2, -160);
  const jinn = new THREE.Group(); jinn.position.set(77, 0, -222); G.add(jinn);
  add(jinn, new THREE.BoxGeometry(14, 9, 18), lam(0xffffff, { map: windowWall({ base: '#f2ede2', trim: '#c9b48a', glass: '#6b6656', rx: 2, ry: 2 }) }), 0, 4.5, 0);
  add(jinn, new THREE.BoxGeometry(1, 5.5, 4), MAT.gold, -7.1, 2.75, 0);
  const jm = minaret(40, 0.75); jm.position.set(4, 0, -7); jinn.add(jm);
  sc.cboxes.push({ x0: 70, x1: 84, z0: -231, z1: -213, h: 60 });
  sign('مسجد الجن', 'Masjid al-Jinn', 6, 68.6, 10.5, -222);
  // East: Jabal Abu Qubais with the royal palace, just beyond Safa
  { const geo = new THREE.IcosahedronGeometry(1, 2), pa = geo.attributes.position, r = rng(55), v = new THREE.Vector3();
    for (let i = 0; i < pa.count; i++){ v.fromBufferAttribute(pa, i); v.multiplyScalar(0.8 + r() * 0.35); if (v.y < 0) v.y *= 0.2; pa.setXYZ(i, v.x, v.y, v.z); }
    geo.computeVertexNormals(); add(G, geo, new THREE.MeshPhongMaterial({ color: 0x8f7a62, flatShading: true, shininess: 4 }), 150, 0, 8).scale.set(46, 42, 62);
    add(G, new THREE.BoxGeometry(46, 14, 30), lam(0xffffff, { map: windowWall({ base: '#f1ece1', trim: '#c9b48a', glass: '#6b6656', rx: 6, ry: 1 }) }), 140, 40, 4);
    sign('جبل أبي قبيس', 'Jabal Abu Qubais', 9, 128, 58, 4); }
  // Street names
  [['شارع إبراهيم الخليل', 'Ibrahim Al Khalil Rd', -30, 190], ['شارع إبراهيم الخليل', 'Ibrahim Al Khalil Rd', -30, 120],
   ['شارع أجياد', 'Ajyad St', 55, 190], ['شارع أجياد', 'Ajyad St', 55, 120],
   ['طريق المسجد الحرام', 'Masjid al-Haram Rd', 60, -196], ['طريق المسجد الحرام', 'Masjid al-Haram Rd', 60, -120]]
   .forEach(([ar, en, x, z]) => sign(ar, en, 6.5, x, 7.5, z));
  // Street lamps along every road and round the plaza edge
  { const lp = [];
    for (const r of ROADS){ const dx = r.x2 - r.x1, dz = r.z2 - r.z1, L = Math.hypot(dx, dz), nx = -dz / L, nz = dx / L;
      for (let d = 8; d < L - 10; d += 14) for (const s2 of [-1, 1]) lp.push([r.x1 + dx * d / L + nx * 6.4 * s2, r.z1 + dz * d / L + nz * 6.4 * s2]); }
    for (let i = 0; i < 48; i++){ const a = i / 48 * TAU, p = polar(92, a); if (walkable(p.x * 1.07, p.z * 1.07)) continue; if (p.x > 58 && Math.abs(p.z) < 70) continue; lp.push([p.x, p.z]); }
    const pg = new THREE.CylinderGeometry(0.12, 0.16, 6, 6); pg.translate(0, 3, 0);
    const hg = new THREE.SphereGeometry(0.35, 8, 6); hg.translate(0, 6.2, 0);
    const pi = new THREE.InstancedMesh(pg, lam(0x6b6458), lp.length), hi = new THREE.InstancedMesh(hg, new THREE.MeshBasicMaterial({ color: 0xfff1cc }), lp.length);
    lp.forEach(([x, z], i) => { mm.makeTranslation(x, 0, z); pi.setMatrixAt(i, mm); hi.setMatrixAt(i, mm); }); G.add(pi, hi); }
  // Skyline of other hotels, kept clear of the streets and named districts
  const blocked = (x, z, w) => {
    for (const r of ROADS) if (segDist(x, z, r) < r.hw + w * 0.75 + 6) return true;
    if (x > -140 && x < 95 && z > 66 && z < 240) return true;          // Clock Tower, Ibrahim Al Khalil, Ajyad
    if (x < -84 && Math.abs(z) < 60) return true;                      // Jabal Omar
    if (x > 92 && Math.abs(z - 8) < 80) return true;                   // Abu Qubais
    if (x > -88 && x < 100 && z < -84) return true;                    // expansion, Mu'alla, Masjid al-Jinn road
    return Math.hypot(x, z) < 108;
  };
  for (let i = 0; i < 70; i++){
    const a = hr() * TAU, d = 112 + hr() * 90, p = polar(d, a), w = 14 + hr() * 14;
    if (blocked(p.x, p.z, w)) continue;
    bld(p.x - w / 2, p.x + w / 2, p.z - w / 2, p.z + w / 2, 24 + hr() * 50, i);
  }

  /* ---- Places to learn about ---- */
  const gI = (no, r) => { const q = polar(R_OUT + 4, GATE_ANG[no]); return { key: 'gate' + no, x: q.x, z: q.z, r }; };
  const bsI = kw(-KW / 2 - 1.2, KD / 2 + 1.2), mzI = kw(-4.4, KD / 2 + 1.6), drI = kw(-2.6, KD / 2 + 2.6), hjI = kw(KW / 2 + HIJR_A + 1.6, 0), ymI = kw(-KW / 2 - 1.3, -KD / 2 - 1.3), sw = polar(37.5, A_SAFA);
  sc.infos.push(
    { key: 'blackStone', x: bsI.x, z: bsI.z, r: 3.6 }, { key: 'multazam', x: mzI.x, z: mzI.z, r: 2.0 }, { key: 'door', x: drI.x, z: drI.z, r: 2.4 },
    { key: 'hijr', x: hjI.x, z: hjI.z, r: 4.5 }, { key: 'yamani', x: ymI.x, z: ymI.z, r: 3.8 }, { key: 'maqam', x: MAQAM.x, z: MAQAM.z, r: 4 },
    { key: 'zamzam', x: ZAMZAM_WP.x, z: ZAMZAM_WP.z, r: 4 }, { key: 'safaWay', x: sw.x, z: sw.z, r: 4 },
    gI(1, 7), gI(45, 6), gI(100, 6), gI(62, 6), gI(79, 6),
    { key: 'ibrahimKhalil', x: -30, z: 165, r: 14 }, { key: 'masjidJinn', x: 63, z: -219, r: 8 }, { key: 'mualla', x: 55, z: -158, r: 9 },
    { key: 'clockTower', x: 14, z: 89, r: 9 }, { key: 'ajyad', x: 55, z: 140, r: 14 }, { key: 'jabalOmar', x: -130, z: 1, r: 14 },
    { key: 'jarwal', ...polar(140, A_G62), r: 14 }, { key: 'abuQubais', x: 88, z: 2, r: 8 }, { key: 'expansion', ...polar(84, A_G100), r: 9 },
    { key: 'safa', x: 70, z: 40.5, r: 5 }, { key: 'marwa', x: 70, z: -48.5, r: 5 }, { key: 'green', x: 70, z: 16.5, r: 7.5 },
    { key: 'safaGate', x: 75.6, z: 34, r: 2.6 }, { key: 'marwahGate', x: 75.6, z: -42, r: 2.6 });

  /* ---- Crowds ---- */
  const NM = isTouch ? 280 : 460, NSIT = isTouch ? 60 : 100, NHW = isTouch ? 20 : 40, NOUT = isTouch ? 110 : 180, NMASA = isTouch ? 130 : 210;
  const mat = makeCrowd(NM, 3, MALE_IHRAM); G.add(mat.bodies, mat.heads);
  mat.agents.forEach(a => { a.rad = 13 + 20 * Math.pow(a.r1, 1.3); a.ang = a.r2 * TAU; a.w = (0.9 + a.r3 * 0.6) / a.rad; });
  const hall = makeCrowd(NSIT + NHW, 11, MALE_IHRAM); G.add(hall.bodies, hall.heads);
  hall.agents.forEach((a, i) => {
    let ang = a.r2 * TAU; for (let k = 0; k < 8 && openingAt(52, ang, 4); k++) ang += 0.21;
    if (i < NSIT){ const p = polar(46.6 + a.r1 * 10.8, ang); hall.set(i, p.x, p.z, Math.atan2(-p.x, -p.z), 0, 0.6); a.kind = 's'; }
    else { a.kind = 'w'; a.ang = ang; a.rad = 47 + a.r1 * 10; a.w = (a.r3 < .5 ? -1 : 1) * (0.8 + a.r3 * 0.5) / a.rad; }
  });
  const outC = makeCrowd(NOUT, 23, MALE_IHRAM); G.add(outC.bodies, outC.heads);
  const PATHS = ROUTE_KEYS.map(k => { const R = ROUTES[k]; return [R.start, ...R.out.slice(0, -1), polar(R_OUT + 4, GATE_ANG[R.no])]; });
  const rr = rng(91);
  const wanderPt = a0 => { for (let k = 0; k < 10; k++){ const a = a0 + (rr() - .5) * 0.9, p = polar(64 + rr() * 28, a); if (!(p.x > 57 && p.z > -70 && p.z < 62)) return p; } return polar(80, a0 + Math.PI); };
  outC.agents.forEach((a, i) => {
    a.v = 0.95 + a.r3 * 0.5;
    if (i < NOUT * 0.6){
      const base = PATHS[i % PATHS.length], off = (a.r1 - .5) * 9, rev = a.r2 < 0.35, d0 = base[1], st = base[0];
      const L = Math.hypot(d0.x - st.x, d0.z - st.z) || 1, nx = -(d0.z - st.z) / L, nz = (d0.x - st.x) / L;
      let pts = base.map((p, j) => { const k = j < 2 ? 1 : 0.4; return { x: p.x + nx * off * k, z: p.z + nz * off * k }; }); if (rev) pts = pts.reverse();
      const j = Math.floor(rr() * (pts.length - 1)), f = rr();
      Object.assign(a, { kind: 'p', pts, i: j + 1, x: lerp(pts[j].x, pts[j + 1].x, f), z: lerp(pts[j].z, pts[j + 1].z, f) });
    } else { const p = wanderPt(a.r2 * TAU); Object.assign(a, { kind: 'w', x: p.x, z: p.z, t: wanderPt(a.r2 * TAU) }); }
  });
  const masa = makeCrowd(NMASA, 17, MALE_IHRAM); G.add(masa.bodies, masa.heads);
  masa.agents.forEach(a => { a.dir = a.r1 < .5 ? -1 : 1; a.z = MZN + 2 + a.r2 * (MZS - MZN - 4); a.x = a.dir < 0 ? 63.4 + a.r3 * 6 : 70.6 + a.r3 * 6; a.v = 1.0 + a.r3 * 0.5; });

  sc.update = (dt, T) => {
    for (let i = 0; i < NM; i++){
      const a = mat.agents[i]; a.ang += a.w * dt;
      let x = CROWD_C.x + a.rad * Math.cos(a.ang), z = CROWD_C.z - a.rad * Math.sin(a.ang);
      const dx = x - MAQAM.x, dz = z - MAQAM.z, d2 = dx * dx + dz * dz;
      if (d2 < 3.6){ const d = Math.sqrt(d2) || 1, k = (1.9 - d) / d; x += dx * k; z += dz * k; }
      dodge(x, z, _o); mat.set(i, _o.x, _o.z, Math.atan2(-Math.sin(a.ang), -Math.cos(a.ang)), Math.abs(Math.sin(T * 5.5 + a.ph)) * 0.05);
    }
    mat.commit();
    for (let i = NSIT; i < NSIT + NHW; i++){
      const a = hall.agents[i]; a.ang += a.w * dt; const p = polar(a.rad, a.ang); dodge(p.x, p.z, _o);
      const s = Math.sign(a.w); hall.set(i, _o.x, _o.z, Math.atan2(-Math.sin(a.ang) * s, -Math.cos(a.ang) * s), Math.abs(Math.sin(T * 5.5 + a.ph)) * 0.05);
    }
    hall.commit();
    outC.agents.forEach((a, i) => {
      const tgt = a.kind === 'p' ? a.pts[a.i] : a.t, dx = tgt.x - a.x, dz = tgt.z - a.z, d = Math.hypot(dx, dz);
      if (d < 0.8){
        if (a.kind === 'p'){ a.i++; if (a.i >= a.pts.length){ a.i = 1; a.x = a.pts[0].x; a.z = a.pts[0].z; } }
        else a.t = wanderPt(angleOf(a.x, a.z));
        return;
      }
      a.x += dx / d * a.v * dt; a.z += dz / d * a.v * dt; dodge(a.x, a.z, _o);
      outC.set(i, _o.x, _o.z, Math.atan2(dx, dz), Math.abs(Math.sin(T * 5.5 + a.ph)) * 0.05);
    });
    outC.commit();
    masa.agents.forEach((a, i) => {
      const fast = !a.female && a.z > GZ0 && a.z < GZ1;
      a.z += a.dir * a.v * (fast ? 1.9 : 1) * dt;
      if (a.z < MZN + 1.5 || a.z > MZS - 1.5){ a.dir *= -1; a.x += (a.dir < 0 ? -7.2 : 7.2); a.x = clamp(a.x, 63.4, 76.6); a.z = clamp(a.z, MZN + 1.5, MZS - 1.5); }
      dodge(a.x, a.z, _o); masa.set(i, _o.x, _o.z, a.dir > 0 ? 0 : Math.PI, Math.abs(Math.sin(T * (fast ? 9 : 5.5) + a.ph)) * 0.05);
    });
    masa.commit();
  };

  /* ---- Movement rules ---- */
  sc.collide = (p, prev) => {
    const l = kl(p.x, p.z);
    if (l.x > KW / 2){
      const A = HIJR_A + 0.45 + PR, B = HIJR_B + 0.45 + PR, ex = (l.x - KW / 2) / A, ez = l.z / B, k = Math.hypot(ex, ez);
      if (k < 1 && k > 1e-6){ l.x = KW / 2 + ex / k * A; l.z = ez / k * B; }
    }
    pushBox(l, { x0: -KW / 2 - 0.5, x1: KW / 2 + 0.5, z0: -KD / 2 - 0.5, z1: KD / 2 + 0.5 }, PR);
    const w = kw(l.x, l.z); p.x = w.x; p.z = w.z;
    pushRing(p, R_IN, 1.2); pushRing(p, R_OUT, 1.6);
    for (const b of sc.boxes) pushBox(p, b, PR);
    for (const s of sc.segs) pushSeg(p, s, PR);
    for (const c of sc.circles) pushCircle(p, c, PR);
    if (!walkable(p.x, p.z)){
      if (walkable(p.x, prev.z)) p.z = prev.z; else if (walkable(prev.x, p.z)) p.x = prev.x; else { p.x = prev.x; p.z = prev.z; }
    }
  };
  const _q = { x: 0, z: 0 };
  sc.camOK = p => {
    if (p.y < 0.3) return false;
    const r = Math.hypot(p.x, p.z), a = angleOf(p.x, p.z);
    if (p.y < KB + KH + 0.5){ const l = kl(p.x, p.z); if (Math.abs(l.x) < KW / 2 + 0.6 && Math.abs(l.z) < KD / 2 + 0.6) return false; }
    if (r > 36.8 && r < 44.8 && p.y > 6.7 && p.y < 7.8) return false;
    if (r > 43.2 && r < 61 && p.y > 9.5 && p.y < 10.8) return false;
    if (r > 46.4 && r < 61 && p.y >= 10.8 && p.y < BLDG_H + 0.6) return false;
    if (p.y < HALL_H + 0.3 && Math.abs(r - R_IN) < 0.95 && (!openingAt(R_IN, a) || p.y > IN_LINTEL)) return false;
    if (p.y < BLDG_H + 0.6 && Math.abs(r - R_OUT) < 1.15 && (!openingAt(R_OUT, a) || p.y > OUT_LINTEL)) return false;
    if (p.x > MX0 && p.x < MX1 && p.z > -65 && p.z < 57 && p.y > 10.9 && p.y < 18.5) return false;
    for (const b of sc.cboxes) if (p.y < b.h && p.y >= (b.y0 || 0) && p.x > b.x0 - .3 && p.x < b.x1 + .3 && p.z > b.z0 - .3 && p.z < b.z1 + .3) return false;
    for (const s of sc.segs){ if (p.y >= s.h) continue; _q.x = p.x; _q.z = p.z; pushSeg(_q, s, 0.3); if (_q.x !== p.x || _q.z !== p.z) return false; }
    if (p.y < 9) for (const c of sc.circles) if (c.cam && Math.abs(p.x - c.x) < 1.1 && Math.abs(p.z - c.z) < 1.1 && Math.hypot(p.x - c.x, p.z - c.z) < c.r + .25) return false;
    return true;
  };
  return sc;
}

/* ================= Player avatar ================= */
function buildAvatar(male){
  const root = new THREE.Group(), body = new THREE.Group(); root.add(body);
  const robe = lam(male ? 0xfbfaf5 : 0x24212b), skin = lam(0xc08a64);
  add(body, new THREE.CylinderGeometry(0.34, male ? 0.41 : 0.5, 0.85, 16), robe, 0, 0.425, 0);
  const upper = new THREE.Group(); upper.position.y = 0.85; body.add(upper);
  add(upper, new THREE.CylinderGeometry(0.29, 0.34, 0.72, 16), robe, 0, 0.36, 0);
  let shoulder = null;
  if (male){
    add(upper, new THREE.SphereGeometry(0.21, 16, 12), skin, 0, 0.93, 0);
    shoulder = add(upper, new THREE.SphereGeometry(0.15, 12, 10), skin, -0.25, 0.63, 0); shoulder.scale.set(1, 0.8, 1);
    add(upper, new THREE.BoxGeometry(0.34, 0.06, 0.46), robe, 0.16, 0.7, 0).rotation.z = 0.5;
  } else {
    add(upper, new THREE.SphereGeometry(0.23, 16, 12), lam(0x2b2833), 0, 0.93, 0).scale.set(1, 1.08, 1);
    add(upper, new THREE.CircleGeometry(0.11, 16), skin, 0, 0.93, 0.232);
  }
  const ring = add(root, new THREE.RingGeometry(0.58, 0.74, 40).rotateX(-Math.PI / 2), new THREE.MeshBasicMaterial({ color: 0xe2b84f, transparent: true, opacity: .95, depthWrite: false }), 0, 0.035, 0);
  return { root, body, upper, shoulder, ring };
}
const AV = { m: buildAvatar(true), f: buildAvatar(false) };
const arrowShape = new THREE.Shape(); arrowShape.moveTo(0, 0.6); arrowShape.lineTo(0.42, -0.26); arrowShape.lineTo(0, -0.06); arrowShape.lineTo(-0.42, -0.26); arrowShape.closePath();
const arrow = new THREE.Mesh(new THREE.ShapeGeometry(arrowShape).rotateX(-Math.PI / 2), new THREE.MeshBasicMaterial({ color: 0xe2b84f, transparent: true, opacity: .92, depthWrite: false }));
const wp = new THREE.Group();
const wpRing = add(wp, new THREE.RingGeometry(1.0, 1.28, 48).rotateX(-Math.PI / 2), new THREE.MeshBasicMaterial({ color: 0xf0c75e, transparent: true, opacity: .95, depthWrite: false, side: THREE.DoubleSide }), 0, 0.05, 0);
const wpBeam = add(wp, new THREE.CylinderGeometry(0.95, 0.95, 9, 24, 1, true), new THREE.MeshBasicMaterial({ color: 0xf5d27a, transparent: true, opacity: .2, depthWrite: false, side: THREE.DoubleSide, blending: THREE.AdditiveBlending }), 0, 4.5, 0);

/* ================= State ================= */
const S = {
  phase: 'intro', mode: 'orbit', gender: store.get('gender') === 'f' ? 'f' : 'm', route: ['A', 'B', 'C', 'D', 'E'].includes(store.get('route')) ? store.get('route') : 'A',
  target: null, targetR: 2, routePts: null, routeIdx: 0, gateNo: null, enterNo: null,
  actionKey: null, actionFn: null, auto: false, modal: false, locked: false, fading: false,
  cardBuilder: null, objKey: null, objVars: null, objText: null, hintKey: null, stripKey: null, nearKey: null,
  tw: null, sai: null, prayer: null, talb: 0, camSnap: true, walked: 0
};
const P = { pos: new THREE.Vector3(0, 0, 30), yaw: Math.PI, moving: false, running: false, runHeld: false, stuck: 0, autoR: null };
const cam = { yaw: 0, pitch: 0.36, dist: 11, lastUser: -10 };
const pose = { bend: 0, sy: 1 }, poseT = { bend: 0, sy: 1 };
let T = 0;
let cur = null;
const inMasa = p => p.x > MX0 - 0.3 && p.x < MX1 + 0.3 && p.z > MZN - 2 && p.z < MZS + 2;
const gateName = no => GATES[no].n[lang];
const fmtDist = m => m < 1000 ? num(Math.max(0, Math.round(m / 10) * 10)) + ' ' + t('unit_m') : num((m / 1000).toFixed(2)) + ' ' + t('unit_km');
const SAI_LAP_M = 450, MASA_SCALE = SAI_LAP_M / (MZ_SAFA - MZ_MARWA);
function zoneScale(p){
  if (inMasa(p)) return MASA_SCALE;
  const r = Math.hypot(p.x, p.z);
  if (r < 37) return 1;                 // the mataf is modelled close to real size
  if (r < 61.5) return 4.5;             // halls between the colonnade and the gates
  if (S.phase === 'to_safa' || S.phase === 'sai_safa') return 3;
  return ROUTES[S.route].scale;         // streets: scaled to the real walking distance
}
function routeLeft(){
  const pts = S.routePts; let u = Math.hypot(P.pos.x - pts[S.routeIdx].x, P.pos.z - pts[S.routeIdx].z);
  for (let i = S.routeIdx + 1; i < pts.length; i++) u += Math.hypot(pts[i].x - pts[i - 1].x, pts[i].z - pts[i - 1].z);
  return u;
}
let distTick = 0;
function renderDist(){
  const lines = [];
  if (S.phase === 'approach' && S.routePts) lines.push(`<span class="go">${esc(t('d_gate', { n: ROUTES[S.route].no, d: fmtDist(routeLeft() * ROUTES[S.route].scale) }))}</span>`);
  if (S.phase === 'inside' && S.routePts) lines.push(`<span class="go">${esc(t('d_mataf', { d: fmtDist(routeLeft() * 4.5) }))}</span>`);
  lines.push(`<span>${esc(t('d_walked', { d: fmtDist(S.walked) }))}</span>`);
  mapDist.innerHTML = lines.join('');
  if (!counterEl.hidden){
    if (S.phase === 'tawaf' && S.tw) cKm.textContent = fmtDist(S.tw.dist);
    else if (S.phase === 'sai' && S.sai) cKm.textContent = t('d_of', { d: fmtDist(S.sai.dist), t: fmtDist(7 * SAI_LAP_M) });
  }
}

/* ================= DOM ================= */
const hud = $('#hud'), stepsEl = $('#steps'), objEl = $('#objective'), objK = $('#objK'), objT = $('#objT'), objH = $('#objH');
const counterEl = $('#counter'), ringEl = $('#ring'), cNum = $('#cNum'), cLab = $('#cLab'), toastEl = $('#toast');
const stripEl = $('#duaStrip'), nearEl = $('#near'), controlsEl = $('#controls'), actBtn = $('#actBtn'), autoBtn = $('#autoBtn'), runBtn = $('#runBtn');
const joyEl = $('#joy'), knobEl = $('#knob'), joyHint = $('#joyHint'), prayEl = $('#pray');
const mapBox = $('#mapBox'), mapC = $('#map'), mapGate = $('#mapGate'), mapDist = $('#mapDist'), cKm = $('#cKm'), mctx = mapC.getContext('2d');
const modal = $('#modal'), cardEl = $('#card'), cardK = $('#cardK'), cardTitle = $('#cardTitle'), cardBody = $('#cardBody'), cardBtns = $('#cardBtns');
const fadeEl = $('#fade');

function arcD(r, a0, a1){
  const p = a => { const rad = (a - 90) * Math.PI / 180; return [50 + r * Math.cos(rad), 50 + r * Math.sin(rad)]; };
  const [x0, y0] = p(a0), [x1, y1] = p(a1);
  return `M${x0.toFixed(2)} ${y0.toFixed(2)} A${r} ${r} 0 ${a1 - a0 > 180 ? 1 : 0} 1 ${x1.toFixed(2)} ${y1.toFixed(2)}`;
}
const SEG = 7, segA = i => [i * 360 / SEG + 4, (i + 1) * 360 / SEG - 4];
ringEl.innerHTML = Array.from({ length: SEG }, (_, i) => `<path class="trk" d="${arcD(40, ...segA(i))}"/><path class="fil" d=""/>`).join('');
const segEls = [...ringEl.querySelectorAll('.fil')];
let lastCounter = '';
function updateCounter(done, frac, labelKey){
  const key = done + ':' + Math.round(frac * 60) + ':' + labelKey + lang;
  if (key === lastCounter) return; lastCounter = key;
  segEls.forEach((el, i) => { const [s, e] = segA(i); el.setAttribute('d', i < done ? arcD(40, s, e) : (i === done && frac > 0.02 ? arcD(40, s, s + (e - s) * frac) : '')); });
  cNum.textContent = num(Math.min(done + 1, 7)) + '/' + num(7); cLab.textContent = t(labelKey);
}
let toastTimer = 0;
function toast(html, kind){
  toastEl.className = 'toast show' + (kind ? ' ' + kind : ''); toastEl.innerHTML = html;
  clearTimeout(toastTimer); toastTimer = setTimeout(() => toastEl.classList.remove('show'), kind === 'warn' ? 2600 : 3000);
}
function setObjective(key, vars){
  S.objKey = key; S.objVars = vars || null;
  if (!key){ if (!objEl.hidden) objEl.hidden = true; S.objText = null; return; }
  const txt = t(key, vars);
  if (txt !== S.objText){ S.objText = txt; objT.textContent = txt; objK.textContent = t('now'); }
  if (objEl.hidden) objEl.hidden = false;
}
function setHint(key){ if (key === S.hintKey) return; S.hintKey = key; objH.hidden = !key; if (key) objH.textContent = t(key); }
function setStrip(duaKey, labelKey){
  const k = duaKey ? duaKey + '|' + labelKey : null;
  if (k === S.stripKey) return; S.stripKey = k; stripEl.hidden = !duaKey;
  if (duaKey){ const d = DUA[duaKey]; stripEl.innerHTML = `<span class="k">${esc(t(labelKey))}</span><span class="ar" lang="ar">${d.ar}</span><span class="tr">${esc(d.tr[lang])}</span>`; }
}
function setAction(key, fn){ S.actionFn = fn; if (key === S.actionKey) return; S.actionKey = key; actBtn.textContent = t(key); actBtn.hidden = false; }
function clearAction(){ if (S.actionKey === null) return; S.actionKey = null; S.actionFn = null; actBtn.hidden = true; }
function nudgeRun(on){ runBtn.classList.toggle('nudge', !!on); }
function renderMapGate(){
  mapGate.hidden = !S.gateNo;
  if (S.gateNo) mapGate.innerHTML = `${esc(t('yourGate'))}<b>${num(S.gateNo)}</b>`;
}

/* Steps bar */
const CHIPS = [['steps_ihram', 'ihram'], ['steps_gate', 'route'], ['steps_tawaf', 'tawaf_start'], ['steps_maqam', 'maqam'], ['steps_zamzam', 'zamzam'], ['steps_sai', 'to_safa'], ['steps_halq', 'halq']];
function renderSteps(){
  const ci = PH[S.phase].chip;
  stepsEl.setAttribute('aria-label', t('stepsLabel'));
  stepsEl.innerHTML = CHIPS.map(([k, ph], i) => `<li class="${i < ci ? 'done' : ''} ${i === ci ? 'cur' : ''}"><button data-jump="${ph}" ${i === ci ? 'aria-current="step"' : ''}><span class="n">${num(i + 1)}</span>${esc(t(k))}</button></li>`).join('');
  const c = stepsEl.querySelector('.cur'); if (c) c.scrollIntoView({ inline: 'nearest', block: 'nearest' });
}
stepsEl.addEventListener('click', e => { const b = e.target.closest('[data-jump]'); if (!b || S.fading) return; S.talb = 0; goTo(b.dataset.jump); });

/* Cards */
function showCard(builder){ S.cardBuilder = builder; S.modal = true; modal.hidden = false; renderCard(true); }
function closeCard(){ S.cardBuilder = null; S.modal = false; modal.hidden = true; }
function renderCard(focus){
  const spec = S.cardBuilder(); cardEl.className = 'card' + (spec.cls ? ' ' + spec.cls : '');
  cardK.textContent = spec.kicker || ''; cardK.hidden = !spec.kicker;
  cardTitle.textContent = spec.title; cardBody.innerHTML = spec.html; cardBtns.innerHTML = '';
  spec.buttons.forEach(b => { const el = document.createElement('button'); el.className = 'btn' + (b.primary ? ' primary' : ''); el.textContent = b.label; el.addEventListener('click', b.fn); cardBtns.appendChild(el); });
  if (spec.after) spec.after();
  if (focus){ cardEl.scrollTop = 0; const f = cardBtns.querySelector('.primary') || cardBtns.firstChild; f && f.focus({ preventScroll: true }); }
}
modal.addEventListener('click', e => {
  const g = e.target.closest('[data-gender]'); if (g){ S.gender = g.dataset.gender; store.set('gender', S.gender); renderCard(); return; }
  const r = e.target.closest('[data-route]'); if (r){ S.route = r.dataset.route; store.set('route', S.route); renderCard(); return; }
  const l = e.target.closest('[data-lang]'); if (l) setLang(l.dataset.lang);
});
const segBtns = () => `<div class="seg" role="group" aria-label="${esc(t('langLabel'))}"><button data-lang="en" aria-pressed="${lang === 'en'}">English</button><button data-lang="bn" aria-pressed="${lang === 'bn'}">বাংলা</button></div>`;
const P_ = s => `<p>${esc(s)}</p>`, NOTE = s => `<p class="note">${esc(s)}</p>`;
const opt = (attr, val, on, title, note) => `<button class="opt" data-${attr}="${val}" aria-pressed="${on}"><span class="opt-t">${esc(title)}</span><span class="opt-n">${esc(note)}</span></button>`;

const cardIntro = () => ({ cls: 'intro', title: t('title'),
  html: `<p class="lead">${esc(t('sub'))}</p>${segBtns()}<p class="q">${esc(t('performAs'))}</p><div class="choice">` +
    ['m', 'f'].map(g => opt('gender', g, S.gender === g, t(g === 'm' ? 'man' : 'woman'), t(g === 'm' ? 'manNote' : 'womanNote'))).join('') +
    `</div><p class="fine">${esc(t('disclaimer'))}</p>`,
  buttons: [{ label: t('begin'), primary: true, fn: () => goTo('ihram') }] });
const cardIhram1 = () => ({ kicker: t('steps_ihram'), title: t('c_ih1_t'), html: P_(t(S.gender === 'm' ? 'c_ih1_m' : 'c_ih1_f')) + NOTE(t('c_flight')),
  buttons: [{ label: t('cont'), primary: true, fn: () => showCard(cardIhram2) }] });
let justSaid = false;
const cardIhram2 = () => ({ kicker: t('steps_ihram'), title: t('c_ih2_t'),
  html: P_(t('c_ih2_a')) + duaHTML('niyyah') + P_(t('c_ih2_b')) + duaHTML('talbiyah'),
  buttons: S.talb < 3 ? [{ label: t('c_talb_btn', { n: S.talb }), primary: true, fn: () => { S.talb++; justSaid = true; renderCard(); } }]
                      : [{ label: t('cont'), primary: true, fn: () => showCard(cardIhram3) }],
  after: () => { if (justSaid){ justSaid = false; const d = cardBody.querySelector('[data-dua="talbiyah"]'); d && d.classList.add('said'); const b = cardBtns.querySelector('.primary'); b && b.focus({ preventScroll: true }); } } });
const cardIhram3 = () => ({ kicker: t('steps_ihram'), title: t('c_ih3_t'),
  html: `<ul class="rules">${[...t('c_ih3_list'), t(S.gender === 'm' ? 'c_ih3_m' : 'c_ih3_f')].map(i => `<li>${esc(i)}</li>`).join('')}</ul>` + NOTE(t('c_wudu')),
  buttons: [{ label: t('c_arrive'), primary: true, fn: () => goTo('route') }] });
const cardRoute = () => ({ kicker: t('c_rt_k'), title: t('c_rt_t'),
  html: P_(t('c_rt_a')) + `<div class="choice list">` + ROUTE_KEYS.map(k => opt('route', k, S.route === k, t('rt' + k + '_t'), t('rt' + k + '_n'))).join('') + `</div>` +
    NOTE(t('c_rt_note')) + NOTE(t(isTouch ? 'ctrlTouch' : 'ctrlDesk')),
  buttons: [{ label: t('c_rt_go'), primary: true, fn: () => goTo('approach') }] });
const cardGate = () => { const no = S.enterNo; return { kicker: t('gateNo', { n: no }), title: t('c_enter_t'),
  html: P_(t('c_enter_a')) + duaHTML('enter') + NOTE(t('c_gate_note', { n: no, gate: gateName(no) })) + NOTE(t('view_' + no)) + NOTE(t('c_land_tip')),
  buttons: [{ label: t('c_step_in'), primary: true, fn: () => { closeCard(); S.gateNo = no; renderMapGate(); setPhase('inside'); } }] }; };
const cardTawafBegin = () => ({ kicker: t('steps_tawaf'), title: t('c_tb_t'),
  html: P_(t('c_tb_a')) + duaHTML('istilam') + P_(t('c_tb_b')) + (S.gender === 'm' ? NOTE(t('c_tb_m')) : ''),
  buttons: [{ label: t('c_tb_go'), primary: true, fn: () => { closeCard(); setPhase('tawaf'); } }] });
const cardMaqam = () => ({ kicker: t('steps_maqam'), title: t('c_mq_t'), html: P_(t('c_mq_a')) + duaHTML('maqam') + NOTE(t('c_mq_b')),
  buttons: [{ label: t('c_start_prayer'), primary: true, fn: () => { closeCard(); pray(Math.atan2(-P.pos.x, -P.pos.z), () => { toast(esc(t('t_prayed')), 'ok'); setPhase('zamzam'); }); } }] });
const cardZamzam = () => ({ kicker: t('steps_zamzam'), title: t('c_zz_t'), html: P_(t('c_zz_a')) + duaHTML('zamzam'),
  buttons: [{ label: t('c_done'), primary: true, fn: () => { closeCard(); setPhase('to_safa'); } }] });
const cardSafa = () => ({ kicker: t('c_sf_k'), title: t('c_sf_t'), html: P_(t('c_sf_a')) + duaHTML('safa') + P_(t('c_sf_b')) + duaHTML('dhikr') + NOTE(t('c_sf_c')),
  buttons: [{ label: t('c_sf_go'), primary: true, fn: () => { closeCard(); setPhase('sai'); } }] });
const cardDhikr = place => () => ({ kicker: t('steps_sai'), title: t('c_dk_t', { dest: t('place_' + place) }), html: P_(t('c_dk_a')) + duaHTML('dhikr'),
  buttons: [{ label: t('cont'), primary: true, fn: closeCard }] });
const cardHalq = () => { const m = S.gender === 'm', done = () => showCard(cardUmrahDone);
  return { kicker: t('steps_halq'), title: t('c_hq_t'), html: P_(t(m ? 'c_hq_m' : 'c_hq_f')) + NOTE(t('c_hq_exit')),
    buttons: m ? [{ label: t('c_hq_trim'), fn: done }, { label: t('c_hq_shave'), primary: true, fn: done }] : [{ label: t('c_hq_cut'), primary: true, fn: done }] }; };
const cardUmrahDone = () => ({ kicker: t('steps_halq'), title: t('c_ud_t'), html: P_(t('c_ud_a')) + (S.gateNo ? NOTE(t('c_way', { n: S.gateNo, gate: gateName(S.gateNo) }) + ' ' + t('view_' + S.gateNo)) : '') + NOTE(t('d_walked', { d: fmtDist(S.walked) })) + NOTE(t('c_ud_b')),
  buttons: [{ label: t('c_restart'), fn: restart }, { label: t('c_free'), primary: true, fn: () => { closeCard(); setPhase('free'); } }] });
const cardHelp = () => ({ title: t('ctrlTitle'), html: P_(t(isTouch ? 'ctrlTouch' : 'ctrlDesk')) + P_(t('ctrlAuto')), buttons: [{ label: t('close'), primary: true, fn: closeCard }] });
const cardInfo = key => () => { const I = INFO[key]; return { kicker: t('nearby'), title: I.n[lang], html: P_(I.x[lang]) + (I.d ? duaHTML(I.d) : ''), buttons: [{ label: t('close'), primary: true, fn: closeCard }] }; };

/* ================= Prayer animation ================= */
const PRAY_SEQ = [['stand', 'pr_qiyam', 1.8], ['bow', 'pr_ruku', 1.3], ['stand', 'pr_qiyam', .8], ['prost', 'pr_sujood', 1.2], ['sit', 'pr_jalsa', .8], ['prost', 'pr_sujood', 1.2],
  ['stand', 'pr_qiyam', 1.6], ['bow', 'pr_ruku', 1.3], ['stand', 'pr_qiyam', .8], ['prost', 'pr_sujood', 1.2], ['sit', 'pr_jalsa', .8], ['prost', 'pr_sujood', 1.2], ['sit', 'pr_tash', 1.8], ['sit', 'pr_salam', 1.4]];
const POSES = { stand: { bend: 0, sy: 1 }, bow: { bend: 1.35, sy: 1 }, prost: { bend: 1.45, sy: .5 }, sit: { bend: .05, sy: .6 } };
function pray(yaw, cb){ S.locked = true; P.yaw = yaw; S.prayer = { i: 0, t: 0, cb }; cam.lastUser = -10; clearAction(); prayEl.hidden = false; renderNear(); renderPray(); }
function renderPray(){ const [p, k] = PRAY_SEQ[S.prayer.i]; Object.assign(poseT, POSES[p]); prayEl.innerHTML = `<span>${esc(t(S.prayer.i < 6 ? 'pr_rak1' : 'pr_rak2'))}</span><b>${esc(t(k))}</b>`; }
function prayerTick(dt){
  const pr = S.prayer; pr.t += dt; if (pr.t < PRAY_SEQ[pr.i][2]) return;
  pr.t = 0; pr.i++;
  if (pr.i >= PRAY_SEQ.length){ S.prayer = null; Object.assign(poseT, POSES.stand); prayEl.hidden = true; S.locked = false; renderNear(); pr.cb(); return; }
  renderPray();
}

/* ================= Step flow ================= */
const PH = {
  intro: { mode: 'orbit', chip: -1 }, ihram: { mode: 'orbit', chip: 0 }, route: { mode: 'orbit', chip: 1 },
  approach: { chip: 1, spawn: () => ROUTES[S.route].start }, inside: { chip: 1 },
  tawaf_start: { chip: 2, spawn: () => ({ x: 0, z: 30, yaw: Math.PI }) }, tawaf: { chip: 2 },
  maqam: { chip: 3, spawn: () => { const p = polar(12.5, A0 + 0.08); return { x: p.x, z: p.z, yaw: Math.PI }; } },
  zamzam: { chip: 4, spawn: () => ({ x: PRAY_SPOT.x, z: PRAY_SPOT.z, yaw: 0 }) },
  to_safa: { chip: 5, spawn: () => ({ x: ZAMZAM_WP.x, z: ZAMZAM_WP.z, yaw: Math.PI }) },
  sai_safa: { chip: 5, spawn: () => ({ x: SAFA_PT.x, z: SAFA_PT.z, yaw: Math.PI }) }, sai: { chip: 5 },
  halq: { chip: 6, spawn: () => ({ x: MARWA_PT.x, z: MARWA_PT.z + 1, yaw: 0 }) },
  free: { chip: 7 }
};
function place(s){ P.pos.set(s.x, 0, s.z); P.yaw = s.yaw; cam.yaw = s.yaw + Math.PI; cam.pitch = 0.36; S.camSnap = true; }
function goTo(ph){
  if (S.fading) return; S.fading = true; fadeEl.classList.add('on');
  setTimeout(() => {
    const def = PH[ph]; if (def.spawn) place(def.spawn());
    closeCard(); setPhase(ph);
    requestAnimationFrame(() => { fadeEl.classList.remove('on'); S.fading = false; });
  }, 420);
}
function restart(){ S.talb = 0; S.gateNo = null; S.walked = 0; renderMapGate(); goTo('intro'); }
function setIdtiba(on){ if (AV.m.shoulder) AV.m.shoulder.visible = on; }
function nearestGate(){ const a = angleOf(P.pos.x, P.pos.z); let best = 1, bd = 9; for (const no of MAIN_GATES){ const d = Math.abs(wrapPi(a - GATE_ANG[no])); if (d < bd){ bd = d; best = no; } } return best; }

function setPhase(ph){
  S.phase = ph; const def = PH[ph];
  S.mode = def.mode || 'follow';
  clearAction(); setHint(null); setStrip(null); nudgeRun(false);
  S.target = null; S.routePts = null; S.routeIdx = 0; S.auto = false; P.autoR = null; P.stuck = 0; autoBtn.setAttribute('aria-pressed', 'false');
  counterEl.hidden = true; lastCounter = '';
  hud.hidden = ph === 'intro';
  const follow = S.mode === 'follow';
  controlsEl.hidden = !follow; joyHint.hidden = !(follow && isTouch); mapBox.hidden = !follow;
  if (!['tawaf_start', 'tawaf'].includes(ph)) setIdtiba(false);
  setObjective(null);
  switch (ph){
    case 'intro': showCard(cardIntro); break;
    case 'ihram': showCard(cardIhram1); break;
    case 'route': showCard(cardRoute); break;
    case 'approach': S.routePts = ROUTES[S.route].out; S.targetR = 3.2; S.gateNo = null; S.walked = 0; renderMapGate(); break;
    case 'inside': S.routePts = ROUTES[S.route].inn; S.targetR = 2.5; break;
    case 'tawaf_start':
      S.target = polar(10.5, A0); S.targetR = 2.5;
      if (S.gender === 'm'){ setIdtiba(true); toast(esc(t('t_idtiba'))); }
      break;
    case 'tawaf':
      if (S.gender === 'm') setIdtiba(true);
      S.tw = { prevA: angleOf(P.pos.x, P.pos.z), cum: 0, max: 0, rounds: 0, lastWarn: -9, istUntil: 0, dist: 0 };
      counterEl.hidden = false; break;
    case 'maqam': S.target = PRAY_SPOT; S.targetR = 2.0; if (S.gender === 'm') toast(esc(t('t_cover'))); break;
    case 'zamzam': S.target = ZAMZAM_WP; S.targetR = 2.0; break;
    case 'to_safa': S.routePts = SAFA_ROUTE; S.targetR = 2.5; break;
    case 'sai_safa': S.target = SAFA_PT; S.targetR = 3; break;
    case 'sai': S.sai = { lap: 0, heading: 'marwa', dhikr: null, dist: 0 }; counterEl.hidden = false; break;
    case 'halq': showCard(cardHalq); break;
  }
  if (S.routePts) S.target = S.routePts[0];
  renderSteps(); updateAvatarVis();
}

function routeTick(){
  const pts = S.routePts, last = pts.length - 1, px = P.pos.x, pz = P.pos.z;
  let i = S.routeIdx;
  // Move on once the pilgrim reaches a waypoint or is already past it on the way to the next one
  while (i < last){
    const a = pts[i], b = pts[i + 1], vx = b.x - a.x, vz = b.z - a.z, u = ((px - a.x) * vx + (pz - a.z) * vz) / (vx * vx + vz * vz);
    const uc = clamp(u, 0, 1), dSeg = Math.hypot(px - (a.x + vx * uc), pz - (a.z + vz * uc));
    if (Math.hypot(px - a.x, pz - a.z) < 3.2 || (u > 0 && dSeg < 12)) i++; else break;
  }
  S.routeIdx = i; S.target = pts[i];
  return i === last && Math.hypot(px - pts[i].x, pz - pts[i].z) < S.targetR;
}
function tawafTick(){
  const p = P.pos, tw = S.tw, a = angleOf(p.x, p.z);
  tw.cum += wrapPi(a - tw.prevA); tw.prevA = a;
  if (tw.cum > tw.max) tw.max = tw.cum;
  if (tw.cum < tw.max - 0.5 && T - tw.lastWarn > 3.5){ toast(esc(t('t_wrong')), 'warn'); tw.lastWarn = T; }
  const done = Math.floor(Math.max(0, tw.cum) / TAU);
  if (done > tw.rounds){
    tw.rounds = done;
    if (done >= 7){ toast(esc(t('t_tawaf_done')), 'ok'); setPhase('maqam'); return; }
    toast(esc(t('t_round', { n: done }))); tw.istUntil = T + 7;
  }
  const round = tw.rounds + 1;
  setObjective('obj_tawaf', { n: round });
  T < tw.istUntil ? setAction('act_istilam', doIstilam) : clearAction();
  const r = Math.hypot(p.x, p.z);
  setStrip(a > AY && a < A0 && r < 33 ? 'rabbana' : null, 'hint_yamani');
  const raml = S.gender === 'm' && round <= 3;
  setHint(raml ? 'hint_raml' : null); nudgeRun(raml && !P.running);
  updateCounter(tw.rounds, (Math.max(0, tw.cum) % TAU) / TAU, 'c_tawaf');
}
function doIstilam(){ S.tw.istUntil = 0; clearAction(); toast(`<span class="ar" lang="ar">${DUA.istilam.ar}</span>${esc(DUA.istilam.tr[lang])}`); }
function saiTick(){
  const z = P.pos.z, sai = S.sai, inM = inMasa(P.pos), atM = inM && z < MZ_MARWA, atS = inM && z > MZ_SAFA;
  if ((sai.heading === 'marwa' && atM) || (sai.heading === 'safa' && atS)){
    const reached = sai.heading; sai.lap++;
    if (sai.lap >= 7){ toast(esc(t('t_sai_done')), 'ok'); setPhase('halq'); return; }
    toast(esc(t('t_lap', { n: sai.lap, dest: t('place_' + reached) })));
    sai.heading = reached === 'marwa' ? 'safa' : 'marwa'; sai.dhikr = reached;
  }
  S.target = sai.heading === 'marwa' ? MARWA_PT : SAFA_PT; S.targetR = 3;
  setObjective('obj_sai', { n: sai.lap + 1, dest: t('place_' + sai.heading) });
  const inEnd = (sai.dhikr === 'marwa' && atM) || (sai.dhikr === 'safa' && atS);
  inEnd ? setAction(sai.dhikr === 'marwa' ? 'act_dhikr_marwa' : 'act_dhikr_safa', () => { P.yaw = -Math.PI / 2; showCard(cardDhikr(sai.dhikr)); }) : clearAction();
  const inG = inM && z > GZ0 && z < GZ1;
  setHint(inG ? (S.gender === 'm' ? 'hint_green_m' : 'hint_green_f') : null);
  setStrip(inG ? 'green' : null, 'hint_green_dua');
  nudgeRun(inG && S.gender === 'm' && !P.running);
  const span = MZ_SAFA - MZ_MARWA, prog = sai.heading === 'marwa' ? (MZ_SAFA - z) / span : (z - MZ_MARWA) / span;
  sai.dist = (sai.lap + clamp(prog, 0, 1)) * SAI_LAP_M;
  updateCounter(sai.lap, clamp(prog, 0, 1), 'c_sai');
}
function phaseTick(){
  const p = P.pos, near = S.target ? Math.hypot(p.x - S.target.x, p.z - S.target.z) < S.targetR : false;
  const at = (obj, act, fn) => { setObjective(obj); near ? setAction(act, fn) : clearAction(); };
  switch (S.phase){
    case 'approach': {
      const no = ROUTES[S.route].no, arrived = routeTick();
      if (Math.hypot(p.x, p.z) < R_OUT - 1.5){ S.enterNo = nearestGate(); showCard(cardGate); break; }
      setObjective(arrived ? 'obj_gate' : (S.routeIdx === 0 ? 'obj_road_' + S.route : 'obj_plaza'), { n: no, gate: gateName(no) });
      arrived ? setAction('act_enter', () => { S.enterNo = no; showCard(cardGate); }) : clearAction();
      break;
    }
    case 'inside': {
      const arrived = routeTick(); setObjective('obj_inside');
      if (arrived || Math.hypot(p.x, p.z) < 34.5){ toast(esc(t('t_firstsight')), 'ok'); setPhase('tawaf_start'); }
      break;
    }
    case 'tawaf_start': {
      const a = angleOf(p.x, p.z), r = Math.hypot(p.x, p.z), on = Math.abs(wrapPi(a - A0)) < 0.2 && r < 30;
      setObjective(on ? 'obj_on_line' : 'obj_tawaf_start');
      on ? setAction('act_begin_tawaf', () => showCard(cardTawafBegin)) : clearAction();
      break;
    }
    case 'tawaf': tawafTick(); break;
    case 'maqam': at('obj_maqam', 'act_pray', () => showCard(cardMaqam)); break;
    case 'zamzam': at('obj_zamzam', 'act_drink', () => showCard(cardZamzam)); break;
    case 'to_safa': {
      routeTick(); const inM = inMasa(p);
      setObjective(inM ? 'obj_safa_turn' : 'obj_to_safa');
      if (inM && p.z > MZ_SAFA) setPhase('sai_safa');
      break;
    }
    case 'sai_safa': setObjective('obj_sai_safa'); (inMasa(p) && p.z > MZ_SAFA) ? setAction('act_safa', () => { P.yaw = -Math.PI / 2; showCard(cardSafa); }) : clearAction(); break;
    case 'sai': saiTick(); break;
    case 'free': setObjective('obj_free'); break;
  }
  let best = null, bd = 1e9;
  for (const ip of cur.infos){ const d = Math.hypot(p.x - ip.x, p.z - ip.z); if (d < ip.r && d < bd){ bd = d; best = ip.key; } }
  if (best !== S.nearKey){ S.nearKey = best; renderNear(); }
}
function renderNear(){
  nearEl.hidden = !S.nearKey || S.mode !== 'follow' || !!S.prayer;
  if (S.nearKey) nearEl.innerHTML = `<b>${esc(INFO[S.nearKey].n[lang])}</b><span>${esc(t('learn'))}</span>`;
}
nearEl.addEventListener('click', () => { if (S.nearKey && !S.modal) showCard(cardInfo(S.nearKey)); });

/* ================= Mini-map (north up, centred on the pilgrim) ================= */
let mapPx = 0;
function sizeMap(){
  const w = mapC.clientWidth || 160; if (w === mapPx) return;
  const d = Math.min(2, window.devicePixelRatio || 1); mapPx = w; mapC.width = w * d; mapC.height = w * d; mctx.setTransform(d, 0, 0, d, 0, 0);
}
function drawMap(){
  if (mapBox.hidden) return; sizeMap();
  const W = mapPx, s = W / 175, cx = W / 2, cy = W / 2, g = mctx, px = P.pos.x, pz = P.pos.z;
  const X = x => cx + (x - px) * s, Y = z => cy + (z - pz) * s, circ = (x, z, r) => { g.beginPath(); g.arc(X(x), Y(z), r * s, 0, TAU); };
  g.clearRect(0, 0, W, W); g.save(); g.beginPath(); g.arc(cx, cy, W / 2, 0, TAU); g.clip();
  g.fillStyle = '#d3cab8'; g.fillRect(0, 0, W, W);
  g.strokeStyle = '#bfb39e'; g.lineCap = 'butt'; for (const r of ROADS){ g.lineWidth = r.hw * 2 * s; g.beginPath(); g.moveTo(X(r.x1), Y(r.z1)); g.lineTo(X(r.x2), Y(r.z2)); g.stroke(); }
  g.fillStyle = '#f3f0e9'; circ(0, 0, PLAZA_R); g.fill();
  g.fillStyle = '#d4c3a0'; g.beginPath(); g.arc(X(0), Y(0), R_OUT * s, 0, TAU); g.arc(X(0), Y(0), R_IN * s, 0, TAU, true); g.fill();
  g.fillStyle = '#e8e0cf'; circ(0, 0, R_IN - 0.6); g.fill();
  g.fillStyle = '#ffffff'; circ(0, 0, 36); g.fill();
  g.strokeStyle = '#e8e0cf'; g.lineWidth = 6.6 * s;
  for (const o of OPENINGS){ const a = polar(R_IN - 1, o.a), b = polar(R_OUT + 1, o.a); g.beginPath(); g.moveTo(X(a.x), Y(a.z)); g.lineTo(X(b.x), Y(b.z)); g.stroke(); }
  g.beginPath(); g.moveTo(X(PB.x), Y(PB.z)); g.lineTo(X(MX0 + 1), Y((ZW0 + ZW1) / 2)); g.stroke();
  g.fillStyle = '#e2d6bd'; g.fillRect(X(61.6), Y(-66), 16.8 * s, 124 * s);
  g.fillStyle = '#efe8da'; g.fillRect(X(MX0), Y(-65), (MX1 - MX0) * s, 122 * s);
  g.fillStyle = '#9c846a'; g.fillRect(X(64), Y(46), 12 * s, 9 * s); g.fillRect(X(64), Y(-63), 12 * s, 8 * s);
  g.fillStyle = 'rgba(47,224,124,.55)'; g.fillRect(X(MX0), Y(GZ0), (MX1 - MX0) * s, (GZ1 - GZ0) * s);
  g.save(); g.translate(X(0), Y(0)); g.rotate(-KAABA_ROT); g.fillStyle = '#16130e'; g.fillRect(-KW / 2 * s, -KD / 2 * s, KW * s, KD * s); g.restore();
  // landmarks: Clock Tower complex, Jabal Omar, Abu Qubais, King Abdullah expansion
  g.fillStyle = '#b9a67f'; g.fillRect(X(-16), Y(112), 60 * s, 80 * s); g.fillRect(X(-80), Y(-150), 70 * s, 50 * s);
  g.fillStyle = '#8fa2ad'; g.fillRect(X(-200), Y(-44), 104 * s, 33 * s); g.fillRect(X(-198), Y(13), 102 * s, 31 * s);
  g.fillStyle = '#9c8a70'; g.beginPath(); g.ellipse(X(150), Y(8), 46 * s, 62 * s, 0, 0, TAU); g.fill();
  g.fillStyle = '#c9a54c'; g.strokeStyle = '#16130e'; g.lineWidth = 1.5; g.beginPath(); g.arc(X(14), Y(152), 5, 0, TAU); g.fill(); g.stroke();
  // route still to walk
  if (S.mode === 'follow' && S.target){
    g.setLineDash([3, 3]); g.strokeStyle = '#a8862f'; g.lineWidth = 2.2; g.beginPath(); g.moveTo(cx, cy);
    if (S.routePts) for (let i = S.routeIdx; i < S.routePts.length; i++) g.lineTo(X(S.routePts[i].x), Y(S.routePts[i].z));
    else g.lineTo(X(S.target.x), Y(S.target.z));
    g.stroke(); g.setLineDash([]);
    const tp = S.routePts ? S.routePts[S.routePts.length - 1] : S.target;
    g.fillStyle = '#e2b84f'; g.strokeStyle = '#16130e'; g.lineWidth = 1.5; g.beginPath(); g.arc(X(tp.x), Y(tp.z), 4, 0, TAU); g.fill(); g.stroke();
  }
  // gate numbers
  g.textAlign = 'center'; g.textBaseline = 'middle'; g.font = '700 8.5px "Hind Siliguri", system-ui, sans-serif';
  g.font = '700 7.5px "Hind Siliguri", system-ui, sans-serif';
  for (const no of MAIN_GATES){
    const q = polar(R_OUT + 6.5, GATE_ANG[no]), x = X(q.x), y = Y(q.z);
    g.fillStyle = (S.gateNo === no || (S.phase === 'approach' && ROUTES[S.route].no === no)) ? '#c9a54c' : '#16130e';
    g.beginPath(); g.arc(x, y, 7.5, 0, TAU); g.fill(); g.fillStyle = (S.gateNo === no || (S.phase === 'approach' && ROUTES[S.route].no === no)) ? '#16130e' : '#e7cb82'; g.fillText(num(no), x, y + 0.5);
  }
  // pilgrim
  g.save(); g.translate(cx, cy); g.rotate(Math.PI - P.yaw);
  g.fillStyle = '#1f6a47'; g.strokeStyle = '#ffffff'; g.lineWidth = 1.5; g.beginPath(); g.moveTo(0, -7); g.lineTo(5, 5); g.lineTo(0, 2.5); g.lineTo(-5, 5); g.closePath(); g.fill(); g.stroke();
  g.restore(); g.restore();
  g.fillStyle = '#16130e'; g.beginPath(); g.arc(cx, 9, 7, 0, TAU); g.fill(); g.fillStyle = '#e7cb82'; g.font = '700 8px system-ui, sans-serif'; g.fillText('N', cx, 9.5);
}

/* ================= Movement ================= */
const K = new Set();
const joy = { active: false, id: null, ox: 0, oy: 0, x: 0, y: 0 };
function autoShouldRun(){
  if (S.gender !== 'm') return false;
  if (S.phase === 'tawaf') return S.tw.rounds < 3;
  if (S.phase === 'sai') return inMasa(P.pos) && P.pos.z > GZ0 && P.pos.z < GZ1;
  return false;
}
function autoDir(){
  const p = P.pos;
  if (S.phase === 'tawaf'){
    const r = Math.hypot(p.x, p.z) || 1;
    if (P.autoR === null) P.autoR = clamp(r, 16, 30);
    const err = P.autoR - r; let x = p.z / r + (p.x / r) * err * 0.25, z = -p.x / r + (p.z / r) * err * 0.25;
    const l = Math.hypot(x, z); return { x: x / l, z: z / l };
  }
  if (S.phase === 'sai' && inMasa(p)){
    const tz = S.sai.heading === 'marwa' ? MARWA_PT.z - 2 : SAFA_PT.z + 2, dz = tz - p.z;
    if (Math.abs(dz) < 0.5) return null;
    const x = (Math.abs(p.x - 66.5) < 1.5 || Math.abs(p.x - 73.5) < 1.5) ? (70 - p.x) * 0.25 : 0, l = Math.hypot(x, 1);
    return { x: x / l, z: Math.sign(dz) / l };
  }
  if (!S.target) return null;
  const dx = S.target.x - p.x, dz = S.target.z - p.z, d = Math.hypot(dx, dz);
  if (d < Math.min(0.8, S.targetR * 0.5)) return null;
  let x = dx / d, z = dz / d;
  const r = Math.hypot(p.x, p.z) || 1;
  if (r < 36){
    const tc = clamp(-(p.x * x + p.z * z), 0, d), cx = p.x + x * tc, cz = p.z + z * tc;
    if (Math.hypot(cx, cz) < 16.5 && tc > 0.5){
      let tx = p.z / r, tz = -p.x / r;
      if (wrapPi(angleOf(S.target.x, S.target.z) - angleOf(p.x, p.z)) < 0){ tx = -tx; tz = -tz; }
      const o = r < 17 ? 0.6 : 0; x = tx + p.x / r * o; z = tz + p.z / r * o; const l = Math.hypot(x, z); x /= l; z /= l;
    }
  }
  if (P.stuck > 0.35){ const c = Math.cos(1.0), s = Math.sin(1.0), nx = x * c - z * s; z = x * s + z * c; x = nx; if (P.stuck > 1.4) P.stuck = 0; }
  return { x, z };
}
function updatePlayer(dt){
  let ix = 0, iy = 0;
  if (K.has('KeyW') || K.has('ArrowUp')) iy += 1; if (K.has('KeyS') || K.has('ArrowDown')) iy -= 1;
  if (K.has('KeyA') || K.has('ArrowLeft')) ix -= 1; if (K.has('KeyD') || K.has('ArrowRight')) ix += 1;
  if (joy.active){ ix = joy.x; iy = joy.y; }
  const manual = Math.abs(ix) > 0.08 || Math.abs(iy) > 0.08;
  let mx = 0, mz = 0, mag = 0;
  if (manual){
    const fx = -Math.sin(cam.yaw), fz = -Math.cos(cam.yaw);
    mx = fx * iy - fz * ix; mz = fz * iy + fx * ix; mag = Math.min(1, Math.hypot(ix, iy));
    const l = Math.hypot(mx, mz) || 1; mx /= l; mz /= l;
    if (S.auto){ S.auto = false; autoBtn.setAttribute('aria-pressed', 'false'); }
  } else if (S.auto){ const d = autoDir(); if (d){ mx = d.x; mz = d.z; mag = 1; } }
  const run = K.has('ShiftLeft') || K.has('ShiftRight') || P.runHeld || (S.auto && autoShouldRun());
  P.running = run && mag > 0;
  if (mag <= 0){ P.moving = false; return; }
  const sp = (run ? 7.0 : 4.4) * mag, q = { x: P.pos.x + mx * sp * dt, z: P.pos.z + mz * sp * dt };
  cur.collide(q, P.pos);
  const moved = Math.hypot(q.x - P.pos.x, q.z - P.pos.z);
  S.walked += moved * zoneScale(q); if (S.phase === 'tawaf' && S.tw) S.tw.dist += moved;
  P.pos.x = q.x; P.pos.z = q.z; P.moving = moved > sp * dt * 0.15;
  P.yaw = lerpAngle(P.yaw, Math.atan2(mx, mz), 1 - Math.exp(-dt * 12));
  if (S.auto) P.stuck = moved < sp * dt * 0.3 ? P.stuck + dt : Math.max(0, P.stuck - dt);
}
function updateAvatarVis(){ const show = S.mode === 'follow'; AV.m.root.visible = show && S.gender === 'm'; AV.f.root.visible = show && S.gender === 'f'; renderNear(); }
function animateAvatar(dt){
  const A = AV[S.gender];
  A.root.position.set(P.pos.x, 0, P.pos.z); A.root.rotation.y = P.yaw;
  const k = 1 - Math.exp(-dt * 9);
  pose.bend = lerp(pose.bend, poseT.bend, k); pose.sy = lerp(pose.sy, poseT.sy, k);
  A.upper.rotation.x = pose.bend;
  A.body.scale.y = pose.sy * (1 + (P.moving ? Math.sin(T * (P.running ? 15 : 9)) * 0.03 : 0));
  A.ring.material.opacity = 0.7 + Math.sin(T * 3) * 0.25;
}
function updateGuides(){
  const show = S.mode === 'follow' && S.target && !S.prayer;
  wp.visible = !!show; arrow.visible = false; if (!show) return;
  wp.position.set(S.target.x, 0, S.target.z);
  const s = 1 + Math.sin(T * 3) * 0.1; wpRing.scale.set(s, 1, s);
  wpBeam.material.opacity = 0.14 + Math.sin(T * 2) * 0.06; wpBeam.visible = S.phase !== 'sai';
  const dx = S.target.x - P.pos.x, dz = S.target.z - P.pos.z, d = Math.hypot(dx, dz);
  if (d > 4 && S.phase !== 'tawaf'){ arrow.visible = true; arrow.position.set(P.pos.x + dx / d * 2.3, 0.06, P.pos.z + dz / d * 2.3); arrow.rotation.y = Math.atan2(-dx, -dz); }
}

/* ================= Camera ================= */
let camLookUp = 0;
const head = new THREE.Vector3(), desired = new THREE.Vector3(), probe = new THREE.Vector3(), look = new THREE.Vector3(), dir = new THREE.Vector3();
function camDir(yaw, out){ const cp = Math.cos(cam.pitch); return out.set(Math.sin(yaw) * cp, Math.sin(cam.pitch), Math.cos(yaw) * cp); }
function camFrac(yaw){
  camDir(yaw, dir); const steps = 18;
  for (let i = 1; i <= steps; i++){ probe.copy(dir).multiplyScalar(cam.dist * i / steps).add(head); if (!cur.camOK(probe)) return Math.max(0.12, (i - 1) / steps - 0.02); }
  return 1;
}
function updateCamera(dt){
  if (S.mode === 'orbit'){ const a = T * 0.045; camera.position.set(Math.cos(a) * 31, 17, Math.sin(a) * 31); camera.lookAt(0, 5, 0); S.camSnap = true; return; }
  if ((P.moving || S.prayer) && T - cam.lastUser > 1.6) cam.yaw = lerpAngle(cam.yaw, P.yaw + Math.PI, 1 - Math.exp(-dt * (S.prayer ? 2.5 : 1.5)));
  head.set(P.pos.x, 1.6, P.pos.z);
  let f = camFrac(cam.yaw);
  if (f < 0.5 && T - cam.lastUser > 1.0){
    let best = 0, bf = f;
    for (const o of [0.6, -0.6, 1.2, -1.2, 1.8, -1.8, 2.4, -2.4]){ const ff = camFrac(cam.yaw + o); if (ff > bf + 0.12){ bf = ff; best = o; } }
    if (best){ cam.yaw = S.camSnap ? cam.yaw + best : lerpAngle(cam.yaw, cam.yaw + best, 1 - Math.exp(-dt * 4)); f = camFrac(cam.yaw); }
  }
  camDir(cam.yaw, desired); desired.multiplyScalar(cam.dist * f).add(head);
  if (S.camSnap){ camera.position.copy(desired); S.camSnap = false; } else camera.position.lerp(desired, 1 - Math.exp(-dt * 10));
  // Tilt the view up while walking toward a gate so its number sign stays in sight
  let lookUp = 0;
  if (S.phase === 'approach'){ const g = polar(R_OUT, GATE_ANG[ROUTES[S.route].no]), d = Math.hypot(P.pos.x - g.x, P.pos.z - g.z); lookUp = clamp((40 - d) / 30, 0, 1) * 4.5; }
  camLookUp = lerp(camLookUp, lookUp, 1 - Math.exp(-dt * 3));
  look.set(P.pos.x, 3.0 + camLookUp - (1 - pose.sy) * 1.5, P.pos.z); camera.lookAt(look);
}

/* ================= Input ================= */
window.addEventListener('keydown', e => {
  if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.code) && !S.modal) e.preventDefault();
  K.add(e.code);
  if (e.code === 'KeyE' && !S.modal && S.actionFn && !S.locked) S.actionFn();
});
window.addEventListener('keyup', e => K.delete(e.code));
window.addEventListener('blur', () => { K.clear(); P.runHeld = false; runBtn.classList.remove('held'); });
const ptrs = new Map();
canvas.addEventListener('pointerdown', e => {
  canvas.setPointerCapture(e.pointerId);
  if (e.pointerType !== 'mouse' && e.clientX < window.innerWidth * 0.45 && !joy.active && S.mode === 'follow'){
    Object.assign(joy, { active: true, id: e.pointerId, ox: e.clientX, oy: e.clientY, x: 0, y: 0 });
    joyEl.style.left = e.clientX + 'px'; joyEl.style.top = e.clientY + 'px'; knobEl.style.transform = ''; joyEl.hidden = false; joyHint.hidden = true;
  } else ptrs.set(e.pointerId, { x: e.clientX, y: e.clientY });
});
canvas.addEventListener('pointermove', e => {
  if (joy.active && e.pointerId === joy.id){
    let dx = e.clientX - joy.ox, dy = e.clientY - joy.oy; const R = 52, l = Math.hypot(dx, dy);
    if (l > R){ dx *= R / l; dy *= R / l; }
    joy.x = dx / R; joy.y = -dy / R; knobEl.style.transform = `translate(${dx}px,${dy}px)`; return;
  }
  const pp = ptrs.get(e.pointerId); if (!pp) return;
  if (ptrs.size === 2){
    const other = [...ptrs.entries()].find(([id]) => id !== e.pointerId)[1];
    const before = Math.hypot(pp.x - other.x, pp.y - other.y), after = Math.hypot(e.clientX - other.x, e.clientY - other.y);
    if (after > 0) cam.dist = clamp(cam.dist * before / after, 4, 18);
  } else { cam.yaw -= (e.clientX - pp.x) * 0.006; cam.pitch = clamp(cam.pitch + (e.clientY - pp.y) * 0.004, 0.08, 1.25); cam.lastUser = T; }
  pp.x = e.clientX; pp.y = e.clientY;
});
const endPtr = e => { if (joy.active && e.pointerId === joy.id){ joy.active = false; joy.x = joy.y = 0; joyEl.hidden = true; joyHint.hidden = !(S.mode === 'follow' && isTouch); } ptrs.delete(e.pointerId); };
canvas.addEventListener('pointerup', endPtr); canvas.addEventListener('pointercancel', endPtr);
canvas.addEventListener('wheel', e => { e.preventDefault(); cam.dist = clamp(cam.dist * (1 + Math.sign(e.deltaY) * 0.08), 4, 18); }, { passive: false });
canvas.addEventListener('contextmenu', e => e.preventDefault());
actBtn.addEventListener('click', () => { if (S.actionFn && !S.modal && !S.locked) S.actionFn(); });
autoBtn.addEventListener('click', () => { S.auto = !S.auto; P.autoR = null; P.stuck = 0; autoBtn.setAttribute('aria-pressed', String(S.auto)); });
const runOn = e => { e.preventDefault(); P.runHeld = true; runBtn.classList.add('held'); };
const runOff = () => { P.runHeld = false; runBtn.classList.remove('held'); };
runBtn.addEventListener('pointerdown', runOn); runBtn.addEventListener('pointerup', runOff); runBtn.addEventListener('pointerleave', runOff); runBtn.addEventListener('pointercancel', runOff);
runBtn.addEventListener('keydown', e => { if (e.code === 'Space' || e.code === 'Enter') runOn(e); });
runBtn.addEventListener('keyup', runOff);
$('#helpBtn').addEventListener('click', () => { if (!S.modal) showCard(cardHelp); });
document.querySelectorAll('.langs [data-lang]').forEach(b => b.addEventListener('click', () => setLang(b.dataset.lang)));
window.addEventListener('resize', () => { mapPx = 0; });

/* ================= Language ================= */
function setLang(l){
  lang = l; store.set('lang', l);
  document.documentElement.lang = l === 'bn' ? 'bn' : 'en'; document.body.classList.toggle('bn', l === 'bn');
  document.title = t('title');
  document.querySelectorAll('.langs [data-lang]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.lang === l)));
  $('#brand').textContent = t('brand'); $('#helpBtn').setAttribute('aria-label', t('help')); mapC.setAttribute('aria-label', t('mapLabel'));
  autoBtn.textContent = t('auto'); runBtn.textContent = t('run'); joyHint.textContent = t('move');
  if (S.objKey){ S.objText = null; setObjective(S.objKey, S.objVars); }
  if (S.hintKey){ const h = S.hintKey; S.hintKey = null; setHint(h); }
  if (S.stripKey){ const [d, k] = S.stripKey.split('|'); S.stripKey = null; setStrip(d, k); }
  if (S.actionKey) actBtn.textContent = t(S.actionKey);
  lastCounter = ''; renderNear(); renderSteps(); renderMapGate(); renderDist();
  if (S.prayer) renderPray();
  if (S.cardBuilder) renderCard();
}

/* ================= Loop ================= */
let last = performance.now();
function frame(now){
  requestAnimationFrame(frame);
  const dt = Math.min(0.05, Math.max(0, (now - last) / 1000)); last = now; T += dt;
  if (!cur) return;
  const follow = S.mode === 'follow', active = follow && !S.modal && !S.locked && !S.fading;
  if (active) updatePlayer(dt); else P.moving = false;
  if (S.prayer) prayerTick(dt);
  if (follow) animateAvatar(dt);
  cur.update(dt, T);
  if (active) phaseTick();
  updateGuides(); updateCamera(dt); drawMap();
  if (follow && (distTick -= dt) <= 0){ distTick = 0.2; renderDist(); }
  renderer.render(world, camera);
}

/* ================= Start ================= */
async function init(){
  resize();
  try {
    await Promise.race([
      Promise.all([document.fonts.load('700 64px Amiri'), document.fonts.load('600 40px "Hind Siliguri"'), document.fonts.load('400 30px "Tiro Bangla"')]),
      new Promise(r => setTimeout(r, 2500))
    ]);
  } catch (e) {}
  cur = buildWorld(); world.add(cur.group); world.background = cur.bg; world.fog = cur.fog;
  world.add(AV.m.root, AV.f.root, arrow, wp);
  setLang(lang);
  setPhase('intro');
  $('#loading').hidden = true;
  requestAnimationFrame(frame);
  if (DEBUG) window.__umrah = { S, P, goTo, setPhase, cam, place, camFrac, ROUTES, get cur(){ return cur; } };
}
init();
})();
</script>
</body>
</html>
