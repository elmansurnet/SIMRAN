@php
$code        = 403;
$title       = 'Akses Ditolak';
$description = 'Anda tidak memiliki izin untuk mengakses halaman ini. Jika Anda yakin ini adalah kesalahan, silakan hubungi administrator sistem.';
$httpLabel   = 'Forbidden';
$floatClass  = 'shake';
$backUrl     = true;
$primaryUrl  = route('login');
$primaryLabel = 'Masuk ke Sistem';
$primaryIcon  = '<path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>';
$svg = <<<'SVG'
<svg width="128" height="128" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M60 8L16 26V60C16 84 38 104 60 112C82 104 104 84 104 60V26L60 8Z"
        fill="url(#sg403)" fill-opacity="0.9"/>
  <path d="M60 8L16 26V60C16 84 38 104 60 112C82 104 104 84 104 60V26L60 8Z"
        stroke="rgba(255,255,255,0.4)" stroke-width="2" fill="none"/>
  <rect x="42" y="58" width="36" height="28" rx="5" fill="white" fill-opacity="0.9"/>
  <path d="M50 58V48C50 40.3 70 40.3 70 48V58"
        stroke="white" stroke-width="5" stroke-linecap="round" fill="none"/>
  <circle cx="60" cy="70" r="5" fill="#1B6B3A"/>
  <rect x="57.5" y="70" width="5" height="9" rx="2" fill="#1B6B3A"/>
  <path d="M28 30L44 24" stroke="rgba(255,255,255,0.5)" stroke-width="3" stroke-linecap="round"/>
  <defs>
    <linearGradient id="sg403" x1="16" y1="8" x2="104" y2="112" gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="#ef4444"/>
      <stop offset="100%" stop-color="#dc2626"/>
    </linearGradient>
  </defs>
</svg>
SVG;
@endphp
@include('errors.layout')
