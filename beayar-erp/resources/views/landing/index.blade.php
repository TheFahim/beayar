<!DOCTYPE html>
<html lang="en" x-data="{ mobileOpen: false, billingYearly: false, openFaq: null }">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Beayar ERP — One platform. Every business operation. Zero chaos.</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy: #0B0F1A;
      --navy-mid: #111827;
      --navy-light: #1a2236;
      --indigo: #4F46E5;
      --indigo-bright: #6366F1;
      --indigo-glow: #818CF8;
      --teal: #14B8A6;
      --slate: #94A3B8;
      --white: #F8FAFC;
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--white);
      overflow-x: hidden;
    }
    h1, h2, h3, h4, h5, .font-display { font-family: 'Syne', sans-serif; }

    /* Grid overlay texture */
    .grid-overlay {
      background-image:
        linear-gradient(rgba(79,70,229,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(79,70,229,0.04) 1px, transparent 1px);
      background-size: 60px 60px;
    }

    /* Gradient glow blobs */
    .blob-1 {
      position: absolute; width: 600px; height: 600px; border-radius: 50%;
      background: radial-gradient(circle, rgba(79,70,229,0.18) 0%, transparent 70%);
      top: -150px; left: -150px; pointer-events: none;
    }
    .blob-2 {
      position: absolute; width: 500px; height: 500px; border-radius: 50%;
      background: radial-gradient(circle, rgba(20,184,166,0.12) 0%, transparent 70%);
      top: 100px; right: -100px; pointer-events: none;
    }
    .blob-3 {
      position: absolute; width: 700px; height: 700px; border-radius: 50%;
      background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%);
      bottom: -200px; right: -200px; pointer-events: none;
    }

    /* Animations */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
      from { opacity: 0; } to { opacity: 1; }
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    @keyframes pulse-ring {
      0% { box-shadow: 0 0 0 0 rgba(79,70,229,0.4); }
      70% { box-shadow: 0 0 0 12px rgba(79,70,229,0); }
      100% { box-shadow: 0 0 0 0 rgba(79,70,229,0); }
    }
    @keyframes shimmer {
      0% { background-position: -200% center; }
      100% { background-position: 200% center; }
    }

    .anim-fade-up { animation: fadeUp 0.7s ease forwards; }
    .anim-fade-up-1 { animation: fadeUp 0.7s 0.1s ease both; }
    .anim-fade-up-2 { animation: fadeUp 0.7s 0.25s ease both; }
    .anim-fade-up-3 { animation: fadeUp 0.7s 0.4s ease both; }
    .anim-fade-up-4 { animation: fadeUp 0.7s 0.55s ease both; }
    .float { animation: float 5s ease-in-out infinite; }

    /* Glassmorphism */
    .glass {
      background: rgba(255,255,255,0.04);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,0.08);
    }
    .glass-dark {
      background: rgba(11,15,26,0.7);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255,255,255,0.07);
    }

    /* Feature cards */
    .feature-card {
      background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
      border: 1px solid rgba(255,255,255,0.08);
      transition: all 0.3s ease;
    }
    .feature-card:hover {
      border-color: rgba(99,102,241,0.4);
      background: linear-gradient(135deg, rgba(79,70,229,0.12) 0%, rgba(79,70,229,0.04) 100%);
      transform: translateY(-4px);
      box-shadow: 0 20px 40px rgba(79,70,229,0.15);
    }

    /* Pricing cards */
    .pricing-card {
      background: linear-gradient(145deg, rgba(26,34,54,0.8) 0%, rgba(17,24,39,0.9) 100%);
      border: 1px solid rgba(255,255,255,0.08);
      transition: all 0.3s ease;
    }
    .pricing-card:hover { transform: translateY(-6px); }
    .pricing-featured {
      background: linear-gradient(145deg, rgba(79,70,229,0.25) 0%, rgba(99,102,241,0.1) 100%);
      border: 1.5px solid rgba(99,102,241,0.6);
      box-shadow: 0 0 40px rgba(79,70,229,0.2), inset 0 1px 0 rgba(255,255,255,0.12);
    }

    /* CTA Button */
    .btn-primary {
      background: linear-gradient(135deg, #4F46E5 0%, #6366F1 50%, #818CF8 100%);
      background-size: 200% auto;
      transition: all 0.3s ease;
      animation: pulse-ring 2.5s infinite;
    }
    .btn-primary:hover {
      background-position: right center;
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(79,70,229,0.5);
    }
    .btn-outline {
      border: 1.5px solid rgba(99,102,241,0.5);
      transition: all 0.3s ease;
    }
    .btn-outline:hover {
      border-color: #818CF8;
      background: rgba(79,70,229,0.12);
      transform: translateY(-2px);
    }

    /* Stat bar */
    .stat-bar {
      background: linear-gradient(90deg, rgba(79,70,229,0.08) 0%, rgba(20,184,166,0.05) 100%);
      border-top: 1px solid rgba(255,255,255,0.06);
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    /* Step connector */
    .step-line::after {
      content: '';
      position: absolute;
      top: 24px;
      left: calc(100% + 8px);
      width: calc(100% - 16px);
      height: 1px;
      background: linear-gradient(90deg, rgba(79,70,229,0.6), rgba(79,70,229,0.1));
    }

    /* Tech badge */
    .tech-badge {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.1);
      transition: all 0.2s;
    }
    .tech-badge:hover {
      background: rgba(79,70,229,0.15);
      border-color: rgba(99,102,241,0.5);
    }

    /* Navbar */
    .navbar-blur {
      background: rgba(11,15,26,0.85);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }

    /* Dashboard mockup */
    .dashboard-bar { height: 6px; border-radius: 3px; }
    .db-green { background: linear-gradient(90deg, #10B981, #34D399); }
    .db-indigo { background: linear-gradient(90deg, #4F46E5, #818CF8); }
    .db-teal { background: linear-gradient(90deg, #14B8A6, #5EEAD4); }
    .db-amber { background: linear-gradient(90deg, #F59E0B, #FCD34D); }

    /* Toggle */
    .toggle-track {
      background: rgba(255,255,255,0.1);
      transition: background 0.3s;
    }
    .toggle-track.active { background: #4F46E5; }
    .toggle-thumb {
      width: 20px; height: 20px;
      background: white; border-radius: 50%;
      transition: transform 0.3s;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .toggle-thumb.active { transform: translateX(24px); }

    /* Divider dot line */
    .dot-divider {
      background-image: radial-gradient(circle, rgba(99,102,241,0.4) 1px, transparent 1px);
      background-size: 12px 12px;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--navy); }
    ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); border-radius: 3px; }

    /* Mobile menu */
    [x-cloak] { display: none !important; }

    /* Noise grain overlay */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
      pointer-events: none; z-index: 0; opacity: 0.4;
    }

    /* Section label */
    .section-label {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.7rem; font-weight: 600;
      letter-spacing: 0.15em; text-transform: uppercase;
      color: var(--indigo-glow);
    }

    /* Highlighted word */
    .highlight-word {
      background: linear-gradient(135deg, #818CF8, #14B8A6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>
</head>
<body class="relative">

<!-- ============================================
     A. NAVIGATION
============================================ -->
<nav class="navbar-blur fixed top-0 left-0 right-0 z-50">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Logo -->
      <a href="#" class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #4F46E5, #14B8A6);">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
            <rect x="1" y="1" width="7" height="7" rx="1.5" fill="white" opacity="0.9"/>
            <rect x="10" y="1" width="7" height="7" rx="1.5" fill="white" opacity="0.6"/>
            <rect x="1" y="10" width="7" height="7" rx="1.5" fill="white" opacity="0.6"/>
            <rect x="10" y="10" width="7" height="7" rx="1.5" fill="white" opacity="0.3"/>
          </svg>
        </div>
        <span class="font-display font-800 text-lg tracking-tight text-white" style="font-weight:700;">Beayar<span class="text-indigo-400"> ERP</span></span>
      </a>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center gap-8">
        <a href="#features" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Features</a>
        <a href="#how-it-works" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">How It Works</a>
        <a href="#pricing" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Pricing</a>
        <!-- <a href="#tech" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Tech Stack</a> -->
      </div>

      <!-- CTA -->
      <div class="hidden md:flex items-center gap-3">
        <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Sign In</a>
        <a href="/checkout/free" class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-lg">Start Free</a>
      </div>

      <!-- Mobile hamburger -->
      <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg glass" aria-label="Menu">
        <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div x-show="mobileOpen" x-cloak x-transition class="md:hidden glass-dark border-t border-white/5 px-6 py-4 space-y-3">
    <a href="#features" @click="mobileOpen=false" class="block text-sm text-slate-300 py-2">Features</a>
    <a href="#how-it-works" @click="mobileOpen=false" class="block text-sm text-slate-300 py-2">How It Works</a>
    <a href="#pricing" @click="mobileOpen=false" class="block text-sm text-slate-300 py-2">Pricing</a>
    <!-- <a href="#tech" @click="mobileOpen=false" class="block text-sm text-slate-300 py-2">Tech Stack</a> -->
    <a href="/checkout/free" class="btn-primary block text-center text-white text-sm font-semibold px-5 py-3 rounded-lg mt-2">Start Free</a>
  </div>
</nav>


<!-- ============================================
     B. HERO SECTION
============================================ -->
<section class="relative grid-overlay min-h-screen flex items-center pt-16 overflow-hidden">
  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="max-w-7xl mx-auto px-6 lg:px-8 py-24 lg:py-32 relative z-10 w-full">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <!-- Left: Copy -->
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-medium mb-6 anim-fade-up">
          <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
          <span class="text-teal-300">Now live for South Asian SMEs</span>
        </div>

        <h1 class="font-display font-extrabold text-4xl lg:text-6xl leading-[1.08] tracking-tight mb-6 anim-fade-up-1">
          Run Your Entire<br>
          Business From <span class="highlight-word">One Dashboard</span>
        </h1>

        <p class="text-slate-400 text-lg lg:text-xl leading-relaxed mb-8 max-w-xl anim-fade-up-2">
          Quotations. Invoicing. Deliveries. Payments. Teams. All in one place.<br>
          <span class="text-slate-300">Built for growing businesses in South Asia.</span>
        </p>

        <div class="flex flex-col sm:flex-row gap-4 anim-fade-up-3">
          <a href="/checkout/free" class="btn-primary inline-flex items-center justify-center gap-2 text-white font-semibold px-7 py-4 rounded-xl text-base">
            Start Free — No Credit Card Required
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
          <a href="#how-it-works" class="btn-outline inline-flex items-center justify-center gap-2 text-slate-300 font-medium px-6 py-4 rounded-xl text-base">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Watch Demo
          </a>
        </div>

        <p class="text-slate-600 text-sm mt-5 anim-fade-up-4">Trusted by 200+ companies across Bangladesh, India & Pakistan</p>
      </div>

      <!-- Right: Dashboard Mockup -->
      <div class="float anim-fade-up-2 relative">
        <div class="relative rounded-2xl overflow-hidden" style="border: 1px solid rgba(99,102,241,0.25); box-shadow: 0 40px 100px rgba(79,70,229,0.25), 0 0 0 1px rgba(255,255,255,0.05);">
          <!-- Title bar -->
          <div class="flex items-center gap-2 px-4 py-3" style="background: rgba(17,24,39,0.95);">
            <div class="w-3 h-3 rounded-full bg-red-400 opacity-70"></div>
            <div class="w-3 h-3 rounded-full bg-yellow-400 opacity-70"></div>
            <div class="w-3 h-3 rounded-full bg-green-400 opacity-70"></div>
            <div class="ml-3 flex-1 h-5 rounded-md" style="background: rgba(255,255,255,0.07); max-width: 200px;"></div>
          </div>
          <!-- Dashboard body -->
          <div style="background: linear-gradient(145deg, #111827 0%, #0d1526 100%);" class="p-5">
            <!-- Stats row -->
            <div class="grid grid-cols-4 gap-3 mb-4">
              <div class="rounded-xl p-3" style="background:rgba(79,70,229,0.15); border:1px solid rgba(79,70,229,0.2);">
                <div class="text-xs text-indigo-300 mb-1">Revenue</div>
                <div class="text-lg font-display font-bold text-white">৳2.4M</div>
                <div class="text-xs text-green-400 mt-1">▲ 18.2%</div>
              </div>
              <div class="rounded-xl p-3" style="background:rgba(20,184,166,0.1); border:1px solid rgba(20,184,166,0.2);">
                <div class="text-xs text-teal-300 mb-1">Invoices</div>
                <div class="text-lg font-display font-bold text-white">142</div>
                <div class="text-xs text-green-400 mt-1">▲ 6.1%</div>
              </div>
              <div class="rounded-xl p-3" style="background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2);">
                <div class="text-xs text-yellow-300 mb-1">Pending</div>
                <div class="text-lg font-display font-bold text-white">17</div>
                <div class="text-xs text-red-400 mt-1">▼ 2.4%</div>
              </div>
              <div class="rounded-xl p-3" style="background:rgba(139,92,246,0.1); border:1px solid rgba(139,92,246,0.2);">
                <div class="text-xs text-purple-300 mb-1">Teams</div>
                <div class="text-lg font-display font-bold text-white">8</div>
                <div class="text-xs text-slate-400 mt-1">Active</div>
              </div>
            </div>
            <!-- Chart area -->
            <div class="rounded-xl p-4 mb-4" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400 font-medium">Revenue Overview · 2026</span>
                <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(79,70,229,0.2); color:#818CF8;">Monthly</span>
              </div>
              <div class="flex items-end gap-2 h-20">
                <div class="flex-1 rounded-t" style="height:45%; background:rgba(79,70,229,0.3);"></div>
                <div class="flex-1 rounded-t" style="height:60%; background:rgba(79,70,229,0.4);"></div>
                <div class="flex-1 rounded-t" style="height:40%; background:rgba(79,70,229,0.3);"></div>
                <div class="flex-1 rounded-t" style="height:75%; background:rgba(79,70,229,0.5);"></div>
                <div class="flex-1 rounded-t" style="height:55%; background:rgba(79,70,229,0.35);"></div>
                <div class="flex-1 rounded-t" style="height:85%; background:linear-gradient(180deg, #818CF8, #4F46E5);"></div>
                <div class="flex-1 rounded-t" style="height:65%; background:rgba(79,70,229,0.4);"></div>
                <div class="flex-1 rounded-t" style="height:90%; background:linear-gradient(180deg, #34D399, #14B8A6);"></div>
              </div>
            </div>
            <!-- Bottom rows -->
            <div class="grid grid-cols-2 gap-3">
              <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05);">
                <div class="text-xs text-slate-500 mb-2">Recent Quotations</div>
                <div class="space-y-1.5">
                  <div class="flex justify-between items-center"><span class="text-xs text-slate-300">QT-2206</span><span class="text-xs text-green-400">Approved</span></div>
                  <div class="flex justify-between items-center"><span class="text-xs text-slate-300">QT-2205</span><span class="text-xs text-yellow-400">Pending</span></div>
                  <div class="flex justify-between items-center"><span class="text-xs text-slate-300">QT-2204</span><span class="text-xs text-blue-400">Sent</span></div>
                </div>
              </div>
              <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05);">
                <div class="text-xs text-slate-500 mb-2">Cash Flow</div>
                <div class="space-y-2 mt-1">
                  <div><div class="flex justify-between text-xs mb-1"><span class="text-slate-400">Collected</span><span class="text-green-400">73%</span></div><div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.06);"><div class="h-full rounded-full db-green" style="width:73%;"></div></div></div>
                  <div><div class="flex justify-between text-xs mb-1"><span class="text-slate-400">Target</span><span class="text-indigo-400">58%</span></div><div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.06);"><div class="h-full rounded-full db-indigo" style="width:58%;"></div></div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Floating badge -->
        <div class="absolute -bottom-4 -left-4 glass rounded-xl px-4 py-3 flex items-center gap-3" style="border-color: rgba(20,184,166,0.3);">
          <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(20,184,166,0.2);">
            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </div>
          <div><div class="text-xs font-semibold text-white">Invoice Sent</div><div class="text-xs text-slate-400">৳84,500 · Just now</div></div>
        </div>
        <!-- Floating badge 2 -->
        <div class="absolute -top-4 -right-4 glass rounded-xl px-4 py-3 flex items-center gap-3" style="border-color: rgba(79,70,229,0.3);">
          <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(79,70,229,0.2);">
            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          </div>
          <div><div class="text-xs font-semibold text-white">Revenue Up 18%</div><div class="text-xs text-slate-400">vs last month</div></div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ============================================
     C. TRUST BAR
============================================ -->
<div class="stat-bar relative z-10">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 py-5">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-0 md:divide-x divide-white/10">
      <div class="flex items-center justify-center gap-3 md:px-8">
        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <span class="text-sm font-medium text-slate-300">Enterprise-grade security</span>
      </div>
      <div class="flex items-center justify-center gap-3 md:px-8">
        <svg class="w-5 h-5 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
        <span class="text-sm font-medium text-slate-300">99.9% uptime SLA</span>
      </div>
      <div class="flex items-center justify-center gap-3 md:px-8">
        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        <span class="text-sm font-medium text-slate-300">Built on Laravel</span>
      </div>
      <div class="flex items-center justify-center gap-3 md:px-8">
        <svg class="w-5 h-5 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
        <span class="text-sm font-medium text-slate-300">Multi-Currency Ready</span>
      </div>
    </div>
  </div>
</div>


<!-- ============================================
     D. PROBLEM vs SOLUTION
============================================ -->
<section class="relative py-28 overflow-hidden">
  <div class="blob-3" style="bottom: -100px; left: -200px; right: auto;"></div>
  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <!-- Problem -->
      <div class="rounded-2xl p-8 lg:p-10 relative overflow-hidden" style="background: linear-gradient(135deg, rgba(239,68,68,0.08) 0%, rgba(17,24,39,0.6) 100%); border: 1px solid rgba(239,68,68,0.15);">
        <div class="absolute top-4 right-4 text-red-500/20 font-display font-black text-7xl select-none">!</div>
        <div class="section-label mb-4" style="color: #F87171;">The problem</div>
        <h2 class="font-display font-bold text-2xl lg:text-3xl text-white mb-5">Your tools are working against you</h2>
        <div class="space-y-4">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(239,68,68,0.15);">
              <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
            <p class="text-slate-400 text-sm leading-relaxed">Disconnected spreadsheets, WhatsApp forwards, and email chains — no single source of truth</p>
          </div>
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(239,68,68,0.15);">
              <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
            <p class="text-slate-400 text-sm leading-relaxed">Quotations lost in inboxes, revisions untracked, clients confused about pricing</p>
          </div>
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(239,68,68,0.15);">
              <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
            <p class="text-slate-400 text-sm leading-relaxed">Manual invoice tracking, missed payments, and accountants working in the dark</p>
          </div>
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(239,68,68,0.15);">
              <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
            <p class="text-slate-400 text-sm leading-relaxed">Zero visibility into what your teams are doing — chaos scales with your business</p>
          </div>
        </div>
      </div>

      <!-- Solution -->
      <div class="rounded-2xl p-8 lg:p-10 relative overflow-hidden" style="background: linear-gradient(135deg, rgba(79,70,229,0.12) 0%, rgba(17,24,39,0.7) 100%); border: 1px solid rgba(79,70,229,0.25);">
        <div class="absolute top-4 right-4 text-indigo-500/10 font-display font-black text-7xl select-none">✓</div>
        <div class="section-label mb-4">The Beayar way</div>
        <h2 class="font-display font-bold text-2xl lg:text-3xl text-white mb-2">One shared source of truth</h2>
        <p class="text-slate-400 text-sm mb-6">For business owners, sales teams, and accountants — finally on the same page.</p>
        <div class="space-y-4">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(20,184,166,0.2);">
              <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-slate-300 text-sm leading-relaxed">Every quotation, invoice, delivery, and payment in one unified workspace</p>
          </div>
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(20,184,166,0.2);">
              <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-slate-300 text-sm leading-relaxed">Real-time dashboards so leadership sees what matters, instantly</p>
          </div>
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(20,184,166,0.2);">
              <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-slate-300 text-sm leading-relaxed">Role-based access so each team member sees exactly what they need — nothing more</p>
          </div>
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5" style="background: rgba(20,184,166,0.2);">
              <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-slate-300 text-sm leading-relaxed">From first quote to final payment — completely tracked, audited, and transparent</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ============================================
     E. FEATURE HIGHLIGHTS
============================================ -->
<section id="features" class="relative py-24 grid-overlay">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <div class="text-center mb-16">
      <div class="section-label mb-3">Feature Highlights</div>
      <h2 class="font-display font-extrabold text-3xl lg:text-5xl text-white mb-4">Everything your business needs</h2>
      <p class="text-slate-400 text-lg max-w-2xl mx-auto">Six powerful modules. One cohesive platform. No more juggling apps.</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Card 1 -->
      <div class="feature-card rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background: linear-gradient(135deg, rgba(79,70,229,0.3), rgba(79,70,229,0.1));">
          <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-white mb-2">Quotation Builder</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Multi-currency support with auto-calculations and full revision tracking. Never lose a quote or send the wrong version again.</p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(79,70,229,0.15); color:#818CF8;">Multi-currency</span>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(79,70,229,0.15); color:#818CF8;">Revision tracking</span>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="feature-card rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background: linear-gradient(135deg, rgba(20,184,166,0.3), rgba(20,184,166,0.1));">
          <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-white mb-2">Billing & Invoicing</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Handle Advance, Running, and Regular bill types seamlessly. Automate reminders and get paid faster.</p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(20,184,166,0.15); color:#5EEAD4;">Advance bills</span>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(20,184,166,0.15); color:#5EEAD4;">Running bills</span>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="feature-card rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background: linear-gradient(135deg, rgba(245,158,11,0.3), rgba(245,158,11,0.1));">
          <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-white mb-2">Multi-Company Management</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Manage multiple branches and companies from one account. Switch context instantly without logging in and out.</p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(245,158,11,0.15); color:#FCD34D;">Branch switching</span>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(245,158,11,0.15); color:#FCD34D;">Isolated data</span>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="feature-card rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background: linear-gradient(135deg, rgba(139,92,246,0.3), rgba(139,92,246,0.1));">
          <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-white mb-2">Team Collaboration</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Role-based access control keeps each team member focused. Sales sees quotes, accountants see invoices, owners see everything.</p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(139,92,246,0.15); color:#C4B5FD;">RBAC</span>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(139,92,246,0.15); color:#C4B5FD;">Audit trails</span>
        </div>
      </div>

      <!-- Card 5 -->
      <div class="feature-card rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background: linear-gradient(135deg, rgba(16,185,129,0.3), rgba(16,185,129,0.1));">
          <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-white mb-2">Financial Insights</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Real-time dashboards for income, expenses, and targets. Know your numbers before your accountant does.</p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(16,185,129,0.15); color:#6EE7B7;">Real-time</span>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(16,185,129,0.15); color:#6EE7B7;">Target tracking</span>
        </div>
      </div>

      <!-- Card 6 -->
      <div class="feature-card rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background: linear-gradient(135deg, rgba(239,68,68,0.3), rgba(239,68,68,0.1));">
          <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-white mb-2">Customization</h3>
        <p class="text-slate-400 text-sm leading-relaxed">Dynamic features, custom exchange rates, and localized formats. Beayar adapts to how your business works — not the other way around.</p>
        <div class="mt-4 flex flex-wrap gap-2">
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(239,68,68,0.15); color:#FCA5A5;">Exchange rates</span>
          <span class="text-xs px-2.5 py-1 rounded-full" style="background:rgba(239,68,68,0.15); color:#FCA5A5;">Localized</span>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ============================================
     F. HOW IT WORKS
============================================ -->
<section id="how-it-works" class="py-28 relative overflow-hidden">
  <div class="blob-1" style="top: auto; bottom: -200px; left: 50%; transform: translateX(-50%);"></div>
  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <div class="text-center mb-16">
      <div class="section-label mb-3">How It Works</div>
      <h2 class="font-display font-extrabold text-3xl lg:text-5xl text-white mb-4">From signup to paid — seamlessly</h2>
      <p class="text-slate-400 text-lg">A workflow designed for real businesses, not enterprise IT departments.</p>
    </div>

    <!-- Timeline steps desktop -->
    <div class="hidden lg:block relative">
      <!-- connecting line -->
      <div class="absolute top-12 left-[calc(8.33%+24px)] right-[calc(8.33%+24px)] h-px" style="background: linear-gradient(90deg, rgba(79,70,229,0.8), rgba(20,184,166,0.6), rgba(79,70,229,0.2));"></div>

      <div class="grid grid-cols-6 gap-4">
        <!-- Step 1 -->
        <div class="flex flex-col items-center text-center">
          <div class="w-12 h-12 rounded-full flex items-center justify-center z-10 mb-4" style="background: linear-gradient(135deg, #4F46E5, #818CF8); box-shadow: 0 0 20px rgba(79,70,229,0.5);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="text-xs font-bold text-indigo-400 mb-1">01</div>
          <h4 class="font-display font-semibold text-sm text-white mb-1">Sign Up</h4>
          <p class="text-slate-500 text-xs leading-relaxed">Create your workspace in under 60 seconds</p>
        </div>
        <!-- Step 2 -->
        <div class="flex flex-col items-center text-center">
          <div class="w-12 h-12 rounded-full flex items-center justify-center z-10 mb-4" style="background: linear-gradient(135deg, #5B48EA, #818CF8); box-shadow: 0 0 20px rgba(79,70,229,0.4);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div class="text-xs font-bold text-indigo-400 mb-1">02</div>
          <h4 class="font-display font-semibold text-sm text-white mb-1">Add Clients & Products</h4>
          <p class="text-slate-500 text-xs leading-relaxed">Import or enter your catalog and client list</p>
        </div>
        <!-- Step 3 -->
        <div class="flex flex-col items-center text-center">
          <div class="w-12 h-12 rounded-full flex items-center justify-center z-10 mb-4" style="background: linear-gradient(135deg, #14B8A6, #5EEAD4); box-shadow: 0 0 20px rgba(20,184,166,0.5);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div class="text-xs font-bold text-teal-400 mb-1">03</div>
          <h4 class="font-display font-semibold text-sm text-white mb-1">Create Quotation</h4>
          <p class="text-slate-500 text-xs leading-relaxed">Build professional quotes in minutes, not hours</p>
        </div>
        <!-- Step 4 -->
        <div class="flex flex-col items-center text-center">
          <div class="w-12 h-12 rounded-full flex items-center justify-center z-10 mb-4" style="background: linear-gradient(135deg, #14B8A6, #34D399); box-shadow: 0 0 20px rgba(20,184,166,0.4);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
          </div>
          <div class="text-xs font-bold text-teal-400 mb-1">04</div>
          <h4 class="font-display font-semibold text-sm text-white mb-1">Deliver (Challans)</h4>
          <p class="text-slate-500 text-xs leading-relaxed">Generate delivery challans tied to your orders</p>
        </div>
        <!-- Step 5 -->
        <div class="flex flex-col items-center text-center">
          <div class="w-12 h-12 rounded-full flex items-center justify-center z-10 mb-4" style="background: linear-gradient(135deg, #4F46E5, #14B8A6); box-shadow: 0 0 20px rgba(79,70,229,0.4);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div class="text-xs font-bold text-indigo-400 mb-1">05</div>
          <h4 class="font-display font-semibold text-sm text-white mb-1">Invoice & Get Paid</h4>
          <p class="text-slate-500 text-xs leading-relaxed">Send professional invoices and track every payment</p>
        </div>
        <!-- Step 6 -->
        <div class="flex flex-col items-center text-center">
          <div class="w-12 h-12 rounded-full flex items-center justify-center z-10 mb-4" style="background: linear-gradient(135deg, #F59E0B, #FCD34D); box-shadow: 0 0 20px rgba(245,158,11,0.4);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          <div class="text-xs font-bold text-yellow-400 mb-1">06</div>
          <h4 class="font-display font-semibold text-sm text-white mb-1">Analyze Reports</h4>
          <p class="text-slate-500 text-xs leading-relaxed">Make data-driven decisions with full-picture insights</p>
        </div>
      </div>
    </div>

    <!-- Mobile steps -->
    <div class="lg:hidden space-y-0">
      <div class="relative pl-16">
        <div class="absolute left-5 top-0 bottom-0 w-px" style="background: linear-gradient(180deg, rgba(79,70,229,0.8), rgba(20,184,166,0.3));"></div>
        <div class="space-y-8">
          <div class="relative pb-8">
            <div class="absolute -left-11 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #4F46E5, #818CF8);"><span class="text-white text-xs font-bold">01</span></div>
            <h4 class="font-display font-semibold text-white mb-1">Sign Up</h4>
            <p class="text-slate-500 text-sm">Create your workspace in under 60 seconds</p>
          </div>
          <div class="relative pb-8">
            <div class="absolute -left-11 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #5B48EA, #818CF8);"><span class="text-white text-xs font-bold">02</span></div>
            <h4 class="font-display font-semibold text-white mb-1">Add Clients & Products</h4>
            <p class="text-slate-500 text-sm">Import or enter your catalog and client list</p>
          </div>
          <div class="relative pb-8">
            <div class="absolute -left-11 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #14B8A6, #5EEAD4);"><span class="text-white text-xs font-bold">03</span></div>
            <h4 class="font-display font-semibold text-white mb-1">Create Quotation</h4>
            <p class="text-slate-500 text-sm">Build professional quotes in minutes</p>
          </div>
          <div class="relative pb-8">
            <div class="absolute -left-11 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #14B8A6, #34D399);"><span class="text-white text-xs font-bold">04</span></div>
            <h4 class="font-display font-semibold text-white mb-1">Deliver (Challans)</h4>
            <p class="text-slate-500 text-sm">Generate delivery challans tied to your orders</p>
          </div>
          <div class="relative pb-8">
            <div class="absolute -left-11 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #4F46E5, #14B8A6);"><span class="text-white text-xs font-bold">05</span></div>
            <h4 class="font-display font-semibold text-white mb-1">Invoice & Get Paid</h4>
            <p class="text-slate-500 text-sm">Send invoices and track every payment</p>
          </div>
          <div class="relative">
            <div class="absolute -left-11 w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #F59E0B, #FCD34D);"><span class="text-white text-xs font-bold">06</span></div>
            <h4 class="font-display font-semibold text-white mb-1">Analyze Reports</h4>
            <p class="text-slate-500 text-sm">Make data-driven decisions with full insights</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ============================================
     G. PRICING
============================================ -->
<section id="pricing" class="py-24 relative grid-overlay">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <div class="text-center mb-12">
      <div class="section-label mb-3">Pricing</div>
      <h2 class="font-display font-extrabold text-3xl lg:text-5xl text-white mb-4">Plans that grow with you</h2>
      <p class="text-slate-400 text-lg mb-8">No hidden fees. No surprise charges. Cancel anytime.</p>
      <!-- Toggle -->
      <div class="inline-flex items-center gap-4 glass rounded-xl px-4 py-2">
        <span class="text-sm font-medium" :class="!billingYearly ? 'text-white' : 'text-slate-400'">Monthly</span>
        <div class="relative cursor-pointer w-12 h-6 rounded-full toggle-track flex items-center px-0.5"
             :class="billingYearly ? 'active' : ''"
             @click="billingYearly = !billingYearly">
          <div class="toggle-thumb" :class="billingYearly ? 'active' : ''"></div>
        </div>
        <span class="text-sm font-medium" :class="billingYearly ? 'text-white' : 'text-slate-400'">Yearly</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:rgba(20,184,166,0.2); color:#5EEAD4;">Save 20%</span>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
      <!-- Free -->
      <div class="pricing-card rounded-2xl p-7 flex flex-col">
        <div class="mb-6">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Free</div>
          <div class="flex items-baseline gap-1 mb-1">
            <span class="font-display font-extrabold text-4xl text-white">$0</span>
            <span class="text-slate-500 text-sm">/mo</span>
          </div>
          <p class="text-slate-500 text-xs">Perfect for trying Beayar</p>
        </div>
        <ul class="space-y-3 mb-8 flex-1">
          <li class="flex items-center gap-2.5 text-sm text-slate-400"><svg class="w-4 h-4 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>1 Company</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-400"><svg class="w-4 h-4 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>20 Quotes / month</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-400"><svg class="w-4 h-4 text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>3 Employees</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-400 opacity-40"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Revision tracking</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-400 opacity-40"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Financial dashboards</li>
        </ul>
        <a href="/checkout/free" class="btn-outline text-center text-sm font-semibold text-slate-300 py-3 rounded-xl">Get Started Free</a>
      </div>

      <!-- Pro -->
      <div class="pricing-card rounded-2xl p-7 flex flex-col">
        <div class="mb-6">
          <div class="text-xs font-semibold text-indigo-400 uppercase tracking-widest mb-2">Pro</div>
          <div class="flex items-baseline gap-1 mb-1">
            <span class="font-display font-extrabold text-4xl text-white" x-text="billingYearly ? '$23' : '$29'">$29</span>
            <span class="text-slate-500 text-sm">/mo</span>
          </div>
          <p class="text-slate-500 text-xs">For growing teams</p>
        </div>
        <ul class="space-y-3 mb-8 flex-1">
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>5 Companies</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>100 Quotes / month</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>10 Employees</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Revision tracking</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Advance/Running Bills</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Financial Dashboards</li>
        </ul>
        <a href="/checkout/pro" class="btn-outline text-center text-sm font-semibold text-slate-300 py-3 rounded-xl">Get Pro</a>
      </div>

      <!-- Pro Plus (FEATURED) -->
      <div class="pricing-card pricing-featured rounded-2xl p-7 flex flex-col relative lg:-mt-4 lg:-mb-4">
        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
          <span class="text-xs font-bold px-4 py-1.5 rounded-full text-white" style="background: linear-gradient(135deg, #4F46E5, #14B8A6);">⭐ Most Popular</span>
        </div>
        <div class="mb-6 pt-2">
          <div class="text-xs font-semibold text-indigo-300 uppercase tracking-widest mb-2">Pro Plus</div>
          <div class="flex items-baseline gap-1 mb-1">
            <span class="font-display font-extrabold text-4xl text-white" x-text="billingYearly ? '$63' : '$79'">$79</span>
            <span class="text-slate-400 text-sm">/mo</span>
          </div>
          <p class="text-slate-400 text-xs">For scaling businesses</p>
        </div>
        <ul class="space-y-3 mb-8 flex-1">
          <li class="flex items-center gap-2.5 text-sm text-slate-200"><svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>15 Companies</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-200"><svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Unlimited Quotes</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-200"><svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>50 Employees</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-200"><svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Priority Support</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-200"><svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Everything in Pro</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-200"><svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Custom Exchange Rates</li>
        </ul>
        <a href="/checkout/pro-plus" class="btn-primary text-center text-sm font-semibold text-white py-3 rounded-xl">Get Pro Plus</a>
      </div>

      <!-- Enterprise -->
      <div class="pricing-card rounded-2xl p-7 flex flex-col" style="border-color: rgba(245,158,11,0.2);">
        <div class="mb-6">
          <div class="text-xs font-semibold text-yellow-500 uppercase tracking-widest mb-2">Enterprise</div>
          <div class="flex items-baseline gap-1 mb-1">
            <span class="font-display font-extrabold text-3xl text-white">Custom</span>
          </div>
          <p class="text-slate-500 text-xs">Calculated dynamically</p>
        </div>
        <ul class="space-y-3 mb-4 flex-1">
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Custom company limit</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Unlimited everything</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Dedicated manager</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>SLA guarantee</li>
          <li class="flex items-center gap-2.5 text-sm text-slate-300"><svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Custom integrations</li>
        </ul>
        <p class="text-slate-500 text-xs mb-4 italic">Need more? Build your perfect plan with our dynamic pricing calculator.</p>
        <a href="#" class="text-center text-sm font-semibold py-3 rounded-xl" style="border: 1.5px solid rgba(245,158,11,0.4); color: #FCD34D; transition: all 0.2s;" onmouseover="this.style.background='rgba(245,158,11,0.1)'" onmouseout="this.style.background='transparent'">Contact Sales</a>
      </div>
    </div>
  </div>
</section>





<!-- ============================================
     I. FINAL CTA SECTION
============================================ -->
<section class="py-28 relative overflow-hidden">
  <div class="blob-1" style="top: 50%; transform: translateY(-50%); left: -100px;"></div>
  <div class="blob-3" style="bottom: auto; top: 50%; transform: translateY(-50%); right: -100px;"></div>

  <div class="max-w-4xl mx-auto px-6 lg:px-8 relative z-10 text-center">
    <!-- Large decorative number -->
    <div class="font-display font-black text-[160px] lg:text-[220px] leading-none select-none pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 opacity-[0.03]" style="-webkit-text-stroke: 1px rgba(99,102,241,0.5);">0</div>

    <div class="section-label mb-4">Get Started Today</div>
    <h2 class="font-display font-extrabold text-4xl lg:text-6xl text-white mb-5 leading-tight">
      Ready to eliminate<br>
      <span class="highlight-word">business chaos?</span>
    </h2>
    <p class="text-slate-400 text-xl mb-10 max-w-2xl mx-auto">
      Join modern businesses streamlining their operations with Beayar ERP. Start for free — upgrade when you're ready to scale.
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="/checkout/free" class="btn-primary inline-flex items-center justify-center gap-2 text-white font-bold px-8 py-4 rounded-xl text-base">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Create Free Account
      </a>
      <a href="#" class="btn-outline inline-flex items-center justify-center gap-2 text-slate-300 font-semibold px-8 py-4 rounded-xl text-base">
        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Book a Demo
      </a>
    </div>
    <p class="text-slate-600 text-sm mt-6">No credit card required · Free plan available · Cancel anytime</p>
  </div>
</section>


<!-- ============================================
     J. FOOTER
============================================ -->
<footer style="background: #070B14; border-top: 1px solid rgba(255,255,255,0.05);">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-5 gap-12 mb-16">
      <!-- Brand column -->
      <div class="lg:col-span-2">
        <a href="#" class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #4F46E5, #14B8A6);">
            <svg width="20" height="20" viewBox="0 0 18 18" fill="none">
              <rect x="1" y="1" width="7" height="7" rx="1.5" fill="white" opacity="0.9"/>
              <rect x="10" y="1" width="7" height="7" rx="1.5" fill="white" opacity="0.6"/>
              <rect x="1" y="10" width="7" height="7" rx="1.5" fill="white" opacity="0.6"/>
              <rect x="10" y="10" width="7" height="7" rx="1.5" fill="white" opacity="0.3"/>
            </svg>
          </div>
          <span class="font-display font-bold text-xl text-white">Beayar<span class="text-indigo-400"> ERP</span></span>
        </a>
        <p class="text-slate-500 text-sm leading-relaxed mb-4 max-w-xs">One platform. Every business operation. Zero chaos. Built for South Asian SMEs.</p>
        <p class="text-slate-600 text-xs italic">"The ERP that finally speaks your language."</p>
      </div>

      <!-- Product -->
      <div>
        <h5 class="font-display font-semibold text-white text-sm mb-4">Product</h5>
        <ul class="space-y-3">
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Features</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Pricing</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Changelog</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Roadmap</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">API Docs</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div>
        <h5 class="font-display font-semibold text-white text-sm mb-4">Company</h5>
        <ul class="space-y-3">
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">About Us</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Blog</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Careers</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Contact</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Press Kit</a></li>
        </ul>
      </div>

      <!-- Legal -->
      <div>
        <h5 class="font-display font-semibold text-white text-sm mb-4">Legal</h5>
        <ul class="space-y-3">
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Terms & Conditions</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Privacy Policy</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Cookie Policy</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">Security</a></li>
          <li><a href="#" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">GDPR</a></li>
        </ul>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
      <p class="text-slate-600 text-sm">© 2026 Beayar ERP. All rights reserved. Crafted with care for South Asian businesses.</p>
      <div class="flex items-center gap-4">
        <!-- Social icons -->
        <a href="#" class="w-8 h-8 rounded-lg glass flex items-center justify-center hover:border-indigo-500/40 transition-colors">
          <svg class="w-4 h-4 text-slate-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
        </a>
        <a href="#" class="w-8 h-8 rounded-lg glass flex items-center justify-center hover:border-indigo-500/40 transition-colors">
          <svg class="w-4 h-4 text-slate-500" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
        </a>
        <a href="#" class="w-8 h-8 rounded-lg glass flex items-center justify-center hover:border-indigo-500/40 transition-colors">
          <svg class="w-4 h-4 text-slate-500" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
        </a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>
