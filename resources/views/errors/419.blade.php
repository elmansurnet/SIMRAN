@php
$code        = 419;
$title       = 'Sesi Halaman Berakhir';
$description = 'Formulir atau sesi keamanan Anda telah kedaluwarsa karena tidak aktif dalam jangka waktu tertentu. Muat ulang halaman untuk melanjutkan aktivitas Anda.';
$httpLabel   = 'Page Expired';
$floatClass  = 'float';
$reloadBtn   = true;
$primaryUrl  = url()->previous() ?: route('login');
$primaryLabel = 'Kembali & Coba Lagi';
$svg = <<<'SVG'
<svg width="128" height="128" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="60" cy="60" r="48" fill="url(#sg419)" fill-opacity="0.9"/>
  <circle cx="60" cy="60" r="48" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
  <circle cx="60" cy="60" r="42" fill="rgba(255,255,255,0.08)"/>
  <rect x="58.5" y="16" width="3" height="8" rx="1.5" fill="rgba(255,255,255,0.7)"/>
  <rect x="58.5" y="96" width="3" height="8" rx="1.5" fill="rgba(255,255,255,0.7)"/>
  <rect x="16"   y="58.5" width="8" height="3" rx="1.5" fill="rgba(255,255,255,0.7)"/>
  <rect x="96"   y="58.5" width="8" height="3" rx="1.5" fill="rgba(255,255,255,0.7)"/>
  <line x1="60" y1="60" x2="42" y2="34" stroke="white" stroke-width="4" stroke-linecap="round"/>
  <line x1="60" y1="60" x2="60" y2="26" stroke="white" stroke-width="3" stroke-linecap="round"/>
  <circle cx="60" cy="60" r="5" fill="white"/>
  <path d="M36 14 Q32 18 34 22" stroke="rgba(255,255,255,0.5)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
  <path d="M84 14 Q88 18 86 22" stroke="rgba(255,255,255,0.5)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
  <text x="60" y="88" font-family="Arial" font-size="14" font-weight="900" fill="rgba(255,255,255,0.8)" text-anchor="middle">!</text>
  <defs>
    <linearGradient id="sg419" x1="12" y1="12" x2="108" y2="108" gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="#d97706"/>
      <stop offset="100%" stop-color="#b45309"/>
    </linearGradient>
  </defs>
</svg>
SVG;
@endphp
@include('errors.layout')
