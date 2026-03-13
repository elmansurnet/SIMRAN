<template>
  <!-- Full-screen green gradient — identical to Login page brand panel -->
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-900 via-green-800 to-emerald-700 relative overflow-hidden px-4 py-10">

    <!-- ── Decorative background blobs ────────────────────────────────── -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-white/5 blur-3xl animate-blob-1"></div>
      <div class="absolute -bottom-20 -right-20 w-96 h-96 rounded-full bg-emerald-400/10 blur-3xl animate-blob-2"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full bg-green-300/5 blur-3xl animate-blob-3"></div>
    </div>

    <!-- ── Subtle grid overlay ─────────────────────────────────────────── -->
    <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
         style="background-image:url('data:image/svg+xml,<svg width=%2240%22 height=%2240%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M0 40h40M40 0v40%22 stroke=%22white%22 stroke-width=%220.5%22 fill=%22none%22/></svg>')">
    </div>

    <!-- ── Main card ───────────────────────────────────────────────────── -->
    <div class="relative z-10 w-full max-w-2xl animate-card-in">

      <!-- Brand logo -->
      <div class="flex items-center justify-center space-x-3 mb-8">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div>
          <div class="text-white font-black text-lg tracking-wide leading-none">SIMRAN</div>
          <div class="text-green-300 text-xs font-semibold leading-none mt-0.5">UNISYA</div>
        </div>
      </div>

      <!-- Glass card -->
      <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl p-8 sm:p-12 text-center">

        <!-- Animated SVG illustration -->
        <div class="flex justify-center mb-6">
          <!-- 403 — Kunci & Perisai -->
          <div v-if="config.icon === 'shield'" class="relative">
            <svg class="w-32 h-32 animate-shake-slow" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Shield -->
              <path d="M60 8L16 26V60C16 84 38 104 60 112C82 104 104 84 104 60V26L60 8Z"
                    fill="url(#shieldGrad)" opacity="0.9"/>
              <path d="M60 8L16 26V60C16 84 38 104 60 112C82 104 104 84 104 60V26L60 8Z"
                    stroke="rgba(255,255,255,0.4)" stroke-width="2" fill="none"/>
              <!-- Lock body -->
              <rect x="42" y="58" width="36" height="28" rx="5"
                    fill="white" fill-opacity="0.9"/>
              <!-- Lock shackle -->
              <path d="M50 58V48C50 40.3 70 40.3 70 48V58"
                    stroke="white" stroke-width="5" stroke-linecap="round" fill="none"/>
              <!-- Keyhole -->
              <circle cx="60" cy="70" r="5" fill="#1B6B3A"/>
              <rect x="57.5" y="70" width="5" height="9" rx="2" fill="#1B6B3A"/>
              <!-- Shine -->
              <path d="M28 30L44 24" stroke="rgba(255,255,255,0.5)" stroke-width="3" stroke-linecap="round"/>
              <defs>
                <linearGradient id="shieldGrad" x1="16" y1="8" x2="104" y2="112" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#ef4444"/>
                  <stop offset="100%" stop-color="#dc2626"/>
                </linearGradient>
              </defs>
            </svg>
            <!-- Pulse ring -->
            <div class="absolute inset-0 rounded-full border-4 border-red-400/30 animate-ping-slow scale-110 pointer-events-none"></div>
          </div>

          <!-- 404 — Dokumen Hilang dengan Kaca Pembesar -->
          <div v-else-if="config.icon === 'search'" class="relative animate-float">
            <svg class="w-36 h-36" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Document -->
              <rect x="20" y="15" width="70" height="90" rx="8" fill="white" fill-opacity="0.15"/>
              <rect x="20" y="15" width="70" height="90" rx="8" stroke="rgba(255,255,255,0.5)" stroke-width="2"/>
              <!-- Folded corner -->
              <path d="M70 15L90 35H70V15Z" fill="white" fill-opacity="0.1"/>
              <path d="M70 15L90 35H70V15Z" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" fill="none"/>
              <!-- Lines (content placeholder) -->
              <rect x="32" y="48" width="38" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
              <rect x="32" y="58" width="30" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
              <rect x="32" y="68" width="34" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
              <!-- Big "?" -->
              <text x="55" y="95" font-family="Arial" font-size="36" font-weight="900"
                    fill="rgba(255,255,255,0.5)" text-anchor="middle">?</text>
              <!-- Magnifying glass handle -->
              <line x1="94" y1="94" x2="116" y2="118" stroke="rgba(255,255,255,0.8)"
                    stroke-width="8" stroke-linecap="round"/>
              <!-- Magnifying glass lens -->
              <circle cx="83" cy="82" r="24" fill="rgba(255,255,255,0.08)"
                      stroke="rgba(255,255,255,0.7)" stroke-width="5"/>
              <!-- Lens shine -->
              <path d="M72 70 Q76 66 82 68" stroke="rgba(255,255,255,0.5)"
                    stroke-width="2.5" stroke-linecap="round" fill="none"/>
            </svg>
            <!-- Floating stars -->
            <div class="absolute top-2 right-4 w-2 h-2 rounded-full bg-yellow-300/70 animate-twinkle-1"></div>
            <div class="absolute top-8 right-0 w-1.5 h-1.5 rounded-full bg-white/60 animate-twinkle-2"></div>
            <div class="absolute bottom-4 left-2 w-2 h-2 rounded-full bg-green-300/70 animate-twinkle-3"></div>
          </div>

          <!-- 419 — Jam & Sesi Berakhir -->
          <div v-else-if="config.icon === 'clock'" class="relative animate-float">
            <svg class="w-32 h-32" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Clock face -->
              <circle cx="60" cy="60" r="48" fill="url(#clockGrad)" fill-opacity="0.9"/>
              <circle cx="60" cy="60" r="48" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
              <!-- Inner face -->
              <circle cx="60" cy="60" r="42" fill="rgba(255,255,255,0.08)"/>
              <!-- Hour markers -->
              <rect x="58.5" y="16" width="3" height="8" rx="1.5" fill="rgba(255,255,255,0.7)"/>
              <rect x="58.5" y="96" width="3" height="8" rx="1.5" fill="rgba(255,255,255,0.7)"/>
              <rect x="16" y="58.5" width="8" height="3" rx="1.5" fill="rgba(255,255,255,0.7)"/>
              <rect x="96" y="58.5" width="8" height="3" rx="1.5" fill="rgba(255,255,255,0.7)"/>
              <!-- Hour hand (points to ~10) -->
              <line x1="60" y1="60" x2="42" y2="34" stroke="white" stroke-width="4"
                    stroke-linecap="round" class="animate-hour-hand"/>
              <!-- Minute hand (points to ~12) -->
              <line x1="60" y1="60" x2="60" y2="26" stroke="white" stroke-width="3"
                    stroke-linecap="round"/>
              <!-- Center dot -->
              <circle cx="60" cy="60" r="5" fill="white"/>
              <!-- Alarm lines -->
              <path d="M36 14 Q32 18 34 22" stroke="rgba(255,255,255,0.5)" stroke-width="2.5"
                    stroke-linecap="round" fill="none"/>
              <path d="M84 14 Q88 18 86 22" stroke="rgba(255,255,255,0.5)" stroke-width="2.5"
                    stroke-linecap="round" fill="none"/>
              <!-- Exclamation (expired!) -->
              <text x="60" y="88" font-family="Arial" font-size="14" font-weight="900"
                    fill="rgba(255,255,255,0.8)" text-anchor="middle">!</text>
              <defs>
                <linearGradient id="clockGrad" x1="12" y1="12" x2="108" y2="108" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#d97706"/>
                  <stop offset="100%" stop-color="#b45309"/>
                </linearGradient>
              </defs>
            </svg>
          </div>

          <!-- 429 — Terlalu Cepat / Batas Permintaan -->
          <div v-else-if="config.icon === 'speed'" class="relative">
            <svg class="w-36 h-36" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Gauge background arc -->
              <path d="M20 100 A55 55 0 0 1 120 100" stroke="rgba(255,255,255,0.15)"
                    stroke-width="12" stroke-linecap="round" fill="none"/>
              <!-- Green zone arc -->
              <path d="M20 100 A55 55 0 0 1 55 37" stroke="#22c55e"
                    stroke-width="12" stroke-linecap="round" fill="none" opacity="0.7"/>
              <!-- Amber zone arc -->
              <path d="M55 37 A55 55 0 0 1 85 31" stroke="#f59e0b"
                    stroke-width="12" stroke-linecap="round" fill="none" opacity="0.8"/>
              <!-- Red zone arc -->
              <path d="M85 31 A55 55 0 0 1 120 100" stroke="#ef4444"
                    stroke-width="12" stroke-linecap="round" fill="none" opacity="0.9"/>
              <!-- Zone labels -->
              <text x="22" y="118" font-family="Arial" font-size="9" fill="rgba(255,255,255,0.6)">0</text>
              <text x="114" y="118" font-family="Arial" font-size="9" fill="rgba(255,255,255,0.6)">MAX</text>
              <!-- Needle (pointing to max/red zone) -->
              <line x1="70" y1="100" x2="112" y2="48" stroke="white" stroke-width="3.5"
                    stroke-linecap="round" class="animate-needle-bounce"/>
              <!-- Needle pivot -->
              <circle cx="70" cy="100" r="7" fill="white"/>
              <circle cx="70" cy="100" r="3.5" fill="#dc2626"/>
              <!-- Warning flashes -->
              <circle cx="110" cy="40" r="4" fill="#fbbf24" class="animate-warning-flash"/>
              <circle cx="122" cy="70" r="3" fill="#fbbf24" class="animate-warning-flash-2"/>
              <!-- Center display -->
              <text x="70" y="78" font-family="Arial" font-size="11" font-weight="700"
                    fill="rgba(255,255,255,0.9)" text-anchor="middle">LIMIT</text>
            </svg>
          </div>

          <!-- 500 — Server Error -->
          <div v-else-if="config.icon === 'server'" class="relative">
            <svg class="w-36 h-36" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Server rack units -->
              <rect x="24" y="22" width="92" height="28" rx="6" fill="url(#serverGrad1)" fill-opacity="0.9"/>
              <rect x="24" y="22" width="92" height="28" rx="6" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
              <rect x="24" y="56" width="92" height="28" rx="6" fill="url(#serverGrad1)" fill-opacity="0.75"/>
              <rect x="24" y="56" width="92" height="28" rx="6" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
              <rect x="24" y="90" width="92" height="28" rx="6" fill="url(#serverGrad1)" fill-opacity="0.6"/>
              <rect x="24" y="90" width="92" height="28" rx="6" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
              <!-- LED indicators — unit 1 (error) -->
              <circle cx="102" cy="36" r="4" fill="#ef4444" class="animate-led-blink"/>
              <circle cx="90" cy="36" r="4" fill="#f59e0b"/>
              <circle cx="78" cy="36" r="4" fill="rgba(255,255,255,0.25)"/>
              <!-- LED indicators — unit 2 (normal) -->
              <circle cx="102" cy="70" r="4" fill="#22c55e"/>
              <circle cx="90" cy="70" r="4" fill="#22c55e"/>
              <circle cx="78" cy="70" r="4" fill="rgba(255,255,255,0.25)"/>
              <!-- LED indicators — unit 3 (normal) -->
              <circle cx="102" cy="104" r="4" fill="#22c55e"/>
              <circle cx="90" cy="104" r="4" fill="#22c55e"/>
              <circle cx="78" cy="104" r="4" fill="rgba(255,255,255,0.25)"/>
              <!-- Drive slots -->
              <rect x="34" y="31" width="28" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
              <rect x="34" y="65" width="28" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
              <rect x="34" y="99" width="28" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
              <!-- Lightning bolt (error) -->
              <path d="M66 0 L48 20 L62 20 L44 42 L80 16 L66 16 Z"
                    fill="#fbbf24" fill-opacity="0.95" class="animate-lightning"/>
              <defs>
                <linearGradient id="serverGrad1" x1="24" y1="22" x2="116" y2="50" gradientUnits="userSpaceOnUse">
                  <stop offset="0%" stop-color="#374151"/>
                  <stop offset="100%" stop-color="#1f2937"/>
                </linearGradient>
              </defs>
            </svg>
          </div>

          <!-- 503 — Pemeliharaan -->
          <div v-else-if="config.icon === 'wrench'" class="relative animate-float">
            <svg class="w-36 h-36" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- Gears -->
              <!-- Large gear (left) -->
              <g class="animate-spin-gear-slow" style="transform-origin:45px 75px">
                <circle cx="45" cy="75" r="28" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                <circle cx="45" cy="75" r="10" fill="rgba(255,255,255,0.2)"/>
                <!-- Gear teeth -->
                <rect x="41" y="43" width="8" height="9" rx="2" fill="rgba(255,255,255,0.5)"/>
                <rect x="41" y="99" width="8" height="9" rx="2" fill="rgba(255,255,255,0.5)"/>
                <rect x="13" y="71" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)"/>
                <rect x="69" y="71" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)"/>
                <rect x="22" y="52" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(-45 26.5 56)"/>
                <rect x="60" y="52" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(45 64.5 56)"/>
                <rect x="22" y="82" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(45 26.5 86)"/>
                <rect x="60" y="82" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(-45 64.5 86)"/>
              </g>
              <!-- Small gear (right) -->
              <g class="animate-spin-gear-fast" style="transform-origin:95px 60px">
                <circle cx="95" cy="60" r="18" fill="rgba(255,255,255,0.10)" stroke="rgba(255,255,255,0.35)" stroke-width="2"/>
                <circle cx="95" cy="60" r="6" fill="rgba(255,255,255,0.2)"/>
                <!-- Teeth -->
                <rect x="91.5" y="38" width="7" height="6" rx="1.5" fill="rgba(255,255,255,0.5)"/>
                <rect x="91.5" y="76" width="7" height="6" rx="1.5" fill="rgba(255,255,255,0.5)"/>
                <rect x="73" y="56.5" width="6" height="7" rx="1.5" fill="rgba(255,255,255,0.5)"/>
                <rect x="111" y="56.5" width="6" height="7" rx="1.5" fill="rgba(255,255,255,0.5)"/>
              </g>
              <!-- Wrench -->
              <g transform="rotate(-35 70 70)">
                <rect x="60" y="52" width="20" height="68" rx="5" fill="rgba(255,255,255,0.85)"/>
                <ellipse cx="70" cy="52" rx="16" ry="10" fill="rgba(255,255,255,0.85)"/>
                <ellipse cx="70" cy="52" rx="8" ry="5" fill="#1B6B3A"/>
                <rect x="63" y="106" width="14" height="14" rx="3" fill="rgba(255,255,255,0.85)"/>
              </g>
              <!-- Construction cone -->
              <path d="M108 118 L118 118 L113 98 Z" fill="#f59e0b" fill-opacity="0.9"/>
              <rect x="106" y="116" width="14" height="4" rx="2" fill="rgba(255,255,255,0.7)"/>
            </svg>
          </div>
        </div>

        <!-- Error code — large outlined number -->
        <div class="relative mb-2">
          <span class="text-8xl sm:text-9xl font-black text-white/10 select-none leading-none absolute inset-0 flex items-center justify-center blur-sm">
            {{ props.status }}
          </span>
          <span class="text-7xl sm:text-8xl font-black leading-none select-none relative"
                :style="`color: transparent; -webkit-text-stroke: 3px rgba(255,255,255,0.55);`">
            {{ props.status }}
          </span>
        </div>

        <!-- Title -->
        <h1 class="text-2xl sm:text-3xl font-black text-white mb-3 leading-tight">
          {{ config.title }}
        </h1>

        <!-- Description -->
        <p class="text-green-100/90 text-sm sm:text-base leading-relaxed max-w-md mx-auto mb-8">
          {{ config.description }}
        </p>

        <!-- Countdown (429 only) -->
        <div v-if="props.status === 429 && countdown > 0"
             class="mb-6 inline-flex items-center space-x-2 bg-amber-500/20 border border-amber-400/30 rounded-full px-5 py-2.5">
          <svg class="w-4 h-4 text-amber-300 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span class="text-amber-200 text-sm font-medium">
            Coba lagi dalam <strong class="text-amber-100">{{ countdown }}</strong> detik
          </span>
        </div>

        <!-- Auto-refresh countdown (503 only) -->
        <div v-if="props.status === 503 && maintenanceCountdown > 0"
             class="mb-6 inline-flex items-center space-x-2 bg-blue-500/20 border border-blue-400/30 rounded-full px-5 py-2.5">
          <div class="w-2 h-2 rounded-full bg-blue-300 animate-pulse"></div>
          <span class="text-blue-200 text-sm font-medium">
            Halaman akan dimuat ulang otomatis dalam
            <strong class="text-blue-100">{{ maintenanceCountdown }}</strong> detik
          </span>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 flex-wrap">

          <!-- Kembali ke halaman sebelumnya -->
          <button v-if="config.showBack"
                  @click="goBack"
                  class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl border border-white/30 text-white text-sm font-semibold hover:bg-white/10 active:scale-95 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-white/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            <span>Kembali</span>
          </button>

          <!-- Muat Ulang -->
          <button v-if="config.showReload"
                  @click="reloadPage"
                  class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl bg-white/20 border border-white/30 text-white text-sm font-semibold hover:bg-white/30 active:scale-95 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-white/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Muat Ulang</span>
          </button>

          <!-- Dashboard (jika sudah login) -->
          <button v-if="config.showDashboard && isAuthenticated"
                  @click="goToDashboard"
                  class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl bg-white text-green-800 text-sm font-bold hover:bg-green-50 active:scale-95 transition-all duration-150 shadow-lg focus:outline-none focus:ring-2 focus:ring-white/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Beranda</span>
          </button>

          <!-- Halaman Login (jika belum login) -->
          <button v-if="config.showLogin && !isAuthenticated"
                  @click="goToLogin"
                  class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl bg-white text-green-800 text-sm font-bold hover:bg-green-50 active:scale-95 transition-all duration-150 shadow-lg focus:outline-none focus:ring-2 focus:ring-white/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span>Masuk ke Sistem</span>
          </button>

          <!-- Coba Lagi (429) -->
          <button v-if="config.showRetry"
                  @click="reloadPage"
                  :disabled="countdown > 0"
                  class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl bg-white text-green-800 text-sm font-bold hover:bg-green-50 active:scale-95 transition-all duration-150 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 focus:outline-none focus:ring-2 focus:ring-white/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>{{ countdown > 0 ? `Tunggu ${countdown}d` : 'Coba Lagi' }}</span>
          </button>

        </div>

        <!-- Error code detail badge -->
        <div class="mt-8 pt-6 border-t border-white/10">
          <span class="inline-flex items-center space-x-2 text-green-300/70 text-xs">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>Kode HTTP <strong class="text-green-200/80">{{ props.status }}</strong> — {{ config.httpLabel }}</span>
          </span>
        </div>

      </div><!-- /glass card -->

      <!-- Footer -->
      <p class="text-center text-green-300/50 text-xs mt-6">
        SIMRAN UNISYA &copy; {{ currentYear }} — Universitas Islam Syarifuddin
      </p>

    </div><!-- /main card -->
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// ── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  /** HTTP status code passed from the Laravel error handler (e.g. 403, 404). */
  status: {
    type: Number,
    required: true,
  },
})

// ── Auth state ───────────────────────────────────────────────────────────────
const page          = usePage()
const isAuthenticated = computed(() => !! page.props.auth?.user)

// ── Tahun berjalan ───────────────────────────────────────────────────────────
const currentYear = new Date().getFullYear()

// ── Konfigurasi per status ───────────────────────────────────────────────────
/**
 * Masing-masing entri mendefinisikan:
 *   title       — Judul singkat bahasa Indonesia
 *   description — Penjelasan informatif
 *   httpLabel   — Label teknis HTTP resmi
 *   icon        — Identifikasi ilustrasi SVG
 *   showBack         — Tampilkan tombol "Kembali"
 *   showReload       — Tampilkan tombol "Muat Ulang"
 *   showDashboard    — Tampilkan tombol "Beranda" (hanya jika login)
 *   showLogin        — Tampilkan tombol "Masuk" (hanya jika belum login)
 *   showRetry        — Tampilkan tombol "Coba Lagi" (khusus 429)
 */
const errorConfigs = {
  403: {
    title:         'Akses Ditolak',
    description:   'Anda tidak memiliki izin untuk mengakses halaman ini. Jika Anda yakin ini adalah kesalahan, silakan hubungi administrator sistem.',
    httpLabel:     'Forbidden',
    icon:          'shield',
    showBack:      true,
    showReload:    false,
    showDashboard: true,
    showLogin:     true,
    showRetry:     false,
  },
  404: {
    title:         'Halaman Tidak Ditemukan',
    description:   'Halaman yang Anda cari tidak ada, mungkin sudah dipindahkan, dihapus, atau alamatnya salah. Periksa kembali URL yang Anda masukkan.',
    httpLabel:     'Not Found',
    icon:          'search',
    showBack:      true,
    showReload:    false,
    showDashboard: true,
    showLogin:     true,
    showRetry:     false,
  },
  419: {
    title:         'Sesi Halaman Berakhir',
    description:   'Formulir atau sesi keamanan Anda telah kedaluwarsa karena tidak aktif dalam jangka waktu tertentu. Muat ulang halaman untuk melanjutkan aktivitas Anda.',
    httpLabel:     'Page Expired',
    icon:          'clock',
    showBack:      false,
    showReload:    true,
    showDashboard: true,
    showLogin:     false,
    showRetry:     false,
  },
  429: {
    title:         'Terlalu Banyak Permintaan',
    description:   'Anda telah melakukan terlalu banyak permintaan dalam waktu singkat. Sistem membatasi akses sementara untuk melindungi layanan. Harap tunggu sebentar sebelum mencoba kembali.',
    httpLabel:     'Too Many Requests',
    icon:          'speed',
    showBack:      true,
    showReload:    false,
    showDashboard: false,
    showLogin:     false,
    showRetry:     true,
  },
  500: {
    title:         'Terjadi Kesalahan Server',
    description:   'Server mengalami kesalahan internal yang tidak terduga saat memproses permintaan Anda. Tim teknis kami telah diberitahu dan sedang menanganinya. Silakan coba lagi beberapa saat kemudian.',
    httpLabel:     'Internal Server Error',
    icon:          'server',
    showBack:      true,
    showReload:    true,
    showDashboard: true,
    showLogin:     false,
    showRetry:     false,
  },
  503: {
    title:         'Layanan Sedang Dalam Pemeliharaan',
    description:   'Sistem SIMRAN UNISYA sedang menjalani pemeliharaan terjadwal untuk meningkatkan performa dan keandalan. Kami akan segera kembali. Terima kasih atas kesabaran Anda.',
    httpLabel:     'Service Unavailable',
    icon:          'wrench',
    showBack:      false,
    showReload:    true,
    showDashboard: false,
    showLogin:     false,
    showRetry:     false,
  },
}

// Default fallback jika status tidak dikenali
const defaultConfig = {
  title:         'Terjadi Kesalahan',
  description:   'Terjadi kesalahan yang tidak terduga. Silakan kembali ke halaman sebelumnya atau hubungi administrator jika masalah berlanjut.',
  httpLabel:     'Error',
  icon:          'server',
  showBack:      true,
  showReload:    true,
  showDashboard: true,
  showLogin:     true,
  showRetry:     false,
}

const config = computed(() => errorConfigs[props.status] ?? defaultConfig)

// ── Countdown untuk 429 (30 detik) ──────────────────────────────────────────
const countdown = ref(props.status === 429 ? 30 : 0)
let countdownTimer = null

if (props.status === 429) {
  onMounted(() => {
    countdownTimer = setInterval(() => {
      if (countdown.value > 0) {
        countdown.value--
      } else {
        clearInterval(countdownTimer)
      }
    }, 1000)
  })
  onUnmounted(() => clearInterval(countdownTimer))
}

// ── Auto-reload untuk 503 (60 detik) ────────────────────────────────────────
const maintenanceCountdown = ref(props.status === 503 ? 60 : 0)
let maintenanceTimer = null

if (props.status === 503) {
  onMounted(() => {
    maintenanceTimer = setInterval(() => {
      if (maintenanceCountdown.value > 1) {
        maintenanceCountdown.value--
      } else {
        clearInterval(maintenanceTimer)
        window.location.reload()
      }
    }, 1000)
  })
  onUnmounted(() => clearInterval(maintenanceTimer))
}

// ── Aksi tombol ──────────────────────────────────────────────────────────────
function goBack()        { window.history.back() }
function reloadPage()    { window.location.reload() }
function goToDashboard() { router.visit(route('dashboard')) }
function goToLogin()     { router.visit(route('login')) }
</script>

<style scoped>
/* ── Card entrance animation ─────────────────────────────────────────────── */
.animate-card-in {
  animation: cardIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}
@keyframes cardIn {
  from { opacity: 0; transform: translateY(28px) scale(0.97); }
  to   { opacity: 1; transform: translateY(0)    scale(1);    }
}

/* ── Background blobs ────────────────────────────────────────────────────── */
.animate-blob-1 {
  animation: blobDrift1 8s ease-in-out infinite alternate;
}
.animate-blob-2 {
  animation: blobDrift2 10s ease-in-out infinite alternate;
}
.animate-blob-3 {
  animation: blobDrift3 12s ease-in-out infinite alternate;
}
@keyframes blobDrift1 {
  from { transform: translate(0,    0   ) scale(1);    }
  to   { transform: translate(30px, 20px) scale(1.1); }
}
@keyframes blobDrift2 {
  from { transform: translate(0,     0    ) scale(1);    }
  to   { transform: translate(-25px, -15px) scale(1.08); }
}
@keyframes blobDrift3 {
  from { transform: translate(-50%, -50%) scale(1);    }
  to   { transform: translate(-48%, -53%) scale(1.12); }
}

/* ── Floating animation (404, 419, 503) ─────────────────────────────────── */
.animate-float {
  animation: floatUpDown 3.5s ease-in-out infinite;
}
@keyframes floatUpDown {
  0%, 100% { transform: translateY(0);    }
  50%       { transform: translateY(-10px); }
}

/* ── Shield shake (403) ─────────────────────────────────────────────────── */
.animate-shake-slow {
  animation: shakeGently 4s ease-in-out infinite;
}
@keyframes shakeGently {
  0%, 100% { transform: rotate(0deg);    }
  15%       { transform: rotate(-6deg);   }
  30%       { transform: rotate(6deg);    }
  45%       { transform: rotate(-4deg);   }
  60%       { transform: rotate(4deg);    }
  75%       { transform: rotate(-2deg);   }
  90%       { transform: rotate(2deg);    }
}

/* ── Ping / pulse ring (403) ─────────────────────────────────────────────── */
.animate-ping-slow {
  animation: pingSlow 2.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes pingSlow {
  0%   { transform: scale(1.1); opacity: 0.6; }
  100% { transform: scale(1.5); opacity: 0;   }
}

/* ── Twinkling stars (404) ───────────────────────────────────────────────── */
.animate-twinkle-1 {
  animation: twinkle 2s ease-in-out 0s   infinite;
}
.animate-twinkle-2 {
  animation: twinkle 2s ease-in-out 0.7s infinite;
}
.animate-twinkle-3 {
  animation: twinkle 2s ease-in-out 1.3s infinite;
}
@keyframes twinkle {
  0%, 100% { opacity: 0.3; transform: scale(0.8); }
  50%       { opacity: 1;   transform: scale(1.3); }
}

/* ── Clock hour hand (419) ───────────────────────────────────────────────── */
.animate-hour-hand {
  transform-origin: 60px 60px;
  animation: tickFast 2s steps(12, end) infinite;
}
@keyframes tickFast {
  from { transform: rotate(0deg);   }
  to   { transform: rotate(360deg); }
}

/* ── Gauge needle bounce (429) ───────────────────────────────────────────── */
.animate-needle-bounce {
  transform-origin: 70px 100px;
  animation: needleBounce 1.2s ease-in-out infinite;
}
@keyframes needleBounce {
  0%, 100% { transform: rotate(0deg);   }
  40%       { transform: rotate(-10deg); }
  70%       { transform: rotate(8deg);   }
}

/* ── Warning flash dots (429) ────────────────────────────────────────────── */
.animate-warning-flash {
  animation: warningFlash 0.9s ease-in-out 0s infinite;
}
.animate-warning-flash-2 {
  animation: warningFlash 0.9s ease-in-out 0.4s infinite;
}
@keyframes warningFlash {
  0%, 100% { opacity: 0.3; }
  50%       { opacity: 1;   }
}

/* ── LED blink (500 — error LED) ─────────────────────────────────────────── */
.animate-led-blink {
  animation: ledBlink 0.7s ease-in-out infinite;
}
@keyframes ledBlink {
  0%, 100% { opacity: 1;   }
  50%       { opacity: 0.1; }
}

/* ── Lightning flash (500) ───────────────────────────────────────────────── */
.animate-lightning {
  animation: lightningFlash 2.5s ease-in-out infinite;
}
@keyframes lightningFlash {
  0%, 85%, 100% { opacity: 1;   filter: drop-shadow(0 0 4px #fbbf24); }
  90%            { opacity: 0.1; filter: none; }
  95%            { opacity: 1;   filter: drop-shadow(0 0 8px #fbbf24); }
}

/* ── Slow gear spin (503) ────────────────────────────────────────────────── */
.animate-spin-gear-slow {
  animation: spinGear 8s linear infinite;
}
@keyframes spinGear {
  from { transform: rotate(0deg);   }
  to   { transform: rotate(360deg); }
}

/* ── Fast gear spin (503 — counter-rotate) ───────────────────────────────── */
.animate-spin-gear-fast {
  animation: spinGearFast 4s linear infinite reverse;
}
@keyframes spinGearFast {
  from { transform: rotate(0deg);   }
  to   { transform: rotate(360deg); }
}
</style>
