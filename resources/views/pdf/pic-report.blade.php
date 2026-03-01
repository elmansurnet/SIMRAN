<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Pertanggungjawaban – {{ $disbursement->name }}</title>
<style>
/* ═══════════════════════════════════════════════════════════════
   SIMRAN UNISYA — Unified PDF Design System v2
   Paper : F4/Folio  8.27 × 12.99 in  (portrait)
   Engine: dompdf via barryvdh/laravel-dompdf
   Font  : DejaVu Sans (dompdf native — do NOT use system fonts)
   NOTE  : dompdf has limited CSS support.
           Use table-based layout; avoid flexbox/grid at block level.
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
  margin-bottom:12px;
}
.hdr-inner { width:100%; border-collapse:collapse; }
.hdr-inner td { border:none; background:transparent; padding:0; vertical-align:top; }
.hdr-brand  { font-size:18pt; font-weight:900; letter-spacing:1px; color:#fff; line-height:1.1; }
.hdr-tag    { font-size:7pt; color:#a7f3d0; margin-top:3px; }
.hdr-stamp  { text-align:right; font-size:7.5pt; color:#d1fae5; line-height:1.55; }
.hdr-div    { border-top:1px solid rgba(255,255,255,0.25); margin-top:9px; padding-top:8px; }
.hdr-title  { font-size:12.5pt; font-weight:700; color:#fff; }
.hdr-sub    { font-size:7.5pt; color:#d1fae5; margin-top:3px; }

/* ── SECTION HEADING ─────────────────────────────────────────── */
.sec {
  font-size:9pt; font-weight:700; color:#1B6B3A;
  border-bottom:2px solid #d1fae5;
  padding-bottom:3px; margin-bottom:7px; margin-top:11px;
  letter-spacing:0.2px;
}

/* ── DETAIL TABLE (two-column key/value layout) ─────────────── */
.detail { width:100%; border-collapse:collapse; margin-bottom:11px; }
.detail td { padding:3.5px 6px; font-size:8.5pt; border-bottom:1px solid #f0f0f0; }
.detail .k { color:#6b7280; font-weight:600; width:22%; }
.detail .v { width:28%; }
.detail .k2{ color:#6b7280; font-weight:600; width:22%; padding-left:14px; }
.detail .v2{ width:28%; }

/* ── KPI CARDS ───────────────────────────────────────────────── */
.kpi { width:100%; border-collapse:separate; border-spacing:5px 0; margin-bottom:11px; }
.kpi td { border:1.5px solid #e5e7eb; border-radius:6px; padding:8px 9px; text-align:center; vertical-align:top; }
.kpi .lbl { font-size:6.5pt; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
.kpi .val { font-size:9.5pt; font-weight:700; color:#111827; }
.kpi .k-green { background:#f0fdf4; border-color:#86efac; }
.kpi .k-green .val { color:#166534; }
.kpi .k-red  .val { color:#dc2626; }
.kpi .k-teal .val { color:#0d9488; }
.kpi .k-gray { background:#f9fafb; }
.kpi .k-warn .val { color:#dc2626; }

/* ── PROGRESS BAR ────────────────────────────────────────────── */
.prog-row { width:100%; border-collapse:collapse; margin-bottom:3px; }
.prog-row td { border:none; background:transparent; padding:0; font-size:7.5pt; color:#6b7280; }
.prog-bg   { background:#e5e7eb; border-radius:4px; height:7px; overflow:hidden; margin-bottom:11px; }
.prog-fill { height:7px; border-radius:4px; }

/* ── TRANSACTION TABLE ───────────────────────────────────────── */
.tx { width:100%; border-collapse:collapse; margin-bottom:11px; }
.tx thead tr { background:#1B6B3A; }
.tx th {
  color:#fff; padding:6px 5px;
  font-size:7pt; font-weight:600;
  text-transform:uppercase; letter-spacing:0.35px;
  text-align:left;
}
.tx td { padding:5.5px 5px; font-size:7.5pt; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.tx .row-alt td { background:#f9fafb; }
.tr  { text-align:right; }
.tc  { text-align:center; }
.amt-e { color:#dc2626; font-weight:700; }
.amt-i { color:#0d9488; font-weight:700; }
.bal-pos { color:#374151; font-weight:600; font-size:7pt; }
.bal-neg { color:#dc2626; font-weight:600; font-size:7pt; }

/* ── QR CODE CELL ────────────────────────────────────────────── */
.qrc     { text-align:center; }
.qrc img { width:55pt; height:55pt; display:block; margin:0 auto; }
.qrc-lbl { font-size:5.5pt; color:#9ca3af; margin-top:2px; }
.no-prf  { color:#d1d5db; font-size:7pt; }

/* ── BADGE ───────────────────────────────────────────────────── */
.badge { display:inline; padding:2px 7px; border-radius:99px; font-size:7pt; font-weight:700; }
.b-green { background:#dcfce7; color:#166534; }
.b-red   { background:#fee2e2; color:#991b1b; }
.b-teal  { background:#ccfbf1; color:#0f766e; }
.b-gray  { background:#f3f4f6; color:#374151; }
.b-blue  { background:#dbeafe; color:#1d4ed8; }
.b-amber { background:#fef3c7; color:#92400e; }

/* ── BALANCE SUMMARY STRIP ───────────────────────────────────── */
.bsum { width:100%; border-collapse:separate; border-spacing:5px 0; margin-bottom:11px; }
.bsum td { border:1.5px solid #d1fae5; background:#f0fdf4; border-radius:6px; padding:7px 9px; text-align:center; }
.bsum .blbl { font-size:6pt; color:#6b7280; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px; }
.bsum .bval { font-size:9pt; font-weight:700; }

/* ── FOOTER ──────────────────────────────────────────────────── */
.ftr-wrap { margin-top:13px; padding-top:9px; border-top:2px solid #d1fae5; }
.ftr { width:100%; border-collapse:collapse; }
.ftr td { border:none; background:transparent; padding:0; vertical-align:bottom; }
.ftr-l { font-size:7.5pt; color:#6b7280; line-height:1.7; }
.ftr-l strong { color:#1B6B3A; }
.ftr-r { text-align:center; width:96pt; }
.ftr-r img { width:84pt; height:84pt; display:block; margin:0 auto; }
.ftr-r p { font-size:6.5pt; color:#6b7280; margin-top:3px; }

.empty { text-align:center; padding:20px; color:#9ca3af; font-size:8.5pt; border:1px dashed #e5e7eb; border-radius:6px; }
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
          Laporan Pertanggungjawaban<br>
          Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
        </div>
      </td>
    </tr>
  </table>
  <div class="hdr-div">
    <div class="hdr-title">{{ $disbursement->name }}</div>
    <div class="hdr-sub">
      {{ $disbursement->purpose->label() }}
      &nbsp;·&nbsp; Ketua: {{ $disbursement->chairperson }}
      &nbsp;·&nbsp; PIC: {{ $disbursement->pic->name }}
    </div>
  </div>
</div>

{{-- ══════════════════ DETAIL KEGIATAN ══════════════════ --}}
<div class="sec" style="margin-top:0;">Informasi Kegiatan</div>
<table class="detail">
  <tr>
    <td class="k">Nama Kegiatan</td>
    <td class="v" colspan="3">{{ $disbursement->name }}</td>
  </tr>
  <tr>
    <td class="k">Tujuan Kegiatan</td>
    <td class="v">{{ $disbursement->purpose->label() }}</td>
    <td class="k2">Ketua Pelaksana</td>
    <td class="v2">{{ $disbursement->chairperson }}</td>
  </tr>
  <tr>
    <td class="k">PIC</td>
    <td class="v">{{ $disbursement->pic->name }}</td>
    <td class="k2">Alokasi Anggaran</td>
    <td class="v2">{{ $disbursement->budgetAllocation->name }}</td>
  </tr>
  <tr>
    <td class="k">Periode</td>
    <td class="v">{{ $disbursement->start_date->format('d/m/Y') }} – {{ $disbursement->end_date->format('d/m/Y') }}</td>
    <td class="k2">Status</td>
    <td class="v2">
      @php
        $sb = match($disbursement->status) {
          'active'   => 'b-green',
          'grace'    => 'b-amber',
          'upcoming' => 'b-blue',
          default    => 'b-gray',
        };
      @endphp
      <span class="badge {{ $sb }}">{{ $disbursement->status_label }}</span>
    </td>
  </tr>
</table>

{{-- ══════════════════ KPI CARDS ══════════════════ --}}
@php $rem = $disbursement->remaining_funds; @endphp
<table class="kpi" width="100%">
  <tr>
    <td class="k-gray" width="25%">
      <div class="lbl">Dana Pencairan</div>
      <div class="val">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</div>
    </td>
    <td class="k-red" width="25%">
      <div class="lbl">Total Pengeluaran</div>
      <div class="val">Rp {{ number_format($disbursement->total_expense, 0, ',', '.') }}</div>
    </td>
    <td class="k-teal" width="25%">
      <div class="lbl">Total Pemasukan</div>
      <div class="val">Rp {{ number_format($disbursement->total_income, 0, ',', '.') }}</div>
    </td>
    <td class="{{ $rem < 0 ? 'k-warn' : 'k-green' }}" width="25%">
      <div class="lbl">Sisa Dana</div>
      <div class="val">Rp {{ number_format($rem, 0, ',', '.') }}</div>
    </td>
  </tr>
</table>

{{-- ══════════════════ PROGRESS ══════════════════ --}}
@php
  $pct  = min($disbursement->realization_percentage, 100);
  $pcol = $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#d97706' : '#16a34a');
  $bcol = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#22c55e');
@endphp
<table class="prog-row" width="100%">
  <tr>
    <td>Progres Realisasi Anggaran</td>
    <td style="text-align:right; font-weight:700; color:{{ $pcol }};">{{ number_format($pct, 1) }}%</td>
  </tr>
</table>
<div class="prog-bg"><div class="prog-fill" style="width:{{ $pct }}%; background:{{ $bcol }};"></div></div>

{{-- ══════════════════ TRANSAKSI ══════════════════ --}}
<div class="sec">Daftar Transaksi ({{ $transactions->count() }})</div>

@if($transactions->count())
  @php
    $runBal = (float) $disbursement->amount;
    $rows   = [];
    foreach ($transactions as $tx) {
      $runBal += $tx->type->value === 'income' ? (float)$tx->amount : -(float)$tx->amount;
      $rows[]  = ['tx' => $tx, 'bal' => $runBal];
    }
  @endphp

  <table class="tx">
    <thead>
      <tr>
        <th style="width:4%">No</th>
        <th style="width:9%">Tanggal</th>
        <th style="width:8%">Tipe</th>
        <th style="width:34%">Keterangan</th>
        <th style="width:16%; text-align:right;">Jumlah</th>
        <th style="width:16%; text-align:right;">Saldo Berjalan</th>
        <th style="width:13%; text-align:center;">QR Bukti</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i => $row)
      @php $tx = $row['tx']; $bal = $row['bal']; @endphp
      <tr class="{{ $i % 2 === 1 ? 'row-alt' : '' }}">
        <td>{{ $i + 1 }}</td>
        <td>{{ $tx->transaction_date->format('d/m/Y') }}</td>
        <td>
          <span class="badge {{ $tx->type->value === 'expense' ? 'b-red' : 'b-teal' }}">
            {{ $tx->type->label() }}
          </span>
        </td>
        <td>{{ $tx->description }}</td>
        <td class="tr {{ $tx->type->value === 'expense' ? 'amt-e' : 'amt-i' }}">
          {{ $tx->type->value === 'expense' ? '−' : '+' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
        </td>
        <td class="tr {{ $bal < 0 ? 'bal-neg' : 'bal-pos' }}">
          Rp {{ number_format($bal, 0, ',', '.') }}
        </td>
        <td>
          @if($tx->hasProof() && isset($qrCodes[$tx->id]))
            <div class="qrc">
              <img src="data:image/png;base64,{{ $qrCodes[$tx->id] }}" alt="QR" />
              <div class="qrc-lbl">Scan bukti</div>
            </div>
          @else
            <div class="tc"><span class="no-prf">—</span></div>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Balance summary strip --}}
  <table class="bsum" width="100%">
    <tr>
      <td>
        <div class="blbl">Dana Awal</div>
        <div class="bval" style="color:#374151;">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</div>
      </td>
      <td>
        <div class="blbl">Total Pengeluaran</div>
        <div class="bval" style="color:#dc2626;">Rp {{ number_format($disbursement->total_expense, 0, ',', '.') }}</div>
      </td>
      <td>
        <div class="blbl">Total Pemasukan</div>
        <div class="bval" style="color:#0d9488;">Rp {{ number_format($disbursement->total_income, 0, ',', '.') }}</div>
      </td>
      <td>
        <div class="blbl">Sisa Dana Akhir</div>
        <div class="bval" style="color:{{ $rem < 0 ? '#dc2626' : '#166534' }};">Rp {{ number_format($rem, 0, ',', '.') }}</div>
      </td>
      <td>
        <div class="blbl">Realisasi</div>
        <div class="bval" style="color:{{ $pcol }};">{{ number_format($pct, 1) }}%</div>
      </td>
    </tr>
  </table>

@else
  <div class="empty">Belum ada transaksi yang dicatat untuk pencairan ini.</div>
@endif

{{-- ══════════════════ FOOTER ══════════════════ --}}
<div class="ftr-wrap">
  <table class="ftr" width="100%">
    <tr>
      <td class="ftr-l">
        <strong>SIMRAN UNISYA</strong> — Universitas Islam Syarifuddin<br>
        Laporan ini digenerate secara digital oleh sistem. QR Code dapat dipindai untuk<br>
        memverifikasi keaslian setiap bukti transaksi yang terlampir.<br>
        <span style="display:block; margin-top:4px;">
          Dicetak: {{ now()->format('l, d F Y') }} pukul {{ now()->format('H:i') }} WIB
        </span>
      </td>
      <td class="ftr-r">
        <img src="data:image/png;base64,{{ $zipQr }}" alt="ZIP QR" />
        <p>Unduh semua bukti (ZIP)</p>
      </td>
    </tr>
  </table>
</div>

</div>
</body>
</html>