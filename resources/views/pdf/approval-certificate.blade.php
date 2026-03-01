<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Sertifikat Persetujuan — {{ $proposal->code }}</title>
<style>
/* ═══════════════════════════════════════════════════════════════
   SIMRAN UNISYA — Unified PDF Design System v2
   Paper : F4/Folio  8.27 × 12.99 in  (portrait)
   Engine: dompdf via barryvdh/laravel-dompdf
   Font  : DejaVu Sans (dompdf native)
   ═══════════════════════════════════════════════════════════════ */

* { margin:0; padding:0; box-sizing:border-box; }
body  { font-family:'DejaVu Sans', Arial, sans-serif; font-size:9pt; color:#1a1a1a; background:#fff; }
.page { padding:13mm 14mm 12mm 14mm; }

/* ── HEADER ──────────────────────────────────────────────────── */
.hdr {
  background-color:#1B6B3A;
  color:#fff;
  padding:13px 17px 12px 17px;
  border-radius:7px;
  margin-bottom:13px;
}
.hdr-inner { width:100%; border-collapse:collapse; }
.hdr-inner td { border:none; background:transparent; padding:0; vertical-align:top; }
.hdr-brand  { font-size:18pt; font-weight:900; letter-spacing:1px; color:#fff; line-height:1.1; }
.hdr-tag    { font-size:7pt; color:#a7f3d0; margin-top:3px; }
.hdr-stamp  { text-align:right; font-size:7.5pt; color:#d1fae5; line-height:1.55; }
.hdr-div    { border-top:1px solid rgba(255,255,255,0.25); margin-top:9px; padding-top:8px; }
.hdr-title  { font-size:13pt; font-weight:700; color:#fff; }
.hdr-sub    { font-size:7.5pt; color:#d1fae5; margin-top:3px; }

/* ── SECTION HEADING ─────────────────────────────────────────── */
.sec {
  font-size:9pt; font-weight:700; color:#1B6B3A;
  border-bottom:2px solid #d1fae5;
  padding-bottom:3px; margin-bottom:7px; margin-top:12px;
  letter-spacing:0.2px;
}

/* ── CERTIFICATE BANNER ─────────────────────────────────────── */
.cert-banner {
  border:2px solid #d1fae5;
  border-radius:8px;
  background:#f0fdf4;
  padding:14px 18px;
  text-align:center;
  margin-bottom:13px;
}
.cert-lbl  { font-size:7.5pt; color:#6b7280; margin-bottom:3px; }
.cert-code { font-size:8pt; color:#1B6B3A; font-weight:700; letter-spacing:1px; margin-bottom:5px; }
.cert-name { font-size:13pt; font-weight:900; color:#145A32; margin:4px 0; }
.cert-meta { font-size:8pt; color:#374151; margin-top:4px; }
.cert-amt-lbl { font-size:7pt; color:#6b7280; margin-top:10px; text-transform:uppercase; letter-spacing:0.4px; }
.cert-amt { font-size:14pt; font-weight:900; color:#166534; margin-top:2px; }
.cert-period { font-size:8pt; color:#374151; margin-top:6px; }
.cert-approved{ font-size:8pt; color:#16a34a; font-weight:700; margin-top:4px; }

/* ── KPI CARDS ───────────────────────────────────────────────── */
.kpi { width:100%; border-collapse:separate; border-spacing:5px 0; margin-bottom:12px; }
.kpi td { border:1.5px solid #e5e7eb; border-radius:6px; padding:8px 10px; text-align:center; vertical-align:top; }
.kpi .lbl { font-size:6pt; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
.kpi .val { font-size:9.5pt; font-weight:700; color:#111827; }
.k-green { background:#f0fdf4; border-color:#86efac; }
.k-green .val { color:#166534; }
.k-blue  .val { color:#1d4ed8; }
.k-gray  { background:#f9fafb; }

/* ── DETAIL TABLE ────────────────────────────────────────────── */
.detail { width:100%; border-collapse:collapse; margin-bottom:12px; }
.detail td { padding:3.5px 6px; font-size:8.5pt; border-bottom:1px solid #f0f0f0; }
.detail .k { color:#6b7280; font-weight:600; width:30%; }
.detail .v { width:70%; }

/* ── APPROVERS TABLE ─────────────────────────────────────────── */
.appr { width:100%; border-collapse:collapse; margin-bottom:13px; }
.appr thead tr { background:#1B6B3A; }
.appr th {
  color:#fff; padding:6px 7px;
  font-size:7pt; font-weight:600;
  text-transform:uppercase; letter-spacing:0.35px; text-align:left;
}
.appr td { padding:5.5px 7px; font-size:8.5pt; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.appr .alt td { background:#f9fafb; }
.appr .chk { color:#16a34a; font-weight:700; font-size:9pt; }

/* ── BADGE ───────────────────────────────────────────────────── */
.badge { display:inline; padding:2px 8px; border-radius:99px; font-size:7pt; font-weight:700; }
.b-green { background:#dcfce7; color:#166534; }
.b-blue  { background:#dbeafe; color:#1d4ed8; }
.b-gray  { background:#f3f4f6; color:#374151; }

/* ── FOOTER / QR + SIGNATURE ─────────────────────────────────── */
.ftr-wrap { margin-top:14px; padding-top:10px; border-top:2px solid #d1fae5; }
.ftr { width:100%; border-collapse:collapse; }
.ftr td { border:none; background:transparent; padding:0; vertical-align:top; }
.ftr-l { font-size:7.5pt; color:#6b7280; line-height:1.7; }
.ftr-l strong { color:#1B6B3A; }
.ftr-qr { text-align:center; width:100pt; }
.ftr-qr img { width:88pt; height:88pt; display:block; margin:0 auto; }
.ftr-qr p { font-size:6.5pt; color:#6b7280; margin-top:3px; }
.sig-block { text-align:center; width:130pt; }
.sig-line  { border-top:1px solid #374151; width:120pt; display:block; margin:44px auto 4px auto; }
.sig-name  { font-size:8.5pt; font-weight:700; color:#1a1a1a; }
.sig-role  { font-size:7.5pt; color:#6b7280; }

.notice-box {
  background:#fffbeb; border:1px solid #fde68a; border-radius:6px;
  padding:8px 12px; margin-bottom:12px;
  font-size:7.5pt; color:#92400e; line-height:1.6;
}
</style>
</head>
<body>
<div class="page">

{{-- ══════════════════ HEADER ══════════════════ --}}
<div class="hdr">
  <table class="hdr-inner" width="100%">
    <tr>
      <td>
        <div class="hdr-brand">SIMRAN UNISYA</div>
        <div class="hdr-tag">Sistem Manajemen dan Realisasi Anggaran — Universitas Islam Syarifuddin</div>
      </td>
      <td style="white-space:nowrap; text-align:right;">
        <div class="hdr-stamp">
          Sertifikat Persetujuan Anggaran<br>
          Diterbitkan: {{ now()->format('d/m/Y H:i') }} WIB
        </div>
      </td>
    </tr>
  </table>
  <div class="hdr-div">
    <div class="hdr-title">SERTIFIKAT PERSETUJUAN ANGGARAN</div>
    <div class="hdr-sub">{{ $proposal->code }}</div>
  </div>
</div>

{{-- ══════════════════ CERTIFICATE BANNER ══════════════════ --}}
<div class="cert-banner">
  <div class="cert-lbl">Dengan ini menyatakan bahwa proposal kegiatan berikut telah disetujui:</div>
  <div class="cert-code">{{ $proposal->code }}</div>
  <div class="cert-name">{{ $proposal->name }}</div>
  <div class="cert-meta">
    {{ $proposal->purpose_label }}
    &nbsp;·&nbsp; Ketua: {{ $proposal->chairperson }}
  </div>
  <div class="cert-amt-lbl">Jumlah Anggaran Disetujui</div>
  <div class="cert-amt">Rp {{ number_format($proposal->approved_amount, 0, ',', '.') }}</div>
  <div class="cert-period">
    Periode: {{ $proposal->start_date->format('d/m/Y') }} – {{ $proposal->end_date->format('d/m/Y') }}
  </div>
  <div class="cert-approved">
    ✓ Disetujui pada {{ $proposal->approved_at?->format('d F Y') }}
  </div>
</div>

{{-- ══════════════════ KPI CARDS ══════════════════ --}}
<table class="kpi" width="100%">
  <tr>
    <td class="k-blue" width="33.33%">
      <div class="lbl">Anggaran Diusulkan</div>
      <div class="val">Rp {{ number_format($proposal->proposed_amount, 0, ',', '.') }}</div>
    </td>
    <td class="k-green" width="33.33%">
      <div class="lbl">Anggaran Disetujui</div>
      <div class="val">Rp {{ number_format($proposal->approved_amount, 0, ',', '.') }}</div>
    </td>
    <td class="k-gray" width="33.33%">
      <div class="lbl">Jumlah Approver</div>
      <div class="val">{{ $proposal->approvers->count() }} Orang</div>
    </td>
  </tr>
</table>

{{-- ══════════════════ DETAIL PROPOSAL ══════════════════ --}}
<div class="sec" style="margin-top:0;">Detail Proposal</div>
<table class="detail">
  <tr>
    <td class="k">Kode Proposal</td>
    <td class="v">{{ $proposal->code }}</td>
  </tr>
  <tr>
    <td class="k">Nama Kegiatan</td>
    <td class="v">{{ $proposal->name }}</td>
  </tr>
  <tr>
    <td class="k">Tujuan Kegiatan</td>
    <td class="v">{{ $proposal->purpose_label }}</td>
  </tr>
  <tr>
    <td class="k">Pemohon</td>
    <td class="v">{{ $proposal->applicant?->name ?? '-' }}</td>
  </tr>
  <tr>
    <td class="k">PIC</td>
    <td class="v">{{ $proposal->pic?->name ?? '-' }}</td>
  </tr>
  <tr>
    <td class="k">Ketua Pelaksana</td>
    <td class="v">{{ $proposal->chairperson }}</td>
  </tr>
  <tr>
    <td class="k">Periode Pelaksanaan</td>
    <td class="v">{{ $proposal->start_date->format('d/m/Y') }} – {{ $proposal->end_date->format('d/m/Y') }}</td>
  </tr>
  <tr>
    <td class="k">Anggaran Diusulkan</td>
    <td class="v">Rp {{ number_format($proposal->proposed_amount, 0, ',', '.') }}</td>
  </tr>
  <tr>
    <td class="k">Anggaran Disetujui</td>
    <td class="v" style="font-weight:700; color:#166534;">Rp {{ number_format($proposal->approved_amount, 0, ',', '.') }}</td>
  </tr>
  <tr>
    <td class="k">Diteruskan Oleh</td>
    <td class="v">{{ $proposal->reviewer?->name ?? '-' }} (Superadmin)</td>
  </tr>
  @if(!empty($proposal->superadmin_note))
  <tr>
    <td class="k">Catatan Admin</td>
    <td class="v" style="color:#374151; font-style:italic;">{{ $proposal->superadmin_note }}</td>
  </tr>
  @endif
</table>

{{-- ══════════════════ APPROVERS ══════════════════ --}}
<div class="sec">Daftar Persetujuan ({{ $proposal->approvers->count() }} Approver)</div>
<table class="appr">
  <thead>
    <tr>
      <th style="width:5%">No</th>
      <th>Nama Approver</th>
      <th style="width:35%">Tanggal Persetujuan</th>
      <th style="width:20%">Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($proposal->approvers as $i => $pa)
    <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td style="font-weight:600;">{{ $pa->user?->name ?? '-' }}</td>
      <td>{{ $pa->approved_at?->format('d F Y, H:i') ?? '-' }}</td>
      <td><span class="chk">✓</span> <span class="badge b-green">Disetujui</span></td>
    </tr>
    @endforeach
  </tbody>
</table>

{{-- ══════════════════ NOTICE ══════════════════ --}}
<div class="notice-box">
  Dokumen ini merupakan sertifikat digital yang digenerate secara otomatis oleh sistem SIMRAN UNISYA.
  Untuk memverifikasi keaslian dokumen ini, silakan scan QR Code yang tersedia atau hubungi
  pengelola sistem.
</div>

{{-- ══════════════════ FOOTER: QR + SIGNATURE ══════════════════ --}}
<div class="ftr-wrap">
  <table class="ftr" width="100%">
    <tr>
      <td class="ftr-l" style="vertical-align:bottom;">
        <strong>SIMRAN UNISYA</strong> — Universitas Islam Syarifuddin<br>
        Dokumen ini digenerate secara digital. QR Code dapat dipindai<br>
        untuk memverifikasi keaslian sertifikat ini melalui sistem.<br>
        <span style="display:block; margin-top:4px;">
          Diterbitkan: {{ now()->format('l, d F Y') }} pukul {{ now()->format('H:i') }} WIB
        </span>
      </td>
      <td class="ftr-qr">
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Verifikasi" />
        <p>Scan untuk verifikasi keaslian</p>
      </td>
      <td class="sig-block">
        <span class="sig-line"></span>
        <div class="sig-name">{{ $proposal->reviewer?->name ?? 'Administrator' }}</div>
        <div class="sig-role">Superadmin SIMRAN UNISYA</div>
      </td>
    </tr>
  </table>
</div>

</div>
</body>
</html>