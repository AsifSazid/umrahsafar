<?php
// FILE PATH: /includes/header.php
$pageTitle       = $pageTitle       ?? 'TravHub | Premium Umrah & Hajj Packages from Bangladesh';
$pageDescription = $pageDescription ?? 'Book your Umrah journey with TravHub. Customize packages, hotels, transport and visa from Bangladesh.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Site base path — auto-detected in config.php, used by every fetch()/link
       in inline scripts so the app works unchanged whether it's installed at
       the domain root or in a subfolder like /umrah-safar. -->
  <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Poppins','ui-sans-serif','system-ui','sans-serif'] },
          colors: {
            primary:   '#1A2039',
            secondary: '#50BC81',
            navy:      '#1E2648',
            dark:      '#111625',
            emerald:   '#3AAB71',
            teal:      '#02CCFE',
          },
          animation: {
            'fade-in':    'fade-in 0.5s ease-out forwards',
            'bounce-slow':'bounce-slow 4s ease-in-out infinite',
            'slide-up':   'slide-up 0.4s ease-out forwards',
            'step-in':    'step-in 0.45s cubic-bezier(0.22,1,0.36,1) forwards',
            'step-out':   'step-out 0.25s ease-in forwards',
            'select-update': 'select-update 0.15s ease-out forwards',
            'rail-flow':  'rail-flow 2.4s linear infinite',
            'node-pulse': 'node-pulse 2.2s ease-in-out infinite',
            'count-pop':  'count-pop 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards',
          },
          keyframes: {
            'fade-in':  { from:{ opacity:'0', transform:'translateY(10px)'}, to:{opacity:'1',transform:'translateY(0)'} },
            'bounce-slow':{ '0%,100%':{transform:'translateY(0)'},'50%':{transform:'translateY(-15px)'} },
            'slide-up': { from:{ opacity:'0', transform:'translateY(20px)'}, to:{opacity:'1',transform:'translateY(0)'} },
            'step-in':  { from:{ opacity:'0', transform:'translateX(28px)'}, to:{opacity:'1',transform:'translateX(0)'} },
            'step-out': { from:{ opacity:'1', transform:'translateX(0)'}, to:{opacity:'0',transform:'translateX(-28px)'} },
            'select-update': { from:{ opacity:'0.55' }, to:{ opacity:'1' } },
            'rail-flow':{ '0%':{backgroundPosition:'0% 0'}, '100%':{backgroundPosition:'-48px 0'} },
            'node-pulse':{ '0%,100%':{boxShadow:'0 0 0 0 rgba(80,188,129,0.45)'}, '50%':{boxShadow:'0 0 0 8px rgba(80,188,129,0)'} },
            'count-pop':{ '0%':{transform:'scale(1)'}, '40%':{transform:'scale(1.08)'}, '100%':{transform:'scale(1)'} },
          }
        }
      }
    }
  </script>
  <!-- Global component styles — available on ALL pages -->
  <style type="text/tailwindcss">
    @layer base {
      body { background-color: #111625; }
    }
    @layer components {
      .glass-card  { @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl; }
      .btn-primary { @apply bg-secondary hover:bg-emerald text-primary font-semibold py-3 px-6 rounded-xl transition-all duration-300; }
      .btn-outline  { @apply border border-secondary text-secondary hover:bg-secondary hover:text-primary font-semibold py-3 px-6 rounded-xl transition-all duration-300; }
      .input-field  { @apply w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary focus:outline-none placeholder-white/20; }
      .section-label{ @apply text-secondary font-bold tracking-widest uppercase text-xs; }
      .step-node    { @apply relative w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300; }
      .step-node.active    { @apply border-secondary bg-secondary/20 text-secondary; }
      .step-node.completed { @apply border-secondary bg-secondary text-primary; }
      .step-node.pending   { @apply border-white/20 bg-white/5 text-white/30; }
      .option-card  { @apply glass-card p-5 cursor-pointer transition-all duration-200 hover:border-secondary/50 hover:bg-white/10 hover:scale-[1.015]; }
      .option-card.selected { @apply border-secondary bg-secondary/10 shadow-lg shadow-secondary/20 scale-[1.015]; }
      .accent-secondary { accent-color: #50BC81; }
      /* Clean native date input — hide the browser's own calendar icon since
         we render our own to the left, and force dark color-scheme so the
         native picker popup itself isn't a jarring white box. */
      .date-input-clean { color-scheme: dark; }
      .date-input-clean::-webkit-calendar-picker-indicator {
        background: transparent;
        color: transparent;
        cursor: pointer;
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
      }
      .date-input-clean::-webkit-datetime-edit { padding-left: 0; }

      /* ── "Live Constellation" — homepage mini-builder ── */
      .hex-panel {
        @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl relative;
        background-image:
          radial-gradient(circle at 15% 15%, rgba(80,188,129,0.06) 0%, transparent 40%),
          radial-gradient(circle at 85% 85%, rgba(2,204,254,0.05) 0%, transparent 40%);
      }
      .hex-card {
        @apply bg-white/5 border border-white/10 backdrop-blur-md cursor-pointer transition-all duration-200 relative rounded-2xl p-5;
      }
      .hex-card:hover { @apply border-secondary/50 bg-white/10; transform: scale(1.02); }
      .hex-card.selected {
        @apply border-secondary bg-secondary/10;
        box-shadow: 0 0 20px rgba(80,188,129,0.2);
        transform: scale(1.02);
      }
      .constellation-node {
        @apply relative rounded-full flex items-center justify-center transition-all duration-500;
      }
      .constellation-node.node-active {
        @apply bg-secondary text-primary;
        box-shadow: 0 0 0 4px rgba(80,188,129,0.15), 0 0 18px rgba(80,188,129,0.6);
      }
      .constellation-node.node-completed { @apply bg-secondary/70 text-primary; box-shadow: 0 0 10px rgba(80,188,129,0.4); }
      .constellation-node.node-pending   { @apply bg-white/10 text-white/30; }

      /* ── "Boarding Pass" — full package builder ── */
      .ticket-card {
        @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl cursor-pointer transition-all duration-200 relative overflow-hidden;
        border-left: 3px dashed rgba(255,255,255,0.15);
      }
      .ticket-card:hover { @apply border-secondary/40 bg-white/10; }
      .ticket-card.selected {
        @apply bg-secondary/10;
        border-left: 3px dashed #50BC81;
        box-shadow: 0 8px 24px rgba(80,188,129,0.15);
      }
      .ticket-card.selected::after {
        content: '';
        position: absolute; left: -3px; top: 50%; width: 14px; height: 14px;
        background: #111625; border-radius: 50%; transform: translateY(-50%);
        box-shadow: -2px 0 0 rgba(255,255,255,0.1) inset;
      }
      .ticket-punch { @apply flex items-center gap-1; }
      .ticket-punch span { @apply w-1.5 h-1.5 rounded-full bg-white/10; }
      .ticket-punch span.filled { @apply bg-secondary; }
      .flight-path-track {
        background: linear-gradient(90deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.08) 100%);
      }
      .flight-path-fill {
        background: linear-gradient(90deg, #50BC81, #02CCFE);
        transition: width 0.6s cubic-bezier(0.22,1,0.36,1);
      }
      .boarding-stub {
        @apply bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl relative;
      }
      .boarding-stub::before {
        content: '';
        position: absolute; left: 0; right: 0; top: 0;
        border-top: 2px dashed rgba(255,255,255,0.15);
      }
    }
  </style>
</head>
<body class="bg-dark text-white font-sans antialiased">