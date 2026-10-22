<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pesanan PO - {{ $po->nomor_po ?? 'PO #'.$po->id }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #111111;
            margin: 0;
            padding: 32px 36px;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .logo-title h1 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
            color: #111111;
            text-transform: uppercase;
        }
        .logo-title p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #6b7280;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
            color: #d97706;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .doc-title p {
            margin: 4px 0 0 0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        .info-col h3 {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin: 0 0 8px 0;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .info-col p {
            margin: 3px 0;
            font-size: 11px;
        }
        .info-col .name {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
        }
        .card .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .card .value {
            font-size: 15px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            color: #111827;
        }
        .card .value.text-green { color: #059669; }
        .card .value.text-amber { color: #d97706; }
        .card .value.text-sky { color: #0284c7; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10.5px;
        }
        th, td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.05em;
            color: #374151;
            border-top: 1px solid #d1d5db;
            border-bottom: 2px solid #d1d5db;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .font-bold { font-weight: 700; }

        .status-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-selesai { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-sebagian { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-belum { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .status-lebih { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .status-ditutup { background: #f3f4f6; color: #6b7280; border: 1px dashed #d1d5db; }

        tfoot td {
            font-weight: 700;
            background-color: #f9fafb;
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
        }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #111827;
            margin: 20px 0 10px 0;
            border-left: 3px solid #d97706;
            padding-left: 8px;
        }

        .terms-box {
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 24px;
            font-size: 9.5px;
            color: #4b5563;
        }
        .terms-box ol {
            margin: 4px 0 0 18px;
            padding: 0;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 28px;
            page-break-inside: avoid;
        }
        .signature-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            background: #ffffff;
        }
        .signature-box .role {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
            margin-bottom: 54px;
        }
        .signature-box .line {
            border-top: 1px dashed #9ca3af;
            margin: 0 14px 6px 14px;
        }
        .signature-box .name {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        .action-bar {
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print {
            background: #d97706;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #b45309; }

        @media print {
            body { padding: 14px; font-size: 9.5px; }
            .action-bar { display: none !important; }
            @page { margin: 10mm 10mm; size: auto; }
            .card .value { font-size: 13px; }
            .header { margin-bottom: 14px; padding-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <a href="{{ route('purchase-order.show', $po->id) }}" class="btn-back">
            &larr; Kembali ke Detail PO
        </a>
        <button class="btn-print" onclick="window.print()">
            &#128438; Cetak / Simpan PDF
        </button>
    </div>

    <!-- Header Section -->
    <div class="header">
        <div class="logo-title">
            <h1>LAF PROJECT WMS</h1>
            <p>Warehouse & Inventory Management System &bull; Dokumen Resmi</p>
        </div>
        <div class="doc-title">
            <h2>Surat Pesanan (Purchase Order)</h2>
            <p>{{ $po->nomor_po ?? 'PO #'.$po->id }}</p>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-col">
            <h3>Tujuan Pemesanan (Supplier / Vendor)</h3>
            <p class="name">{{ $po->supplier?->nama_supplier ?? '-' }}</p>
            <p><strong>Kontak:</strong> {{ $po->supplier?->kontak ?? '-' }}</p>
            <p><strong>Alamat:</strong> {{ $po->supplier?->alamat ?? '-' }}</p>
        </div>
        <div class="info-col">
            <h3>Informasi Dokumen PO</h3>
            <p><strong>Tanggal PO:</strong> {{ $po->tanggal ? \Carbon\Carbon::parse($po->tanggal)->translatedFormat('d F Y') : '-' }}</p>
            <p><strong>Nomor Artikel:</strong> {{ $po->artikel ?? '-' }}</p>
            <p><strong>Status Dokumen:</strong> 
                @if ($totalSisa === 0)
                    <span class="status-badge status-selesai">{{ $totalDitutup > 0 ? 'Selesai (Ada Ditutup)' : 'Lengkap / Selesai' }}</span>
                @elseif ($totalDiterima > 0)
                    <span class="status-badge status-sebagian">Diterima Sebagian</span>
                @else
                    <span class="status-badge status-belum">Terbuka (Outstanding)</span>
                @endif
            </p>
            <p><strong>Dicetak Pada:</strong> {{ now()->format('d/m/Y H:i') }} WIB</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="label">Total Dipesan</div>
            <div class="value">{{ number_format($totalDipesan) }}</div>
        </div>
        <div class="card">
            <div class="label">Total Diterima</div>
            <div class="value text-green">{{ number_format($totalDiterima) }}</div>
        </div>
        <div class="card">
            <div class="label">Sisa Outstanding</div>
            <div class="value text-amber">{{ number_format($totalSisa) }}</div>
        </div>
        <div class="card">
            <div class="label">Sisa Ditutup</div>
            <div class="value text-sky">{{ number_format($totalDitutup) }}</div>
        </div>
        <div class="card">
            <div class="label">Estimasi Nilai PO</div>
            <div class="value">Rp{{ number_format($totalEstimasiHpp, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Table of Ordered Items -->
    <div class="section-title">Daftar Barang Pesanan</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Nama Produk & Variasi</th>
                <th>Kode SKU</th>
                <th class="text-center" style="width: 70px;">Dipesan</th>
                <th class="text-center" style="width: 50px;">Satuan</th>
                <th class="text-center" style="width: 70px;">Diterima</th>
                <th class="text-center" style="width: 60px;">Sisa</th>
                <th class="text-right" style="width: 90px;">Estimasi HPP</th>
                <th class="text-right" style="width: 100px;">Subtotal Nilai</th>
                <th class="text-center" style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($po->poDetails as $idx => $d)
                @php
                    $subtotal = $d->jumlah * (float) $d->hpp;
                    $st = $d->statusPenerimaan();
                    $badgeClass = match($st) {
                        'selesai' => 'status-selesai',
                        'sebagian' => 'status-sebagian',
                        'lebih' => 'status-lebih',
                        'ditutup' => 'status-ditutup',
                        default => 'status-belum',
                    };
                    $badgeLabel = match($st) {
                        'selesai' => 'Lengkap',
                        'sebagian' => 'Sebagian',
                        'lebih' => 'Lebih',
                        'ditutup' => 'Ditutup',
                        default => 'Belum Datang',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <span class="font-bold">{{ $d->produk?->nama_barang ?? '-' }}</span>
                        @if ($d->produk?->variasi_barang)
                            <div style="font-size: 9px; color: #6b7280;">{{ $d->produk->variasi_barang }}</div>
                        @endif
                        @if ($d->ditutup_pada)
                            <div style="font-size: 8.5px; color: #0284c7; font-style: italic;">
                                Ditutup {{ number_format($d->qty_ditutup) }} unit: "{{ $d->alasan_tutup }}"
                            </div>
                        @endif
                    </td>
                    <td class="font-mono">{{ $d->produk?->kode_barang ?? '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ number_format($d->jumlah) }}</td>
                    <td class="text-center">{{ $d->produk?->satuan ?? 'pcs' }}</td>
                    <td class="text-center font-mono font-bold" style="color: #059669;">{{ number_format($d->barang_masuk) }}</td>
                    <td class="text-center font-mono font-bold" style="{{ $d->sisa > 0 ? 'color: #d97706;' : 'color: #9ca3af;' }}">
                        {{ number_format($d->sisa) }}
                    </td>
                    <td class="text-right font-mono">Rp{{ number_format((float) $d->hpp, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center font-mono font-bold">{{ number_format($totalDipesan) }}</td>
                <td></td>
                <td class="text-center font-mono font-bold text-green" style="color: #059669;">{{ number_format($totalDiterima) }}</td>
                <td class="text-center font-mono font-bold text-amber" style="color: #d97706;">{{ number_format($totalSisa) }}</td>
                <td></td>
                <td class="text-right font-mono font-bold" style="color: #d97706;">Rp{{ number_format($totalEstimasiHpp, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Goods Receipt History Table (if any) -->
    @if ($receipts->isNotEmpty())
        <div class="section-title">Riwayat Penerimaan Fisik Barang Masuk</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">No</th>
                    <th>Nomor Nota Penerimaan</th>
                    <th>Tanggal Masuk</th>
                    <th>Produk Diterima</th>
                    <th class="text-center" style="width: 70px;">Qty Masuk</th>
                    <th>Lokasi Simpan</th>
                    <th>Selisih &amp; Catatan</th>
                    <th>Penerima</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipts as $rIdx => $rc)
                    <tr>
                        <td class="text-center">{{ $rIdx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $rc->barangMasukHeader?->nomor_nota ?? '-' }}</td>
                        <td>{{ $rc->barangMasukHeader?->tanggal ? date('d/m/Y', strtotime($rc->barangMasukHeader->tanggal)) : '-' }}</td>
                        <td>{{ $rc->produk?->nama_barang }}</td>
                        <td class="text-center font-mono font-bold" style="color: #059669;">+{{ number_format($rc->qty_masuk) }}</td>
                        <td class="font-mono">{{ $rc->lokasi_simpan }}</td>
                        <td>
                            @if ($rc->selisih_po)
                                <span class="status-badge {{ $rc->selisih_po < 0 ? 'status-sebagian' : 'status-lebih' }}">
                                    {{ $rc->selisih_po < 0 ? 'Kurang '.abs($rc->selisih_po) : 'Lebih '.$rc->selisih_po }}
                                </span>
                                @if ($rc->alasan_selisih)
                                    <span style="font-style: italic; color: #4b5563;">"{{ $rc->alasan_selisih }}"</span>
                                @endif
                            @else
                                <span style="color: #9ca3af;">- Sesuai -</span>
                            @endif
                        </td>
                        <td>{{ $rc->barangMasukHeader?->data_dimasukan_oleh ?? 'Gudang' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Terms & Notes -->
    <div class="terms-box">
        <strong>Ketentuan Pengiriman &amp; Penerimaan Barang:</strong>
        <ol>
            <li>Surat Pesanan ini merupakan bukti resmi pemesanan barang dari LAF Project kepada Supplier yang bersangkutan.</li>
            <li>Barang yang dikirim wajib disertai Surat Jalan / Faktur pengiriman yang mencantumkan Nomor PO ini.</li>
            <li>Barang akan melalui proses Quality Control (QC) dan verifikasi kuantitas fisik sebelum di-input ke sistem gudang.</li>
            <li>Apabila terdapat selisih kuantitas atau barang reject, akan dicatat secara otomatis dalam Berita Acara Penerimaan.</li>
        </ol>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="role">Dibuat Oleh (PIC Gudang)</div>
            <div class="line"></div>
            <div class="name">{{ auth()->user()->name ?? 'Purchasing / Gudang' }}</div>
        </div>
        <div class="signature-box">
            <div class="role">Disetujui Oleh (Manajemen)</div>
            <div class="line"></div>
            <div class="name">Manajer Operasional / Keuangan</div>
        </div>
        <div class="signature-box">
            <div class="role">Diterima &amp; Dikonfirmasi (Supplier)</div>
            <div class="line"></div>
            <div class="name">{{ $po->supplier?->nama_supplier ?? 'Pihak Supplier / Vendor' }}</div>
        </div>
    </div>
</body>
</html>
