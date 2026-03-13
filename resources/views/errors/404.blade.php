@php
$code        = 404;
$title       = 'Halaman Tidak Ditemukan';
$description = 'Halaman yang Anda cari tidak ada, mungkin sudah dipindahkan, dihapus, atau alamatnya salah. Periksa kembali URL yang Anda masukkan.';
$httpLabel   = 'Not Found';
$floatClass  = 'float';
$backUrl     = true;
$primaryUrl  = route('login');
$primaryLabel = 'Beranda / Masuk';
$svg = <<<'SVG'
<svg width="144" height="144" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="20" y="15" width="70" height="90" rx="8" fill="white" fill-opacity="0.15"/>
  <rect x="20" y="15" width="70" height="90" rx="8" stroke="rgba(255,255,255,0.5)" stroke-width="2"/>
  <path d="M70 15L90 35H70V15Z" fill="white" fill-opacity="0.1"/>
  <path d="M70 15L90 35H70V15Z" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" fill="none"/>
  <rect x="32" y="48" width="38" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
  <rect x="32" y="58" width="30" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
  <rect x="32" y="68" width="34" height="4" rx="2" fill="rgba(255,255,255,0.2)"/>
  <text x="55" y="95" font-family="Arial" font-size="36" font-weight="900"
        fill="rgba(255,255,255,0.45)" text-anchor="middle">?</text>
  <line x1="94" y1="94" x2="116" y2="118" stroke="rgba(255,255,255,0.8)" stroke-width="8" stroke-linecap="round"/>
  <circle cx="83" cy="82" r="24" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.7)" stroke-width="5"/>
  <path d="M72 70 Q76 66 82 68" stroke="rgba(255,255,255,0.5)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
</svg>
SVG;
@endphp
@include('errors.layout')
