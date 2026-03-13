@php
$code        = 500;
$title       = 'Terjadi Kesalahan Server';
$description = 'Server mengalami kesalahan internal yang tidak terduga saat memproses permintaan Anda. Tim teknis kami sedang menanganinya. Silakan coba lagi beberapa saat kemudian.';
$httpLabel   = 'Internal Server Error';
$floatClass  = '';
$backUrl     = true;
$reloadBtn   = true;
$primaryUrl  = route('login');
$primaryLabel = 'Kembali ke Login';
$svg = <<<'SVG'
<svg width="144" height="128" viewBox="0 0 140 120" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="24" y="22" width="92" height="28" rx="6" fill="rgba(55,65,81,0.9)"/>
  <rect x="24" y="22" width="92" height="28" rx="6" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
  <rect x="24" y="56" width="92" height="28" rx="6" fill="rgba(55,65,81,0.75)"/>
  <rect x="24" y="56" width="92" height="28" rx="6" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
  <rect x="24" y="90" width="92" height="24" rx="6" fill="rgba(55,65,81,0.6)"/>
  <rect x="24" y="90" width="92" height="24" rx="6" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
  <circle cx="102" cy="36" r="4" fill="#ef4444" class="blink"/>
  <circle cx="90"  cy="36" r="4" fill="#f59e0b"/>
  <circle cx="78"  cy="36" r="4" fill="rgba(255,255,255,0.2)"/>
  <circle cx="102" cy="70" r="4" fill="#22c55e"/>
  <circle cx="90"  cy="70" r="4" fill="#22c55e"/>
  <circle cx="102" cy="102" r="4" fill="#22c55e"/>
  <circle cx="90"  cy="102" r="4" fill="#22c55e"/>
  <rect x="34" y="31" width="28" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
  <rect x="34" y="65" width="28" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
  <rect x="34" y="97" width="28" height="4" rx="2" fill="rgba(255,255,255,0.3)"/>
  <path d="M66 0 L48 20 L62 20 L44 42 L80 16 L66 16 Z" fill="#fbbf24" fill-opacity="0.95"/>
</svg>
SVG;
@endphp
@include('errors.layout')
