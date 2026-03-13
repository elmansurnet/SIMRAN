@php
$code        = 503;
$title       = 'Layanan Sedang Dalam Pemeliharaan';
$description = 'Sistem SIMRAN UNISYA sedang menjalani pemeliharaan terjadwal untuk meningkatkan performa dan keandalan. Kami akan segera kembali. Terima kasih atas kesabaran Anda.';
$httpLabel   = 'Service Unavailable';
$floatClass  = 'float';
$reloadBtn   = true;
$svg = <<<'SVG'
<svg width="144" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
  <!-- Large gear -->
  <g class="spin-slow" style="transform-origin:45px 75px">
    <circle cx="45" cy="75" r="28" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
    <circle cx="45" cy="75" r="10" fill="rgba(255,255,255,0.2)"/>
    <rect x="41" y="43" width="8" height="9" rx="2" fill="rgba(255,255,255,0.5)"/>
    <rect x="41" y="99" width="8" height="9" rx="2" fill="rgba(255,255,255,0.5)"/>
    <rect x="13" y="71" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)"/>
    <rect x="69" y="71" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)"/>
    <rect x="22" y="52" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(-45 26.5 56)"/>
    <rect x="60" y="52" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(45 64.5 56)"/>
    <rect x="22" y="82" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(45 26.5 86)"/>
    <rect x="60" y="82" width="9" height="8" rx="2" fill="rgba(255,255,255,0.5)" transform="rotate(-45 64.5 86)"/>
  </g>
  <!-- Small gear -->
  <g class="spin-fast" style="transform-origin:95px 60px">
    <circle cx="95" cy="60" r="18" fill="rgba(255,255,255,0.10)" stroke="rgba(255,255,255,0.35)" stroke-width="2"/>
    <circle cx="95" cy="60" r="6" fill="rgba(255,255,255,0.2)"/>
    <rect x="91.5" y="38" width="7" height="6" rx="1.5" fill="rgba(255,255,255,0.5)"/>
    <rect x="91.5" y="76" width="7" height="6" rx="1.5" fill="rgba(255,255,255,0.5)"/>
    <rect x="73"   y="56.5" width="6" height="7" rx="1.5" fill="rgba(255,255,255,0.5)"/>
    <rect x="111"  y="56.5" width="6" height="7" rx="1.5" fill="rgba(255,255,255,0.5)"/>
  </g>
  <!-- Wrench -->
  <g transform="rotate(-35 70 70)">
    <rect x="60" y="52" width="20" height="68" rx="5" fill="rgba(255,255,255,0.85)"/>
    <ellipse cx="70" cy="52" rx="16" ry="10" fill="rgba(255,255,255,0.85)"/>
    <ellipse cx="70" cy="52" rx="8"  ry="5"  fill="#1B6B3A"/>
    <rect x="63" y="106" width="14" height="14" rx="3" fill="rgba(255,255,255,0.85)"/>
  </g>
</svg>
SVG;
@endphp
@include('errors.layout')
