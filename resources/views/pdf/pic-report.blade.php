<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Pertanggungjawaban</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9pt;
    color: #1a1a1a;
    background: #fff;
  }

  .page { padding: 12mm 12mm; }

  /* ── HEADER ─────────────────────────────────────── */
  .header {
    background-color: #1B6B3A;
    color: #fff;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 12px;
  }
  .header-brand   { font-size: 17pt; font-weight: 900; letter-spacing: 1px; color: #fff; }
  .header-sub     { font-size: 7.5pt; color: #a7f3d0; margin-top: 2px; }
  .header-meta    { font-size: 7.5pt; color: #d1fae5; text-align: right; }
  .header-divider { border-top: 1px solid rgba(255,255,255,0.3); margin-top: 10px; padding-top: 8px; }
  .header-title   { font-size: 12pt; font-weight: 700; color: #fff; }
  .header-sub2    { font-size: 7.5pt; color: #d1fae5; margin-top: 3px; }

  /* ── INFO TABLE ──────────────────────────────────── */
  .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .info-table td { padding: 3px 6px; font-size: 8.5pt; border-bottom: 1px solid #f0f0f0; }
  .info-table .lbl { color: #6b7280; width: 34%; font-weight: 600; }

  /* ── SUMMARY CARDS ───────────────────────────────── */
  .summary-table { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin-bottom: 10px; }
  .summary-table td { border: 1px solid #e5e7eb; border-radius: 6px; padding: 7px 8px; text-align: center; }
  .summary-lbl { font-size: 6.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; }
  .summary-val { font-size: 9.5pt; font-weight: 700; color: #111827; margin-top: 2px; }
  .card-green { background: #f0fdf4; border-color: #86efac; }
  .card-green .summary-val { color: #166534; }
  .card-red   .summary-val { color: #dc2626; }
  .card-teal  .summary-val { color: #0d9488; }

  /* ── PROGRESS BAR ────────────────────────────────── */
  .prog-table { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
  .prog-table td { padding: 0; border: none; font-size: 7.5pt; color: #6b7280; background: transparent; }
  .prog-bg   { background: #e5e7eb; border-radius: 4px; height: 7px; overflow: hidden; margin-bottom: 10px; }
  .prog-fill { height: 7px; border-radius: 4px; }

  /* ── SECTION TITLE ───────────────────────────────── */
  .section-title {
    font-size: 9.5pt; font-weight: 700; color: #1B6B3A;
    border-bottom: 2px solid #d1fae5;
    padding-bottom: 4px; margin-bottom: 8px;
  }

  /* ── TRANSACTIONS TABLE ──────────────────────────── */
  .tx-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .tx-table thead tr { background-color: #1B6B3A; }
  .tx-table th {
    color: #fff; padding: 6px 5px;
    font-size: 7pt; font-weight: 600;
    text-align: left; text-transform: uppercase; letter-spacing: 0.3px;
  }
  .tx-table td { padding: 6px 5px; font-size: 7.5pt; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
  .tx-table tr.even td { background: #f9fafb; }
  .t-right  { text-align: right; }
  .t-center { text-align: center; }
  .expense  { color: #dc2626; font-weight: 700; }
  .income   { color: #0d9488; font-weight: 700; }

  /* QR cell — large enough to scan (75×75 pt minimum) */
  .qr-cell { text-align: center; vertical-align: middle; }
  .qr-cell img { width: 75pt; height: 75pt; display: block; margin: 0 auto; }
  .qr-sub  { font-size: 5.5pt; color: #9ca3af; margin-top: 2px; }
  .no-proof { color: #d1d5db; font-size: 7pt; }

  /* ── BADGE ───────────────────────────────────────── */
  .badge { display: inline; padding: 2px 6px; border-radius: 99px; font-size: 7pt; font-weight: 700; }
  .bg-green { background: #dcfce7; color: #166534; }
  .bg-red   { background: #fee2e2; color: #991b1b; }
  .bg-teal  { background: #ccfbf1; color: #0f766e; }
  .bg-gray  { background: #f3f4f6; color: #374151; }
  .bg-blue  { background: #dbeafe; color: #1d4ed8; }

  /* ── FOOTER ──────────────────────────────────────── */
  .footer-table { width: 100%; border-collapse: collapse; margin-top: 12px; padding-top: 10px; border-top: 1px solid #e5e7eb; }
  .footer-table td { padding: 0; border: none; vertical-align: bottom; background: transparent; }
  .footer-left { font-size: 7.5pt; color: #6b7280; line-height: 1.6; }
  .footer-right { text-align: center; width: 105pt; }
  .footer-right img { width: 95pt; height: 95pt; display: block; margin: 0 auto; }
  .footer-right p { font-size: 6.5pt; color: #6b7280; margin-top: 2px; }

  .no-data { text-align: center; padding: 18px; color: #9ca3af; font-size: 8.5pt; }
</style>
</head>
<body>
<div class="page">

  {{-- ══ HEADER ══ --}}
  <div class="header">
    <table width="100%" style="border-collapse:collapse;">
      <tr>
        <td style="vertical-align:top; border:none; background:transparent; padding:0;">
          <div class="header-brand">SIMRAN UNISYA</div>
          <div class="header-sub">Sistem Manajemen dan Realisasi Anggaran — Universitas Islam Syarifuddin</div>
        </td>
        <td style="vertical-align:top; text-align:right; border:none; background:transparent; padding:0;">
          <div class="header-meta">
            Laporan Pertanggungjawaban<br>
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
          </div>
        </td>
      </tr>
    </table>
    <div class="header-divider">
      <div class="header-title">{{ $disbursement->name }}</div>
      <div class="header-sub2">
        {{ $disbursement->purpose->label() }}
        &nbsp;·&nbsp; Ketua: {{ $disbursement->chairperson }}
        &nbsp;·&nbsp; PIC: {{ $disbursement->pic->name }}
      </div>
    </div>
  </div>

  {{-- ══ INFO TABLE ══ --}}
  <table class="info-table">
    <tr><td class="lbl">Nama Kegiatan</td>    <td>{{ $disbursement->name }}</td></tr>
    <tr><td class="lbl">Tujuan</td>           <td>{{ $disbursement->purpose->label() }}</td></tr>
    <tr><td class="lbl">Ketua Pelaksana</td>  <td>{{ $disbursement->chairperson }}</td></tr>
    <tr><td class="lbl">PIC</td>              <td>{{ $disbursement->pic->name }}</td></tr>
    <tr><td class="lbl">Alokasi Anggaran</td> <td>{{ $disbursement->budgetAllocation->name }}</td></tr>
    <tr>
      <td class="lbl">Periode</td>
      <td>{{ $disbursement->start_date->format('d/m/Y') }} – {{ $disbursement->end_date->format('d/m/Y') }}</td>
    </tr>
    <tr>
      <td class="lbl">Status</td>
      <td>
        <span class="badge {{ $disbursement->isActive() ? 'bg-green' : ($disbursement->isExpired() ? 'bg-gray' : 'bg-blue') }}">
          {{ $disbursement->status_label }}
        </span>
      </td>
    </tr>
  </table>

  {{-- ══ SUMMARY CARDS ══ --}}
  <table class="summary-table">
    <tr>
      <td>
        <div class="summary-lbl">Dana Pencairan</div>
        <div class="summary-val">Rp {{ number_format($disbursement->amount, 0, ',', '.') }}</div>
      </td>
      <td class="card-red">
        <div class="summary-lbl">Total Pengeluaran</div>
        <div class="summary-val">Rp {{ number_format($disbursement->total_expense, 0, ',', '.') }}</div>
      </td>
      <td class="card-teal">
        <div class="summary-lbl">Total Pemasukan</div>
        <div class="summary-val">Rp {{ number_format($disbursement->total_income, 0, ',', '.') }}</div>
      </td>
      <td class="card-green">
        <div class="summary-lbl">Sisa Dana</div>
        <div class="summary-val">Rp {{ number_format($disbursement->remaining_funds, 0, ',', '.') }}</div>
      </td>
    </tr>
  </table>

  {{-- ══ PROGRESS BAR ══ --}}
  @php
    $pct      = min($disbursement->realization_percentage, 100);
    $pctColor = $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#d97706' : '#16a34a');
    $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#22c55e');
  @endphp
  <table class="prog-table">
    <tr>
      <td>Progres Realisasi</td>
      <td style="text-align:right; font-weight:700; color:{{ $pctColor }}">{{ number_format($pct,1) }}%</td>
    </tr>
  </table>
  <div class="prog-bg">
    <div class="prog-fill" style="width:{{ $pct }}%; background:{{ $barColor }};"></div>
  </div>

  {{-- ══ TRANSACTIONS ══ --}}
  <div class="section-title">Daftar Transaksi ({{ $transactions->count() }})</div>

  @if($transactions->count())
  <table class="tx-table">
    <thead>
      <tr>
        <th style="width:4%">No</th>
        <th style="width:10%">Tanggal</th>
        <th style="width:8%">Tipe</th>
        <th style="width:36%">Keterangan</th>
        <th style="width:18%; text-align:right;">Jumlah</th>
        <th style="width:14%; text-align:center;">QR Bukti</th>
      </tr>
    </thead>
    <tbody>
      @foreach($transactions as $i => $tx)
      <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
        <td>{{ $i + 1 }}</td>
        <td>{{ $tx->transaction_date->format('d/m/Y') }}</td>
        <td>
          <span class="badge {{ $tx->type->value === 'expense' ? 'bg-red' : 'bg-teal' }}">
            {{ $tx->type->label() }}
          </span>
        </td>
        <td>{{ $tx->description }}</td>
        <td class="t-right {{ $tx->type->value === 'expense' ? 'expense' : 'income' }}">
          {{ $tx->type->value === 'expense' ? '−' : '+' }}
          Rp {{ number_format($tx->amount, 0, ',', '.') }}
        </td>
        <td class="qr-cell">
          @if($tx->hasProof() && isset($qrCodes[$tx->id]))
            <img src="data:image/png;base64,{{ $qrCodes[$tx->id] }}" alt="QR" />
            <div class="qr-sub">Scan untuk lihat bukti</div>
          @else
            <span class="no-proof">—</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-data">Belum ada transaksi yang dicatat.</div>
  @endif

  {{-- ══ FOOTER ══ --}}
  <table class="footer-table">
    <tr>
      <td class="footer-left">
        <strong>SIMRAN UNISYA</strong> — Universitas Islam Syarifuddin<br>
        Dokumen ini dicetak secara digital. QR Code dapat dipindai untuk verifikasi.<br>
        Dicetak pada: {{ now()->format('l, d F Y') }} pukul {{ now()->format('H:i') }} WIB
      </td>
      <td class="footer-right">
        <img src="data:image/png;base64,{{ $zipQr }}" alt="QR ZIP" />
        <p>Unduh semua bukti (ZIP)</p>
      </td>
    </tr>
  </table>

</div>
</body>
</html>