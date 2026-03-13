<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1B6B3A">
  <title>{{ $title }} — SIMRAN UNISYA</title>
  <style>
    /* ── Reset & Base ───────────────────────────────────── */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      background: linear-gradient(135deg, #14532d 0%, #166534 40%, #047857 100%);
      overflow: hidden;
      position: relative;
    }

    /* ── Decorative blobs ───────────────────────────────── */
    body::before {
      content: '';
      position: absolute;
      top: -80px; left: -80px;
      width: 320px; height: 320px;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
      filter: blur(40px);
      animation: blobA 8s ease-in-out infinite alternate;
    }
    body::after {
      content: '';
      position: absolute;
      bottom: -80px; right: -80px;
      width: 400px; height: 400px;
      border-radius: 50%;
      background: rgba(167,243,208,0.06);
      filter: blur(50px);
      animation: blobB 10s ease-in-out infinite alternate;
    }
    @keyframes blobA { from{transform:translate(0,0) scale(1)} to{transform:translate(30px,20px) scale(1.1)} }
    @keyframes blobB { from{transform:translate(0,0) scale(1)} to{transform:translate(-25px,-15px) scale(1.08)} }

    /* ── Card ───────────────────────────────────────────── */
    .card {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 580px;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 24px;
      padding: 48px 40px;
      text-align: center;
      box-shadow: 0 25px 50px rgba(0,0,0,0.3);
      animation: cardIn 0.5s cubic-bezier(0.16,1,0.3,1) both;
    }
    @keyframes cardIn {
      from { opacity:0; transform: translateY(28px) scale(0.97); }
      to   { opacity:1; transform: translateY(0)    scale(1);    }
    }

    /* ── Brand ──────────────────────────────────────────── */
    .brand {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 28px;
    }
    .brand-icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      background: rgba(255,255,255,0.2);
      display: flex; align-items: center; justify-content: center;
    }
    .brand-icon svg { width: 22px; height: 22px; color: white; fill: white; }
    .brand-name { line-height: 1; }
    .brand-main { color: white; font-weight: 900; font-size: 16px; letter-spacing: 1px; }
    .brand-sub  { color: #86efac; font-size: 11px; font-weight: 600; margin-top: 2px; }

    /* ── SVG illustration ───────────────────────────────── */
    .illustration { margin-bottom: 20px; }
    .illustration svg { overflow: visible; }

    /* ── Error code ─────────────────────────────────────── */
    .error-code {
      font-size: clamp(64px, 15vw, 100px);
      font-weight: 900;
      line-height: 1;
      margin-bottom: 8px;
      color: transparent;
      -webkit-text-stroke: 3px rgba(255,255,255,0.5);
      letter-spacing: -2px;
    }

    /* ── Title & description ────────────────────────────── */
    h1 {
      color: white;
      font-size: clamp(20px, 4vw, 26px);
      font-weight: 800;
      margin-bottom: 12px;
      line-height: 1.3;
    }
    .desc {
      color: rgba(209,250,229,0.85);
      font-size: 14px;
      line-height: 1.75;
      max-width: 440px;
      margin: 0 auto 28px;
    }

    /* ── Buttons ────────────────────────────────────────── */
    .btn-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 11px 22px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: transform 0.12s, opacity 0.12s;
      border: none;
    }
    .btn:active { transform: scale(0.95); }
    .btn-primary {
      background: white;
      color: #14532d;
      box-shadow: 0 4px 14px rgba(0,0,0,0.2);
    }
    .btn-primary:hover { background: #f0fdf4; }
    .btn-outline {
      background: rgba(255,255,255,0.12);
      color: white;
      border: 1px solid rgba(255,255,255,0.3);
    }
    .btn-outline:hover { background: rgba(255,255,255,0.2); }
    .btn svg { width: 16px; height: 16px; }

    /* ── HTTP badge ─────────────────────────────────────── */
    .http-badge {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid rgba(255,255,255,0.12);
      font-size: 11px;
      color: rgba(167,243,208,0.6);
    }

    /* ── Float animation ────────────────────────────────── */
    .float { animation: floatY 3.5s ease-in-out infinite; }
    @keyframes floatY {
      0%,100% { transform: translateY(0);    }
      50%      { transform: translateY(-8px); }
    }

    /* ── Shake animation ────────────────────────────────── */
    .shake { animation: shakeGently 4s ease-in-out infinite; }
    @keyframes shakeGently {
      0%,100%{transform:rotate(0)}
      15%{transform:rotate(-6deg)} 30%{transform:rotate(6deg)}
      45%{transform:rotate(-4deg)} 60%{transform:rotate(4deg)}
      75%{transform:rotate(-2deg)} 90%{transform:rotate(2deg)}
    }

    /* ── Blink ──────────────────────────────────────────── */
    .blink { animation: blinkAnim 0.8s ease-in-out infinite; }
    @keyframes blinkAnim { 0%,100%{opacity:1} 50%{opacity:0.1} }

    /* ── Spin ───────────────────────────────────────────── */
    .spin-slow { animation: spinFull 8s linear infinite; }
    .spin-fast { animation: spinFull 4s linear infinite reverse; }
    @keyframes spinFull { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

    /* ── Footer ─────────────────────────────────────────── */
    .footer { margin-top: 20px; font-size: 11px; color: rgba(167,243,208,0.4); }

    @media (max-width: 480px) {
      .card { padding: 32px 24px; }
    }
  </style>
</head>
<body>
  <div>
    <div class="card">
      <!-- Brand -->
      <div class="brand">
        <div class="brand-icon">
          <svg viewBox="0 0 20 20" fill="white">
            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="brand-name">
          <div class="brand-main">SIMRAN</div>
          <div class="brand-sub">UNISYA</div>
        </div>
      </div>

      <!-- SVG Illustration -->
      <div class="illustration {{ $floatClass ?? '' }}">
        {!! $svg !!}
      </div>

      <!-- Error code -->
      <div class="error-code">{{ $code }}</div>

      <!-- Title & description -->
      <h1>{{ $title }}</h1>
      <p class="desc">{{ $description }}</p>

      <!-- Buttons -->
      <div class="btn-group">
        @isset($backUrl)
          <a href="javascript:history.back()" class="btn btn-outline">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Kembali
          </a>
        @endisset
        @isset($reloadBtn)
          <a href="javascript:location.reload()" class="btn btn-outline">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Muat Ulang
          </a>
        @endisset
        @isset($primaryUrl)
          <a href="{{ $primaryUrl }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              {!! $primaryIcon ?? '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>' !!}
            </svg>
            {{ $primaryLabel ?? 'Beranda' }}
          </a>
        @endisset
      </div>

      <!-- HTTP badge -->
      <div class="http-badge">
        Kode HTTP <strong style="color:rgba(167,243,208,0.8)">{{ $code }}</strong> — {{ $httpLabel }}
      </div>
    </div>

    <p class="footer" style="text-align:center;margin-top:16px;">
      SIMRAN UNISYA &copy; {{ date('Y') }} — Universitas Islam Syarifuddin
    </p>
  </div>
</body>
</html>
