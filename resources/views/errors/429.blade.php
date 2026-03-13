@php
$code        = 429;
$title       = 'Terlalu Banyak Permintaan';
$description = 'Anda telah melakukan terlalu banyak permintaan dalam waktu singkat. Sistem membatasi akses sementara untuk melindungi layanan. Harap tunggu beberapa saat sebelum mencoba kembali.';
$httpLabel   = 'Too Many Requests';
$floatClass  = '';
$backUrl     = true;
$reloadBtn   = true;
$svg = <<<'SVG'
<svg width="144" height="130" viewBox="0 0 140 130" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M20 100 A55 55 0 0 1 120 100" stroke="rgba(255,255,255,0.15)" stroke-width="12" stroke-linecap="round" fill="none"/>
  <path d="M20 100 A55 55 0 0 1 55 37"  stroke="#22c55e"  stroke-width="12" stroke-linecap="round" fill="none" opacity="0.7"/>
  <path d="M55 37  A55 55 0 0 1 85 31"  stroke="#f59e0b"  stroke-width="12" stroke-linecap="round" fill="none" opacity="0.8"/>
  <path d="M85 31  A55 55 0 0 1 120 100" stroke="#ef4444" stroke-width="12" stroke-linecap="round" fill="none" opacity="0.9"/>
  <text x="22"  y="118" font-family="Arial" font-size="9" fill="rgba(255,255,255,0.6)">0</text>
  <text x="112" y="118" font-family="Arial" font-size="9" fill="rgba(255,255,255,0.6)">MAX</text>
  <line x1="70" y1="100" x2="112" y2="48" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
  <circle cx="70" cy="100" r="7"   fill="white"/>
  <circle cx="70" cy="100" r="3.5" fill="#dc2626"/>
  <text x="70" y="78" font-family="Arial" font-size="11" font-weight="700" fill="rgba(255,255,255,0.9)" text-anchor="middle">LIMIT</text>
</svg>
SVG;
@endphp
@include('errors.layout')
