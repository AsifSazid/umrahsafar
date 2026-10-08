'use strict';

/* ===== TEXTURE HELPERS ===== */
const TAU = Math.PI * 2;
function rng(seed) { return function () { seed |= 0; seed = seed + 0x6D2B79F5 | 0; let t = Math.imul(seed ^ seed >>> 15, 1 | seed); t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t; return ((t ^ t >>> 14) >>> 0) / 4294967296; }; }
const pick = (r, arr) => arr[Math.floor(r() * arr.length)];
const MAXANISO = Math.min(8, (function () { try { const r = new THREE.WebGLRenderer(); const a = r.capabilities.getMaxAnisotropy(); r.dispose(); return a; } catch (e) { return 1; } })());

function ctex(w, h, draw, rx = 1, ry = 1) {
  const c = document.createElement('canvas'); c.width = w; c.height = h;
  draw(c.getContext('2d'), w, h);
  const tx = new THREE.CanvasTexture(c);
  tx.wrapS = tx.wrapT = THREE.RepeatWrapping; tx.repeat.set(rx, ry); tx.anisotropy = MAXANISO;
  return tx;
}
function drawMarble(g, w, h, seed, base, grout) {
  const r = rng(seed); g.fillStyle = base; g.fillRect(0, 0, w, h);
  for (let i = 0; i < 24; i++) {
    g.strokeStyle = `rgba(135,140,145,${0.04 + r() * 0.08})`; g.lineWidth = 0.6 + r() * 1.6;
    g.beginPath(); const x = r() * w, y = r() * h; g.moveTo(x, y);
    g.bezierCurveTo(x + (r() - .5) * w, y + (r() - .5) * h, x + (r() - .5) * w, y + (r() - .5) * h, x + (r() - .5) * w * 1.2, y + (r() - .5) * h * 1.2);
    g.stroke();
  }
  g.strokeStyle = grout; g.lineWidth = 3; g.strokeRect(1.5, 1.5, w - 3, h - 3);
}
function archPath(g, x0, yb, x1, ya) {
  const mid = (x0 + x1) / 2, ys = ya + (x1 - x0) * 0.5;
  g.beginPath(); g.moveTo(x0, yb); g.lineTo(x0, ys);
  g.quadraticCurveTo(x0, ya + (ys - ya) * 0.12, mid, ya);
  g.quadraticCurveTo(x1, ya + (ys - ya) * 0.12, x1, ys);
  g.lineTo(x1, yb); g.closePath();
}
function windowWall(o) {
  return ctex(256, 256, (g, w, h) => {
    g.fillStyle = o.base; g.fillRect(0, 0, w, h);
    g.fillStyle = o.trim; g.fillRect(0, 0, w, 9); g.fillRect(0, h - 7, w, 7);
    g.fillStyle = o.pil || o.trim; g.fillRect(0, 0, 12, h); g.fillRect(w - 12, 0, 12, h);
    g.fillStyle = o.glass; archPath(g, w * .27, h * .88, w * .73, h * .16); g.fill();
    g.strokeStyle = o.trim; g.lineWidth = 6; g.stroke();
    g.fillStyle = o.trim; g.fillRect(w / 2 - 2, h * .42, 4, h * .46); g.fillRect(w * .27, h * .62, w * .46, 3);
  }, o.rx || 1, o.ry || 1);
}
function archBand(n) {
  return ctex(256, 128, (g, w, h) => {
    g.fillStyle = '#ddd0b6'; g.fillRect(0, 0, w, h);
    g.fillStyle = '#c7b48f'; g.fillRect(0, 0, w, 12); g.fillRect(0, 20, w, 3);
    g.globalCompositeOperation = 'destination-out';
    archPath(g, 26, h + 2, w - 26, h * .3); g.fill();
    g.globalCompositeOperation = 'source-over';
    g.strokeStyle = '#b39d74'; g.lineWidth = 5; archPath(g, 26, h + 2, w - 26, h * .3); g.stroke();
  }, n, 1);
}
function hotelTex(base, win) {
  return ctex(128, 256, (g, w, h) => {
    g.fillStyle = base; g.fillRect(0, 0, w, h); g.fillStyle = win;
    for (let y = 10; y < h; y += 22) for (let x = 9; x < w; x += 20) g.fillRect(x, y, 11, 13);
  }, 2, 5);
}
function roundRect(g, x, y, w, h, r) {
  g.beginPath(); g.moveTo(x + r, y); g.lineTo(x + w - r, y); g.quadraticCurveTo(x + w, y, x + w, y + r);
  g.lineTo(x + w, y + h - r); g.quadraticCurveTo(x + w, y + h, x + w - r, y + h); g.lineTo(x + r, y + h);
  g.quadraticCurveTo(x, y + h, x, y + h - r); g.lineTo(x, y + r); g.quadraticCurveTo(x, y, x + r, y); g.closePath();
}
function signSprite(ar, en, width) {
  const c = document.createElement('canvas'); c.width = 1024; c.height = 300; const g = c.getContext('2d');
  roundRect(g, 8, 8, 1008, 284, 24); g.fillStyle = 'rgba(15,74,52,0.94)'; g.fill();
  g.lineWidth = 8; g.strokeStyle = '#d4b35e'; g.stroke();
  g.fillStyle = '#ffffff'; g.textAlign = 'center'; g.textBaseline = 'middle';
  g.font = '700 118px Amiri,"Noto Naskh Arabic",serif'; g.fillText(ar, 512, 108);
  g.font = '600 74px "Hind Siliguri",system-ui,sans-serif'; g.fillText(en, 512, 224);
  const tx = new THREE.CanvasTexture(c); tx.anisotropy = MAXANISO;
  const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tx, depthWrite: false, fog: false }));
  sp.scale.set(width, width * 300 / 1024, 1);
  return sp;
}
function gateSign(no) {
  const arDigits = n => String(n).replace(/\d/g, d => '٠١٢٣٤٥٦٧٨٩'[d]);
  const c = document.createElement('canvas'); c.width = 1024; c.height = 340; const g = c.getContext('2d');
  roundRect(g, 8, 8, 1008, 324, 26); g.fillStyle = 'rgba(15,74,52,0.95)'; g.fill();
  g.lineWidth = 8; g.strokeStyle = '#d4b35e'; g.stroke();
  g.beginPath(); g.arc(172, 170, 130, 0, TAU); g.fillStyle = '#fbfaf6'; g.fill(); g.lineWidth = 10; g.strokeStyle = '#d4b35e'; g.stroke();
  g.fillStyle = '#0f4a34'; g.textAlign = 'center'; g.textBaseline = 'middle';
  g.font = `700 ${String(no).length > 1 ? 128 : 160}px "Hind Siliguri",system-ui,sans-serif`; g.fillText(String(no), 172, 150);
  g.font = '700 58px Amiri,serif'; g.fillText(arDigits(no), 172, 248);
  g.fillStyle = '#ffffff';
  g.font = '700 100px Amiri,"Noto Naskh Arabic",serif'; g.fillText(GATES[no].ar, 655, 115);
  g.font = '600 62px "Hind Siliguri",system-ui,sans-serif'; g.fillText(GATES[no].n.en, 655, 238);
  const tx = new THREE.CanvasTexture(c); tx.anisotropy = MAXANISO;
  const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tx, depthWrite: false, fog: false }));
  sp.scale.set(12, 12 * 340 / 1024, 1);
  return sp;
}

/* ===== MATERIALS ===== */
const lam = (color, extra) => new THREE.MeshLambertMaterial(Object.assign({ color }, extra || {}));
const MAT = {
  gold: new THREE.MeshPhongMaterial({ color: 0xc9a24a, specular: 0xfff0b0, shininess: 70, emissive: 0x2b1e06 }),
  silver: new THREE.MeshPhongMaterial({ color: 0xcfd4d8, specular: 0xffffff, shininess: 90 }),
  stone: lam(0xe3d7c1), stoneDark: lam(0xcdbd9e)
};

/* ===== MESH HELPERS ===== */
function add(parent, geo, mat, x = 0, y = 0, z = 0) {
  const m = new THREE.Mesh(geo, mat); m.position.set(x, y, z); parent.add(m); return m;
}
function crescent(parent, y, s = 1) {
  const c = add(parent, new THREE.TorusGeometry(0.55 * s, 0.11 * s, 8, 20, Math.PI * 1.35), MAT.gold, 0, y, 0);
  c.rotation.z = Math.PI * 0.82; return c;
}
function minaret(h, s = 1) {
  const g = new THREE.Group(), m = MAT.stone;
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

/* ===== GEOMETRY CONSTANTS ===== */
const KAABA_ROT = Math.PI * 0.75, KC = Math.cos(KAABA_ROT), KS = Math.sin(KAABA_ROT);
const KW = 11.5, KD = 10.5, KH = 13, KB = 0.4;
const kw = (lx, lz) => ({ x: lx * KC + lz * KS, z: -lx * KS + lz * KC });
const kl = (x, z) => ({ x: x * KC - z * KS, z: x * KS + z * KC });
const polar = (r, a) => ({ x: r * Math.cos(a), z: -r * Math.sin(a) });
const angleOf = (x, z) => Math.atan2(-z, x);
const BS = kw(-KW / 2, KD / 2), A0 = angleOf(BS.x, BS.z);
const YM = kw(-KW / 2, -KD / 2), AY = angleOf(YM.x, YM.z);
const MAQAM_POS = kw(-1, KD / 2 + 10.5);
const ZAMZAM_POS = polar(31.2, 0.30);
const HIJR_A = 8.3, HIJR_B = 6.7;
const R_IN = 44, R_OUT = 60, HALL_H = 10, BLDG_H = 24, PLAZA_R = 95;
const IN_LINTEL = 7.5, OUT_LINTEL = 8.6;
const A_G1 = -Math.PI / 2, A_G45 = 1.5, A_G100 = 1.95, A_G62 = 2.42, A_G79 = Math.PI, A_SAFA = -0.5;
const GATE_ANG = { 1: A_G1, 45: A_G45, 100: A_G100, 62: A_G62, 79: A_G79 };
const MAIN_GATES = [1, 45, 100, 62, 79];
const OPENINGS = MAIN_GATES.map(n => ({ a: GATE_ANG[n], hw: 3.4 })).concat([{ a: A_SAFA, hw: 3.3 }]);
const MX0 = 62.6, MX1 = 77.4, MZS = 45, MZN = -53, GZ0 = 8, GZ1 = 25;

function openingAt(R, a, extra = 0) {
  for (const o of OPENINGS) if (Math.abs(wrapPi(a - o.a)) * R < o.hw + extra) return o;
  return null;
}
function wrapPi(a) { while (a > Math.PI) a -= TAU; while (a < -Math.PI) a += TAU; return a; }

/* ===== COLLISION HELPERS ===== */
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
function pushBox(p, b, r) {
  const cx = clamp(p.x, b.x0, b.x1), cz = clamp(p.z, b.z0, b.z1), dx = p.x - cx, dz = p.z - cz, d2 = dx * dx + dz * dz;
  if (d2 >= r * r) return;
  if (d2 > 1e-9) { const d = Math.sqrt(d2); p.x = cx + dx / d * r; p.z = cz + dz / d * r; return; }
  const l = p.x - b.x0, rr = b.x1 - p.x, tt = p.z - b.z0, bb = b.z1 - p.z, m = Math.min(l, rr, tt, bb);
  if (m === l) p.x = b.x0 - r; else if (m === rr) p.x = b.x1 + r; else if (m === tt) p.z = b.z0 - r; else p.z = b.z1 + r;
}
function pushSeg(p, s, r) {
  const vx = s.x2 - s.x1, vz = s.z2 - s.z1, L2 = vx * vx + vz * vz;
  const u = clamp(((p.x - s.x1) * vx + (p.z - s.z1) * vz) / L2, 0, 1), cx = s.x1 + vx * u, cz = s.z1 + vz * u;
  const dx = p.x - cx, dz = p.z - cz, d = Math.hypot(dx, dz), m = s.t / 2 + r;
  if (d < m && d > 1e-6) { p.x = cx + dx / d * m; p.z = cz + dz / d * m; }
}
function pushCircle(p, c, r) {
  const dx = p.x - c.x, dz = p.z - c.z, d = Math.hypot(dx, dz), m = c.r + r;
  if (d < m && d > 1e-6) { p.x = c.x + dx / d * m; p.z = c.z + dz / d * m; }
}
function pushRing(p, R, t) {
  const r = Math.hypot(p.x, p.z), m = t / 2 + 0.45;
  if (Math.abs(r - R) >= m || r < 1e-6) return;
  if (openingAt(R, angleOf(p.x, p.z))) return;
  const k = (r < R ? R - m : R + m) / r; p.x *= k; p.z *= k;
}
function segDist(x, z, s) {
  const vx = s.x2 - s.x1, vz = s.z2 - s.z1, u = clamp(((x - s.x1) * vx + (z - s.z1) * vz) / (vx * vx + vz * vz), 0, 1);
  return Math.hypot(x - (s.x1 + vx * u), z - (s.z1 + vz * u));
}

const ROADS = [
  { k: 'A', x1: -30, z1: 214, x2: -30, z2: 78, hw: 7 },
  { k: 'B', x1: 55, z1: 214, x2: 55, z2: 72, hw: 7 },
  { k: 'C', x1: -212, z1: 1, x2: -80, z2: 1, hw: 7 },
  { k: 'D', x1: polar(212, A_G62).x, z1: polar(212, A_G62).z, x2: polar(84, A_G62).x, z2: polar(84, A_G62).z, hw: 7 },
  { k: 'E', x1: 60, z1: -230, x2: 60, z2: -66, hw: 7 }
];
function walkable(x, z) {
  if (x * x + z * z < PLAZA_R * PLAZA_R) return true;
  for (const r of ROADS) if (segDist(x, z, r) < r.hw) return true;
  return false;
}

/* ===== CROWD ===== */
const MALE_IHRAM = ['#f6f5f0', '#f1efe8', '#ebe8de', '#f8f8f6'];
const FEMALE_CLR = ['#1d1b20', '#26232b', '#2f2a2a', '#3a3f4a', '#55483b'];
const SKIN_CLR = ['#c99a76', '#a87656', '#7a5236', '#e0b896'];
const HIJAB_CLR = ['#1b1b1f', '#f2efe8', '#2c2f3a', '#3d2f2f'];

function makeCrowd(n, seed, maleRobes) {
  const bodyGeo = new THREE.CylinderGeometry(0.26, 0.36, 1.32, 8); bodyGeo.translate(0, 0.66, 0);
  const headGeo = new THREE.SphereGeometry(0.19, 10, 8); headGeo.translate(0, 1.5, 0);
  const bodies = new THREE.InstancedMesh(bodyGeo, lam(0xffffff), n);
  const heads = new THREE.InstancedMesh(headGeo, lam(0xffffff), n);
  bodies.instanceMatrix.setUsage(THREE.DynamicDrawUsage);
  heads.instanceMatrix.setUsage(THREE.DynamicDrawUsage);
  bodies.frustumCulled = heads.frustumCulled = false;
  const r = rng(seed), col = new THREE.Color(), agents = [];
  for (let i = 0; i < n; i++) {
    const female = r() < 0.4;
    bodies.setColorAt(i, col.set(female ? pick(r, FEMALE_CLR) : pick(r, maleRobes)));
    heads.setColorAt(i, col.set(female ? pick(r, HIJAB_CLR) : pick(r, SKIN_CLR)));
    agents.push({ female, ph: r() * TAU, r1: r(), r2: r(), r3: r() });
  }
  bodies.instanceColor.needsUpdate = true; heads.instanceColor.needsUpdate = true;
  const mm = new THREE.Matrix4();
  return {
    bodies, heads, agents,
    set(i, x, z, yaw, y = 0, sy = 1) {
      mm.makeRotationY(yaw); const e = mm.elements;
      if (sy !== 1) { e[4] *= sy; e[5] *= sy; e[6] *= sy; }
      e[12] = x; e[13] = y; e[14] = z;
      bodies.setMatrixAt(i, mm); heads.setMatrixAt(i, mm);
    },
    commit() { bodies.instanceMatrix.needsUpdate = true; heads.instanceMatrix.needsUpdate = true; }
  };
}

/* ===== BUILD WORLD ===== */
function buildWorld(scene, isTouch) {
  const G = new THREE.Group();
  const sc = { name: 'world', group: G, boxes: [], segs: [], circles: [], infos: [], cboxes: [] };
  sc.bg = (function () {
    return ctex(4, 512, (g, w, h) => {
      const gr = g.createLinearGradient(0, 0, 0, h);
      gr.addColorStop(0, '#79a5c9'); gr.addColorStop(.58, '#cfe1ea'); gr.addColorStop(1, '#f3e7d0');
      g.fillStyle = gr; g.fillRect(0, 0, w, h);
    });
  })();
  sc.fog = new THREE.Fog(0xefe3cc, 220, 780);
  const dummy = new THREE.Object3D(), mm = new THREE.Matrix4();
  const unitBox = new THREE.BoxGeometry(1, 1, 1); unitBox.translate(0, 0.5, 0);

  /* Ground */
  const gTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 5, '#d9d1c1', 'rgba(110,100,85,.25)'), 260, 260);
  add(G, new THREE.PlaneGeometry(1600, 1600).rotateX(-Math.PI / 2), lam(0xd8d2c6, { map: gTex }));
  const pTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 7, '#f2f0ea', 'rgba(140,140,135,.28)'), 50, 50);
  add(G, new THREE.CircleGeometry(PLAZA_R, 128).rotateX(-Math.PI / 2), lam(0xd3d0c9, { map: pTex }), 0, 0.01, 0);
  const rTex = ctex(128, 128, (g, w, h) => {
    g.fillStyle = '#bdb3a2'; g.fillRect(0, 0, w, h);
    g.strokeStyle = 'rgba(90,80,70,.25)'; g.lineWidth = 2;
    for (let i = 0; i <= w; i += 32) { g.beginPath(); g.moveTo(i, 0); g.lineTo(i, h); g.stroke(); g.beginPath(); g.moveTo(0, i); g.lineTo(w, i); g.stroke(); }
  });
  for (const r of ROADS) {
    const dx = r.x2 - r.x1, dz = r.z2 - r.z1, L = Math.hypot(dx, dz), tx = rTex.clone(); tx.needsUpdate = true; tx.repeat.set(4, Math.round(L / 3.5));
    add(G, new THREE.PlaneGeometry(r.hw * 2, L).rotateX(-Math.PI / 2), lam(0xffffff, { map: tx }), (r.x1 + r.x2) / 2, 0.012, (r.z1 + r.z2) / 2).rotation.y = Math.atan2(dx, dz);
  }
  const mTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 9, '#f5f6f4', 'rgba(140,150,155,.26)'), 26, 26);
  add(G, new THREE.CircleGeometry(R_IN, 128).rotateX(-Math.PI / 2), lam(0xe4e6e2, { map: mTex }), 0, 0.02, 0);

  /* Kaaba */
  const kiswaTex = ctex(1024, 1024, (g, w, h) => {
    g.fillStyle = '#0d0c0e'; g.fillRect(0, 0, w, h);
    g.strokeStyle = 'rgba(255,255,255,0.05)'; g.lineWidth = 2;
    for (let y = 0; y < h + 48; y += 48) for (let x = ((y / 48) % 2) * 24; x < w + 48; x += 48) {
      g.beginPath(); g.moveTo(x - 14, y); g.lineTo(x, y - 20); g.lineTo(x + 14, y); g.lineTo(x, y + 20); g.closePath(); g.stroke();
    }
    const y0 = h * .26, bh = h * .09;
    g.fillStyle = '#d4ad55'; g.fillRect(0, y0, w, 7); g.fillRect(0, y0 + bh - 7, w, 7);
    const r = rng(7); g.strokeStyle = '#dab45f'; g.fillStyle = '#dab45f'; g.lineCap = 'round'; g.lineWidth = 5;
    let x = 12;
    while (x < w - 50) {
      const k = r();
      if (k < .35) { g.beginPath(); g.moveTo(x, y0 + bh - 16); g.lineTo(x, y0 + 16 + r() * 22); g.stroke(); x += 15; }
      else if (k < .7) { g.beginPath(); g.ellipse(x + 15, y0 + bh - 24, 16, 8 + r() * 7, 0, 0, Math.PI); g.stroke(); x += 34; }
      else { g.beginPath(); g.moveTo(x, y0 + bh - 18); g.quadraticCurveTo(x + 20, y0 + bh * .3, x + 40, y0 + bh - 18); g.stroke(); g.beginPath(); g.arc(x + 20, y0 + 22, 4, 0, TAU); g.fill(); x += 46; }
    }
  });
  const doorTex = ctex(128, 256, (g, w, h) => {
    const gr = g.createLinearGradient(0, 0, w, 0); gr.addColorStop(0, '#9e7a2c'); gr.addColorStop(.5, '#e6c672'); gr.addColorStop(1, '#9e7a2c');
    g.fillStyle = gr; g.fillRect(0, 0, w, h);
    g.strokeStyle = '#6f531b'; g.lineWidth = 4; g.strokeRect(6, 6, w - 12, h - 12);
    g.beginPath(); g.moveTo(w / 2, 6); g.lineTo(w / 2, h - 6); g.stroke();
    for (let y = 26; y < h - 30; y += 46) { g.strokeRect(14, y, w / 2 - 22, 32); g.strokeRect(w / 2 + 8, y, w / 2 - 22, 32); }
  });
  const K = new THREE.Group(); K.rotation.y = KAABA_ROT; G.add(K);
  add(K, new THREE.BoxGeometry(KW + 1.0, KB, KD + 1.0), lam(0xd9d9d3), 0, KB / 2, 0);
  const kSide = lam(0xffffff, { map: kiswaTex }), kTop = lam(0x141210);
  add(K, new THREE.BoxGeometry(KW, KH, KD), [kSide, kSide, kTop, kTop, kSide, kSide], 0, KB + KH / 2, 0);
  add(K, new THREE.BoxGeometry(2.0, 3.3, 0.22), new THREE.MeshPhongMaterial({ map: doorTex, specular: 0x775522, shininess: 40 }), -3.0, KB + 2.2 + 1.65, KD / 2 + 0.09);
  const bs = new THREE.Group(); bs.position.set(-KW / 2 - 0.07, KB + 1.15, KD / 2 + 0.07); bs.rotation.y = -Math.PI / 4; K.add(bs);
  add(bs, new THREE.TorusGeometry(0.34, 0.09, 10, 32), MAT.silver).scale.set(0.85, 1.15, 1);
  add(bs, new THREE.CircleGeometry(0.33, 24), lam(0x2a201b), 0, 0, -0.02).scale.set(0.85, 1.15, 1);
  add(K, new THREE.BoxGeometry(1.8, 0.3, 0.5), MAT.gold, KW / 2 + 0.9, KB + KH - 0.5, 0);
  /* Hijr */
  const hijrMat = lam(0xf2f2ef), capMat = lam(0xd6cfc0);
  { const N = 28, p0 = -Math.PI / 2 + 0.16, p1 = Math.PI / 2 - 0.16; let prev = null;
    for (let i = 0; i <= N; i++) {
      const ph = p0 + (p1 - p0) * i / N, pt = { x: KW / 2 + HIJR_A * Math.cos(ph), z: HIJR_B * Math.sin(ph) };
      if (prev) {
        const dx = pt.x - prev.x, dz = pt.z - prev.z, len = Math.hypot(dx, dz), ry = -Math.atan2(dz, dx);
        add(K, new THREE.BoxGeometry(len + 0.06, 1.3, 0.85), hijrMat, (pt.x + prev.x) / 2, 0.65, (pt.z + prev.z) / 2).rotation.y = ry;
        add(K, new THREE.BoxGeometry(len + 0.06, 0.12, 0.95), capMat, (pt.x + prev.x) / 2, 1.36, (pt.z + prev.z) / 2).rotation.y = ry;
      }
      prev = pt;
    } }
  /* Tawaf line */
  const lineLen = 35.5 - 8.6, rc = 8.6 + lineLen / 2;
  add(G, new THREE.PlaneGeometry(lineLen, 0.22).rotateX(-Math.PI / 2), new THREE.MeshBasicMaterial({ color: 0x7a5a3a, transparent: true, opacity: .42, depthWrite: false }), rc * Math.cos(A0), 0.04, -rc * Math.sin(A0)).rotation.y = A0;
  const lamp = polar(37.0, A0); add(G, new THREE.BoxGeometry(0.6, 0.6, 0.6), new THREE.MeshBasicMaterial({ color: 0x3ee28a }), lamp.x, 4.0, lamp.z);
  /* Maqam */
  const mq = new THREE.Group(); mq.position.set(MAQAM_POS.x, 0, MAQAM_POS.z); G.add(mq);
  add(mq, new THREE.CylinderGeometry(1.0, 1.15, 0.4, 8), lam(0xdedad0), 0, 0.2, 0);
  add(mq, new THREE.BoxGeometry(0.55, 0.55, 0.55), lam(0x9d8a6e), 0, 0.68, 0);
  add(mq, new THREE.CylinderGeometry(0.72, 0.72, 1.9, 8, 1, true), new THREE.MeshPhongMaterial({ color: 0xd8b65a, transparent: true, opacity: .5, side: THREE.DoubleSide, depthWrite: false }), 0, 1.35, 0);
  add(mq, new THREE.CylinderGeometry(0.8, 0.8, 0.12, 8), MAT.gold, 0, 0.42, 0);
  add(mq, new THREE.CylinderGeometry(0.8, 0.8, 0.12, 8), MAT.gold, 0, 2.3, 0);
  add(mq, new THREE.SphereGeometry(0.75, 16, 8, 0, TAU, 0, Math.PI / 2), MAT.gold, 0, 2.35, 0);
  add(mq, new THREE.ConeGeometry(0.1, 0.6, 8), MAT.gold, 0, 3.35, 0);
  sc.circles.push({ x: MAQAM_POS.x, z: MAQAM_POS.z, r: 1.15 });
  /* Zamzam */
  const zz = new THREE.Group(); zz.position.set(ZAMZAM_POS.x, 0, ZAMZAM_POS.z); zz.rotation.y = 0.30 + Math.PI / 2; G.add(zz);
  add(zz, new THREE.BoxGeometry(5.2, 0.9, 1.1), lam(0xd9d4c8), 0, 0.45, 0);
  for (const k of [-1.8, -0.6, 0.6, 1.8]) { add(zz, new THREE.CylinderGeometry(0.32, 0.32, 1.0, 16), MAT.silver, k, 1.4, 0); }
  const zs = signSprite('زمزم', 'Zamzam', 4.2); zs.position.set(0, 3.3, 0); zz.add(zs);
  const tg = { x: -Math.sin(0.30), z: -Math.cos(0.30) };
  sc.circles.push({ x: ZAMZAM_POS.x + tg.x * 1.4, z: ZAMZAM_POS.z + tg.z * 1.4, r: 1.3 });

  /* Colonnade */
  const NC = 72, RC = 37.4, colGeo = new THREE.CylinderGeometry(0.4, 0.46, 4.4, 10); colGeo.translate(0, 2.2, 0);
  const colTh = []; for (let i = 0; i < NC; i++) { const th = i / NC * TAU; if (!openingAt(RC, th - Math.PI / 2, 0.9)) colTh.push(th); }
  const cols = new THREE.InstancedMesh(colGeo, lam(0xefe8da), colTh.length);
  colTh.forEach((th, i) => { const x = RC * Math.sin(th), z = RC * Math.cos(th); mm.makeTranslation(x, 0, z); cols.setMatrixAt(i, mm); sc.circles.push({ x, z, r: 0.46, cam: true }); });
  G.add(cols);
  add(G, new THREE.CylinderGeometry(RC, RC, 3.0, NC * 2, 1, true), lam(0xffffff, { map: archBand(NC), alphaTest: .5, side: THREE.DoubleSide }), 0, 5.6, 0);
  add(G, new THREE.RingGeometry(RC - 0.5, R_IN, 144, 1).rotateX(-Math.PI / 2), lam(0xd9ccb3, { side: THREE.DoubleSide }), 0, 7.1, 0);
  add(G, new THREE.CylinderGeometry(RC - 0.5, RC - 0.5, 0.6, 144, 1, true), lam(0xcdbd9e, { side: THREE.DoubleSide }), 0, 7.4, 0);
  const domes = new THREE.InstancedMesh(new THREE.SphereGeometry(1.5, 14, 8, 0, TAU, 0, Math.PI / 2), lam(0xd6c8ad), NC / 2);
  for (let i = 0; i < NC / 2; i++) { const th = (i + .5) / (NC / 2) * TAU; mm.makeTranslation(40.7 * Math.sin(th), 7.1, 40.7 * Math.cos(th)); domes.setMatrixAt(i, mm); }
  G.add(domes);

  /* Ring walls */
  function ringWall(R, t, h, segLen, mat, lintelY, lintelMat) {
    const ops = OPENINGS.slice().sort((p, q) => p.a - q.a), parts = [];
    ops.forEach((o, k) => {
      const nx = ops[(k + 1) % ops.length];
      const e1 = o.a + o.hw / R; let e2 = nx.a - nx.hw / R; if (e2 <= e1) e2 += TAU;
      const span = e2 - e1, n = Math.max(1, Math.round(span * R / segLen));
      for (let j = 0; j < n; j++) parts.push({ a: e1 + (j + .5) / n * span, len: span * R / n });
      const p = polar(R, o.a);
      add(G, new THREE.BoxGeometry(o.hw * 2 + 0.3, h - lintelY, t + 0.1), lintelMat, p.x, (h + lintelY) / 2, p.z).rotation.y = o.a + Math.PI / 2;
    });
    const im = new THREE.InstancedMesh(unitBox, mat, parts.length);
    parts.forEach((pt, i) => { const p = polar(R, pt.a); dummy.position.set(p.x, 0, p.z); dummy.rotation.set(0, pt.a + Math.PI / 2, 0); dummy.scale.set(pt.len + 0.08, h, t); dummy.updateMatrix(); im.setMatrixAt(i, dummy.matrix); });
    G.add(im);
  }
  ringWall(R_IN, 1.2, HALL_H, 3.2, lam(0xffffff, { map: windowWall({ base: '#e4d8c1', trim: '#c4b08b', glass: '#6a6253' }) }), IN_LINTEL, lam(0xd9ccb3));
  ringWall(R_OUT, 1.6, BLDG_H, 4.2, lam(0xffffff, { map: windowWall({ base: '#ebe2cf', trim: '#c7b38c', glass: '#58544b', ry: 3 }) }), OUT_LINTEL, lam(0xd9ccb3));
  add(G, new THREE.RingGeometry(R_IN - 0.6, R_OUT + 0.8, 160).rotateX(-Math.PI / 2), lam(0xe9e1cf, { side: THREE.DoubleSide }), 0, HALL_H, 0);
  add(G, new THREE.CylinderGeometry(47, 47, BLDG_H - HALL_H, 160, 1, true), lam(0xffffff, { map: windowWall({ base: '#e2d6bf', trim: '#c9b792', glass: '#4f4c46', rx: 60, ry: 3 }), side: THREE.DoubleSide }), 0, (BLDG_H + HALL_H) / 2, 0);
  add(G, new THREE.RingGeometry(46.8, R_OUT + 0.8, 160).rotateX(-Math.PI / 2), lam(0xd9cdb5, { side: THREE.DoubleSide }), 0, BLDG_H, 0);

  /* Gate portals */
  MAIN_GATES.forEach(no => {
    const a = GATE_ANG[no], p = polar(R_OUT, a), g = new THREE.Group(), th = Math.atan2(p.x, p.z), hw = 3.4;
    g.position.set(p.x, 0, p.z); g.rotation.y = th; G.add(g);
    const tan = { x: Math.cos(th), z: -Math.sin(th) }, out = { x: Math.sin(th), z: Math.cos(th) };
    for (const s of [-1, 1]) {
      add(g, new THREE.BoxGeometry(2.6, 15, 3.2), MAT.stoneDark, s * (hw + 1.3), 7.5, 1.0);
      sc.circles.push({ x: p.x + tan.x * s * (hw + 1.3) + out.x * 1.0, z: p.z + tan.z * s * (hw + 1.3) + out.z * 1.0, r: 1.7 });
      const mn = minaret(72, 1.05), q = polar(R_OUT + 7, a + s * 0.17); mn.position.set(q.x, 0, q.z); G.add(mn);
      sc.boxes.push({ x0: q.x - 2.3, x1: q.x + 2.3, z0: q.z - 2.3, z1: q.z + 2.3 });
      sc.cboxes.push({ x0: q.x - 2.3, x1: q.x + 2.3, z0: q.z - 2.3, z1: q.z + 2.3, h: 90 });
    }
    add(g, new THREE.BoxGeometry(hw * 2 + 5.2, 3.6, 3.2), MAT.stoneDark, 0, 13.2, 1.0);
    add(g, new THREE.BoxGeometry(hw * 2 + 0.2, 0.5, 0.4), MAT.gold, 0, OUT_LINTEL - 0.2, 2.2);
    const sgn = gateSign(no); sgn.position.set(0, 11.2, 3.4); g.add(sgn);
    const ms = signSprite('المطاف', 'Mataf', 4); const mp = polar(R_IN + 1.7, a); ms.position.set(mp.x, 8.7, mp.z); G.add(ms);
  });

  /* Passage to Masa */
  const PDIR = { x: Math.cos(A_SAFA), z: -Math.sin(A_SAFA) }, PPERP = { x: -PDIR.z, z: PDIR.x }, PB = polar(R_OUT, A_SAFA);
  function passageWall(side) {
    const b = { x: PB.x + PPERP.x * 3.3 * side, z: PB.z + PPERP.z * 3.3 * side }, tt = (MX0 - 1 - b.x) / PDIR.x;
    return { x1: b.x, z1: b.z, x2: b.x + PDIR.x * tt, z2: b.z + PDIR.z * tt, t: 0.8, h: HALL_H };
  }
  const PW = [passageWall(-1), passageWall(1)];
  const ZW0 = Math.min(PW[0].z2, PW[1].z2) + 0.4, ZW1 = Math.max(PW[0].z2, PW[1].z2) - 0.4;
  PW.forEach(w => {
    const dx = w.x2 - w.x1, dz = w.z2 - w.z1, L = Math.hypot(dx, dz);
    add(G, new THREE.BoxGeometry(L, HALL_H, w.t), MAT.stone, (w.x1 + w.x2) / 2, HALL_H / 2, (w.z1 + w.z2) / 2).rotation.y = -Math.atan2(dz, dx);
    sc.segs.push(w);
  });

  /* Masa */
  const masaWall = windowWall({ base: '#ece4d4', trim: '#c9b48a', glass: '#b9ad94' });
  function mbox(x0, x1, z0, z1, h, y0 = 0, collide = true) {
    const len = Math.max(x1 - x0, z1 - z0), tx = masaWall.clone(); tx.needsUpdate = true;
    tx.repeat.set(Math.max(1, Math.round(len / 7)), Math.max(1, Math.round((h - y0) / 6)));
    add(G, new THREE.BoxGeometry(x1 - x0, h - y0, z1 - z0), lam(0xffffff, { map: tx }), (x0 + x1) / 2, (h + y0) / 2, (z0 + z1) / 2);
    if (collide) sc.boxes.push({ x0, x1, z0, z1 }); sc.cboxes.push({ x0, x1, z0, z1, h, y0 });
  }
  mbox(61.6, MX0, -66, ZW0, 18); mbox(61.6, MX0, ZW1, 58, 18); mbox(61.6, MX0, ZW0, ZW1, 18, 9, false);
  mbox(MX1, 78.4, -66, 58, 18); mbox(61.6, 78.4, 57, 58, 18); mbox(61.6, 78.4, -66, -65, 18);
  const fTex = ctex(256, 256, (g, w, h) => drawMarble(g, w, h, 13, '#f3f3f0', 'rgba(140,150,155,.3)'), 4, 30);
  add(G, new THREE.PlaneGeometry(MX1 - MX0, 122).rotateX(-Math.PI / 2), lam(0xe6e6e2, { map: fTex }), 70, 0.03, -4);
  add(G, new THREE.PlaneGeometry(MX1 - MX0, 122).rotateX(Math.PI / 2), lam(0xf3ecdf), 70, 11.3, -4);
  /* Green lights */
  { const lg = new THREE.BoxGeometry(2.2, 0.12, 3.2), white = [], green = [];
    for (let z = -62; z <= 54; z += 5) for (const x of [65.5, 70, 74.5]) ((z > GZ0 && z < GZ1) ? green : white).push([x, z]);
    const mkL = (list, mat) => { const im = new THREE.InstancedMesh(lg, mat, list.length); list.forEach(([x, z], i) => { mm.makeTranslation(x, 11.2, z); im.setMatrixAt(i, mm); }); G.add(im); };
    mkL(white, new THREE.MeshBasicMaterial({ color: 0xfff7e6 })); mkL(green, new THREE.MeshBasicMaterial({ color: 0x2fe07c })); }
  /* Safa/Marwa rocks */
  function rock(z, sx, sy, sz, seed) {
    const geo = new THREE.IcosahedronGeometry(1, 2), pa = geo.attributes.position, r = rng(seed), v = new THREE.Vector3();
    for (let i = 0; i < pa.count; i++) { v.fromBufferAttribute(pa, i); v.multiplyScalar(0.78 + r() * 0.4); pa.setXYZ(i, v.x, v.y, v.z); }
    geo.computeVertexNormals();
    add(G, geo, new THREE.MeshPhongMaterial({ color: 0x9c846a, flatShading: true, shininess: 5 }), 70, 0, z).scale.set(sx, sy, sz);
  }
  rock(51.5, 6.5, 3.6, 5.5, 4); rock(-59.5, 6, 3, 5, 8);
  /* Masa signs */
  [[signSprite('الصفا', 'Safa', 6), 70, 8.6, 43.5],
   [signSprite('المروة', 'Marwa', 6), 70, 8.6, -51.5],
   [signSprite('القبلة', 'Qibla', 3.4), 63.4, 5.2, 40],
   [signSprite('القبلة', 'Qibla', 3.4), 63.4, 5.2, -48],
   [signSprite('باب المروة', 'Al-Marwah Gate', 6), 80, 9, -42]]
    .forEach(([s, x, y, z]) => { s.position.set(x, y, z); G.add(s); });

  /* City buildings */
  const hTex = [hotelTex('#d8cdb8', '#7c7466'), hotelTex('#c9c2b5', '#5e6670'), hotelTex('#e3dccd', '#8a7d68')];
  const glassTex = ctex(128, 256, (g, w, h) => { g.fillStyle = '#9fb3bf'; g.fillRect(0, 0, w, h); g.fillStyle = '#6f8796'; for (let y = 6; y < h; y += 16) g.fillRect(0, y, w, 7); }, 2, 6);
  const glassM = lam(0xffffff, { map: glassTex });
  const hr = rng(21);
  const bld = (x0, x1, z0, z1, h, ti, mat) => {
    add(G, new THREE.BoxGeometry(x1 - x0, h, z1 - z0), mat || lam(0xffffff, { map: hTex[ti % 3] }), (x0 + x1) / 2, h / 2, (z0 + z1) / 2);
    sc.cboxes.push({ x0, x1, z0, z1, h });
  };
  /* Clock Tower */
  bld(-16, 44, 112, 192, 30, 0); bld(-12, 8, 120, 140, 70, 1); bld(24, 42, 165, 188, 80, 2); bld(-14, 4, 160, 186, 64, 2); bld(26, 42, 118, 138, 58, 1);
  const clockTex = ctex(256, 256, (g, w, h) => {
    g.fillStyle = '#e9e2d0'; g.fillRect(0, 0, w, h);
    g.beginPath(); g.arc(w / 2, h / 2, 116, 0, TAU); g.fillStyle = '#f8f6ef'; g.fill(); g.lineWidth = 12; g.strokeStyle = '#1f6a47'; g.stroke();
    g.strokeStyle = '#16130e'; g.lineWidth = 7; g.beginPath(); g.moveTo(w / 2, h / 2); g.lineTo(w / 2 + 40, h / 2 - 48); g.moveTo(w / 2, h / 2); g.lineTo(w / 2 - 8, h / 2 - 86); g.stroke();
  });
  const ct = new THREE.Group(); ct.position.set(14, 30, 152); G.add(ct);
  add(ct, new THREE.BoxGeometry(26, 100, 26), lam(0xffffff, { map: hTex[0] }), 0, 50, 0);
  add(ct, new THREE.BoxGeometry(20, 26, 20), lam(0xe9e2d0), 0, 113, 0);
  const cf = lam(0xffffff, { map: clockTex });
  for (let i = 0; i < 4; i++) add(ct, new THREE.PlaneGeometry(15, 15), cf, 10.1 * Math.sin(i * Math.PI / 2), 113, 10.1 * Math.cos(i * Math.PI / 2)).rotation.y = i * Math.PI / 2;
  add(ct, new THREE.CylinderGeometry(6, 8.5, 10, 8), lam(0xe9e2d0), 0, 131, 0);
  add(ct, new THREE.ConeGeometry(3, 32, 8), MAT.gold, 0, 152, 0);
  /* Jabal Omar */
  for (let z = -44; z < 44; z += 22) { bld(-200, -100 + hr() * 10, z, z + 18, 60 + hr() * 55, 0, hr() < .5 ? glassM : null); }
  /* Jarwal */
  const jA = A_G62, jd = { x: Math.cos(jA), z: -Math.sin(jA) }, jn = { x: -jd.z, z: jd.x };
  for (let r2 = 104; r2 < 206; r2 += 21) for (const s2 of [-1, 1]) {
    const off = 17 + hr() * 5, c2 = { x: jd.x * r2 + jn.x * off * s2, z: jd.z * r2 + jn.z * off * s2 }, h2 = 26 + hr() * 30;
    add(G, new THREE.BoxGeometry(16, h2, 15), lam(0xffffff, { map: hTex[Math.floor(hr() * 3)] }), c2.x, h2 / 2, c2.z).rotation.y = Math.atan2(jd.x, jd.z);
  }
  /* Expansion */
  add(G, new THREE.BoxGeometry(70, 32, 50), lam(0xffffff, { map: windowWall({ base: '#ebe2cf', trim: '#c7b38c', glass: '#58544b', rx: 10, ry: 3 }) }), -45, 16, -125);
  sc.cboxes.push({ x0: -80, x1: -10, z0: -150, z1: -100, h: 32 });
  for (const x of [-62, -28]) { const mn = minaret(80, 1.1); mn.position.set(x, 0, -97); G.add(mn); }
  /* Jabal Abu Qubais */
  { const geo2 = new THREE.IcosahedronGeometry(1, 2), pa2 = geo2.attributes.position, r2 = rng(55), v2 = new THREE.Vector3();
    for (let i = 0; i < pa2.count; i++) { v2.fromBufferAttribute(pa2, i); v2.multiplyScalar(0.8 + r2() * 0.35); if (v2.y < 0) v2.y *= 0.2; pa2.setXYZ(i, v2.x, v2.y, v2.z); }
    geo2.computeVertexNormals();
    add(G, geo2, new THREE.MeshPhongMaterial({ color: 0x8f7a62, flatShading: true, shininess: 4 }), 150, 0, 8).scale.set(46, 42, 62);
    add(G, new THREE.BoxGeometry(46, 14, 30), lam(0xffffff, { map: windowWall({ base: '#f1ece1', trim: '#c9b48a', glass: '#6b6656', rx: 6, ry: 1 }) }), 140, 40, 4); }
  /* Random skyline */
  const blocked = (x, z, w) => {
    for (const r of ROADS) if (segDist(x, z, r) < r.hw + w * 0.75 + 6) return true;
    if (x > -140 && x < 95 && z > 66 && z < 240) return true;
    if (x < -84 && Math.abs(z) < 60) return true;
    if (x > 92 && Math.abs(z - 8) < 80) return true;
    if (x > -88 && x < 100 && z < -84) return true;
    return Math.hypot(x, z) < 108;
  };
  for (let i = 0; i < 70; i++) {
    const a = hr() * TAU, d = 112 + hr() * 90, p = polar(d, a), w = 14 + hr() * 14;
    if (blocked(p.x, p.z, w)) continue;
    bld(p.x - w / 2, p.x + w / 2, p.z - w / 2, p.z + w / 2, 24 + hr() * 50, i);
  }
  /* Street lamps */
  { const lp = [];
    for (const r of ROADS) { const dx = r.x2 - r.x1, dz = r.z2 - r.z1, L = Math.hypot(dx, dz), nx = -dz / L, nz = dx / L; for (let d = 8; d < L - 10; d += 14) for (const s2 of [-1, 1]) lp.push([r.x1 + dx * d / L + nx * 6.4 * s2, r.z1 + dz * d / L + nz * 6.4 * s2]); }
    const pg = new THREE.CylinderGeometry(0.12, 0.16, 6, 6); pg.translate(0, 3, 0);
    const hg = new THREE.SphereGeometry(0.35, 8, 6); hg.translate(0, 6.2, 0);
    const pi = new THREE.InstancedMesh(pg, lam(0x6b6458), lp.length), hi = new THREE.InstancedMesh(hg, new THREE.MeshBasicMaterial({ color: 0xfff1cc }), lp.length);
    lp.forEach(([x, z], i) => { mm.makeTranslation(x, 0, z); pi.setMatrixAt(i, mm); hi.setMatrixAt(i, mm); }); G.add(pi, hi); }

  /* Info spots */
  const gI = no => { const q = polar(R_OUT + 4, GATE_ANG[no]); return { key: 'gate' + no, x: q.x, z: q.z, r: 6 }; };
  const bsI = kw(-KW / 2 - 1.2, KD / 2 + 1.2), mzI = kw(-4.4, KD / 2 + 1.6), drI = kw(-2.6, KD / 2 + 2.6);
  const hjI = kw(KW / 2 + HIJR_A + 1.6, 0), ymI = kw(-KW / 2 - 1.3, -KD / 2 - 1.3), sw = polar(37.5, A_SAFA);
  sc.infos.push(
    { key: 'blackStone', x: bsI.x, z: bsI.z, r: 3.6 }, { key: 'multazam', x: mzI.x, z: mzI.z, r: 2.0 },
    { key: 'door', x: drI.x, z: drI.z, r: 2.4 }, { key: 'hijr', x: hjI.x, z: hjI.z, r: 4.5 },
    { key: 'yamani', x: ymI.x, z: ymI.z, r: 3.8 }, { key: 'maqam', x: MAQAM_POS.x, z: MAQAM_POS.z, r: 4 },
    { key: 'zamzam', x: ZAMZAM_POS.x, z: ZAMZAM_POS.z, r: 4 }, { key: 'safaWay', x: sw.x, z: sw.z, r: 4 },
    gI(1), gI(45), gI(100), gI(62), gI(79),
    { key: 'clockTower', x: 14, z: 89, r: 9 },
    { key: 'jabalOmar', x: -130, z: 1, r: 14 },
    { key: 'safa', x: 70, z: 40.5, r: 5 }, { key: 'marwa', x: 70, z: -48.5, r: 5 },
    { key: 'green', x: 70, z: 16.5, r: 7.5 },
    { key: 'marwahGate', x: 75.6, z: -42, r: 2.6 }
  );

  /* Crowd */
  const NM = isTouch ? 280 : 460, NOUT = isTouch ? 110 : 180, NMASA = isTouch ? 130 : 210;
  const mat = makeCrowd(NM, 3, MALE_IHRAM); G.add(mat.bodies, mat.heads);
  mat.agents.forEach(a => { a.rad = 13 + 20 * Math.pow(a.r1, 1.3); a.ang = a.r2 * TAU; a.w = (0.9 + a.r3 * 0.6) / a.rad; });
  const outC = makeCrowd(NOUT, 23, MALE_IHRAM); G.add(outC.bodies, outC.heads);
  const PATHS = ['A', 'B', 'C', 'D', 'E'].map(k => {
    const R = { A: { start: { x: -30, z: 205 }, out: [{ x: -30, z: 95 }, { x: -8, z: 74 }, polar(R_OUT + 3, A_G1)] }, B: { start: { x: 55, z: 205 }, out: [{ x: 55, z: 88 }, { x: 14, z: 72 }, polar(R_OUT + 3, A_G1)] }, C: { start: { x: -203, z: 1 }, out: [{ x: -92, z: 1 }, polar(R_OUT + 3, A_G79)] }, D: { start: polar(203, A_G62), out: [polar(92, A_G62), polar(R_OUT + 3, A_G62)] }, E: { start: { x: 61, z: -214 }, out: [{ x: 60, z: -82 }, { x: 30, z: -70 }, polar(R_OUT + 3, A_G45)] } }[k];
    return [R.start, ...R.out.slice(0, -1), polar(R_OUT + 4, GATE_ANG[{ A: 1, B: 1, C: 79, D: 62, E: 45 }[k]])];
  });
  const rr = rng(91);
  const wanderPt = a0 => { for (let k = 0; k < 10; k++) { const a = a0 + (rr() - .5) * 0.9, p = polar(64 + rr() * 28, a); if (!(p.x > 57 && p.z > -70 && p.z < 62)) return p; } return polar(80, a0 + Math.PI); };
  outC.agents.forEach((a, i) => {
    a.v = 0.95 + a.r3 * 0.5;
    if (i < NOUT * 0.6) {
      const base = PATHS[i % PATHS.length], off = (a.r1 - .5) * 9;
      const d0 = base[1], st = base[0], L = Math.hypot(d0.x - st.x, d0.z - st.z) || 1;
      const nx = -(d0.z - st.z) / L, nz = (d0.x - st.x) / L;
      const pts = base.map((p, j) => ({ x: p.x + nx * off * (j < 2 ? 1 : 0.4), z: p.z + nz * off * (j < 2 ? 1 : 0.4) }));
      const j = Math.floor(rr() * (pts.length - 1)), f = rr();
      Object.assign(a, { kind: 'p', pts, i: j + 1, x: pts[j].x * (1 - f) + pts[j + 1].x * f, z: pts[j].z * (1 - f) + pts[j + 1].z * f });
    } else { const p = wanderPt(a.r2 * TAU); Object.assign(a, { kind: 'w', x: p.x, z: p.z, t: wanderPt(a.r2 * TAU) }); }
  });
  const masa = makeCrowd(NMASA, 17, MALE_IHRAM); G.add(masa.bodies, masa.heads);
  masa.agents.forEach(a => { a.dir = a.r1 < .5 ? -1 : 1; a.z = MZN + 2 + a.r2 * (MZS - MZN - 4); a.x = a.dir < 0 ? 63.4 + a.r3 * 6 : 70.6 + a.r3 * 6; a.v = 1.0 + a.r3 * 0.5; });
  const lerp = (a, b, k) => a + (b - a) * k;
  sc.update = (dt, T) => {
    const _o = { x: 0, z: 0 };
    const dodge = (x, z) => { const dx = x - window._px, dz = z - window._pz, d2 = dx * dx + dz * dz; if (d2 < 0.8 && d2 > 1e-6) { const d = Math.sqrt(d2), k = (0.9 - d) / d; x += dx * k; z += dz * k; } return { x, z }; };
    for (let i = 0; i < NM; i++) {
      const a = mat.agents[i]; a.ang += a.w * dt;
      let { x, z } = dodge(kw(0, 0).x + a.rad * Math.cos(a.ang), kw(0, 0).z - a.rad * Math.sin(a.ang));
      mat.set(i, x, z, Math.atan2(-Math.sin(a.ang), -Math.cos(a.ang)), Math.abs(Math.sin(T * 5.5 + a.ph)) * 0.05);
    }
    mat.commit();
    outC.agents.forEach((a, i) => {
      const tgt = a.kind === 'p' ? a.pts[a.i] : a.t, dx = tgt.x - a.x, dz = tgt.z - a.z, d = Math.hypot(dx, dz);
      if (d < 0.8) { if (a.kind === 'p') { a.i++; if (a.i >= a.pts.length) { a.i = 1; a.x = a.pts[0].x; a.z = a.pts[0].z; } } else a.t = wanderPt(angleOf(a.x, a.z)); return; }
      a.x += dx / d * a.v * dt; a.z += dz / d * a.v * dt;
      const { x, z } = dodge(a.x, a.z); outC.set(i, x, z, Math.atan2(dx, dz), Math.abs(Math.sin(T * 5.5 + a.ph)) * 0.05);
    });
    outC.commit();
    masa.agents.forEach((a, i) => {
      const fast = !a.female && a.z > GZ0 && a.z < GZ1;
      a.z += a.dir * a.v * (fast ? 1.9 : 1) * dt;
      if (a.z < MZN + 1.5 || a.z > MZS - 1.5) { a.dir *= -1; a.x += a.dir < 0 ? -7.2 : 7.2; a.x = clamp(a.x, 63.4, 76.6); a.z = clamp(a.z, MZN + 1.5, MZS - 1.5); }
      const { x, z } = dodge(a.x, a.z);
      masa.set(i, x, z, a.dir > 0 ? 0 : Math.PI, Math.abs(Math.sin(T * (fast ? 9 : 5.5) + a.ph)) * 0.05);
    });
    masa.commit();
  };

  /* Collision */
  const _q = { x: 0, z: 0 };
  sc.collide = (p, prev) => {
    const l = kl(p.x, p.z);
    if (l.x > KW / 2) {
      const A = HIJR_A + 0.45 + 0.45, B = HIJR_B + 0.45 + 0.45, ex = (l.x - KW / 2) / A, ez = l.z / B, k = Math.hypot(ex, ez);
      if (k < 1 && k > 1e-6) { l.x = KW / 2 + ex / k * A; l.z = ez / k * B; }
    }
    pushBox(l, { x0: -KW / 2 - 0.5, x1: KW / 2 + 0.5, z0: -KD / 2 - 0.5, z1: KD / 2 + 0.5 }, 0.45);
    const w2 = kw(l.x, l.z); p.x = w2.x; p.z = w2.z;
    pushRing(p, R_IN, 1.2); pushRing(p, R_OUT, 1.6);
    for (const b of sc.boxes) pushBox(p, b, 0.45);
    for (const s of sc.segs) pushSeg(p, s, 0.45);
    for (const c of sc.circles) pushCircle(p, c, 0.45);
    if (!walkable(p.x, p.z)) { if (walkable(p.x, prev.z)) p.z = prev.z; else if (walkable(prev.x, p.z)) p.x = prev.x; else { p.x = prev.x; p.z = prev.z; } }
  };
  sc.camOK = p => {
    if (p.y < 0.3) return false;
    const r2 = Math.hypot(p.x, p.z), a = angleOf(p.x, p.z);
    if (p.y < KB + KH + 0.5) { const l = kl(p.x, p.z); if (Math.abs(l.x) < KW / 2 + 0.6 && Math.abs(l.z) < KD / 2 + 0.6) return false; }
    if (r2 > 36.8 && r2 < 44.8 && p.y > 6.7 && p.y < 7.8) return false;
    if (r2 > 43.2 && r2 < 61 && p.y > 9.5 && p.y < 10.8) return false;
    if (r2 > 46.4 && r2 < 61 && p.y >= 10.8 && p.y < BLDG_H + 0.6) return false;
    if (p.y < HALL_H + 0.3 && Math.abs(r2 - R_IN) < 0.95 && (!openingAt(R_IN, a) || p.y > IN_LINTEL)) return false;
    if (p.y < BLDG_H + 0.6 && Math.abs(r2 - R_OUT) < 1.15 && (!openingAt(R_OUT, a) || p.y > OUT_LINTEL)) return false;
    if (p.x > MX0 && p.x < MX1 && p.z > -65 && p.z < 57 && p.y > 10.9 && p.y < 18.5) return false;
    for (const b of sc.cboxes) if (p.y < b.h && p.y >= (b.y0 || 0) && p.x > b.x0 - .3 && p.x < b.x1 + .3 && p.z > b.z0 - .3 && p.z < b.z1 + .3) return false;
    for (const s of sc.segs) { if (p.y >= s.h) continue; _q.x = p.x; _q.z = p.z; pushSeg(_q, s, 0.3); if (_q.x !== p.x || _q.z !== p.z) return false; }
    return true;
  };
  return sc;
}