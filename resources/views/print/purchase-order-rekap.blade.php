<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Tracking PO - LAF Project</title>
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
            padding: 30px 36px;
            font-size: 10.5px;
            line-height: 1.4;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .logo-title h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
            color: #111111;
            text-transform: uppercase;
        }
        .logo-title p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: #6b7280;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
            color: #d97706;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .doc-title p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #4b5563;
        }
        .filter-badge-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 16px;
            align-items: center;
            font-size: 10px;
        }
        .filter-badge {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 2px 8px;
            font-weight: 600;
            color: #374151;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 10px;
            background: #ffffff;
        }
        .card .label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .card .value {
            font-size: 14px;
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
            font-size: 9.5px;
        }
        th, td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
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
            padding: 1px 6px;
            border-radius: 9999px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-selesai { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-sebagian { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .status-ditutup { background: #f3f4f6; color: #6b7280; border: 1px dashed #d1d5db; }

        tfoot td {
            font-weight: 700;
            background-color: #f9fafb;
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            margin-top: 24px;
            page-break-inside: avoid;
            max-width: 500px;
            margin-left: auto;
        }
        .signature-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            background: #ffffff;
        }
        .signature-box .role {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4b5563;
            margin-bottom: 46px;
        }
        .signature-box .line {
            border-top: 1px dashed #9ca3af;
            margin: 0 14px 4px 14px;
        }
        .signature-box .name {
            font-size: 10.5px;
            font-weight: 700;
            color: #111827;
        }

        .action-bar {
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-print {
            background: #d97706;
            color: #ffffff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-print:hover { background: #b45309; }

        @media print {
            body { padding: 10px; font-size: 9px; }
            .action-bar { display: none !important; }
            @page { margin: 8mm 8mm; size: landscape; }
            .card .value { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <a href="{{ route('purchase-order') }}" class="btn-back">
            &larr; Kembali ke Daftar PO
        </a>
        <button class="btn-print" onclick="window.print()">
            &#128438; Cetak / Simpan PDF (Landscape)
        </button>
    </div>

    <!-- Header Section -->
    <div class="header">
        <div class="logo-title">
            <h1>LAF PROJECT WMS</h1>
            <p>Warehouse & Inventory Management System</p>
        </div>
        <div class="doc-title">
            <h2>Laporan Rekap Tracking &amp; Sisa PO</h2>
            <p>Dicetak: {{ now()->format('d/m/Y H:i') }} WIB &bull; Oleh: {{ auth()->user()->name ?? 'System' }}</p>
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-badge-bar">
        <span><strong>Kriteria Laporan:</strong></span>
        @if ($search)
            <span class="filter-badge">Pencarian: "{{ $search }}"</span>
        @endif
        @if (($filter ?? 'all') === 'outstanding')
            <span class="filter-badge" style="background: #fef3c7; color: #92400e; border-color: #fde68a; font-weight: bold;">Status: Belum Lengkap (Outstanding)</span>
        @elseif (($filter ?? 'all') === 'variance')
            <span class="filter-badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd; font-weight: bold;">Status: Ada Selisih / Penyesuaian</span>
        @elseif (($filter ?? 'all') === 'closed')
            <span class="filter-badge" style="background: #f3f4f6; color: #374151; border-color: #d1d5db; font-weight: bold;">Status: Sisa Ditutup</span>
        @else
            <span class="filter-badge">Semua Item PO</span>
        @endif
        <span class="filter-badge">Total Item: {{ number_format(count($items)) }} baris</span>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="label">Total Dipesan</div>
            <div class="value">{{ number_format($totalJumlah) }}</div>
        </div>
        <div class="card">
            <div class="label">Total Diterima</div>
            <div class="value text-green">{{ number_format($totalMasuk) }}</div>
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
            <div class="label">Nilai HPP Sisa</div>
            <div class="value">Rp{{ number_format($totalNilaiSisa, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 65px;">Tgl PO</th>
                <th style="width: 105px;">Nomor PO</th>
                <th>Supplier</th>
                <th style="width: 75px;">Artikel</th>
                <th>Produk</th>
                <th style="width: 95px;">SKU</th>
                <th class="text-center" style="width: 50px;">Awal</th>
                <th class="text-center" style="width: 50px;">Masuk</th>
                <th class="text-center" style="width: 50px;">Sisa</th>
                <th class="text-center" style="width: 45px;">Ditutup</th>
                <th class="text-center" style="width: 50px;">Selisih</th>
                <th class="text-right" style="width: 70px;">HPP PO</th>
                <th class="text-right" style="width: 85px;">Nilai Sisa</th>
                <th class="text-center" style="width: 65px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $idx => $row)
                @php
                    $nilaiRow = $row->sisa * (float) $row->hpp;
                    $st = $row->statusPenerimaan();
                    $badgeClass = match($st) {
                        'selesai' => 'status-selesai',
                        'sebagian' => 'status-sebagian',
                        'ditutup' => 'status-ditutup',
                        default => 'status-sebagian',
                    };
                    $badgeLabel = match($st) {
                        'selesai' => 'Lengkap',
                        'sebagian' => 'Outstanding',
                        'ditutup' => 'Ditutup',
                        default => 'Outstanding',
                    };
                    $sel = (int) ($row->total_selisih ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-mono">{{ $row->poHeader?->tanggal ? \Carbon\Carbon::parse($row->poHeader->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td class="font-mono font-bold">{{ $row->poHeader?->nomor_po ?? 'PO #'.$row->po_header_id }}</td>
                    <td>{{ $row->poHeader?->supplier?->nama_supplier ?? '-' }}</td>
                    <td class="font-mono">{{ $row->poHeader?->artikel ?? '-' }}</td>
                    <td class="font-bold">{{ $row->produk?->nama_barang ?? '-' }}</td>
                    <td class="font-mono">{{ $row->produk?->kode_barang ?? '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ number_format($row->jumlah) }}</td>
                    <td class="text-center font-mono font-bold" style="color: #059669;">{{ number_format($row->barang_masuk) }}</td>
                    <td class="text-center font-mono font-bold" style="{{ $row->sisa > 0 ? 'color: #d97706;' : 'color: #9ca3af;' }}">
                        {{ number_format($row->sisa) }}
                    </td>
                    <td class="text-center font-mono" style="color: #0284c7;">{{ $row->qty_ditutup ? number_format($row->qty_ditutup) : '-' }}</td>
                    <td class="text-center font-mono font-bold">
                        @if ($row->jumlah_adjust > 0)
                            <span style="color: {{ $sel < 0 ? '#d97706' : ($sel > 0 ? '#0284c7' : '#111827') }};">
                                {{ $sel > 0 ? '+'.$sel : $sel }}
                            </span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td class="text-right font-mono">Rp{{ number_format((float) $row->hpp, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp{{ number_format($nilaiRow, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 24px; color: #6b7280;">Tidak ada data tracking PO yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">TOTAL:</td>
                <td class="text-center font-mono font-bold">{{ number_format($totalJumlah) }}</td>
                <td class="text-center font-mono font-bold" style="color: #059669;">{{ number_format($totalMasuk) }}</td>
                <td class="text-center font-mono font-bold" style="color: #d97706;">{{ number_format($totalSisa) }}</td>
                <td class="text-center font-mono" style="color: #0284c7;">{{ number_format($totalDitutup) }}</td>
                <td></td>
                <td></td>
                <td class="text-right font-mono font-bold" style="color: #d97706;">Rp{{ number_format($totalNilaiSisa, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="role">Diverifikasi Oleh (Gudang)</div>
            <div class="line"></div>
            <div class="name">{{ auth()->user()->name ?? 'Staff Gudang' }}</div>
        </div>
        <div class="signature-box">
            <div class="role">Disetujui Oleh (Manajemen)</div>
            <div class="line"></div>
            <div class="name">Kepala Gudang / Manajer</div>
        </div>
    </div>
</body>
</html>
