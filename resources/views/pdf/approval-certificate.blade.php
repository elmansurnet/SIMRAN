<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pengesahan Persetujuan – {{ $proposal->code }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1a1a1a; }
  .page { padding: 15mm 18mm; }

  /* Header */
  .header { background-color: #1B6B3A; color: #fff; padding: 16px 20px; border-radius: 6px; margin-bottom: 16px; }
  .header-brand { font-size: 20pt; font-weight: 900; letter-spacing: 1px; color: #fff; }
  .header-sub { font-size: 8pt; color: #a7f3d0; margin-top: 2px; }
  .header-divider { border-top: 1px solid rgba(255,255,255,0.3); margin-top: 10px; padding-top: 10px; }
  .header-title { font-size: 14pt; font-weight: 700; color: #fff; text-align: center; }
  .header-code { font-size: 9pt; color: #d1fae5; text-align: center; margin-top: 4px; }

  /* Certificate body */
  .cert-body { text-align: center; margin: 16px 0; padding: 14px; border: 2px solid #d1fae5; border-radius: 8px; background: #f9fafb; }
  .cert-body p { font-size: 9pt; color: #6b7280; margin-bottom: 4px; }
  .cert-body .name { font-size: 13pt; font-weight: 700; color: #1B6B3A; margin: 4px 0; }
  .cert-body .amount { font-size: 12pt; font-weight: 700; color: #166534; margin-top: 6px; }
  .cert-body .amount-lbl { font-size: 8pt; color: #6b7280; }

  /* Info table */
  .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .info-table td { padding: 3.5px 6px; font-size: 8.5pt; border-bottom: 1px solid #f0f0f0; }
  .info-table .lbl { color: #6b7280; width: 36%; font-weight: 600; }

  /* Approvers table */
  .approver-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  .approver-table thead tr { background-color: #1B6B3A; }
  .approver-table th { color: #fff; padding: 6px 8px; font-size: 7.5pt; font-weight: 600; text-align: left; text-transform: uppercase; letter-spacing: 0.3px; }
  .approver-table td { padding: 5px 8px; font-size: 8.5pt; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  .approver-table tr:nth-child(even) td { background: #f9fafb; }

  .badge { display: inline; padding: 2px 7px; border-radius: 99px; font-size: 7pt; font-weight: 700; }
  .badge-green { background: #dcfce7; color: #166534; }

  /* QR + signatures */
  .bottom-table { width: 100%; border-collapse: collapse; margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
  .bottom-table td { padding: 0; border: none; vertical-align: top; background: transparent; }
  .qr-box { text-align: center; width: 120pt; }
  .qr-box img { width: 110pt; height: 110pt; display: block; margin: 0 auto; }
  .qr-lbl { font-size: 7pt; color: #6b7280; margin-top: 3px; }
  .sig-box { text-align: center; }
  .sig-line { border-top: 1px solid #374151; margin-top: 50px; margin-bottom: 4px; width: 160pt; display: inline-block; }
  .sig-name { font-size: 8.5pt; font-weight: 600; color: #1a1a1a; }
  .sig-role { font-size: 7.5pt; color: #6b7280; }

  .footer-text { font-size: 7pt; color: #9ca3af; text-align: center; margin-top: 12px; }

  .section-title { font-size: 9pt; font-weight: 700; color: #1B6B3A; border-bottom: 2px solid #d1fae5; padding-bottom: 3px; margin-bottom: 8px; }
</style>
</head>
<body>
<div class="page">

  {{-- HEADER --}}
  <div class="header">
    <table width="100%" style="border-collapse:collapse;">
      <tr>
        <td style="border:none;background:transparent;padding:0;vertical-align:top;">
          <div class="header-brand">SIMRAN UNISYA</div>
          <div class="header-sub">Sistem Manajemen dan Realisasi Anggaran — Universitas Islam Syarifuddin</div>
        </td>
        <td style="border:none;background:transparent;padding:0;text-align:right;vertical-align:top;font-size:7.5pt;color:#d1fae5;">
          Diterbitkan: {{ now()->format('d/m/Y H:i') }} WIB
        </td>
      </tr>
    </table>
    <div class="header-divider">
      <div class="header-title">SERTIFIKAT PERSETUJUAN ANGGARAN</div>
      <div class="header-code">{{ $proposal->code }}</div>
    </div>
  </div>

  {{-- CERTIFICATE BODY --}}
  <div class="cert-body">
    <p>Dengan ini menyatakan bahwa proposal kegiatan berikut telah disetujui:</p>
    <div class="name">{{ $proposal->name }}</div>
    <p style="margin-top:4px;">{{ $proposal->purpose_label }} &nbsp;·&nbsp; Ketua: {{ $proposal->chairperson }}</p>
    <div class="amount-lbl" style="margin-top:8px;">Jumlah Anggaran Disetujui</div>
    <div class="amount">Rp {{ number_format($proposal->approved_amount, 0, ',', '.') }}</div>
    <p style="margin-top:6px;font-size:8pt;">
      Periode: {{ $proposal->start_date->format('d/m/Y') }} – {{ $proposal->end_date->format('d/m/Y') }}
    </p>
    <p style="font-size:8pt;color:#16a34a;font-weight:600;margin-top:4px;">
      Disetujui pada: {{ $proposal->approved_at?->format('d/m/Y') }}
    </p>
  </div>

  {{-- PROPOSAL DETAILS --}}
  <div class="section-title">Detail Proposal</div>
  <table class="info-table">
    <tr><td class="lbl">Kode Proposal</td>    <td>{{ $proposal->code }}</td></tr>
    <tr><td class="lbl">Nama Kegiatan</td>     <td>{{ $proposal->name }}</td></tr>
    <tr><td class="lbl">Tujuan</td>            <td>{{ $proposal->purpose_label }}</td></tr>
    <tr><td class="lbl">Pemohon</td>           <td>{{ $proposal->applicant?->name ?? '-' }}</td></tr>
    <tr><td class="lbl">PIC</td>               <td>{{ $proposal->pic?->name ?? '-' }}</td></tr>
    <tr><td class="lbl">Ketua Pelaksana</td>   <td>{{ $proposal->chairperson }}</td></tr>
    <tr><td class="lbl">Anggaran Diusulkan</td><td>Rp {{ number_format($proposal->proposed_amount, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">Anggaran Disetujui</td><td style="font-weight:700;color:#166534;">Rp {{ number_format($proposal->approved_amount, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">Diteruskan Oleh</td>   <td>{{ $proposal->reviewer?->name ?? '-' }}</td></tr>
    @if($proposal->superadmin_note)
    <tr><td class="lbl">Catatan Admin</td>     <td>{{ $proposal->superadmin_note }}</td></tr>
    @endif
  </table>

  {{-- APPROVERS --}}
  <div class="section-title">Daftar Persetujuan</div>
  <table class="approver-table">
    <thead>
      <tr>
        <th style="width:5%">No</th>
        <th>Nama Approver</th>
        <th style="width:35%">Tanggal Persetujuan</th>
        <th style="width:25%">Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($proposal->approvers as $i => $pa)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td style="font-weight:600;">{{ $pa->user?->name ?? '-' }}</td>
        <td>{{ $pa->approved_at?->format('d F Y, H:i') ?? '-' }}</td>
        <td><span class="badge badge-green">✓ Disetujui</span></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- BOTTOM: QR + Signature --}}
  <table class="bottom-table">
    <tr>
      <td class="qr-box" style="padding-top:12px;">
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Verifikasi" />
        <div class="qr-lbl">Scan untuk verifikasi keaslian dokumen</div>
      </td>
      <td style="text-align:center; padding-top:12px;">
        <div style="font-size:8.5pt;color:#6b7280;">Dibuat oleh Sistem</div>
        <div style="font-size:8pt;color:#6b7280;margin-top:2px;">{{ now()->format('d F Y') }}</div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ $proposal->reviewer?->name ?? 'Administrator' }}</div>
        <div class="sig-role">Superadmin SIMRAN UNISYA</div>
      </td>
    </tr>
  </table>

  <div class="footer-text">
    Dokumen ini digenerate secara digital oleh sistem SIMRAN UNISYA — Universitas Islam Syarifuddin.<br>
    Untuk memverifikasi keaslian dokumen, silakan scan QR Code di atas atau kunjungi halaman pencarian proposal.
  </div>

</div>
</body>
</html>
