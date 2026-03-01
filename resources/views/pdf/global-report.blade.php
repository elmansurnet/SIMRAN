<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Global Anggaran — SIMRAN UNISYA</title>
<style>
/* ═══════════════════════════════════════════════════════════════
   SIMRAN UNISYA — Unified PDF Design System v2
   Paper : F4/Folio  8.27 × 12.99 in  (portrait)
   Engine: dompdf via barryvdh/laravel-dompdf
   Font  : DejaVu Sans (dompdf native)
   ═══════════════════════════════════════════════════════════════ */

* { margin:0; padding:0; box-sizing:border-box; }
body  { font-family:'DejaVu Sans', Arial, sans-serif; font-size:8.5pt; color:#1a1a1a; background:#fff; }
.page { padding:13mm 14mm 12mm 14mm; }

/* ── HEADER (identical to PIC report) ───────────────────────── */
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

/* ── KPI CARDS (3 per row via table) ────────────────────────── */
.kpi { width:100%; border-collapse:separate; border-spacing:5px 0; margin-bottom:6px; }
.kpi td { border:1.5px solid #e5e7eb; border-radius:6px; padding:8px 10px; text-align:center; vertical-align:top; }
.kpi .lbl { font-size:6pt; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
.kpi .val { font-size:9.5pt; font-weight:700; color:#111827; }
.k-green { background:#f0fdf4; border-color:#86efac; }
.k-green .val { color:#166534; }
.k-red   .val { color:#dc2626; }
.k-teal  .val { color:#0d9488; }
.k-blue  .val { color:#1d4ed8; }
.k-gray  { background:#f9fafb; }
.k-warn  .val { color:#92400e; }

/* ── PROGRESS BARS ───────────────────────────────────────────── */
.prog-row { width:100%; border-collapse:collapse; margin-bottom:3px; }
.prog-row td { border:none; background:transparent; padding:0; font-size:7.5pt; color:#6b7280; }
.prog-bg  { background:#e5e7eb; border-radius:4px; height:6px; overflow:hidden; margin-bottom:10px; }
.prog-fill{ height:6px; border-radius:4px; }

/* ── DUAL PROGRESS ROW ───────────────────────────────────────── */
.dprog { width:100%; border-collapse:separate; border-spacing:12px 0; margin-bottom:12px; }
.dprog td { border:none; background:transparent; padding:0; vertical-align:top; }
.dprog-lbl { font-size:7pt; color:#6b7280; margin-bottom:3px; }
.dprog-val { font-size:7pt; font-weight:700; text-align:right; }

/* ── ALLOCATIONS TABLE ───────────────────────────────────────── */
.alloc { width:100%; border-collapse:collapse; margin-bottom:13px; }
.alloc thead tr { background:#1B6B3A; }
.alloc th {
  color:#fff; padding:6px 6px;
  font-size:7pt; font-weight:600;
  text-transform:uppercase; letter-spacing:0.35px; text-align:left;
}
.alloc td { padding:5px 6px; font-size:8pt; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.alloc .alt td { background:#f9fafb; }
.tr { text-align:right; }

/* ── MINI PROGRESS (in table cells) ─────────────────────────── */
.mprog-bg   { background:#e5e7eb; border-radius:3px; height:5px; overflow:hidden; display:block; }
.mprog-fill { height:5px; border-radius:3px; display:block; }

/* ── DISBURSEMENTS TABLE ─────────────────────────────────────── */
.disb { width:100%; border-collapse:collapse; margin-bottom:13px; }
.disb thead tr { background:#145A32; }
.disb th {
  color:#fff; padding:5.5px 5px;
  font-size:6.5pt; font-weight:600;
  text-transform:uppercase; letter-spacing:0.3px; text-align:left;
}
.disb td { padding:5px 5px; font-size:7.5pt; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.disb .alt td { background:#f9fafb; }

/* ── BADGE ───────────────────────────────────────────────────── */
.badge { display:inline; padding:2px 6px; border-radius:99px; font-size:6.5pt; font-weight:700; }
.b-green { background:#dcfce7; color:#166534; }
.b-red   { background:#fee2e2; color:#991b1b; }
.b-teal  { background:#ccfbf1; color:#0f766e; }
.b-gray  { background:#f3f4f6; color:#374151; }
.b-blue  { background:#dbeafe; color:#1d4ed8; }
.b-amber { background:#fef3c7; color:#92400e; }

/* ── SUMMARY STRIP (totals row) ──────────────────────────────── */
.tot-row td {
  background:#f0fdf4; border-top:2px solid #86efac;
  padding:5px 6px; font-size:7.5pt; font-weight:700; color:#166534;
}

/* ── FOOTER ──────────────────────────────────────────────────── */
.ftr-wrap { margin-top:12px; padding-top:8px; border-top:2px solid #d1fae5; }
.ftr { width:100%; border-collapse:collapse; }
.ftr td { border:none; background:transparent; padding:0; vertical-align:middle; }
.ftr-l { font-size:7.5pt; color:#6b7280; line-height:1.7; }
.ftr-l strong { color:#1B6B3A; }
.ftr-r { text-align:right; font-size:7pt; color:#9ca3af; }
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
          Laporan Global Anggaran<br>
          Tahun Anggaran {{ now()->year }}<br>
          Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
        </div>
      </td>
    </tr>
  </table>
  <div class="hdr-div">
    <div class="hdr-title">Ringkasan Realisasi Anggaran Keseluruhan</div>
    <div class="hdr-sub">
      Data mencakup seluruh alokasi dan pencairan anggaran yang tercatat dalam sistem.
    </div>
  </div>
</div>

{{-- ══════════════════ KPI CARDS — ROW 1 ══════════════════ --}}
<div class="sec" style="margin-top:0;">Ringkasan Keuangan</div>
<table class="kpi" width="100%">
  <tr>
    <td class="k-gray" width="33.33%">
      <div class="lbl">Total Anggaran Tersedia</div>
      <div class="val">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
    </td>
    <td class="k-blue" width="33.33%">
      <div class="lbl">Total Dana Dicairkan</div>
      <div class="val">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</div>
    </td>
    <td class="{{ $totalBudget - $totalDisbursed < 0 ? 'k-warn' : 'k-green' }}" width="33.33%">
      <div class="lbl">Sisa Anggaran Belum Dicairkan</div>
      <div class="val">Rp {{ number_format($totalBudget - $totalDisbursed, 0, ',', '.') }}</div>
    </td>
  </tr>
</table>

{{-- KPI ROW 2 --}}
<table class="kpi" width="100%" style="margin-top:5px; margin-bottom:12px;">
  <tr>
    <td class="k-red" width="33.33%">
      <div class="lbl">Total Realisasi Pengeluaran</div>
      <div class="val">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
    </td>
    <td class="k-teal" width="33.33%">
      <div class="lbl">Total Pemasukan Diterima</div>
      <div class="val">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
    </td>
    <td class="{{ $currentCash < 0 ? 'k-warn' : 'k-green' }}" width="33.33%">
      <div class="lbl">Kas Saat Ini</div>
      <div class="val">Rp {{ number_format($currentCash, 0, ',', '.') }}</div>
    </td>
  </tr>
</table>

{{-- ══════════════════ DUAL PROGRESS ══════════════════ --}}
@php
  $util   = $totalBudget    > 0 ? ($totalDisbursed / $totalBudget)    * 100 : 0;
  $real   = $totalDisbursed > 0 ? ($totalExpense   / $totalDisbursed) * 100 : 0;

  $utilColor = $util >= 90 ? '#dc2626' : ($util >= 70 ? '#d97706' : '#16a34a');
  $realColor = $real >= 90 ? '#dc2626' : ($real >= 70 ? '#d97706' : '#16a34a');
  $utilBar   = $util >= 90 ? '#ef4444' : ($util >= 70 ? '#f59e0b' : '#22c55e');
  $realBar   = $real >= 90 ? '#ef4444' : ($real >= 70 ? '#f59e0b' : '#22c55e');
@endphp
<table class="dprog" width="100%">
  <tr>
    <td width="48%">
      <table width="100%" style="border-collapse:collapse;">
        <tr>
          <td style="font-size:7.5pt; color:#6b7280; border:none; background:transparent; padding:0;">
            Utilisasi Anggaran (Dana Dicairkan / Total Anggaran)
          </td>
          <td style="text-align:right; font-size:7.5pt; font-weight:700; color:{{ $utilColor }}; border:none; background:transparent; padding:0; white-space:nowrap;">
            {{ number_format($util, 1) }}%
          </td>
        </tr>
      </table>
      <div class="prog-bg" style="margin-top:3px; margin-bottom:0;">
        <div class="prog-fill" style="width:{{ min($util,100) }}%; background:{{ $utilBar }};"></div>
      </div>
    </td>
    <td width="4%"></td>
    <td width="48%">
      <table width="100%" style="border-collapse:collapse;">
        <tr>
          <td style="font-size:7.5pt; color:#6b7280; border:none; background:transparent; padding:0;">
            Realisasi (Pengeluaran / Total Pencairan)
          </td>
          <td style="text-align:right; font-size:7.5pt; font-weight:700; color:{{ $realColor }}; border:none; background:transparent; padding:0; white-space:nowrap;">
            {{ number_format($real, 1) }}%
          </td>
        </tr>
      </table>
      <div class="prog-bg" style="margin-top:3px; margin-bottom:0;">
        <div class="prog-fill" style="width:{{ min($real,100) }}%; background:{{ $realBar }};"></div>
      </div>
    </td>
  </tr>
</table>
<div style="height:11px;"></div>

{{-- ══════════════════ ALOKASI ANGGARAN ══════════════════ --}}
<div class="sec">Alokasi Anggaran ({{ $allocations->count() }})</div>
<table class="alloc">
  <thead>
    <tr>
      <th style="width:4%">No</th>
      <th>Nama Alokasi</th>
      <th style="width:10%">Tipe</th>
      <th style="width:17%; text-align:right;">Total Alokasi</th>
      <th style="width:17%; text-align:right;">Terpakai</th>
      <th style="width:17%; text-align:right;">Sisa</th>
      <th style="width:13%">Utilisasi</th>
    </tr>
  </thead>
  <tbody>
    @foreach($allocations as $i => $alloc)
    @php
      $u = $alloc->amount > 0 ? ($alloc->total_disbursed / $alloc->amount) * 100 : 0;
      $uc = $u >= 90 ? '#dc2626' : ($u >= 70 ? '#d97706' : '#16a34a');
      $ub = $u >= 90 ? '#ef4444' : ($u >= 70 ? '#f59e0b' : '#22c55e');
    @endphp
    <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td style="font-weight:600;">{{ $alloc->name }}</td>
      <td>{{ $alloc->type->label() }}</td>
      <td class="tr">Rp {{ number_format($alloc->amount, 0, ',', '.') }}</td>
      <td class="tr" style="color:#dc2626; font-weight:600;">Rp {{ number_format($alloc->total_disbursed, 0, ',', '.') }}</td>
      <td class="tr" style="color:#166534; font-weight:600;">Rp {{ number_format($alloc->remaining_amount, 0, ',', '.') }}</td>
      <td>
        <table width="100%" style="border-collapse:collapse;">
          <tr>
            <td style="border:none; background:transparent; padding:0; width:100%;">
              <span class="mprog-bg" style="width:100%;">
                <span class="mprog-fill" style="width:{{ min($u,100) }}%; background:{{ $ub }};"></span>
              </span>
            </td>
            <td style="border:none; background:transparent; padding:0 0 0 4px; white-space:nowrap; font-size:6.5pt; font-weight:700; color:{{ $uc }};">
              {{ number_format($u, 1) }}%
            </td>
          </tr>
        </table>
      </td>
    </tr>
    @endforeach
  </tbody>
  {{-- Totals row --}}
  <tr class="tot-row">
    <td colspan="3" style="color:#1B6B3A; font-size:8pt;">Jumlah Keseluruhan</td>
    <td class="tr">Rp {{ number_format($totalBudget, 0, ',', '.') }}</td>
    <td class="tr" style="color:#dc2626;">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</td>
    <td class="tr" style="color:#166534;">Rp {{ number_format($totalBudget - $totalDisbursed, 0, ',', '.') }}</td>
    <td></td>
  </tr>
</table>

{{-- ══════════════════ DETAIL PENCAIRAN ══════════════════ --}}
<div class="sec">Detail Pencairan Anggaran ({{ $disbursements->count() }})</div>
<table class="disb">
  <thead>
    <tr>
      <th style="width:4%">No</th>
      <th style="width:23%">Nama Kegiatan</th>
      <th style="width:13%">PIC</th>
      <th style="width:12%">Alokasi</th>
      <th style="width:6%">Jenis</th>
      <th style="width:11%">Periode</th>
      <th style="width:12%; text-align:right;">Dana</th>
      <th style="width:12%; text-align:right;">Realisasi</th>
      <th style="width:7%">Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($disbursements as $i => $d)
    @php
      $pct = min($d->realization_percentage, 100);
      $pc  = $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#d97706' : '#16a34a');
      $pb  = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#22c55e');

      $sbadge = match($d->status) {
        'active'   => 'b-green',
        'grace'    => 'b-amber',
        'upcoming' => 'b-blue',
        default    => 'b-gray',
      };
    @endphp
    <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td style="font-weight:600; font-size:7pt;">{{ \Str::limit($d->name, 38) }}</td>
      <td style="font-size:7pt;">{{ $d->pic->name ?? '-' }}</td>
      <td style="font-size:7pt;">{{ \Str::limit($d->budgetAllocation?->name ?? '-', 20) }}</td>
      <td style="font-size:6.5pt;">{{ $d->purpose->label() }}</td>
      <td style="font-size:6.5pt;">
        {{ $d->start_date->format('d/m/Y') }}<br>
        {{ $d->end_date->format('d/m/Y') }}
      </td>
      <td class="tr" style="font-size:7pt;">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
      <td class="tr" style="font-size:7pt; color:#dc2626; font-weight:600;">
        Rp {{ number_format($d->total_expense, 0, ',', '.') }}<br>
        <span style="font-size:6pt; color:{{ $pc }}; font-weight:700;">{{ number_format($pct, 1) }}%</span>
      </td>
      <td>
        <span class="badge {{ $sbadge }}">{{ $d->status_label }}</span>
      </td>
    </tr>
    @endforeach
  </tbody>
  {{-- Disbursement totals --}}
  <tr class="tot-row">
    <td colspan="6" style="color:#1B6B3A; font-size:8pt;">Total Pencairan</td>
    <td class="tr">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</td>
    <td class="tr" style="color:#dc2626;">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
    <td></td>
  </tr>
</table>

{{-- ══════════════════ FOOTER ══════════════════ --}}
<div class="ftr-wrap">
  <table class="ftr" width="100%">
    <tr>
      <td class="ftr-l">
        <strong>SIMRAN UNISYA</strong> — Universitas Islam Syarifuddin<br>
        Dokumen ini digenerate secara otomatis oleh sistem. Data yang ditampilkan adalah<br>
        per tanggal cetak dan mencerminkan kondisi real-time dari basis data sistem.
      </td>
      <td class="ftr-r">
        SIMRAN UNISYA © {{ now()->year }}<br>
        Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
      </td>
    </tr>
  </table>
</div>

</div>
</body>
</html>