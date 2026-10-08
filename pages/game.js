'use strict';

/* ===== RENDERER ===== */
const isTouch = matchMedia('(pointer: coarse)').matches;
const canvas = document.getElementById('c');
const loadMsg = document.getElementById('loadMsg');
loadMsg.textContent = lang === 'bn' ? 'মসজিদুল হারাম প্রস্তুত হচ্ছে' : 'Preparing Masjid al-Haram';

if (!window.THREE) { loadMsg.textContent = lang === 'bn' ? '3D ইঞ্জিন লোড হয়নি। পেজটি আবার লোড করুন।' : 'The 3D engine did not load. Reload the page.'; throw new Error('Three.js not loaded'); }

let renderer;
try { renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'high-performance' }); }
catch (e) { loadMsg.textContent = 'WebGL not supported.'; throw e; }

renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isTouch ? 1.5 : 1.75));
const world = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1400);
world.add(new THREE.HemisphereLight(0xfff6e6, 0xb5a78e, 0.7));
const sun = new THREE.DirectionalLight(0xffffff, 0.62);
sun.position.set(-60, 120, 45); world.add(sun);

function resize() { const w = window.innerWidth, h = window.innerHeight; renderer.setSize(w, h, false); camera.aspect = w / h; camera.updateProjectionMatrix(); }
window.addEventListener('resize', resize);

/* ===== GAME-ONLY CONSTANTS ===== */
const MZ_SAFA = 38, MZ_MARWA = -46;
const SAFA_PT = { x: 70, z: 41 }, MARWA_PT = { x: 70, z: -49 };
const PRAY_SPOT = kw(-1, KD / 2 + 13.6);
const ZAMZAM_WP = polar(28.4, 0.30);

/* ===== GAME-ONLY HELPERS ===== */
const lerp = (a, b, k) => a + (b - a) * k;
// const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
// const wrapPi = a => { while (a > Math.PI) a -= TAU; while (a < -Math.PI) a += TAU; return a; };
const lerpAngle = (a, b, k) => a + wrapPi(b - a) * k;

function t(k, vars) {
  const STR_GAME = {
    en: {
      brand: 'Umrah Safar', now: 'Now', move: 'Move', auto: 'Auto-walk', run: 'Walk briskly', help: 'Controls',
      cont: 'Continue', close: 'Close', learn: 'Tap to read about it', nearby: 'You are near',
      c_tawaf: 'Tawaf round', c_sai: "Sa'i lap", place_safa: 'Safa', place_marwa: 'Marwa',
      gateNo: 'Gate {n}', yourGate: 'Your gate', mapLabel: 'Map', steps_ihram: 'Ihram',
      steps_tawaf: 'Tawaf', steps_maqam: 'Maqam', steps_zamzam: 'Zamzam',
      steps_sai: "Sa'i", steps_halq: 'Halq', steps_gate: 'Gate', stepsLabel: 'Steps of Umrah',
      obj_tawaf_start: 'Walk to the Black Stone corner. Follow the gold arrow.',
      obj_on_line: "You're on the starting line. Face the Black Stone and begin Tawaf.",
      obj_tawaf: 'Round {n} of 7. Keep the Kaaba on your left.',
      obj_maqam: 'Go behind Maqam Ibrahim and pray 2 rakats.',
      obj_zamzam: 'Go to the Zamzam station and drink.',
      obj_to_sai: "Walk to Bab as-Safa to begin Sa'i.",
      obj_sai_safa: "You're on Safa. Face the Kaaba and begin Sa'i.",
      obj_sai: 'Lap {n} of 7. Walk to {dest}.',
      obj_free: 'Walk freely. Use the steps bar to practise any part again.',
      obj_plaza: 'Cross the plaza to {gate}, Gate {n}.', obj_gate: "You're at {gate}, Gate {n}. Enter with your right foot.",
      obj_inside: 'Follow the Mataf signs to the Kaaba.',
      obj_to_safa: 'Follow the Safa signs to the Masaa.', obj_safa_turn: "You're in the Masaa. Turn right and walk up to Safa.",
      obj_road_A: 'Walk north along Ibrahim Al Khalil Road.', obj_road_B: 'Walk north along Ajyad Street.',
      obj_road_C: 'Walk east through Jabal Omar.', obj_road_D: 'Walk south-east from Jarwal.',
      obj_road_E: 'Walk south from Masjid al-Jinn.',
      act_begin_tawaf: 'Begin Tawaf', act_istilam: 'Make Istilam', act_pray: 'Pray 2 rakats',
      act_drink: 'Drink Zamzam', act_gate: "Go to Sa'i", act_safa: 'Recite on Safa',
      act_dhikr_safa: 'Dhikr on Safa', act_dhikr_marwa: 'Dhikr on Marwa', act_enter: 'Enter with right foot',
      t_round: 'Round {n} done. Make Istilam toward the Black Stone.',
      t_wrong: 'Wrong way. Tawaf goes anticlockwise, with the Kaaba on your left.',
      t_tawaf_done: 'Tawaf complete. Alhamdulillah.',
      t_idtiba: 'Idtiba: keep your right shoulder uncovered for all 7 rounds.',
      t_cover: 'Cover your right shoulder again before you pray.',
      t_lap: 'Lap {n} done. You reached {dest}.',
      t_sai_done: "Sa'i complete: 7 laps, finishing on Marwa.",
      t_prayed: 'Prayer complete.', t_firstsight: 'Your first sight of the Kaaba. Pause and make heartfelt dua.',
      hint_raml: 'Raml: walk briskly with short steps in rounds 1 to 3.',
      hint_green_m: 'Green lights: men jog lightly between them.',
      hint_green_f: 'Green lights: women keep walking at a normal pace.',
      hint_yamani: 'Between Rukn Yamani and the Black Stone, recite',
      hint_green_dua: 'Between the green lights, many recite',
      pr_rak1: 'First rakat', pr_rak2: 'Second rakat', pr_qiyam: 'Standing (Qiyam)', pr_ruku: 'Bowing (Ruku)',
      pr_sujood: 'Prostration (Sujood)', pr_jalsa: 'Sitting between sujood', pr_tash: 'Tashahhud', pr_salam: 'Salam',
      c_ih1_t: 'Before the Miqat: put on Ihram',
      c_ih1_m: 'Clip your nails, groom, and take a ghusl. Wear two unstitched white sheets and open-top sandals. Pray 2 rakats for Ihram if not a disliked time.',
      c_ih1_f: 'Clip your nails, groom, and take a ghusl. Wear your normal modest clothes, keeping your face and hands uncovered. Pray 2 rakats for Ihram if not a disliked time.',
      c_flight: 'Flying from Dhaka? Put on Ihram before boarding or before the Miqat. Do not wait until Jeddah.',
      c_ih2_t: 'At the Miqat: make your intention', c_ih2_a: 'Make the intention for Umrah in your heart and say:',
      c_ih2_b: 'Then begin the Talbiyah, men aloud and women quietly. Keep reciting until you start Tawaf.',
      c_talb_btn: 'Recite Talbiyah ({n}/3)',
      c_ih3_t: 'While in Ihram, avoid',
      c_ih3_list: ['Perfume, and scented soap or oil', 'Cutting hair or nails', 'Marital relations', 'Arguing, fighting and bad language', 'Hunting'],
      c_ih3_m: 'Stitched clothing, and covering your head', c_ih3_f: 'Niqab and gloves',
      c_wudu: 'You need wudu for Tawaf, so renew it before you enter the mosque.',
      c_arrive: 'Arrive in Makkah', c_enter_k: 'Masjid al-Haram, Makkah', c_enter_t: 'Entering Masjid al-Haram',
      c_enter_a: 'Step in with your right foot first and recite:',
      c_enter_b: 'When you first see the Kaaba, pause and make heartfelt dua.',
      c_step_in: 'Step inside', c_tb_t: 'Begin Tawaf',
      c_tb_a: 'Make the intention for Tawaf in your heart and stop the Talbiyah. Face the Black Stone, turn your palms toward it, and say:',
      c_tb_b: 'Kiss or touch the stone only if you can reach it without pushing. Now turn so the Kaaba is on your left and walk.',
      c_tb_m: 'Men walk briskly (Raml) in the first 3 rounds.', c_tb_go: 'Start walking',
      c_mq_t: 'Two rakats behind Maqam Ibrahim',
      c_mq_a: 'Recite this verse, then pray 2 rakats behind Maqam Ibrahim. If crowded, anywhere in the mosque is fine.',
      c_mq_b: 'Sunnah: Surah al-Kafirun in the first rakat, Surah al-Ikhlas in the second.',
      c_start_prayer: 'Start the prayer', c_zz_t: 'Drink Zamzam',
      c_zz_a: 'Face the qibla, say Bismillah, and drink in three sips. A good time to ask Allah for what you need:',
      c_done: 'Done', c_sf_k: "The Mas'a", c_sf_t: 'On Safa',
      c_sf_a: "At the start of Sa'i only, recite:", c_sf_b: 'Face the Kaaba, raise your hands, and say:',
      c_sf_c: "Then walk to Marwa. Safa to Marwa is one lap, so after 7 laps you finish on Marwa.",
      c_sf_go: "Begin Sa'i", c_dk_t: 'On {dest}',
      c_dk_a: 'Face the Kaaba, raise your hands, and repeat this dhikr along with your own duas.',
      c_hq_t: 'Halq or Taqsir: leaving Ihram',
      c_hq_m: 'To finish Umrah, shave your head (Halq), which is more virtuous, or shorten the hair from all over your head (Taqsir).',
      c_hq_f: "To finish Umrah, cut about a fingertip's length (~2 cm) from the ends of your hair.",
      c_hq_shave: 'Halq: shave', c_hq_trim: 'Taqsir: trim', c_hq_cut: 'Trim my hair',
      c_hq_exit: "After Sa'i, most pilgrims leave from the Marwa end through Al-Marwah Gate.",
      c_ud_t: 'Alhamdulillah, your Umrah is complete',
      c_ud_a: 'The restrictions of Ihram are now lifted. May Allah accept it from you.',
      c_ud_b: 'Use Walk freely to explore again, or the steps bar to repeat any part.',
      c_restart: 'Start again', c_free: 'Walk freely',
      c_rt_k: 'Arriving in Makkah', c_rt_t: 'Where are you walking from?',
      c_rt_a: 'These are the main walking routes into Masjid al-Haram. Distances shown are real walking distances.',
      c_rt_note: 'On the day, gates can close when the mosque is full. Follow the green open signs and the guards.',
      c_rt_go: 'Start walking',
      rtA_t: 'Ibrahim Al Khalil Road (Misfalah)', rtA_n: 'South-west. ~0.8 km to Gate 1, 10-15 min walk.',
      rtB_t: 'Ajyad Street', rtB_n: 'South-east. ~0.7 km to Gate 1, 8-12 min walk.',
      rtC_t: 'Jabal Omar', rtC_n: 'West. ~0.5 km to Gate 79, 5-10 min walk.',
      rtD_t: 'Jarwal', rtD_n: 'North-west. ~1 km to Gate 62.',
      rtE_t: 'Masjid al-Jinn (Al-Hajun)', rtE_n: 'North-east. ~0.9 km to Gate 45.',
      c_gate_note: 'This is Gate {n}, {gate}. Note the number - it helps you find your way back after Umrah.',
      c_land_tip: 'The Clock Tower can be seen from almost everywhere and always marks the south side.',
      c_way: 'Your way back: you came in by Gate {n}, {gate}.',
      view_1: 'Coming out of Gate 1, you face the Clock Tower.',
      view_79: 'Coming out of Gate 79, Jabal Omar towers are ahead and the Clock Tower is on your left.',
      view_62: 'Coming out of Gate 62, the road to Jarwal is straight ahead.',
      view_45: 'Coming out of Gate 45, the Clock Tower is behind you.',
      view_100: 'Coming out of Gate 100, Jarwal road is ahead left.',
      ctrlTitle: 'Controls',
      ctrlDesk: 'Move with W A S D or arrow keys. Hold Shift or Walk briskly for Raml. Press E for the gold action button. Drag to look around, scroll to zoom.',
      ctrlTouch: 'Put your left thumb on the left side to move. Drag on the right to look around, pinch to zoom.',
      ctrlAuto: 'Auto-walk takes you along the correct route.',
      performAs: 'Who is performing Umrah?', man: 'Man', woman: 'Woman',
      manNote: 'Includes Idtiba, Raml and jogging between the green lights',
      womanNote: 'Walks at a normal pace throughout',
      begin: 'Begin with Ihram', title: 'Umrah Safar Simulator',
      sub: 'Walk the real route from your hotel to the Kaaba, then perform every step of Umrah.',
      disclaimer: 'This is a learning aid. Follow your scholar or group guide on the details.',
      langLabel: 'Language', unit_m: 'm', unit_km: 'km',
      d_walked: 'Walked {d}', d_gate: 'Gate {n}: {d}', d_mataf: 'Mataf: {d}', d_of: '{d} of {t}',
      share_copied: 'Link copied!', offline_ready: 'App saved for offline use.',
    },
    bn: {
      brand: 'উমরাহ সফর', now: 'এখন', move: 'চলুন', auto: 'অটো-হাঁটা', run: 'দ্রুত হাঁটা', help: 'নিয়ন্ত্রণ',
      cont: 'এগিয়ে যান', close: 'বন্ধ করুন', learn: 'বিস্তারিত পড়তে চাপুন', nearby: 'আপনি এর কাছে আছেন',
      c_tawaf: 'তাওয়াফের চক্কর', c_sai: 'সাঈর চক্কর', place_safa: 'সাফা', place_marwa: 'মারওয়া',
      gateNo: 'গেট নম্বর {n}', yourGate: 'আপনার গেট', mapLabel: 'মানচিত্র', steps_ihram: 'ইহরাম',
      steps_tawaf: 'তাওয়াফ', steps_maqam: 'মাকাম', steps_zamzam: 'জমজম',
      steps_sai: 'সাঈ', steps_halq: 'হলক', steps_gate: 'গেট', stepsLabel: 'উমরাহর ধাপ',
      obj_tawaf_start: 'হাজরে আসওয়াদের কোণে যান। সোনালি তীর অনুসরণ করুন।',
      obj_on_line: 'আপনি শুরুর লাইনে আছেন। হাজরে আসওয়াদের দিকে মুখ করে তাওয়াফ শুরু করুন।',
      obj_tawaf: '৭ চক্করের মধ্যে {n} নম্বর। কাবাকে বাম পাশে রাখুন।',
      obj_maqam: 'মাকামে ইবরাহিমের পেছনে গিয়ে দুই রাকাত নামাজ পড়ুন।',
      obj_zamzam: 'জমজমের স্থানে গিয়ে পান করুন।',
      obj_to_sai: 'সাঈ শুরু করতে বাবুস সাফার দিকে হাঁটুন।',
      obj_sai_safa: 'আপনি সাফায় আছেন। কাবার দিকে মুখ করে সাঈ শুরু করুন।',
      obj_sai: '৭ চক্করের মধ্যে {n} নম্বর। {dest}র দিকে হাঁটুন।',
      obj_free: 'নিজের মতো ঘুরে দেখুন। যেকোনো অংশ আবার করতে ওপরের ধাপ ব্যবহার করুন।',
      obj_plaza: 'চত্বর পেরিয়ে {gate}-এ যান, গেট নম্বর {n}।',
      obj_gate: 'আপনি {gate}-এ আছেন, গেট নম্বর {n}। ডান পা দিয়ে প্রবেশ করুন।',
      obj_inside: '"মাতাফ" চিহ্ন ধরে কাবার দিকে যান।',
      obj_to_safa: '"সাফা" চিহ্ন ধরে মাসআর দিকে যান।', obj_safa_turn: 'আপনি মাসআয় পৌঁছেছেন। ডানে ঘুরে সাফার দিকে যান।',
      obj_road_A: 'ইবরাহিম খলিল রোড ধরে উত্তরে হাঁটুন।', obj_road_B: 'আজিয়াদ স্ট্রিট ধরে উত্তরে হাঁটুন।',
      obj_road_C: 'জাবালে ওমরের ভেতর দিয়ে পূর্বে হাঁটুন।', obj_road_D: 'জারওয়াল থেকে দক্ষিণ-পূর্বে হাঁটুন।',
      obj_road_E: 'মসজিদে জিন থেকে দক্ষিণে হাঁটুন।',
      act_begin_tawaf: 'তাওয়াফ শুরু করুন', act_istilam: 'ইস্তিলাম করুন', act_pray: 'দুই রাকাত নামাজ',
      act_drink: 'জমজম পান করুন', act_gate: 'সাঈতে যান', act_safa: 'সাফায় পড়ুন',
      act_dhikr_safa: 'সাফায় জিকির', act_dhikr_marwa: 'মারওয়ায় জিকির', act_enter: 'ডান পা দিয়ে প্রবেশ',
      t_round: '{n} নম্বর চক্কর শেষ। হাজরে আসওয়াদের দিকে ইস্তিলাম করুন।',
      t_wrong: 'ভুল দিক। তাওয়াফ ঘড়ির কাঁটার উল্টো দিকে, কাবা থাকবে বাম পাশে।',
      t_tawaf_done: 'তাওয়াফ সম্পন্ন। আলহামদুলিল্লাহ।',
      t_idtiba: 'ইযতিবা: সাত চক্করেই ডান কাঁধ খোলা রাখুন।',
      t_cover: 'নামাজের আগে ডান কাঁধ আবার ঢেকে নিন।',
      t_lap: '{n} নম্বর চক্কর শেষ। আপনি {dest}য় পৌঁছেছেন।',
      t_sai_done: 'সাঈ সম্পন্ন: ৭ চক্কর, মারওয়ায় শেষ।',
      t_prayed: 'নামাজ সম্পন্ন।', t_firstsight: 'এই প্রথম কাবা দেখছেন। একটু থেমে আন্তরিকভাবে দোয়া করুন।',
      hint_raml: 'রমল: ১ থেকে ৩ নম্বর চক্করে ছোট কদমে দ্রুত হাঁটুন।',
      hint_green_m: 'সবুজ বাতি: পুরুষরা এর মাঝে হালকা দৌড়ান।',
      hint_green_f: 'সবুজ বাতি: নারীরা স্বাভাবিক গতিতেই হাঁটবেন।',
      hint_yamani: 'রুকনে ইয়ামানি ও হাজরে আসওয়াদের মাঝে পড়ুন',
      hint_green_dua: 'সবুজ বাতির মাঝে অনেকে পড়েন',
      pr_rak1: 'প্রথম রাকাত', pr_rak2: 'দ্বিতীয় রাকাত', pr_qiyam: 'দাঁড়ানো (কিয়াম)', pr_ruku: 'রুকু',
      pr_sujood: 'সিজদা', pr_jalsa: 'দুই সিজদার মাঝে বসা', pr_tash: 'তাশাহহুদ', pr_salam: 'সালাম',
      c_ih1_t: 'মিকাতের আগে: ইহরাম পরুন',
      c_ih1_m: 'নখ কাটুন, পরিচ্ছন্ন হয়ে গোসল করুন। দুটি সেলাইবিহীন সাদা চাদর পরুন। মাকরুহ সময় না হলে ইহরামের দুই রাকাত নামাজ পড়ুন।',
      c_ih1_f: 'নখ কাটুন, পরিচ্ছন্ন হয়ে গোসল করুন। যেকোনো রঙের শালীন পোশাক পরুন, মুখ ও হাত খোলা রাখুন।',
      c_flight: 'ঢাকা থেকে বিমানে যাচ্ছেন? বিমানে ওঠার আগেই ইহরাম পরে নিন। জেদ্দা পৌঁছানো পর্যন্ত অপেক্ষা করবেন না।',
      c_ih2_t: 'মিকাতে: নিয়ত করুন', c_ih2_a: 'মনে মনে উমরাহর নিয়ত করুন এবং বলুন:',
      c_ih2_b: 'তারপর তালবিয়া পড়া শুরু করুন; পুরুষরা উচ্চস্বরে, নারীরা নিচু স্বরে।',
      c_talb_btn: 'তালবিয়া পড়ুন ({n}/৩)',
      c_ih3_t: 'ইহরাম অবস্থায় যা থেকে বিরত থাকবেন',
      c_ih3_list: ['সুগন্ধি, সুগন্ধযুক্ত সাবান বা তেল', 'চুল বা নখ কাটা', 'স্বামী-স্ত্রীর মিলন', 'ঝগড়া-বিবাদ ও খারাপ কথা', 'শিকার করা'],
      c_ih3_m: 'সেলাই করা পোশাক পরা এবং মাথা ঢাকা', c_ih3_f: 'নিকাব ও হাতমোজা পরা',
      c_wudu: 'তাওয়াফের জন্য অজু লাগবে, তাই মসজিদে প্রবেশের আগে অজু করে নিন।',
      c_arrive: 'মক্কায় পৌঁছান', c_enter_k: 'মসজিদুল হারাম, মক্কা', c_enter_t: 'মসজিদুল হারামে প্রবেশ',
      c_enter_a: 'ডান পা দিয়ে প্রবেশ করুন এবং পড়ুন:',
      c_enter_b: 'প্রথমবার কাবা দেখে একটু থামুন এবং আন্তরিকভাবে দোয়া করুন।',
      c_step_in: 'ভেতরে প্রবেশ করুন', c_tb_t: 'তাওয়াফ শুরু',
      c_tb_a: 'মনে মনে তাওয়াফের নিয়ত করুন এবং তালবিয়া বন্ধ করুন। হাজরে আসওয়াদের দিকে মুখ করে বলুন:',
      c_tb_b: 'কাউকে ধাক্কা না দিয়ে পৌঁছানো গেলেই কেবল পাথরে চুমু দিন বা স্পর্শ করুন।',
      c_tb_m: 'পুরুষরা প্রথম ৩ চক্করে দ্রুত হাঁটবেন (রমল)।', c_tb_go: 'হাঁটা শুরু করুন',
      c_mq_t: 'মাকামে ইবরাহিমের পেছনে দুই রাকাত',
      c_mq_a: 'এই আয়াতটি পড়ুন, তারপর মাকামে ইবরাহিমের পেছনে দুই রাকাত নামাজ পড়ুন।',
      c_mq_b: 'প্রথম রাকাতে সূরা কাফিরুন এবং দ্বিতীয় রাকাতে সূরা ইখলাস পড়া সুন্নত।',
      c_start_prayer: 'নামাজ শুরু করুন', c_zz_t: 'জমজম পান',
      c_zz_a: 'কিবলামুখী হয়ে বিসমিল্লাহ বলে তিন শ্বাসে পান করুন।',
      c_done: 'সম্পন্ন', c_sf_k: 'মাসআ', c_sf_t: 'সাফায়',
      c_sf_a: 'শুধু সাঈর শুরুতে পড়ুন:', c_sf_b: 'কাবার দিকে মুখ করে দোয়ার মতো হাত তুলে বলুন:',
      c_sf_c: 'তারপর মারওয়ার দিকে হাঁটুন। সাফা থেকে মারওয়া এক চক্কর, তাই ৭ চক্কর শেষে আপনি মারওয়ায় থাকবেন।',
      c_sf_go: 'সাঈ শুরু করুন', c_dk_t: '{dest}য়',
      c_dk_a: 'কাবার দিকে মুখ করে হাত তুলুন এবং এই জিকিরের সঙ্গে নিজের দোয়াগুলো করুন।',
      c_hq_t: 'হলক বা কসর: ইহরাম থেকে মুক্ত হওয়া',
      c_hq_m: 'উমরাহ শেষ করতে মাথা মুণ্ডন করুন (হলক) অথবা পুরো মাথা থেকে চুল ছোট করুন (কসর)।',
      c_hq_f: 'উমরাহ শেষ করতে চুলের আগা থেকে আঙুলের এক কর পরিমাণ (~২ সেমি) কেটে ফেলুন।',
      c_hq_shave: 'হলক: মুণ্ডন', c_hq_trim: 'কসর: ছোট করা', c_hq_cut: 'চুল ছোট করুন',
      c_hq_exit: 'সাঈর পর বেশিরভাগ মানুষ মারওয়ার দিক থেকে বাবুল মারওয়া দিয়ে বের হন।',
      c_ud_t: 'আলহামদুলিল্লাহ, আপনার উমরাহ সম্পন্ন হয়েছে',
      c_ud_a: 'এখন ইহরামের সব বিধিনিষেধ শেষ। আল্লাহ আপনার উমরাহ কবুল করুন।',
      c_ud_b: 'গেট ও পথগুলো আবার ঘুরে দেখতে "নিজের মতো ঘুরে দেখুন" বেছে নিন।',
      c_restart: 'আবার শুরু করুন', c_free: 'নিজের মতো ঘুরে দেখুন',
      c_rt_k: 'মক্কায় পৌঁছে', c_rt_t: 'কোথা থেকে হেঁটে আসবেন?',
      c_rt_a: 'মসজিদুল হারামে হেঁটে যাওয়ার প্রধান পথগুলো এখানে।',
      c_rt_note: 'বাস্তবে মসজিদ পূর্ণ হলে গেট বন্ধ হতে পারে। সবুজ "খোলা" চিহ্ন ও নিরাপত্তাকর্মীদের নির্দেশনা মেনে চলুন।',
      c_rt_go: 'হাঁটা শুরু করুন',
      rtA_t: 'ইবরাহিম খলিল রোড (মিসফালাহ)', rtA_n: 'দক্ষিণ-পশ্চিম। বাদশাহ আবদুল আজিজ গেট (গেট ১) পর্যন্ত প্রায় ০.৮ কিমি, ১০-১৫ মিনিট।',
      rtB_t: 'আজিয়াদ স্ট্রিট', rtB_n: 'দক্ষিণ-পূর্ব। বাদশাহ আবদুল আজিজ গেট (গেট ১) পর্যন্ত প্রায় ০.৭ কিমি, ৮-১২ মিনিট।',
      rtC_t: 'জাবালে ওমর', rtC_n: 'পশ্চিম। বাদশাহ ফাহাদ গেট (গেট ৭৯) পর্যন্ত প্রায় ০.৫ কিমি, ৫-১০ মিনিট।',
      rtD_t: 'জারওয়াল', rtD_n: 'উত্তর-পশ্চিম। বাবুল উমরাহ (গেট ৬২) পর্যন্ত প্রায় ১ কিমি।',
      rtE_t: 'মসজিদে জিন (আল-হাজুন)', rtE_n: 'উত্তর-পূর্ব। বাবুল ফাতহ (গেট ৪৫) পর্যন্ত প্রায় ০.৯ কিমি।',
      c_gate_note: 'এটি গেট নম্বর {n}, {gate}। নম্বরটি মনে রাখুন।',
      c_land_tip: 'ক্লক টাওয়ার প্রায় সব জায়গা থেকে দেখা যায় এবং সবসময় দক্ষিণ দিক বোঝায়।',
      c_way: 'ফেরার পথ: আপনি গেট নম্বর {n}, {gate} দিয়ে ঢুকেছিলেন।',
      view_1: 'গেট ১ দিয়ে বের হলে সামনে ক্লক টাওয়ার।',
      view_79: 'গেট ৭৯ দিয়ে বের হলে সোজা সামনে জাবালে ওমরের টাওয়ারগুলো।',
      view_62: 'গেট ৬২ দিয়ে বের হলে সোজা সামনে জারওয়ালের রাস্তা।',
      view_45: 'গেট ৪৫ দিয়ে বের হলে ক্লক টাওয়ার পেছনে।',
      view_100: 'গেট ১০০ দিয়ে বের হলে সামনে বাম দিকে জারওয়ালের রাস্তা।',
      ctrlTitle: 'নিয়ন্ত্রণ',
      ctrlDesk: 'W A S D বা অ্যারো কি দিয়ে চলুন। রমলের জন্য Shift বা "দ্রুত হাঁটা" চেপে ধরুন। E চাপুন সোনালি বোতামের জন্য।',
      ctrlTouch: 'চলার জন্য স্ক্রিনের বাম পাশে আঙুল রাখুন। ডান পাশে টানুন চারপাশ দেখতে।',
      ctrlAuto: 'অটো-হাঁটা আপনাকে সঠিক পথে নিয়ে যাবে।',
      performAs: 'কে উমরাহ করছেন?', man: 'পুরুষ', woman: 'নারী',
      manNote: 'ইযতিবা, রমল এবং সবুজ বাতির মাঝে হালকা দৌড়সহ',
      womanNote: 'পুরো সময় স্বাভাবিক গতিতে হাঁটা',
      begin: 'ইহরাম দিয়ে শুরু করুন', title: 'উমরাহ সফর সিমুলেটর',
      sub: 'হোটেল থেকে কাবা পর্যন্ত বাস্তব পথ ধরে হেঁটে যান, তারপর মসজিদুল হারামে উমরাহর প্রতিটি ধাপ পালন করুন।',
      disclaimer: 'এটি একটি শেখার সহায়ক। আপনার আলেম বা গ্রুপ গাইডের নির্দেশনা মেনে চলুন।',
      langLabel: 'ভাষা', unit_m: 'মি', unit_km: 'কিমি',
      d_walked: 'হাঁটা হয়েছে {d}', d_gate: 'গেট {n}: {d}', d_mataf: 'মাতাফ: {d}', d_of: '{t}-এর মধ্যে {d}',
      share_copied: 'লিংক কপি হয়েছে!', offline_ready: 'অ্যাপ অফলাইনে সেভ হয়েছে।',
    }
  };
  let s = STR_GAME[lang]?.[k]; if (s === undefined) s = STR_GAME.en[k]; if (s === undefined) return k;
  if (Array.isArray(s)) return s;
  if (vars) for (const v in vars) s = s.split('{' + v + '}').join(typeof vars[v] === 'number' ? num(vars[v]) : vars[v]);
  return s;
}

/* ===== ROUTES (use world.js constants: polar, A_G1 etc already defined) ===== */
const ROUTES = {
  A: { no: 1,  km: 0.8, start: { x: -30,  z: 205,  yaw: Math.PI     }, out: [{ x: -30, z: 95 }, { x: -8, z: 74 }, polar(R_OUT + 3, A_G1)] },
  B: { no: 1,  km: 0.7, start: { x: 55,   z: 205,  yaw: Math.PI     }, out: [{ x: 55,  z: 88 }, { x: 14, z: 72 }, polar(R_OUT + 3, A_G1)] },
  C: { no: 79, km: 0.5, start: { x: -203,  z: 1,   yaw: Math.PI / 2 }, out: [{ x: -92, z: 1  }, polar(R_OUT + 3, A_G79)] },
  D: { no: 62, km: 1.0, start: { ...polar(203, A_G62), yaw: Math.atan2(-Math.cos(A_G62), Math.sin(A_G62)) }, out: [polar(92, A_G62), polar(R_OUT + 3, A_G62)] },
  E: { no: 45, km: 0.9, start: { x: 61,   z: -214, yaw: 0           }, out: [{ x: 60,  z: -82 }, { x: 30, z: -70 }, polar(R_OUT + 3, A_G45)] }
};
const ROUTE_KEYS = ['A', 'B', 'C', 'D', 'E'];
for (const k of ROUTE_KEYS) {
  const R = ROUTES[k], pts = [R.start, ...R.out]; let u = 0;
  for (let i = 1; i < pts.length; i++) u += Math.hypot(pts[i].x - pts[i-1].x, pts[i].z - pts[i-1].z);
  R.units = u; R.scale = R.km * 1000 / u;
  const a = GATE_ANG[R.no]; R.inn = [polar(52, a), polar(40.5, a), polar(34, a)];
}
const SAFA_ROUTE = [polar(35.5, A_SAFA), polar(41, A_SAFA), polar(52, A_SAFA), polar(66, A_SAFA), { x: 66.5, z: 34.5 }, { x: 70, z: 40.5 }];

/* ===== STATE ===== */
const S = {
  phase: 'intro', mode: 'orbit', gender: 'm', route: 'A',
  target: null, targetR: 2, routePts: null, routeIdx: 0, gateNo: null, enterNo: null,
  actionKey: null, actionFn: null, auto: false, modal: false, locked: false, fading: false,
  cardBuilder: null, objKey: null, objVars: null, objText: null, hintKey: null, stripKey: null, nearKey: null,
  tw: null, sai: null, prayer: null, talb: 0, camSnap: true, walked: 0
};
const P = { pos: new THREE.Vector3(0, 0, 30), yaw: Math.PI, moving: false, running: false, runHeld: false, stuck: 0, autoR: null };
const cam = { yaw: 0, pitch: 0.36, dist: 11, lastUser: -10 };
const pose = { bend: 0, sy: 1 }, poseT = { bend: 0, sy: 1 };
let T = 0, cur = null;

window._gender = S.gender; window._phase = S.phase; window._walked = S.walked;
window._px = 0; window._pz = 0;
window._toastFn = null;

/* Load saved */
(function () {
  const ph = store.get('phase');
  if (ph && { ihram:1,route:1,approach:1,inside:1,tawaf_start:1,tawaf:1,maqam:1,zamzam:1,to_safa:1,sai_safa:1,sai:1,halq:1,free:1 }[ph]) S.phase = ph;
  S.gender = store.get('gender') === 'f' ? 'f' : 'm';
  const rk = store.get('route'); if (['A','B','C','D','E'].includes(rk)) S.route = rk;
  const gn = parseInt(store.get('gateNo') || '0'); if (gn) S.gateNo = gn;
  S.walked = parseInt(store.get('walked') || '0') || 0;
  S.talb = parseInt(store.get('talb') || '0') || 0;
})();

const inMasa = p => p.x > MX0 - 0.3 && p.x < MX1 + 0.3 && p.z > MZN - 2 && p.z < MZS + 2;
const gateName = no => GATES[no].n[lang];
const fmtDist = m => m < 1000 ? num(Math.max(0, Math.round(m / 10) * 10)) + ' ' + t('unit_m') : num((m / 1000).toFixed(2)) + ' ' + t('unit_km');
const SAI_LAP_M = 450, MASA_SCALE = SAI_LAP_M / (MZ_SAFA - MZ_MARWA);
function zoneScale(p) {
  if (inMasa(p)) return MASA_SCALE;
  const r = Math.hypot(p.x, p.z);
  if (r < 37) return 1; if (r < 61.5) return 4.5;
  if (S.phase === 'to_safa' || S.phase === 'sai_safa') return 3;
  return ROUTES[S.route].scale;
}
function routeLeft() {
  const pts = S.routePts; let u = Math.hypot(P.pos.x - pts[S.routeIdx].x, P.pos.z - pts[S.routeIdx].z);
  for (let i = S.routeIdx + 1; i < pts.length; i++) u += Math.hypot(pts[i].x - pts[i-1].x, pts[i].z - pts[i-1].z);
  return u;
}
function saveProgress() {
  store.set('phase', S.phase); store.set('gender', S.gender); store.set('route', S.route);
  store.set('gateNo', S.gateNo || ''); store.set('walked', Math.round(S.walked)); store.set('talb', S.talb);
  window._gender = S.gender; window._phase = S.phase; window._walked = S.walked;
}

/* ===== DOM ===== */
const hud = document.getElementById('hud'), stepsEl = document.getElementById('steps');
const objEl = document.getElementById('objective'), objK = document.getElementById('objK'), objT = document.getElementById('objT'), objH = document.getElementById('objH');
const counterEl = document.getElementById('counter'), ringEl = document.getElementById('ring'), cNum = document.getElementById('cNum'), cLab = document.getElementById('cLab'), toastEl = document.getElementById('toast');
const stripEl = document.getElementById('duaStrip'), nearEl = document.getElementById('near'), controlsEl = document.getElementById('controls');
const actBtn = document.getElementById('actBtn'), autoBtn = document.getElementById('autoBtn'), runBtn = document.getElementById('runBtn');
const joyEl = document.getElementById('joy'), knobEl = document.getElementById('knob'), joyHint = document.getElementById('joyHint'), prayEl = document.getElementById('pray');
const mapBox = document.getElementById('mapBox'), mapC = document.getElementById('map'), mapGate = document.getElementById('mapGate'), mapDist = document.getElementById('mapDist'), cKm = document.getElementById('cKm'), mctx = mapC.getContext('2d');
const modal = document.getElementById('modal'), cardEl = document.getElementById('card'), cardK = document.getElementById('cardK'), cardTitle = document.getElementById('cardTitle'), cardBody = document.getElementById('cardBody'), cardBtns = document.getElementById('cardBtns');
const fadeEl = document.getElementById('fade');

/* ===== RING ===== */
function arcD(r, a0, a1) {
  const p = a => { const rad = (a - 90) * Math.PI / 180; return [50 + r * Math.cos(rad), 50 + r * Math.sin(rad)]; };
  const [x0, y0] = p(a0), [x1, y1] = p(a1);
  return `M${x0.toFixed(2)} ${y0.toFixed(2)} A${r} ${r} 0 ${a1 - a0 > 180 ? 1 : 0} 1 ${x1.toFixed(2)} ${y1.toFixed(2)}`;
}
const SEG = 7, segA = i => [i * 360 / SEG + 4, (i + 1) * 360 / SEG - 4];
ringEl.innerHTML = Array.from({ length: SEG }, (_, i) => `<path class="trk" d="${arcD(40, ...segA(i))}"/><path class="fil" d=""/>`).join('');
const segEls = [...ringEl.querySelectorAll('.fil')];
let lastCounter = '';
function updateCounter(done, frac, labelKey) {
  const key = done + ':' + Math.round(frac * 60) + ':' + labelKey + lang;
  if (key === lastCounter) return; lastCounter = key;
  segEls.forEach((el, i) => { const [s, e] = segA(i); el.setAttribute('d', i < done ? arcD(40, s, e) : (i === done && frac > 0.02 ? arcD(40, s, s + (e - s) * frac) : '')); });
  cNum.textContent = num(Math.min(done + 1, 7)) + '/' + num(7); cLab.textContent = t(labelKey);
}

/* ===== TOAST ===== */
let toastTimer = 0;
function toast(html, kind) {
  toastEl.className = 'toast show' + (kind ? ' ' + kind : ''); toastEl.innerHTML = html;
  clearTimeout(toastTimer); toastTimer = setTimeout(() => toastEl.classList.remove('show'), kind === 'warn' ? 2600 : 3000);
}
window._toastFn = toast;

/* ===== OBJECTIVE ===== */
function setObjective(key, vars) {
  S.objKey = key; S.objVars = vars || null;
  if (!key) { if (!objEl.hidden) objEl.hidden = true; S.objText = null; return; }
  const txt = t(key, vars);
  if (txt !== S.objText) { S.objText = txt; objT.textContent = txt; objK.textContent = t('now'); }
  if (objEl.hidden) objEl.hidden = false;
}
function setHint(key) { if (key === S.hintKey) return; S.hintKey = key; objH.hidden = !key; if (key) objH.textContent = t(key); }
function setStrip(duaKey, labelKey) {
  const k = duaKey ? duaKey + '|' + labelKey : null;
  if (k === S.stripKey) return; S.stripKey = k; stripEl.hidden = !duaKey;
  if (duaKey) { const d = DUA[duaKey]; stripEl.innerHTML = `<span class="k">${esc(t(labelKey))}</span><span class="ar" lang="ar">${d.ar}</span><span class="tr">${esc(d.tr[lang])}</span>`; }
}
function setAction(key, fn) { S.actionFn = fn; if (key === S.actionKey) return; S.actionKey = key; actBtn.textContent = t(key); actBtn.hidden = false; }
function clearAction() { if (S.actionKey === null) return; S.actionKey = null; S.actionFn = null; actBtn.hidden = true; }
function nudgeRun(on) { runBtn.classList.toggle('nudge', !!on); }
function renderMapGate() { mapGate.hidden = !S.gateNo; if (S.gateNo) mapGate.innerHTML = `${esc(t('yourGate'))}<b>${num(S.gateNo)}</b>`; }

/* ===== STEPS BAR ===== */
const CHIPS = [['steps_ihram','ihram'],['steps_gate','route'],['steps_tawaf','tawaf_start'],['steps_maqam','maqam'],['steps_zamzam','zamzam'],['steps_sai','to_safa'],['steps_halq','halq']];
const PH_CHIP = { intro:-1, ihram:0, route:1, approach:1, inside:1, tawaf_start:2, tawaf:2, maqam:3, zamzam:4, to_safa:5, sai_safa:5, sai:5, halq:6, free:7 };
function renderSteps() {
  const ci = PH_CHIP[S.phase];
  stepsEl.setAttribute('aria-label', t('stepsLabel'));
  stepsEl.innerHTML = CHIPS.map(([k, ph], i) =>
    `<li class="${i < ci ? 'done' : ''} ${i === ci ? 'cur' : ''}"><button data-jump="${ph}" ${i === ci ? 'aria-current="step"' : ''}><span class="n">${num(i+1)}</span>${esc(t(k))}</button></li>`
  ).join('');
  const c = stepsEl.querySelector('.cur'); if (c) c.scrollIntoView({ inline: 'nearest', block: 'nearest' });
}
stepsEl.addEventListener('click', e => { const b = e.target.closest('[data-jump]'); if (!b || S.fading) return; S.talb = 0; goTo(b.dataset.jump); });

/* ===== CARDS ===== */
function showCard(builder) { S.cardBuilder = builder; S.modal = true; modal.hidden = false; renderCard(true); }
function closeCard() { S.cardBuilder = null; S.modal = false; modal.hidden = true; }
function renderCard(focus) {
  const spec = S.cardBuilder(); cardEl.className = 'card' + (spec.cls ? ' ' + spec.cls : '');
  cardK.textContent = spec.kicker || ''; cardK.hidden = !spec.kicker;
  cardTitle.textContent = spec.title; cardBody.innerHTML = spec.html; cardBtns.innerHTML = '';
  spec.buttons.forEach(b => { const el = document.createElement('button'); el.className = 'btn' + (b.primary ? ' primary' : ''); el.textContent = b.label; el.addEventListener('click', b.fn); cardBtns.appendChild(el); });
  if (spec.after) spec.after();
  if (focus) { cardEl.scrollTop = 0; const f = cardBtns.querySelector('.primary') || cardBtns.firstChild; f && f.focus({ preventScroll: true }); }
}
modal.addEventListener('click', e => {
  const g = e.target.closest('[data-gender]'); if (g) { S.gender = g.dataset.gender; store.set('gender', S.gender); window._gender = S.gender; renderCard(); return; }
  const r = e.target.closest('[data-route]'); if (r) { S.route = r.dataset.route; store.set('route', S.route); renderCard(); return; }
  const l = e.target.closest('[data-lang]'); if (l) setLang(l.dataset.lang);
});
const segBtns = () => `<div class="seg" role="group" aria-label="${esc(t('langLabel'))}"><button data-lang="en" aria-pressed="${lang==='en'}">English</button><button data-lang="bn" aria-pressed="${lang==='bn'}">বাংলা</button></div>`;
const P_ = s => `<p>${esc(s)}</p>`, NOTE = s => `<p class="note">${esc(s)}</p>`;
const opt = (attr, val, on, title, note) => `<button class="opt" data-${attr}="${val}" aria-pressed="${on}"><span class="opt-t">${esc(title)}</span><span class="opt-n">${esc(note)}</span></button>`;

const cardIntro = () => ({ cls: 'intro', title: t('title'),
  html: `<p class="lead">${esc(t('sub'))}</p>${segBtns()}<p class="q">${esc(t('performAs'))}</p><div class="choice">` +
    ['m','f'].map(g => opt('gender', g, S.gender===g, t(g==='m'?'man':'woman'), t(g==='m'?'manNote':'womanNote'))).join('') +
    `</div><p class="fine">${esc(t('disclaimer'))}</p>`,
  buttons: [{ label: t('begin'), primary: true, fn: () => goTo('ihram') }] });
const cardIhram1 = () => ({ kicker: t('steps_ihram'), title: t('c_ih1_t'),
  html: P_(t(S.gender==='m' ? 'c_ih1_m' : 'c_ih1_f')) + NOTE(t('c_flight')),
  buttons: [{ label: t('cont'), primary: true, fn: () => showCard(cardIhram2) }] });
let justSaid = false;
const cardIhram2 = () => ({ kicker: t('steps_ihram'), title: t('c_ih2_t'),
  html: P_(t('c_ih2_a')) + duaHTML('niyyah') + P_(t('c_ih2_b')) + duaHTML('talbiyah'),
  buttons: S.talb < 3 ? [{ label: t('c_talb_btn', { n: S.talb }), primary: true, fn: () => { S.talb++; justSaid = true; renderCard(); } }]
    : [{ label: t('cont'), primary: true, fn: () => showCard(cardIhram3) }],
  after: () => { if (justSaid) { justSaid = false; const d = cardBody.querySelector('[data-dua="talbiyah"]'); d && d.classList.add('said'); const b = cardBtns.querySelector('.primary'); b && b.focus({ preventScroll: true }); } } });
const cardIhram3 = () => ({ kicker: t('steps_ihram'), title: t('c_ih3_t'),
  html: `<ul class="rules">${[...t('c_ih3_list'), t(S.gender==='m' ? 'c_ih3_m' : 'c_ih3_f')].map(i => `<li>${esc(i)}</li>`).join('')}</ul>` + NOTE(t('c_wudu')),
  buttons: [{ label: t('c_arrive'), primary: true, fn: () => goTo('route') }] });
const cardRoute = () => ({ kicker: t('c_rt_k'), title: t('c_rt_t'),
  html: P_(t('c_rt_a')) + `<div class="choice list">` + ROUTE_KEYS.map(k => opt('route', k, S.route===k, t('rt'+k+'_t'), t('rt'+k+'_n'))).join('') + `</div>` + NOTE(t('c_rt_note')) + NOTE(t(isTouch ? 'ctrlTouch' : 'ctrlDesk')),
  buttons: [{ label: t('c_rt_go'), primary: true, fn: () => goTo('approach') }] });
const cardGate = () => { const no = S.enterNo; return { kicker: t('gateNo', { n: no }), title: t('c_enter_t'),
  html: P_(t('c_enter_a')) + duaHTML('enter') + NOTE(t('c_gate_note', { n: no, gate: gateName(no) })) + NOTE(t('view_' + no)) + NOTE(t('c_land_tip')),
  buttons: [{ label: t('c_step_in'), primary: true, fn: () => { closeCard(); S.gateNo = no; renderMapGate(); setPhase('inside'); } }] }; };
const cardTawafBegin = () => ({ kicker: t('steps_tawaf'), title: t('c_tb_t'),
  html: P_(t('c_tb_a')) + duaHTML('istilam') + P_(t('c_tb_b')) + (S.gender==='m' ? NOTE(t('c_tb_m')) : ''),
  buttons: [{ label: t('c_tb_go'), primary: true, fn: () => { closeCard(); setPhase('tawaf'); } }] });
const cardMaqam = () => ({ kicker: t('steps_maqam'), title: t('c_mq_t'),
  html: P_(t('c_mq_a')) + duaHTML('maqam') + NOTE(t('c_mq_b')),
  buttons: [{ label: t('c_start_prayer'), primary: true, fn: () => { closeCard(); pray(Math.atan2(-P.pos.x, -P.pos.z), () => { toast(esc(t('t_prayed')), 'ok'); setPhase('zamzam'); }); } }] });
const cardZamzam = () => ({ kicker: t('steps_zamzam'), title: t('c_zz_t'),
  html: P_(t('c_zz_a')) + duaHTML('zamzam'),
  buttons: [{ label: t('c_done'), primary: true, fn: () => { closeCard(); setPhase('to_safa'); } }] });
const cardSafa = () => ({ kicker: t('c_sf_k'), title: t('c_sf_t'),
  html: P_(t('c_sf_a')) + duaHTML('safa') + P_(t('c_sf_b')) + duaHTML('dhikr') + NOTE(t('c_sf_c')),
  buttons: [{ label: t('c_sf_go'), primary: true, fn: () => { closeCard(); setPhase('sai'); } }] });
const cardDhikr = place => () => ({ kicker: t('steps_sai'), title: t('c_dk_t', { dest: t('place_' + place) }),
  html: P_(t('c_dk_a')) + duaHTML('dhikr'),
  buttons: [{ label: t('cont'), primary: true, fn: closeCard }] });
const cardHalq = () => { const m = S.gender === 'm', done = () => showCard(cardUmrahDone);
  return { kicker: t('steps_halq'), title: t('c_hq_t'), html: P_(t(m ? 'c_hq_m' : 'c_hq_f')) + NOTE(t('c_hq_exit')),
    buttons: m ? [{ label: t('c_hq_trim'), fn: done }, { label: t('c_hq_shave'), primary: true, fn: done }] : [{ label: t('c_hq_cut'), primary: true, fn: done }] }; };
const cardUmrahDone = () => ({ kicker: t('steps_halq'), title: t('c_ud_t'),
  html: P_(t('c_ud_a')) + (S.gateNo ? NOTE(t('c_way', { n: S.gateNo, gate: gateName(S.gateNo) }) + ' ' + t('view_' + S.gateNo)) : '') + NOTE(t('d_walked', { d: fmtDist(S.walked) })) + NOTE(t('c_ud_b')),
  buttons: [{ label: t('c_restart'), fn: restart }, { label: t('c_free'), primary: true, fn: () => { closeCard(); setPhase('free'); } }] });
const cardHelp = () => ({ title: t('ctrlTitle'), html: P_(t(isTouch ? 'ctrlTouch' : 'ctrlDesk')) + P_(t('ctrlAuto')), buttons: [{ label: t('close'), primary: true, fn: closeCard }] });
const cardInfo = key => () => { const I = INFO[key]; return { kicker: t('nearby'), title: I.n[lang], html: P_(I.x[lang]) + (I.d ? duaHTML(I.d) : ''), buttons: [{ label: t('close'), primary: true, fn: closeCard }] }; };

/* ===== PRAYER ===== */
const PRAY_SEQ = [['stand','pr_qiyam',1.8],['bow','pr_ruku',1.3],['stand','pr_qiyam',.8],['prost','pr_sujood',1.2],['sit','pr_jalsa',.8],['prost','pr_sujood',1.2],
  ['stand','pr_qiyam',1.6],['bow','pr_ruku',1.3],['stand','pr_qiyam',.8],['prost','pr_sujood',1.2],['sit','pr_jalsa',.8],['prost','pr_sujood',1.2],['sit','pr_tash',1.8],['sit','pr_salam',1.4]];
const POSES = { stand:{bend:0,sy:1}, bow:{bend:1.35,sy:1}, prost:{bend:1.45,sy:.5}, sit:{bend:.05,sy:.6} };
function pray(yaw, cb) { S.locked = true; P.yaw = yaw; S.prayer = { i:0, t:0, cb }; cam.lastUser = -10; clearAction(); prayEl.hidden = false; renderNear(); renderPray(); }
function renderPray() { const [p, k] = PRAY_SEQ[S.prayer.i]; Object.assign(poseT, POSES[p]); prayEl.innerHTML = `<span>${esc(t(S.prayer.i < 6 ? 'pr_rak1' : 'pr_rak2'))}</span><b>${esc(t(k))}</b>`; }
function prayerTick(dt) {
  const pr = S.prayer; pr.t += dt; if (pr.t < PRAY_SEQ[pr.i][2]) return;
  pr.t = 0; pr.i++;
  if (pr.i >= PRAY_SEQ.length) { S.prayer = null; Object.assign(poseT, POSES.stand); prayEl.hidden = true; S.locked = false; renderNear(); pr.cb(); return; }
  renderPray();
}

/* ===== PHASE SYSTEM ===== */
const PH_MODES = { intro: 'orbit', ihram: 'orbit', route: 'orbit' };
const PH_SPAWN = {
  approach:   () => ROUTES[S.route].start,
  tawaf_start:() => ({ x: 0, z: 30, yaw: Math.PI }),
  maqam:      () => { const p = polar(12.5, A0 + 0.08); return { x: p.x, z: p.z, yaw: Math.PI }; },
  zamzam:     () => ({ x: PRAY_SPOT.x, z: PRAY_SPOT.z, yaw: 0 }),
  to_safa:    () => ({ x: ZAMZAM_WP.x, z: ZAMZAM_WP.z, yaw: Math.PI }),
  sai_safa:   () => ({ x: SAFA_PT.x,   z: SAFA_PT.z,   yaw: Math.PI }),
  halq:       () => ({ x: MARWA_PT.x,  z: MARWA_PT.z + 1, yaw: 0 }),
};

function place(s) { P.pos.set(s.x, 0, s.z); P.yaw = s.yaw; cam.yaw = s.yaw + Math.PI; cam.pitch = 0.36; S.camSnap = true; }
function goTo(ph) {
  if (S.fading) return; S.fading = true; fadeEl.classList.add('on');
  setTimeout(() => {
    if (PH_SPAWN[ph]) place(PH_SPAWN[ph]());
    closeCard(); setPhase(ph);
    requestAnimationFrame(() => { fadeEl.classList.remove('on'); S.fading = false; });
  }, 420);
}
function restart() { S.talb = 0; S.gateNo = null; S.walked = 0; store.del('phase'); renderMapGate(); goTo('intro'); }
function setIdtiba(on) { if (AV.m.shoulder) AV.m.shoulder.visible = on; }
function nearestGate() {
  const a = angleOf(P.pos.x, P.pos.z); let best = 1, bd = 9;
  for (const no of MAIN_GATES) { const d = Math.abs(wrapPi(a - GATE_ANG[no])); if (d < bd) { bd = d; best = no; } }
  return best;
}

function setPhase(ph) {
  S.phase = ph; S.mode = PH_MODES[ph] || 'follow';
  clearAction(); setHint(null); setStrip(null); nudgeRun(false);
  S.target = null; S.routePts = null; S.routeIdx = 0; S.auto = false; P.autoR = null; P.stuck = 0;
  autoBtn.setAttribute('aria-pressed', 'false');
  counterEl.hidden = true; lastCounter = '';
  hud.hidden = ph === 'intro';
  const follow = S.mode === 'follow';
  controlsEl.hidden = !follow; joyHint.hidden = !(follow && isTouch); mapBox.hidden = !follow;
  if (!['tawaf_start','tawaf'].includes(ph)) setIdtiba(false);
  setObjective(null); saveProgress();
  switch (ph) {
    case 'intro':       showCard(cardIntro); break;
    case 'ihram':       showCard(cardIhram1); break;
    case 'route':       showCard(cardRoute); break;
    case 'approach':    S.routePts = ROUTES[S.route].out; S.targetR = 3.2; S.gateNo = null; S.walked = 0; renderMapGate(); break;
    case 'inside':      S.routePts = ROUTES[S.route].inn; S.targetR = 2.5; break;
    case 'tawaf_start':
      S.target = polar(10.5, A0); S.targetR = 2.5;
      if (S.gender === 'm') { setIdtiba(true); toast(esc(t('t_idtiba'))); }
      break;
    case 'tawaf':
      if (S.gender === 'm') setIdtiba(true);
      S.tw = { prevA: angleOf(P.pos.x, P.pos.z), cum:0, max:0, rounds:0, lastWarn:-9, istUntil:0, dist:0 };
      counterEl.hidden = false; break;
    case 'maqam':    S.target = PRAY_SPOT; S.targetR = 2.0; if (S.gender === 'm') toast(esc(t('t_cover'))); break;
    case 'zamzam':   S.target = ZAMZAM_WP; S.targetR = 2.0; break;
    case 'to_safa':  S.routePts = SAFA_ROUTE; S.targetR = 2.5; break;
    case 'sai_safa': S.target = SAFA_PT; S.targetR = 3; break;
    case 'sai':      S.sai = { lap:0, heading:'marwa', dhikr:null, dist:0 }; counterEl.hidden = false; break;
    case 'halq':     showCard(cardHalq); break;
  }
  if (S.routePts) S.target = S.routePts[0];
  renderSteps(); updateAvatarVis();
}

/* ===== GAME TICKS ===== */
function routeTick() {
  const pts = S.routePts, last = pts.length - 1, px = P.pos.x, pz = P.pos.z;
  let i = S.routeIdx;
  while (i < last) {
    const a = pts[i], b = pts[i+1], vx = b.x - a.x, vz = b.z - a.z, u = ((px-a.x)*vx+(pz-a.z)*vz)/(vx*vx+vz*vz);
    const uc = clamp(u,0,1), dSeg = Math.hypot(px-(a.x+vx*uc), pz-(a.z+vz*uc));
    if (Math.hypot(px-a.x, pz-a.z) < 3.2 || (u > 0 && dSeg < 12)) i++; else break;
  }
  S.routeIdx = i; S.target = pts[i];
  return i === last && Math.hypot(px - pts[i].x, pz - pts[i].z) < S.targetR;
}
function tawafTick() {
  const p = P.pos, tw = S.tw, a = angleOf(p.x, p.z);
  tw.cum += wrapPi(a - tw.prevA); tw.prevA = a;
  if (tw.cum > tw.max) tw.max = tw.cum;
  if (tw.cum < tw.max - 0.5 && T - tw.lastWarn > 3.5) { toast(esc(t('t_wrong')), 'warn'); tw.lastWarn = T; }
  const done = Math.floor(Math.max(0, tw.cum) / TAU);
  if (done > tw.rounds) {
    tw.rounds = done;
    if (done >= 7) { toast(esc(t('t_tawaf_done')), 'ok'); setPhase('maqam'); return; }
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
function doIstilam() { S.tw.istUntil = 0; clearAction(); toast(`<span class="ar" lang="ar">${DUA.istilam.ar}</span>${esc(DUA.istilam.tr[lang])}`); }
function saiTick() {
  const z = P.pos.z, sai = S.sai, inM = inMasa(P.pos), atM = inM && z < MZ_MARWA, atS = inM && z > MZ_SAFA;
  if ((sai.heading === 'marwa' && atM) || (sai.heading === 'safa' && atS)) {
    const reached = sai.heading; sai.lap++;
    if (sai.lap >= 7) { toast(esc(t('t_sai_done')), 'ok'); setPhase('halq'); return; }
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
function phaseTick() {
  const p = P.pos, near = S.target ? Math.hypot(p.x - S.target.x, p.z - S.target.z) < S.targetR : false;
  const at = (obj, act, fn) => { setObjective(obj); near ? setAction(act, fn) : clearAction(); };
  switch (S.phase) {
    case 'approach': {
      const no = ROUTES[S.route].no, arrived = routeTick();
      if (Math.hypot(p.x, p.z) < R_OUT - 1.5) { S.enterNo = nearestGate(); showCard(cardGate); break; }
      setObjective(arrived ? 'obj_gate' : (S.routeIdx === 0 ? 'obj_road_' + S.route : 'obj_plaza'), { n: no, gate: gateName(no) });
      arrived ? setAction('act_enter', () => { S.enterNo = no; showCard(cardGate); }) : clearAction();
      break;
    }
    case 'inside': {
      const arrived = routeTick(); setObjective('obj_inside');
      if (arrived || Math.hypot(p.x, p.z) < 34.5) { toast(esc(t('t_firstsight')), 'ok'); setPhase('tawaf_start'); }
      break;
    }
    case 'tawaf_start': {
      const a = angleOf(p.x, p.z), r = Math.hypot(p.x, p.z), on = Math.abs(wrapPi(a - A0)) < 0.2 && r < 30;
      setObjective(on ? 'obj_on_line' : 'obj_tawaf_start');
      on ? setAction('act_begin_tawaf', () => showCard(cardTawafBegin)) : clearAction();
      break;
    }
    case 'tawaf':    tawafTick(); break;
    case 'maqam':   at('obj_maqam',   'act_pray',  () => showCard(cardMaqam)); break;
    case 'zamzam':  at('obj_zamzam',  'act_drink', () => showCard(cardZamzam)); break;
    case 'to_safa': {
      routeTick(); const inM = inMasa(p);
      setObjective(inM ? 'obj_safa_turn' : 'obj_to_safa');
      if (inM && p.z > MZ_SAFA) setPhase('sai_safa');
      break;
    }
    case 'sai_safa': setObjective('obj_sai_safa'); (inMasa(p) && p.z > MZ_SAFA) ? setAction('act_safa', () => { P.yaw = -Math.PI / 2; showCard(cardSafa); }) : clearAction(); break;
    case 'sai':  saiTick(); break;
    case 'free': setObjective('obj_free'); break;
  }
  let best = null, bd = 1e9;
  for (const ip of cur.infos) { const d = Math.hypot(p.x - ip.x, p.z - ip.z); if (d < ip.r && d < bd) { bd = d; best = ip.key; } }
  if (best !== S.nearKey) { S.nearKey = best; renderNear(); }
}
function renderNear() {
  nearEl.hidden = !S.nearKey || S.mode !== 'follow' || !!S.prayer;
  if (S.nearKey) nearEl.innerHTML = `<b>${esc(INFO[S.nearKey].n[lang])}</b><span>${esc(t('learn'))}</span>`;
}
nearEl.addEventListener('click', () => { if (S.nearKey && !S.modal) showCard(cardInfo(S.nearKey)); });

/* ===== MINI MAP ===== */
let mapPx = 0;
function sizeMap() { const w = mapC.clientWidth || 160; if (w === mapPx) return; const d = Math.min(2, window.devicePixelRatio || 1); mapPx = w; mapC.width = w * d; mapC.height = w * d; mctx.setTransform(d, 0, 0, d, 0, 0); }
function drawMap() {
  if (mapBox.hidden) return; sizeMap();
  const W = mapPx, s = W / 175, cx = W / 2, cy = W / 2, g = mctx, px = P.pos.x, pz = P.pos.z;
  const X = x => cx + (x - px) * s, Y = z => cy + (z - pz) * s, circ = (x, z, r) => { g.beginPath(); g.arc(X(x), Y(z), r * s, 0, TAU); };
  g.clearRect(0, 0, W, W); g.save(); g.beginPath(); g.arc(cx, cy, W/2, 0, TAU); g.clip();
  g.fillStyle = '#d3cab8'; g.fillRect(0, 0, W, W);
  const RM = [{x1:-30,z1:214,x2:-30,z2:78,hw:7},{x1:55,z1:214,x2:55,z2:72,hw:7},{x1:-212,z1:1,x2:-80,z2:1,hw:7},{x1:polar(212,A_G62).x,z1:polar(212,A_G62).z,x2:polar(84,A_G62).x,z2:polar(84,A_G62).z,hw:7},{x1:60,z1:-230,x2:60,z2:-66,hw:7}];
  g.strokeStyle = '#bfb39e'; g.lineCap = 'butt';
  for (const r of RM) { g.lineWidth = r.hw*2*s; g.beginPath(); g.moveTo(X(r.x1),Y(r.z1)); g.lineTo(X(r.x2),Y(r.z2)); g.stroke(); }
  g.fillStyle = '#f3f0e9'; circ(0, 0, PLAZA_R); g.fill();
  g.fillStyle = '#d4c3a0'; g.beginPath(); g.arc(X(0),Y(0),R_OUT*s,0,TAU); g.arc(X(0),Y(0),R_IN*s,0,TAU,true); g.fill();
  g.fillStyle = '#e8e0cf'; circ(0, 0, R_IN - 0.6); g.fill();
  g.fillStyle = '#ffffff'; circ(0, 0, 36); g.fill();
  g.fillStyle = '#e2d6bd'; g.fillRect(X(61.6), Y(-66), 16.8*s, 124*s);
  g.fillStyle = '#efe8da'; g.fillRect(X(MX0), Y(-65), (MX1-MX0)*s, 122*s);
  g.fillStyle = 'rgba(47,224,124,.55)'; g.fillRect(X(MX0), Y(GZ0), (MX1-MX0)*s, (GZ1-GZ0)*s);
  g.save(); g.translate(X(0),Y(0)); g.rotate(-KAABA_ROT); g.fillStyle = '#16130e'; g.fillRect(-KW/2*s,-KD/2*s,KW*s,KD*s); g.restore();
  g.fillStyle = '#c9a54c'; g.strokeStyle = '#16130e'; g.lineWidth = 1.5; g.beginPath(); g.arc(X(14),Y(152),5,0,TAU); g.fill(); g.stroke();
  if (S.mode === 'follow' && S.target) {
    g.setLineDash([3,3]); g.strokeStyle = '#a8862f'; g.lineWidth = 2.2; g.beginPath(); g.moveTo(cx, cy);
    if (S.routePts) for (let i = S.routeIdx; i < S.routePts.length; i++) g.lineTo(X(S.routePts[i].x), Y(S.routePts[i].z));
    else g.lineTo(X(S.target.x), Y(S.target.z));
    g.stroke(); g.setLineDash([]);
    const tp = S.routePts ? S.routePts[S.routePts.length-1] : S.target;
    g.fillStyle = '#e2b84f'; g.strokeStyle = '#16130e'; g.lineWidth = 1.5; g.beginPath(); g.arc(X(tp.x),Y(tp.z),4,0,TAU); g.fill(); g.stroke();
  }
  g.textAlign = 'center'; g.textBaseline = 'middle'; g.font = '700 7.5px "Hind Siliguri",system-ui,sans-serif';
  for (const no of MAIN_GATES) {
    const q = polar(R_OUT+6.5, GATE_ANG[no]), x = X(q.x), y = Y(q.z);
    g.fillStyle = S.gateNo === no ? '#c9a54c' : '#16130e'; g.beginPath(); g.arc(x,y,7.5,0,TAU); g.fill();
    g.fillStyle = S.gateNo === no ? '#16130e' : '#e7cb82'; g.fillText(num(no), x, y+0.5);
  }
  g.save(); g.translate(cx,cy); g.rotate(Math.PI - P.yaw);
  g.fillStyle = '#1f6a47'; g.strokeStyle = '#ffffff'; g.lineWidth = 1.5; g.beginPath(); g.moveTo(0,-7); g.lineTo(5,5); g.lineTo(0,2.5); g.lineTo(-5,5); g.closePath(); g.fill(); g.stroke();
  g.restore(); g.restore();
  g.fillStyle = '#16130e'; g.beginPath(); g.arc(cx,9,7,0,TAU); g.fill(); g.fillStyle = '#e7cb82'; g.font = '700 8px system-ui,sans-serif'; g.fillText('N',cx,9.5);
}

/* ===== DISTANCE ===== */
let distTick = 0;
function renderDist() {
  const lines = [];
  if (S.phase === 'approach' && S.routePts) lines.push(`<span class="go">${esc(t('d_gate', { n: ROUTES[S.route].no, d: fmtDist(routeLeft() * ROUTES[S.route].scale) }))}</span>`);
  if (S.phase === 'inside'   && S.routePts) lines.push(`<span class="go">${esc(t('d_mataf', { d: fmtDist(routeLeft() * 4.5) }))}</span>`);
  lines.push(`<span>${esc(t('d_walked', { d: fmtDist(S.walked) }))}</span>`);
  mapDist.innerHTML = lines.join('');
  if (!counterEl.hidden) {
    if (S.phase === 'tawaf' && S.tw)   cKm.textContent = fmtDist(S.tw.dist);
    else if (S.phase === 'sai' && S.sai) cKm.textContent = t('d_of', { d: fmtDist(S.sai.dist), t: fmtDist(7 * SAI_LAP_M) });
  }
}

/* ===== MOVEMENT ===== */
const K = new Set();
const joy = { active: false, id: null, ox: 0, oy: 0, x: 0, y: 0 };
function autoShouldRun() {
  if (S.gender !== 'm') return false;
  if (S.phase === 'tawaf') return S.tw.rounds < 3;
  if (S.phase === 'sai')   return inMasa(P.pos) && P.pos.z > GZ0 && P.pos.z < GZ1;
  return false;
}
function autoDir() {
  const p = P.pos;
  if (S.phase === 'tawaf') {
    const r = Math.hypot(p.x, p.z) || 1;
    if (P.autoR === null) P.autoR = clamp(r, 16, 30);
    const err = P.autoR - r; let x = p.z/r + (p.x/r)*err*0.25, z = -p.x/r + (p.z/r)*err*0.25;
    const l = Math.hypot(x, z); return { x: x/l, z: z/l };
  }
  if (S.phase === 'sai' && inMasa(p)) {
    const tz = S.sai.heading === 'marwa' ? MARWA_PT.z - 2 : SAFA_PT.z + 2, dz = tz - p.z;
    if (Math.abs(dz) < 0.5) return null;
    const x = (Math.abs(p.x-66.5)<1.5 || Math.abs(p.x-73.5)<1.5) ? (70-p.x)*0.25 : 0, l = Math.hypot(x, 1);
    return { x: x/l, z: Math.sign(dz)/l };
  }
  if (!S.target) return null;
  const dx = S.target.x - p.x, dz = S.target.z - p.z, d = Math.hypot(dx, dz);
  if (d < Math.min(0.8, S.targetR * 0.5)) return null;
  let x = dx/d, z = dz/d;
  const r = Math.hypot(p.x, p.z) || 1;
  if (r < 36) {
    const tc = clamp(-(p.x*x + p.z*z), 0, d), cx = p.x + x*tc, cz = p.z + z*tc;
    if (Math.hypot(cx, cz) < 16.5 && tc > 0.5) {
      let tx = p.z/r, tz = -p.x/r;
      if (wrapPi(angleOf(S.target.x, S.target.z) - angleOf(p.x, p.z)) < 0) { tx = -tx; tz = -tz; }
      const o = r < 17 ? 0.6 : 0; x = tx + p.x/r*o; z = tz + p.z/r*o; const l = Math.hypot(x,z); x/=l; z/=l;
    }
  }
  if (P.stuck > 0.35) { const c = Math.cos(1.0), s2 = Math.sin(1.0), nx = x*c - z*s2; z = x*s2 + z*c; x = nx; if (P.stuck > 1.4) P.stuck = 0; }
  return { x, z };
}
function updatePlayer(dt) {
  let ix = 0, iy = 0;
  if (K.has('KeyW')  || K.has('ArrowUp'))    iy += 1;
  if (K.has('KeyS')  || K.has('ArrowDown'))   iy -= 1;
  if (K.has('KeyA')  || K.has('ArrowLeft'))   ix -= 1;
  if (K.has('KeyD')  || K.has('ArrowRight'))  ix += 1;
  if (joy.active) { ix = joy.x; iy = joy.y; }
  const manual = Math.abs(ix) > 0.08 || Math.abs(iy) > 0.08;
  let mx = 0, mz = 0, mag = 0;
  if (manual) {
    const fx = -Math.sin(cam.yaw), fz = -Math.cos(cam.yaw);
    mx = fx*iy - fz*ix; mz = fz*iy + fx*ix; mag = Math.min(1, Math.hypot(ix, iy));
    const l = Math.hypot(mx, mz) || 1; mx /= l; mz /= l;
    if (S.auto) { S.auto = false; autoBtn.setAttribute('aria-pressed', 'false'); }
  } else if (S.auto) { const d = autoDir(); if (d) { mx = d.x; mz = d.z; mag = 1; } }
  const run = K.has('ShiftLeft') || K.has('ShiftRight') || P.runHeld || (S.auto && autoShouldRun());
  P.running = run && mag > 0;
  if (mag <= 0) { P.moving = false; return; }
  const sp = (run ? 7.0 : 4.4) * mag, q = { x: P.pos.x + mx*sp*dt, z: P.pos.z + mz*sp*dt };
  cur.collide(q, P.pos);
  const moved = Math.hypot(q.x - P.pos.x, q.z - P.pos.z);
  S.walked += moved * zoneScale(q);
  if (S.phase === 'tawaf' && S.tw) S.tw.dist += moved;
  P.pos.x = q.x; P.pos.z = q.z; P.moving = moved > sp * dt * 0.15;
  P.yaw = lerpAngle(P.yaw, Math.atan2(mx, mz), 1 - Math.exp(-dt * 12));
  if (S.auto) P.stuck = moved < sp*dt*0.3 ? P.stuck + dt : Math.max(0, P.stuck - dt);
  window._px = P.pos.x; window._pz = P.pos.z;
}

/* ===== AVATAR ===== */
function buildAvatar(male) {
  const root = new THREE.Group(), body = new THREE.Group(); root.add(body);
  const robe = lam(male ? 0xfbfaf5 : 0x24212b), skin = lam(0xc08a64);
  add(body, new THREE.CylinderGeometry(0.34, male ? 0.41 : 0.5, 0.85, 16), robe, 0, 0.425, 0);
  const upper = new THREE.Group(); upper.position.y = 0.85; body.add(upper);
  add(upper, new THREE.CylinderGeometry(0.29, 0.34, 0.72, 16), robe, 0, 0.36, 0);
  let shoulder = null;
  if (male) {
    add(upper, new THREE.SphereGeometry(0.21, 16, 12), skin, 0, 0.93, 0);
    shoulder = add(upper, new THREE.SphereGeometry(0.15, 12, 10), skin, -0.25, 0.63, 0); shoulder.scale.set(1, 0.8, 1);
    add(upper, new THREE.BoxGeometry(0.34, 0.06, 0.46), robe, 0.16, 0.7, 0).rotation.z = 0.5;
  } else {
    add(upper, new THREE.SphereGeometry(0.23, 16, 12), lam(0x2b2833), 0, 0.93, 0).scale.set(1, 1.08, 1);
    add(upper, new THREE.CircleGeometry(0.11, 16), skin, 0, 0.93, 0.232);
  }
  const ring = add(root, new THREE.RingGeometry(0.58, 0.74, 40).rotateX(-Math.PI/2), new THREE.MeshBasicMaterial({ color: 0xe2b84f, transparent: true, opacity: .95, depthWrite: false }), 0, 0.035, 0);
  return { root, body, upper, shoulder, ring };
}
const AV = { m: buildAvatar(true), f: buildAvatar(false) };
const arrowShape = new THREE.Shape(); arrowShape.moveTo(0,0.6); arrowShape.lineTo(0.42,-0.26); arrowShape.lineTo(0,-0.06); arrowShape.lineTo(-0.42,-0.26); arrowShape.closePath();
const arrow = new THREE.Mesh(new THREE.ShapeGeometry(arrowShape).rotateX(-Math.PI/2), new THREE.MeshBasicMaterial({ color: 0xe2b84f, transparent: true, opacity: .92, depthWrite: false }));
const wp = new THREE.Group();
const wpRing = add(wp, new THREE.RingGeometry(1.0,1.28,48).rotateX(-Math.PI/2), new THREE.MeshBasicMaterial({ color: 0xf0c75e, transparent: true, opacity: .95, depthWrite: false, side: THREE.DoubleSide }), 0, 0.05, 0);
const wpBeam = add(wp, new THREE.CylinderGeometry(0.95,0.95,9,24,1,true), new THREE.MeshBasicMaterial({ color: 0xf5d27a, transparent: true, opacity: .2, depthWrite: false, side: THREE.DoubleSide, blending: THREE.AdditiveBlending }), 0, 4.5, 0);

function updateAvatarVis() { const show = S.mode === 'follow'; AV.m.root.visible = show && S.gender === 'm'; AV.f.root.visible = show && S.gender === 'f'; renderNear(); }
function animateAvatar(dt) {
  const A = AV[S.gender];
  A.root.position.set(P.pos.x, 0, P.pos.z); A.root.rotation.y = P.yaw;
  const k = 1 - Math.exp(-dt * 9);
  pose.bend = lerp(pose.bend, poseT.bend, k); pose.sy = lerp(pose.sy, poseT.sy, k);
  A.upper.rotation.x = pose.bend;
  A.body.scale.y = pose.sy * (1 + (P.moving ? Math.sin(T * (P.running ? 15 : 9)) * 0.03 : 0));
  A.ring.material.opacity = 0.7 + Math.sin(T * 3) * 0.25;
}
function updateGuides() {
  const show = S.mode === 'follow' && S.target && !S.prayer;
  wp.visible = !!show; arrow.visible = false; if (!show) return;
  wp.position.set(S.target.x, 0, S.target.z);
  const s = 1 + Math.sin(T * 3) * 0.1; wpRing.scale.set(s, 1, s);
  wpBeam.material.opacity = 0.14 + Math.sin(T * 2) * 0.06; wpBeam.visible = S.phase !== 'sai';
  const dx = S.target.x - P.pos.x, dz = S.target.z - P.pos.z, d = Math.hypot(dx, dz);
  if (d > 4 && S.phase !== 'tawaf') { arrow.visible = true; arrow.position.set(P.pos.x + dx/d*2.3, 0.06, P.pos.z + dz/d*2.3); arrow.rotation.y = Math.atan2(-dx, -dz); }
}

/* ===== CAMERA ===== */
let camLookUp = 0;
const head = new THREE.Vector3(), desired = new THREE.Vector3(), probe = new THREE.Vector3(), look = new THREE.Vector3(), dir = new THREE.Vector3();
function camDir(yaw, out) { const cp = Math.cos(cam.pitch); return out.set(Math.sin(yaw)*cp, Math.sin(cam.pitch), Math.cos(yaw)*cp); }
function camFrac(yaw) {
  camDir(yaw, dir); const steps = 18;
  for (let i = 1; i <= steps; i++) { probe.copy(dir).multiplyScalar(cam.dist * i / steps).add(head); if (!cur.camOK(probe)) return Math.max(0.12, (i-1)/steps - 0.02); }
  return 1;
}
function updateCamera(dt) {
  if (S.mode === 'orbit') { const a = T * 0.045; camera.position.set(Math.cos(a)*31, 17, Math.sin(a)*31); camera.lookAt(0,5,0); S.camSnap = true; return; }
  if ((P.moving || S.prayer) && T - cam.lastUser > 1.6) cam.yaw = lerpAngle(cam.yaw, P.yaw + Math.PI, 1 - Math.exp(-dt * (S.prayer ? 2.5 : 1.5)));
  head.set(P.pos.x, 1.6, P.pos.z);
  let f = camFrac(cam.yaw);
  if (f < 0.5 && T - cam.lastUser > 1.0) {
    let best = 0, bf = f;
    for (const o of [0.6,-0.6,1.2,-1.2,1.8,-1.8,2.4,-2.4]) { const ff = camFrac(cam.yaw + o); if (ff > bf + 0.12) { bf = ff; best = o; } }
    if (best) { cam.yaw = S.camSnap ? cam.yaw + best : lerpAngle(cam.yaw, cam.yaw + best, 1 - Math.exp(-dt*4)); f = camFrac(cam.yaw); }
  }
  camDir(cam.yaw, desired); desired.multiplyScalar(cam.dist * f).add(head);
  if (S.camSnap) { camera.position.copy(desired); S.camSnap = false; } else camera.position.lerp(desired, 1 - Math.exp(-dt*10));
  let lookUp = 0;
  if (S.phase === 'approach') { const g = polar(R_OUT, GATE_ANG[ROUTES[S.route].no]), d = Math.hypot(P.pos.x - g.x, P.pos.z - g.z); lookUp = clamp((40-d)/30, 0, 1) * 4.5; }
  camLookUp = lerp(camLookUp, lookUp, 1 - Math.exp(-dt*3));
  look.set(P.pos.x, 3.0 + camLookUp - (1 - pose.sy)*1.5, P.pos.z); camera.lookAt(look);
}

/* ===== INPUT ===== */
window.addEventListener('keydown', e => {
  if (['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.code) && !S.modal) e.preventDefault();
  K.add(e.code);
  if (e.code === 'KeyE' && !S.modal && S.actionFn && !S.locked) S.actionFn();
});
window.addEventListener('keyup', e => K.delete(e.code));
window.addEventListener('blur', () => { K.clear(); P.runHeld = false; runBtn.classList.remove('held'); });
const ptrs = new Map();
canvas.addEventListener('pointerdown', e => {
  canvas.setPointerCapture(e.pointerId);
  if (e.pointerType !== 'mouse' && e.clientX < window.innerWidth * 0.45 && !joy.active && S.mode === 'follow') {
    Object.assign(joy, { active:true, id:e.pointerId, ox:e.clientX, oy:e.clientY, x:0, y:0 });
    joyEl.style.left = e.clientX + 'px'; joyEl.style.top = e.clientY + 'px'; knobEl.style.transform = ''; joyEl.hidden = false; joyHint.hidden = true;
  } else ptrs.set(e.pointerId, { x: e.clientX, y: e.clientY });
});
canvas.addEventListener('pointermove', e => {
  if (joy.active && e.pointerId === joy.id) {
    let dx = e.clientX - joy.ox, dy = e.clientY - joy.oy; const R = 52, l = Math.hypot(dx, dy);
    if (l > R) { dx *= R/l; dy *= R/l; }
    joy.x = dx/R; joy.y = -dy/R; knobEl.style.transform = `translate(${dx}px,${dy}px)`; return;
  }
  const pp = ptrs.get(e.pointerId); if (!pp) return;
  if (ptrs.size === 2) {
    const other = [...ptrs.entries()].find(([id]) => id !== e.pointerId)[1];
    const before = Math.hypot(pp.x-other.x, pp.y-other.y), after = Math.hypot(e.clientX-other.x, e.clientY-other.y);
    if (after > 0) cam.dist = clamp(cam.dist * before/after, 4, 18);
  } else { cam.yaw -= (e.clientX-pp.x)*0.006; cam.pitch = clamp(cam.pitch+(e.clientY-pp.y)*0.004, 0.08, 1.25); cam.lastUser = T; }
  pp.x = e.clientX; pp.y = e.clientY;
});
const endPtr = e => { if (joy.active && e.pointerId===joy.id) { joy.active=false; joy.x=joy.y=0; joyEl.hidden=true; joyHint.hidden=!(S.mode==='follow'&&isTouch); } ptrs.delete(e.pointerId); };
canvas.addEventListener('pointerup', endPtr); canvas.addEventListener('pointercancel', endPtr);
canvas.addEventListener('wheel', e => { e.preventDefault(); cam.dist = clamp(cam.dist*(1+Math.sign(e.deltaY)*0.08),4,18); }, { passive: false });
canvas.addEventListener('contextmenu', e => e.preventDefault());
actBtn.addEventListener('click', () => { if (S.actionFn && !S.modal && !S.locked) S.actionFn(); });
autoBtn.addEventListener('click', () => { S.auto = !S.auto; P.autoR = null; P.stuck = 0; autoBtn.setAttribute('aria-pressed', String(S.auto)); });
const runOn = e => { e.preventDefault(); P.runHeld = true; runBtn.classList.add('held'); };
const runOff = () => { P.runHeld = false; runBtn.classList.remove('held'); };
runBtn.addEventListener('pointerdown', runOn); runBtn.addEventListener('pointerup', runOff); runBtn.addEventListener('pointerleave', runOff); runBtn.addEventListener('pointercancel', runOff);
runBtn.addEventListener('keydown', e => { if (e.code === 'Space' || e.code === 'Enter') runOn(e); });
runBtn.addEventListener('keyup', runOff);
document.getElementById('helpBtn').addEventListener('click', () => { if (!S.modal) showCard(cardHelp); });
document.querySelectorAll('.langs [data-lang]').forEach(b => b.addEventListener('click', () => setLang(b.dataset.lang)));
window.addEventListener('resize', () => { mapPx = 0; });

/* ===== LANGUAGE ===== */
function setLang(l) {
  lang = l; store.set('lang', l);
  document.documentElement.lang = l === 'bn' ? 'bn' : 'en';
  document.body.classList.toggle('bn', l === 'bn');
  document.title = t('title');
  document.querySelectorAll('.langs [data-lang]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.lang === l)));
  document.getElementById('brand').innerHTML = `${t('brand')}<small>TravHub Global Limited</small>`;
  autoBtn.textContent = t('auto'); runBtn.textContent = t('run');
  joyHint.textContent = t('move');
  if (S.objKey)    { S.objText = null; setObjective(S.objKey, S.objVars); }
  if (S.hintKey)   { const h = S.hintKey; S.hintKey = null; setHint(h); }
  if (S.stripKey)  { const [d, k] = S.stripKey.split('|'); S.stripKey = null; setStrip(d, k); }
  if (S.actionKey)   actBtn.textContent = t(S.actionKey);
  lastCounter = ''; renderNear(); renderSteps(); renderMapGate();
  if (S.prayer)      renderPray();
  if (S.cardBuilder) renderCard();
  if (typeof refreshToolsLang === 'function') refreshToolsLang();
}

/* ===== MAIN LOOP ===== */
let last = performance.now();
function frame(now) {
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
  if (follow && (distTick -= dt) <= 0) { distTick = 0.2; renderDist(); }
  renderer.render(world, camera);
}

/* ===== INIT ===== */
async function init() {
  resize();
  try {
    await Promise.race([
      Promise.all([document.fonts.load('700 64px Amiri'), document.fonts.load('600 40px "Hind Siliguri"'), document.fonts.load('400 30px "Tiro Bangla"')]),
      new Promise(r => setTimeout(r, 2500))
    ]);
  } catch (e) {}
  cur = buildWorld(world, isTouch);
  world.add(cur.group); world.background = cur.bg; world.fog = cur.fog;
  world.add(AV.m.root, AV.f.root, arrow, wp);
  setLang(lang);
  const savedPhase = S.phase;
  if (savedPhase && savedPhase !== 'intro') goTo(savedPhase);
  else setPhase('intro');
  document.getElementById('loading').hidden = true;
  requestAnimationFrame(frame);
}
init();