<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Global Anggaran — SIMRAN UNISYA</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8.5pt; color: #1a1a1a; background: #fff; }
  .page { padding: 12mm 14mm; }

  /* Header */
  .header { background: linear-gradient(135deg, #145A32 0%, #1B6B3A 50%, #2E8B57 100%); color: #fff; padding: 14px 18px; border-radius: 8px; margin-bottom: 14px; }
  .header-inner { display: flex; justify-content: space-between; align-items: flex-start; }
  .header-brand { font-size: 18pt; font-weight: 900; letter-spacing: 1px; }
  .header-sub { font-size: 8pt; color: #a7f3d0; margin-top: 2px; }
  .header-meta { text-align: right; font-size: 8pt; color: #d1fae5; }
  .header-title { margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.2); }
  .header-title h1 { font-size: 14pt; font-weight: 700; }
  .header-title p  { font-size: 8pt; color: #d1fae5; margin-top: 2px; }

  /* KPI grid */
  .kpi-grid { display: flex; gap: 7px; margin-bottom: 14px; flex-wrap: wrap; }
  .kpi-card { flex: 1; min-width: 100px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; }
  .kpi-card .kpi-label { font-size: 6.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
  .kpi-card .kpi-value { font-size: 9pt; font-weight: 700; color: #111827; }
  .kpi-card.highlight { background: #f0fdf4; border-color: #86efac; }
  .kpi-card.highlight .kpi-value { color: #166534; }
  .kpi-card.danger .kpi-value { color: #dc2626; }
  .kpi-card.teal   .kpi-value { color: #0d9488; }

  /* Section */
  .section-title { font-size: 10pt; font-weight: 700; color: #1B6B3A; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 2px solid #d1fae5; }

  /* Allocations table */
  .alloc-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .alloc-table th { background: #1B6B3A; color: #fff; padding: 6px 8px; font-size: 7.5pt; font-weight: 600; text-align: left; text-transform: uppercase; letter-spacing: 0.4px; }
  .alloc-table td { padding: 5px 8px; font-size: 8pt; border-bottom: 1px solid #f3f4f6; }
  .alloc-table tr:nth-child(even) td { background: #f9fafb; }
  .alloc-table .text-right { text-align: right; }
  .alloc-table .fw { font-weight: 600; }

  /* Disbursements table */
  .disb-table { width: 100%; border-collapse: collapse; }
  .disb-table th { background: #2E8B57; color: #fff; padding: 6px 7px; font-size: 7pt; font-weight: 600; text-align: left; text-transform: uppercase; letter-spacing: 0.3px; }
  .disb-table td { padding: 5px 7px; font-size: 7.5pt; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  .disb-table tr:nth-child(even) td { background: #f9fafb; }
  .disb-table .text-right { text-align: right; }

  /* Progress mini */
  .progress-mini { background: #e5e7eb; border-radius: 3px; height: 5px; overflow: hidden; width: 100%; }
  .progress-mini-fill { height: 5px; border-radius: 3px; }

  /* Badge */
  .badge { display: inline-block; padding: 1.5px 7px; border-radius: 99px; font-size: 6.5pt; font-weight: 600; }
  .badge-green { background: #dcfce7; color: #166534; }
  .badge-gray  { background: #f3f4f6; color: #374151; }
  .badge-blue  { background: #dbeafe; color: #1d4ed8; }

  /* Footer */
  .footer { margin-top: 14px; padding-top: 10px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-size: 7pt; color: #9ca3af; }
  .page-break { page-break-before: always; }
</style>
</head>
<body>
<div class="page">

  <!-- HEADER -->
  <div class="header">
    <div class="header-inner">
      <div>
        <div class="header-brand">SIMRAN UNISYA</div>
        <div class="header-sub">Sistem Manajemen Anggaran — Universitas Islam Syarifuddin</div>
      </div>
      <div class="header-meta">
        <div>Laporan Global Anggaran</div>
        <div>Tahun Anggaran {{ now()->year }}</div>
        <div style="margin-top:3px;">Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
      </div>
    </div>
    <div class="header-title">
      <h1>Ringkasan Realisasi Anggaran Keseluruhan</h1>
      <p>Data mencakup seluruh alokasi dan pencairan anggaran yang tercatat dalam sistem.</p>
    </div>
  </div>

  <!-- KPI CARDS -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Total Anggaran</div>
      <div class="kpi-value">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Total Pencairan</div>
      <div class="kpi-value">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-label">Sisa Anggaran</div>
      <div class="kpi-value">Rp {{ number_format($remainingBudget, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-card danger">
      <div class="kpi-label">Total Realisasi</div>
      <div class="kpi-value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-card teal">
      <div class="kpi-label">Total Pemasukan</div>
      <div class="kpi-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
    </div>
    <div class="kpi-card highlight">
      <div class="kpi-label">Kas Saat Ini</div>
      <div class="kpi-value">Rp {{ number_format($currentCash, 0, ',', '.') }}</div>
    </div>
  </div>

  @php
    $utilisasi = $totalBudget > 0 ? ($totalDisbursed / $totalBudget) * 100 : 0;
    $realisasi  = $totalDisbursed > 0 ? ($totalExpense / $totalDisbursed) * 100 : 0;
  @endphp

  <!-- Utilization Progress -->
  <div style="display:flex;gap:20px;margin-bottom:14px;">
    <div style="flex:1;">
      <div style="display:flex;justify-content:space-between;font-size:7.5pt;color:#6b7280;margin-bottom:3px;">
        <span>Utilisasi Anggaran (Pencairan / Total Anggaran)</span>
        <span style="font-weight:700;">{{ number_format($utilisasi, 1) }}%</span>
      </div>
      <div class="progress-mini">
        <div class="progress-mini-fill" style="width:{{ min($utilisasi,100) }}%;background:#3b82f6;"></div>
      </div>
    </div>
    <div style="flex:1;">
      <div style="display:flex;justify-content:space-between;font-size:7.5pt;color:#6b7280;margin-bottom:3px;">
        <span>Realisasi (Pengeluaran / Total Pencairan)</span>
        <span style="font-weight:700;color:{{ $realisasi >= 90 ? '#dc2626' : ($realisasi >= 70 ? '#d97706' : '#16a34a') }};">{{ number_format($realisasi, 1) }}%</span>
      </div>
      <div class="progress-mini">
        <div class="progress-mini-fill" style="width:{{ min($realisasi,100) }}%;background:{{ $realisasi >= 90 ? '#ef4444' : ($realisasi >= 70 ? '#f59e0b' : '#22c55e') }};"></div>
      </div>
    </div>
  </div>

  <!-- ALLOCATIONS -->
  <div class="section-title">Alokasi Anggaran ({{ $allocations->count() }})</div>
  <table class="alloc-table">
    <thead>
      <tr>
        <th style="width:4%">No</th>
        <th>Nama Alokasi</th>
        <th style="width:10%">Tipe</th>
        <th style="width:16%;text-align:right">Total</th>
        <th style="width:16%;text-align:right">Terpakai</th>
        <th style="width:16%;text-align:right">Sisa</th>
        <th style="width:14%">Utilisasi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($allocations as $i => $alloc)
      @php $util = $alloc->amount > 0 ? ($alloc->total_disbursed / $alloc->amount) * 100 : 0; @endphp
      <tr>
        <td>{{ $i + 1 }}</td>
        <td class="fw">{{ $alloc->name }}</td>
        <td>{{ $alloc->type->label() }}</td>
        <td class="text-right">Rp {{ number_format($alloc->amount, 0, ',', '.') }}</td>
        <td class="text-right" style="color:#dc2626;font-weight:600;">Rp {{ number_format($alloc->total_disbursed, 0, ',', '.') }}</td>
        <td class="text-right" style="color:#166534;font-weight:600;">Rp {{ number_format($alloc->remaining_amount, 0, ',', '.') }}</td>
        <td>
          <div style="display:flex;align-items:center;gap:4px;">
            <div class="progress-mini" style="flex:1;">
              <div class="progress-mini-fill" style="width:{{ min($util,100) }}%;background:{{ $util >= 90 ? '#ef4444' : ($util >= 70 ? '#f59e0b' : '#22c55e') }};"></div>
            </div>
            <span style="font-size:6.5pt;font-weight:700;color:{{ $util >= 90 ? '#dc2626' : ($util >= 70 ? '#d97706' : '#16a34a') }};">{{ number_format($util,1) }}%</span>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- DISBURSEMENTS -->
  <div class="section-title" style="margin-top:4px;">Detail Pencairan Anggaran ({{ $disbursements->count() }})</div>
  <table class="disb-table">
    <thead>
      <tr>
        <th style="width:3%">No</th>
        <th style="width:22%">Nama Kegiatan</th>
        <th style="width:12%">PIC</th>
        <th style="width:7%">Tujuan</th>
        <th style="width:14%">Periode</th>
        <th style="width:13%;text-align:right">Dana</th>
        <th style="width:13%;text-align:right">Realisasi</th>
        <th style="width:9%">Progres</th>
        <th style="width:7%">Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($disbursements as $i => $d)
      @php $pct = min($d->realization_percentage, 100); @endphp
      <tr>
        <td>{{ $i + 1 }}</td>
        <td style="font-weight:600;">{{ Str::limit($d->name, 40) }}</td>
        <td>{{ $d->pic->name ?? '-' }}</td>
        <td>{{ $d->purpose->label() }}</td>
        <td style="font-size:7pt;">{{ $d->start_date->format('d/m/Y') }}<br>{{ $d->end_date->format('d/m/Y') }}</td>
        <td class="text-right">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
        <td class="text-right" style="color:#dc2626;font-weight:600;">Rp {{ number_format($d->total_expense, 0, ',', '.') }}</td>
        <td>
          <div style="display:flex;align-items:center;gap:3px;">
            <div class="progress-mini" style="flex:1;">
              <div class="progress-mini-fill" style="width:{{ $pct }}%;background:{{ $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#22c55e') }};"></div>
            </div>
            <span style="font-size:6pt;font-weight:700;">{{ number_format($pct,1) }}%</span>
          </div>
        </td>
        <td>
          <span class="badge {{ $d->isActive() ? 'badge-green' : ($d->isExpired() ? 'badge-gray' : 'badge-blue') }}">
            {{ $d->status_label }}
          </span>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- FOOTER -->
  <div class="footer">
    <span>SIMRAN UNISYA © {{ now()->year }} — Universitas Islam Syarifuddin. Dokumen ini digenerate secara otomatis oleh sistem.</span>
    <span>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB</span>
  </div>

</div>
</body>
</html>
