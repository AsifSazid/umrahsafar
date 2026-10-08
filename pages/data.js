'use strict';

/* ===== DUAS ===== */
const DUA = {
  niyyah: {
    ar: 'اللَّهُمَّ إِنِّي أُرِيدُ الْعُمْرَةَ فَيَسِّرْهَا لِي وَتَقَبَّلْهَا مِنِّي',
    tr: { en: 'Allāhumma innī urīdul-ʿumrata fa-yassirhā lī wa taqabbalhā minnī.', bn: 'আল্লাহুম্মা ইন্নি উরিদুল উমরাতা ফাইয়াসসিরহা লি ওয়া তাকাব্বালহা মিন্নি।' },
    m: { en: 'O Allah, I intend to perform Umrah, so make it easy for me and accept it from me.', bn: 'হে আল্লাহ, আমি উমরাহর নিয়ত করছি; আপনি তা আমার জন্য সহজ করে দিন এবং কবুল করুন।' }
  },
  talbiyah: {
    ar: 'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ، لَبَّيْكَ لَا شَرِيكَ لَكَ لَبَّيْكَ، إِنَّ الْحَمْدَ وَالنِّعْمَةَ لَكَ وَالْمُلْكَ، لَا شَرِيكَ لَكَ',
    tr: { en: 'Labbayk Allāhumma labbayk, labbayka lā sharīka laka labbayk, innal-ḥamda wan-niʿmata laka wal-mulk, lā sharīka lak.', bn: 'লাব্বাইকা আল্লাহুম্মা লাব্বাইক, লাব্বাইকা লা শারিকা লাকা লাব্বাইক, ইন্নাল হামদা ওয়ান নিয়ামাতা লাকা ওয়াল মুলক, লা শারিকা লাক।' },
    m: { en: 'Here I am O Allah, here I am. You have no partner, here I am. All praise, blessings and sovereignty are Yours.', bn: 'আমি হাজির, হে আল্লাহ, আমি হাজির। আপনার কোনো শরিক নেই। নিশ্চয়ই সকল প্রশংসা, নিয়ামত ও রাজত্ব আপনারই।' }
  },
  enter: {
    ar: 'بِسْمِ اللَّهِ، وَالصَّلَاةُ وَالسَّلَامُ عَلَىٰ رَسُولِ اللَّهِ، اللَّهُمَّ افْتَحْ لِي أَبْوَابَ رَحْمَتِكَ',
    tr: { en: 'Bismillāh, waṣ-ṣalātu was-salāmu ʿalā Rasūlillāh. Allāhummaftaḥ lī abwāba raḥmatik.', bn: 'বিসমিল্লাহ, ওয়াস সালাতু ওয়াস সালামু আলা রাসূলিল্লাহ। আল্লাহুম্মাফতাহলি আবওয়াবা রাহমাতিক।' },
    m: { en: 'In the name of Allah, peace and blessings upon the Messenger of Allah. O Allah, open for me the doors of Your mercy.', bn: 'আল্লাহর নামে, রাসূলুল্লাহর ওপর দরুদ ও সালাম। হে আল্লাহ, আমার জন্য আপনার রহমতের দরজাগুলো খুলে দিন।' }
  },
  istilam: {
    ar: 'بِسْمِ اللَّهِ، اللَّهُ أَكْبَرُ',
    tr: { en: 'Bismillāhi, Allāhu akbar.', bn: 'বিসমিল্লাহি আল্লাহু আকবার।' },
    m: { en: 'In the name of Allah. Allah is the Greatest.', bn: 'আল্লাহর নামে, আল্লাহ সর্বশ্রেষ্ঠ।' }
  },
  rabbana: {
    ar: 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
    tr: { en: 'Rabbanā ātinā fid-dunyā ḥasanatan wa fil-ākhirati ḥasanatan wa qinā ʿadhāban-nār.', bn: 'রাব্বানা আতিনা ফিদ্দুনইয়া হাসানাতাও ওয়া ফিল আখিরাতি হাসানাতাও ওয়া কিনা আযাবান নার।' },
    m: { en: 'Our Lord, give us good in this world and good in the Hereafter, and protect us from the Fire. (2:201)', bn: 'হে আমাদের রব, দুনিয়াতে কল্যাণ দিন, আখিরাতেও কল্যাণ দিন এবং জাহান্নাম থেকে রক্ষা করুন। (২:২০১)' }
  },
  maqam: {
    ar: 'وَاتَّخِذُوا مِنْ مَقَامِ إِبْرَاهِيمَ مُصَلًّى',
    tr: { en: 'Wattakhidhū mim-maqāmi Ibrāhīma muṣallā.', bn: 'ওয়াত্তাখিযু মিম মাকামি ইবরাহিমা মুসাল্লা।' },
    m: { en: 'And take the standing place of Ibrahim as a place of prayer. (2:125)', bn: 'আর তোমরা মাকামে ইবরাহিমকে নামাজের স্থান হিসেবে গ্রহণ করো। (২:১২৫)' }
  },
  zamzam: {
    ar: 'اللَّهُمَّ إِنِّي أَسْأَلُكَ عِلْمًا نَافِعًا، وَرِزْقًا وَاسِعًا، وَشِفَاءً مِنْ كُلِّ دَاءٍ',
    tr: { en: "Allāhumma innī as'aluka ʿilman nāfiʿā, wa rizqan wāsiʿā, wa shifā'an min kulli dā'.", bn: 'আল্লাহুম্মা ইন্নি আসআলুকা ইলমান নাফিআ, ওয়া রিযকান ওয়াসিআ, ওয়া শিফাআম মিন কুল্লি দা।' },
    m: { en: 'O Allah, I ask You for beneficial knowledge, plentiful provision, and healing from every illness.', bn: 'হে আল্লাহ, আমি আপনার কাছে উপকারী জ্ঞান, প্রশস্ত রিজিক এবং সব রোগ থেকে আরোগ্য চাই।' }
  },
  safa: {
    ar: 'إِنَّ الصَّفَا وَالْمَرْوَةَ مِنْ شَعَائِرِ اللَّهِ ۝ أَبْدَأُ بِمَا بَدَأَ اللَّهُ بِهِ',
    tr: { en: "Innaṣ-ṣafā wal-marwata min shaʿā'irillāh. Abda'u bimā bada'allāhu bih.", bn: 'ইন্নাস সাফা ওয়াল মারওয়াতা মিন শাআইরিল্লাহ। আবদাউ বিমা বাদাআল্লাহু বিহি।' },
    m: { en: 'Indeed, Safa and Marwa are among the symbols of Allah. (2:158) I begin with what Allah began with.', bn: 'নিশ্চয়ই সাফা ও মারওয়া আল্লাহর নিদর্শনের অন্তর্ভুক্ত। (২:১৫৮) আল্লাহ যা দিয়ে শুরু করেছেন, আমিও তা দিয়ে শুরু করছি।' }
  },
  dhikr: {
    ar: 'اللَّهُ أَكْبَرُ، اللَّهُ أَكْبَرُ، اللَّهُ أَكْبَرُ، لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ',
    tr: { en: 'Allāhu akbar ×3. Lā ilāha illallāhu waḥdahū lā sharīka lah, lahul-mulku wa lahul-ḥamd, wa huwa ʿalā kulli shayʾin qadīr.', bn: 'আল্লাহু আকবার ×৩। লা ইলাহা ইল্লাল্লাহু ওয়াহদাহু লা শারিকা লাহু, লাহুল মুলকু ওয়া লাহুল হামদু, ওয়া হুয়া আলা কুল্লি শাইয়িন কাদির।' },
    m: { en: 'Allah is the Greatest (×3). There is no god but Allah alone. His is the dominion and His is the praise, and He has power over all things.', bn: 'আল্লাহ সর্বশ্রেষ্ঠ (×৩)। আল্লাহ ছাড়া কোনো ইলাহ নেই। রাজত্ব ও প্রশংসা তাঁরই, এবং তিনি সবকিছুর ওপর ক্ষমতাবান।' }
  },
  green: {
    ar: 'رَبِّ اغْفِرْ وَارْحَمْ، إِنَّكَ أَنْتَ الْأَعَزُّ الْأَكْرَمُ',
    tr: { en: 'Rabbighfir warḥam, innaka antal-aʿazzul-akram.', bn: 'রাব্বিগফির ওয়ারহাম, ইন্নাকা আনতাল আআযযুল আকরাম।' },
    m: { en: 'My Lord, forgive and have mercy. You are the Most Mighty, the Most Generous.', bn: 'হে আমার রব, ক্ষমা করুন ও দয়া করুন। নিশ্চয়ই আপনি সর্বাধিক পরাক্রমশালী, সর্বাধিক মহানুভব।' }
  },
  exit: {
    ar: 'اللَّهُمَّ إِنِّي أَسْأَلُكَ مِنْ فَضْلِكَ',
    tr: { en: "Allāhumma innī as'aluka min faḍlik.", bn: 'আল্লাহুম্মা ইন্নি আসআলুকা মিন ফাদলিক।' },
    m: { en: 'O Allah, I ask You of Your bounty.', bn: 'হে আল্লাহ, আমি আপনার অনুগ্রহ প্রার্থনা করি।' }
  }
};

/* ===== GATES ===== */
const GATES = {
  1:   { ar: 'باب الملك عبدالعزيز', n: { en: 'King Abdulaziz Gate', bn: 'বাদশাহ আবদুল আজিজ গেট' } },
  45:  { ar: 'باب الفتح',            n: { en: 'Al-Fath Gate',        bn: 'বাবুল ফাতহ' } },
  62:  { ar: 'باب العمرة',           n: { en: 'Umrah Gate',          bn: 'বাবুল উমরাহ' } },
  79:  { ar: 'باب الملك فهد',        n: { en: 'King Fahd Gate',      bn: 'বাদশাহ ফাহাদ গেট' } },
  100: { ar: 'باب الملك عبدالله',    n: { en: 'King Abdullah Gate',  bn: 'বাদশাহ আবদুল্লাহ গেট' } }
};

/* ===== GLOSSARY ===== */
const GLOSSARY = [
  { ar: 'إِحْرَام',          en: 'Ihram',          bn: 'ইহরাম — উমরাহ বা হজের সময় পালনীয় পবিত্র অবস্থা ও পোশাক।' },
  { ar: 'طَوَاف',            en: 'Tawaf',           bn: 'তাওয়াফ — কাবাকে সাত বার ঘড়ির কাঁটার বিপরীতে প্রদক্ষিণ করা।' },
  { ar: 'سَعْي',             en: "Sa'i",            bn: "সাঈ — সাফা ও মারওয়া পাহাড়ের মাঝে সাত বার চলাচল করা।" },
  { ar: 'مِيقَات',           en: 'Miqat',           bn: 'মিকাত — যে সীমারেখার আগে ইহরাম বাঁধতে হয়।' },
  { ar: 'حَجَر الْأَسْوَد', en: 'Hajar al-Aswad',  bn: 'হাজরে আসওয়াদ — কাবার পূর্ব কোণে স্থাপিত কালো পাথর।' },
  { ar: 'مَقَام إِبْرَاهِيم',en: 'Maqam Ibrahim',  bn: 'মাকামে ইবরাহিম — কাবা নির্মাণের সময় ইবরাহিম (আ.) যে পাথরে দাঁড়িয়েছিলেন।' },
  { ar: 'زَمْزَم',           en: 'Zamzam',          bn: 'জমজম — মসজিদুল হারামের পবিত্র কূপ।' },
  { ar: 'حِجْر إِسْمَاعِيل',en: 'Hijr Ismail',     bn: 'হিজরে ইসমাইল (হাতিম) — কাবার অর্ধবৃত্তাকার অংশ।' },
  { ar: 'رُكْن الْيَمَانِي', en: 'Rukn Yamani',     bn: 'রুকনে ইয়ামানি — কাবার দক্ষিণ-পশ্চিম কোণ।' },
  { ar: 'مَسْعَى',           en: "Mas'a",           bn: "মাসআ — সাঈর জন্য নির্ধারিত হলঘর।" },
  { ar: 'حَلْق',             en: 'Halq',            bn: 'হলক — পুরুষের মাথা মুণ্ডন, উমরাহ শেষে করতে হয়।' },
  { ar: 'تَقْصِير',          en: 'Taqsir',          bn: 'কসর — চুল সামান্য ছোট করা, হলকের বিকল্প।' },
  { ar: 'رَمَل',             en: 'Raml',            bn: 'রমল — তাওয়াফের প্রথম তিন চক্করে পুরুষের দ্রুত হাঁটা।' },
  { ar: 'إِضْطِبَاع',        en: 'Idtiba',          bn: 'ইযতিবা — তাওয়াফের সময় ডান কাঁধ খোলা রাখা (পুরুষ)।' },
  { ar: 'اِسْتِلَام',        en: 'Istilam',         bn: 'ইস্তিলাম — হাজরে আসওয়াদের দিকে হাত তুলে ইশারা করা বা চুম্বন করা।' },
  { ar: 'تَلْبِيَة',         en: 'Talbiyah',        bn: 'তালবিয়া — "লাব্বাইকা আল্লাহুম্মা লাব্বাইক..." এই দোয়া।' },
  { ar: 'مَطَاف',            en: 'Mataf',           bn: 'মাতাফ — কাবার চারপাশের তাওয়াফের মার্বেল এলাকা।' },
  { ar: 'مُلْتَزَم',         en: 'Multazam',        bn: 'মুলতাযাম — কাবার দরজা ও হাজরে আসওয়াদের মাঝের দেয়াল।' },
  { ar: 'كِسْوَة',           en: 'Kiswa',           bn: 'কিসওয়া — কাবার কালো সুতার আবরণ।' },
  { ar: 'وُضُوء',            en: 'Wudu',            bn: 'অজু — নামাজ ও তাওয়াফের আগে পবিত্রতার জন্য অঙ্গপ্রক্ষালন।' },
  { ar: 'مَحْرَم',           en: 'Mahram',          bn: 'মাহরাম — মহিলার সফরসঙ্গী নিকট পুরুষ আত্মীয়।' },
  { ar: 'مَذْهَب',           en: 'Madhab',          bn: 'মাযহাব — ইসলামি ফিকহের চারটি প্রধান ধারার একটি।' },
];

/* ===== COMMON MISTAKES ===== */
const MISTAKES = {
  en: [
    { t: 'Entering Ihram after the Miqat', d: 'Flying from Dhaka, some pilgrims wait until Jeddah to put on Ihram — by then they have already passed the Miqat. This requires a dam (penalty).', fix: '✓ Put on Ihram before boarding, or at the Miqat announcement on the flight.' },
    { t: 'Wrong direction in Tawaf', d: 'Some pilgrims accidentally walk clockwise, especially in crowded areas.', fix: '✓ Always keep the Kaaba on your LEFT. The crowd naturally flows anticlockwise.' },
    { t: 'Walking through Hijr Ismail', d: 'Hijr Ismail is part of the Kaaba. If you walk through it, that round does not count.', fix: '✓ Always walk around the outside of the semicircular Hijr wall.' },
    { t: 'Skipping Idtiba during Tawaf', d: 'Men sometimes forget to keep the right shoulder uncovered for all seven rounds.', fix: '✓ Keep the rida under your right armpit before you begin Tawaf.' },
    { t: "Counting Sa'i laps wrongly", d: "Some pilgrims end on Safa after 7 counts — but Sa'i must end on Marwa.", fix: '✓ Lap 1: Safa → Marwa. Lap 2: Marwa → Safa. Lap 7 ends on Marwa.' },
    { t: 'Performing Tawaf without wudu', d: 'Wudu is required for Tawaf. Breaking wudu mid-Tawaf means that round is invalid.', fix: '✓ Renew wudu before entering the mosque. If it breaks, go make wudu and resume.' },
    { t: 'Skipping Maqam Ibrahim prayer', d: 'After Tawaf, many pilgrims skip the two rakats at Maqam Ibrahim.', fix: '✓ These two rakats are wajib. Pray them even if you have to move elsewhere in the mosque.' },
    { t: 'Women wearing niqab during Ihram', d: 'Women must keep their face uncovered during Ihram — niqab is not permitted.', fix: '✓ Use a visor hat if needed for sun protection without touching the face.' },
  ],
  bn: [
    { t: 'মিকাতের পর ইহরাম বাঁধা', d: 'ঢাকা থেকে আসা অনেক হাজি জেদ্দায় পৌঁছে ইহরাম পরেন — কিন্তু তখন মিকাত পার হয়ে গেছে। এতে দম দিতে হয়।', fix: '✓ বিমানে ওঠার আগেই ইহরাম পরুন, অথবা মিকাতের ঘোষণার সময়।' },
    { t: 'তাওয়াফে ভুল দিকে হাঁটা', d: 'ভিড়ের মধ্যে অনেকে ঘড়ির কাঁটার দিকে হেঁটে ফেলেন।', fix: '✓ সবসময় কাবাকে বাম পাশে রাখুন।' },
    { t: 'হাতিমের ভেতর দিয়ে হাঁটা', d: 'হিজরে ইসমাইল কাবারই অংশ। এর ভেতর দিয়ে হাঁটলে সেই চক্কর গণনায় আসে না।', fix: '✓ সবসময় অর্ধবৃত্তাকার হাতিমের দেয়ালের বাইরে দিয়ে হাঁটুন।' },
    { t: 'তাওয়াফে ইযতিবা ভুলে যাওয়া', d: 'পুরুষরা কখনো কখনো সাত চক্কর ডান কাঁধ খোলা রাখতে ভুলে যান।', fix: '✓ তাওয়াফ শুরুর আগেই রিদা ডান বগলের নিচে রাখুন।' },
    { t: 'সাঈর গণনায় ভুল', d: '৭ গণনা শেষে সাফায় থেকে যান — কিন্তু সাঈ মারওয়ায় শেষ হওয়া আবশ্যক।', fix: '✓ চক্কর ১: সাফা → মারওয়া। চক্কর ৭ মারওয়ায় শেষ।' },
    { t: 'অজু ছাড়া তাওয়াফ', d: 'তাওয়াফের জন্য অজু ফরজ। মাঝে ভেঙে গেলে সেই চক্কর বাতিল।', fix: '✓ মসজিদে প্রবেশের আগে অজু করুন।' },
    { t: 'মাকামে ইবরাহিমের নামাজ বাদ দেওয়া', d: 'তাওয়াফের পর অনেকে দুই রাকাত নামাজ পড়তে ভুলে যান।', fix: '✓ এই দুই রাকাত ওয়াজিব। মসজিদের যেকোনো জায়গায় পড়া যাবে।' },
    { t: 'ইহরাম অবস্থায় নারীদের নিকাব পরা', d: 'ইহরাম অবস্থায় নারীদের মুখ খোলা রাখতে হয়।', fix: '✓ রোদ থেকে বাঁচতে ভিজার হ্যাট ব্যবহার করতে পারেন।' },
  ]
};

/* ===== MADINAH PLACES ===== */
const MADINAH = {
  en: [
    { ar: 'المسجد النبوي',   n: "Al-Masjid an-Nabawi",       d: "The second holiest mosque in Islam, built by the Prophet ﷺ himself. The Green Dome marks his resting place. Pray here and send salawat." },
    { ar: 'الروضة الشريفة', n: "Ar-Rawdah ash-Sharifah",     d: "The area between the Prophet's ﷺ pulpit and his grave, said to be a garden from the gardens of Paradise." },
    { ar: 'مسجد قباء',       n: 'Masjid Quba',               d: "The first mosque built in Islam. Praying two rakats here equals the reward of an Umrah according to a hadith." },
    { ar: 'البقيع',          n: "Jannat al-Baqi",            d: "Madinah's main cemetery, where many Companions and members of the Prophet's ﷺ family are buried." },
    { ar: 'مسجد القبلتين',  n: 'Masjid al-Qiblatayn',       d: "The mosque of two qiblas, where the direction of prayer was changed from Jerusalem to Makkah." },
    { ar: 'جبل أحد',         n: 'Jabal Uhud',                d: "The mountain where the Battle of Uhud was fought. The grave of Sayyiduna Hamza (RA) is here." },
  ],
  bn: [
    { ar: 'المسجد النبوي',   n: 'মসজিদে নববী',               d: 'ইসলামের দ্বিতীয় পবিত্রতম মসজিদ। সবুজ গম্বুজের নিচে নবী ﷺ-এর পবিত্র কবর। এখানে নামাজ পড়ুন এবং দরুদ পাঠান।' },
    { ar: 'الروضة الشريفة', n: 'রওজাতুশ শরিফা',              d: "নবী ﷺ-এর মিম্বর ও পবিত্র কবরের মাঝের অংশ। হাদিসে একে জান্নাতের বাগান বলা হয়েছে।" },
    { ar: 'مسجد قباء',       n: 'মসজিদে কুবা',                d: 'ইসলামের প্রথম মসজিদ। এখানে দুই রাকাত নামাজ পড়লে একটি উমরাহর সওয়াব পাওয়া যায়।' },
    { ar: 'البقيع',          n: 'জান্নাতুল বাকি',            d: 'মদিনার প্রধান কবরস্থান, যেখানে অনেক সাহাবি শায়িত আছেন।' },
    { ar: 'مسجد القبلتين',  n: 'মসজিদুল কিবলাতাইন',          d: 'দুই কিবলার মসজিদ — যেখানে কিবলা জেরুসালেম থেকে মক্কায় পরিবর্তিত হয়েছিল।' },
    { ar: 'جبل أحد',         n: 'জাবালে উহুদ',                d: 'উহুদের যুদ্ধের স্থান। হযরত হামজা (রা.)-এর কবর এখানে।' },
  ]
};

/* ===== PACKING CHECKLIST ===== */
const CHECKLIST = {
  en: {
    'Ihram': ['Ihram cloth (2 white sheets) — men', 'White open-top sandals — men', 'Modest loose clothing — women', 'Hijab (multiple, light colour) — women', 'Safety pins for ihram', 'Money belt / neck pouch'],
    'Documents': ['Passport (6+ months validity)', 'Saudi visa / Umrah permit', 'Airline tickets (printed)', 'Hotel bookings (printed)', 'Travel insurance documents', 'Emergency contact card', 'Vaccination certificate (meningitis)'],
    'Health': ['Prescribed medications (with doctor letter)', 'Paracetamol / ibuprofen', 'Oral rehydration salts', 'Blister plasters', 'Unscented soap and shampoo', 'Unscented moisturiser', 'Sunscreen (fragrance-free)', 'Small umbrella or hat'],
    'Essentials': ['Small Quran', 'Tasbeeh (prayer beads)', 'Dua booklet', 'Phone with offline maps', 'Portable charger', 'Universal adapter', 'Small backpack', 'Water bottle', 'Comfortable walking shoes'],
  },
  bn: {
    'ইহরাম': ['ইহরামের কাপড় (২টি সাদা চাদর) — পুরুষ', 'সাদা স্যান্ডেল (পায়ের ওপর খোলা) — পুরুষ', 'ঢিলেঢালা শালীন পোশাক — নারী', 'হিজাব (একাধিক, হালকা রং) — নারী', 'ইহরামের জন্য সেফটি পিন', 'মানি বেল্ট / গলার পাউচ'],
    'কাগজপত্র': ['পাসপোর্ট (৬+ মাস মেয়াদ)', 'সৌদি ভিসা / উমরাহ পারমিট', 'বিমানের টিকিট (প্রিন্ট করা)', 'হোটেল বুকিং (প্রিন্ট করা)', 'ট্রাভেল ইন্স্যুরেন্স কাগজ', 'ইমার্জেন্সি কন্ট্যাক্ট কার্ড', 'মেনিনজাইটিস ভ্যাকসিন সার্টিফিকেট'],
    'স্বাস্থ্য': ['প্রেসক্রাইবড ওষুধ (ডাক্তারের চিঠিসহ)', 'প্যারাসিটামল / আইবুপ্রোফেন', 'স্যালাইন', 'ফোস্কার প্লাস্টার', 'সুগন্ধিমুক্ত সাবান ও শ্যাম্পু', 'সুগন্ধিমুক্ত ময়েশ্চারাইজার', 'সানস্ক্রিন (সুগন্ধিমুক্ত)', 'ছোট ছাতা বা টুপি'],
    'প্রয়োজনীয়': ['ছোট কুরআন', 'তাসবিহ', 'দোয়ার বই', 'অফলাইন ম্যাপসহ ফোন', 'পোর্টেবল চার্জার', 'ইউনিভার্সাল অ্যাডাপ্টার', 'ছোট ব্যাকপ্যাক', 'পানির বোতল', 'আরামদায়ক হাঁটার জুতা'],
  }
};

/* ===== IHRAM STEPS ===== */
const IHRAM_STEPS = {
  m: {
    en: [
      { t: 'Clip nails & groom', d: 'Cut your nails, trim unwanted hair, and take a full ghusl. Apply no perfume after this — not to your body, not to your Ihram cloth.' },
      { t: 'Put on Ihram garments', d: 'Wrap the izar (lower sheet) around your waist to the ankles. Drape the rida (upper sheet) over both shoulders. Wear sandals that leave the top of the foot exposed.' },
      { t: 'Pray 2 rakats (if not makruh time)', d: 'Cover your head for the prayer only. Recite Surah al-Kafirun in the first rakat and Surah al-Ikhlas in the second.' },
      { t: 'Make intention (niyyah) at the Miqat', d: 'As you cross the Miqat, make the niyyah for Umrah in your heart and say the intention dua.' },
      { t: 'Begin the Talbiyah aloud', d: 'Say the Talbiyah aloud, repeatedly, until you start Tawaf. Avoid perfume, cutting hair or nails, marital relations, arguing, and hunting.' },
      { t: 'Idtiba before Tawaf', d: 'Pass the rida under your right armpit and over your left shoulder, leaving the right shoulder bare. Keep it this way for all 7 rounds.' },
    ],
    bn: [
      { t: 'নখ কাটুন ও পরিচ্ছন্ন হন', d: 'নখ কাটুন, অপ্রয়োজনীয় চুল পরিষ্কার করুন এবং পূর্ণ গোসল করুন। এরপর শরীরে বা ইহরামের কাপড়ে কোনো সুগন্ধি লাগাবেন না।' },
      { t: 'ইহরামের পোশাক পরুন', d: 'ইজার কোমরে পেঁচিয়ে গোড়ালি পর্যন্ত নামান। রিদা উভয় কাঁধের উপর ফেলুন। পায়ের ওপরের অংশ খোলা থাকে এমন স্যান্ডেল পরুন।' },
      { t: 'দুই রাকাত নামাজ পড়ুন (মাকরুহ সময় না হলে)', d: 'শুধু নামাজের জন্য মাথা ঢাকুন। প্রথম রাকাতে সূরা কাফিরুন ও দ্বিতীয় রাকাতে সূরা ইখলাস পড়ুন।' },
      { t: 'মিকাতে নিয়ত করুন', d: 'মিকাত পার হওয়ার সময় মনে মনে উমরাহর নিয়ত করুন এবং নিয়তের দোয়া পড়ুন।' },
      { t: 'উচ্চস্বরে তালবিয়া পড়ুন', d: 'তাওয়াফ শুরু করা পর্যন্ত বারবার তালবিয়া পড়ুন। সুগন্ধি, চুল-নখ কাটা, দাম্পত্য মিলন ও ঝগড়া থেকে বিরত থাকুন।' },
      { t: 'তাওয়াফের আগে ইযতিবা করুন', d: 'রিদা ডান বগলের নিচ দিয়ে বাম কাঁধের উপর ফেলুন, ডান কাঁধ খোলা রাখুন। পুরো সাত চক্কর এভাবেই রাখুন।' },
    ]
  },
  f: {
    en: [
      { t: 'Clip nails & take ghusl', d: 'Cut your nails, remove unwanted hair, and take a full ghusl. A woman on her period can still enter Ihram — she waits until she is pure before Tawaf.' },
      { t: 'Wear your regular modest clothing', d: 'Wear any colour of loose, modest clothing covering everything except your face and hands. Niqab and gloves are not permitted during Ihram.' },
      { t: 'Pray 2 rakats (if not makruh time)', d: 'Pray 2 rakats for Ihram if the time is not disliked for prayer.' },
      { t: 'Make intention (niyyah) at the Miqat', d: 'As you cross the Miqat, make the niyyah for Umrah in your heart and say the intention dua quietly.' },
      { t: 'Begin the Talbiyah quietly', d: 'Women recite the Talbiyah in a low voice so only they themselves can hear. Keep reciting until you begin Tawaf.' },
      { t: 'Keep face and hands uncovered', d: 'Throughout Ihram your face and hands must remain uncovered. Use a visor hat if needed without it touching your skin.' },
    ],
    bn: [
      { t: 'নখ কাটুন ও গুসল করুন', d: 'নখ কাটুন, অপ্রয়োজনীয় চুল পরিষ্কার করুন, পূর্ণ গুসল করুন। মাসিক চলাকালেও ইহরাম বাঁধা যায় — পবিত্র হলে তাওয়াফ করবেন।' },
      { t: 'স্বাভাবিক শালীন পোশাক পরুন', d: 'মুখ ও হাত ছাড়া পুরো শরীর ঢাকে এমন ঢিলেঢালা পোশাক পরুন। ইহরাম অবস্থায় নিকাব ও হাতমোজা নিষিদ্ধ।' },
      { t: 'দুই রাকাত নামাজ পড়ুন (মাকরুহ সময় না হলে)', d: 'ইহরামের নিয়তে দুই রাকাত নামাজ পড়ুন।' },
      { t: 'মিকাতে নিয়ত করুন', d: 'মিকাত পার হওয়ার সময় মনে মনে উমরাহর নিয়ত করুন এবং নিচু স্বরে নিয়তের দোয়া পড়ুন।' },
      { t: 'নিচু স্বরে তালবিয়া পড়ুন', d: 'নারীরা তালবিয়া এত নিচুস্বরে পড়বেন যাতে শুধু নিজেরা শুনতে পান। তাওয়াফ শুরু না করা পর্যন্ত পড়তে থাকুন।' },
      { t: 'মুখ ও হাত খোলা রাখুন', d: 'পুরো ইহরাম অবস্থায় মুখ ও হাত অবশ্যই খোলা থাকতে হবে। রোদ থেকে বাঁচতে ভিজার হ্যাট ব্যবহার করতে পারেন।' },
    ]
  }
};

/* ===== UMRAH vs HAJJ ===== */
const COMPARE = {
  en: {
    heads: ['', 'Umrah', 'Hajj'],
    rows: [
      ['Obligation', 'Sunnah Mu\'akkadah (highly recommended); obligatory according to some scholars', 'Fard (obligatory) once in a lifetime for those who are able'],
      ['Time', 'Any time of year', 'Only during specific days of Dhul-Hijjah (8–13)'],
      ['Duration', 'Can be completed in a few hours', 'At least 5–6 days'],
      ['Key rites', "Ihram → Tawaf → 2 rakats → Zamzam → Sa'i → Halq/Taqsir", "Ihram → Mina → Arafat (wuquf) → Muzdalifah → Mina (rami, qurbani, halq) → Tawaf al-Ifadah → Sa'i"],
      ['Arafat', 'Not required', 'Wuquf at Arafat on 9 Dhul-Hijjah is the central pillar — missing it invalidates Hajj'],
      ['Mina & Muzdalifah', 'Not visited', 'Required: nights at Mina, standing at Muzdalifah'],
      ['Animal sacrifice', 'Not required', 'Required for Hajj Tamattu and Qiran'],
      ['Crowd level', 'Varies; Ramadan is busiest', 'Hajj season is the largest annual gathering of people on Earth'],
    ]
  },
  bn: {
    heads: ['', 'উমরাহ', 'হজ'],
    rows: [
      ['আবশ্যকতা', 'সুন্নতে মুয়াক্কাদা; কেউ কেউ জীবনে একবার ফরজ বলেন', 'সক্ষম মুসলিমের জন্য জীবনে একবার ফরজ'],
      ['সময়', 'বছরের যেকোনো সময়', 'শুধু জিলহজের নির্দিষ্ট দিনে (৮-১৩ তারিখ)'],
      ['সময়কাল', 'কয়েক ঘণ্টায় সম্পন্ন হয়', 'কমপক্ষে ৫-৬ দিন'],
      ['মূল আমল', 'ইহরাম → তাওয়াফ → ২ রাকাত → জমজম → সাঈ → হলক/কসর', 'ইহরাম → মিনা → আরাফাত → মুজদালিফা → মিনা → তাওয়াফুল ইফাদা → সাঈ'],
      ['আরাফাত', 'প্রয়োজন নেই', '৯ জিলহজ আরাফাতে উকুফ হজের মূল রুকন — বাদ পড়লে হজ বাতিল'],
      ['মিনা ও মুজদালিফা', 'যেতে হয় না', 'মিনায় রাত্রিযাপন ও মুজদালিফায় অবস্থান আবশ্যক'],
      ['পশু কুরবানি', 'প্রয়োজন নেই', 'হজে তামাত্তু ও কিরানে আবশ্যক'],
      ['ভিড়', 'বিভিন্ন রকম; রমজানে সবচেয়ে বেশি', 'হজ মৌসুম পৃথিবীর বৃহত্তম বার্ষিক সমাবেশ'],
    ]
  }
};