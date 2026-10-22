<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Stock Opname - {{ $session->nomor_opname }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #111111;
            margin: 0;
            padding: 36px 40px;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 24px;
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
            font-size: 12px;
            font-weight: 700;
            color: #111827;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-item label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin-bottom: 2px;
        }
        .info-item span {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #ffffff;
            text-align: center;
        }
        .card.highlight-blue { border-top: 3px solid #2563eb; }
        .card.highlight-green { border-top: 3px solid #16a34a; }
        .card.highlight-red { border-top: 3px solid #dc2626; }
        .card.highlight-amber { border-top: 3px solid #d97706; }
        .card.highlight-purple { border-top: 3px solid #7c3aed; }
        .card .title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .card .value {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            font-family: 'JetBrains Mono', monospace;
        }
        .card .desc {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            font-size: 10px;
        }
        th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 8px 10px;
            text-align: left;
            border-top: 1px solid #1e293b;
            border-bottom: 1px solid #1e293b;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tr.diff-deficit {
            background-color: #fef2f2 !important;
        }
        tr.diff-surplus {
            background-color: #f0fdf4 !important;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }
        .badge-zero { background: #f3f4f6; color: #4b5563; }
        .badge-plus { background: #dcfce7; color: #15803d; }
        .badge-minus { background: #fee2e2; color: #b91c1c; }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: center;
            background: #ffffff;
        }
        .signature-box .role {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #4b5563;
            margin-bottom: 50px;
        }
        .signature-box .line {
            border-top: 1px dashed #9ca3af;
            margin: 0 16px 6px 16px;
        }
        .signature-box .name {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }
        .action-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn-print {
            background: #d97706;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover {
            background: #b45309;
        }
        @media print {
            body { padding: 16px; font-size: 9px; }
            .action-bar { display: none !important; }
            @page { margin: 12mm 10mm; size: auto; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <button class="btn-print" onclick="window.print()">
            Cetak / Simpan PDF
        </button>
    </div>

    <!-- Header Section -->
    <div class="header">
        <div class="logo-title">
            <h1>LAF PROJECT WMS</h1>
            <p>Warehouse & Inventory Management System</p>
        </div>
        <div class="doc-title">
            <h2>Berita Acara Stock Opname</h2>
            <p>{{ $session->nomor_opname }}</p>
        </div>
    </div>

    <!-- Information Metadata -->
    <div class="info-grid">
        <div class="info-item">
            <label>Lokasi Fisik</label>
            <span>{{ $session->lokasi->kode_lokasi }} ({{ $session->lokasi->nama_lokasi }})</span>
        </div>
        <div class="info-item">
            <label>Tanggal Opname</label>
            <span>{{ $session->tanggal->format('d F Y') }}</span>
        </div>
        <div class="info-item">
            <label>Pelaksana / Operator</label>
            <span>{{ $session->operator_name }}</span>
        </div>
        <div class="info-item">
            <label>Status Dokumen</label>
            <span><strong>{{ $session->status }}</strong></span>
        </div>
    </div>

    <!-- Executive Summary Cards -->
    <div class="summary-cards">
        <div class="card highlight-blue">
            <div class="title">Total SKU</div>
            <div class="value">{{ number_format($totalItems) }}</div>
            <div class="desc">Item terdaftar</div>
        </div>
        <div class="card highlight-green">
            <div class="title">Sesuai (Cocok)</div>
            <div class="value">{{ number_format($matchedItems) }}</div>
            <div class="desc">Selisih = 0</div>
        </div>
        <div class="card highlight-red">
            <div class="title">Selisih Kurang (-)</div>
            <div class="value">{{ number_format($deficitItems) }}</div>
            <div class="desc">Stok fisik kurang</div>
        </div>
        <div class="card highlight-amber">
            <div class="title">Selisih Lebih (+)</div>
            <div class="value">{{ number_format($surplusItems) }}</div>
            <div class="desc">Stok fisik lebih</div>
        </div>
        <div class="card highlight-purple">
            <div class="title">Net Selisih Nilai</div>
            <div class="value" style="font-size: 13px;">{{ ($netNilaiSelisih >= 0 ? '+' : '') . 'Rp' . number_format($netNilaiSelisih, 0, ',', '.') }}</div>
            <div class="desc">Valuasi HPP</div>
        </div>
    </div>

    <!-- Discrepancy & Itemized Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 14%;">Kode SKU</th>
                <th style="width: 28%;">Nama Barang / Produk</th>
                <th style="width: 8%;" class="text-center">Satuan</th>
                <th style="width: 9%;" class="text-center">Sistem</th>
                <th style="width: 9%;" class="text-center">Fisik</th>
                <th style="width: 9%;" class="text-center">Selisih</th>
                <th style="width: 10%;" class="text-right">HPP</th>
                <th style="width: 9%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($session->details as $index => $detail)
                @php
                    $isDeficit = $detail->selisih < 0;
                    $isSurplus = $detail->selisih > 0;
                    $rowClass = $isDeficit ? 'diff-deficit' : ($isSurplus ? 'diff-surplus' : '');
                    $hpp = $detail->produk ? (float)$detail->produk->hpp : 0;
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono" style="font-weight: 700; color: #1e3a8a;">
                        {{ $detail->produk ? $detail->produk->kode_barang : '-' }}
                    </td>
                    <td>
                        <strong>{{ $detail->produk ? $detail->produk->nama_barang : '-' }}</strong>
                        @if($detail->produk && $detail->produk->variasi_barang)
                            <div style="font-size: 9px; color: #6b7280;">Var: {{ $detail->produk->variasi_barang }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $detail->produk ? $detail->produk->satuan : 'pasang' }}</td>
                    <td class="text-center font-mono font-bold">{{ number_format($detail->qty_sistem) }}</td>
                    <td class="text-center font-mono font-bold">{{ number_format($detail->qty_fisik) }}</td>
                    <td class="text-center">
                        @if($detail->selisih == 0)
                            <span class="badge badge-zero">0</span>
                        @elseif($detail->selisih > 0)
                            <span class="badge badge-plus">+{{ $detail->selisih }}</span>
                        @else
                            <span class="badge badge-minus">{{ $detail->selisih }}</span>
                        @endif
                    </td>
                    <td class="text-right font-mono">Rp{{ number_format($hpp, 0, ',', '.') }}</td>
                    <td style="color: #4b5563;">{{ $detail->catatan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1;">
                <td colspan="4" class="text-right">TOTAL KUANTITAS:</td>
                <td class="text-center font-mono">{{ number_format($totalQtySistem) }}</td>
                <td class="text-center font-mono">{{ number_format($totalQtyFisik) }}</td>
                <td class="text-center font-mono {{ $netSelisihQty < 0 ? 'text-red-600' : ($netSelisihQty > 0 ? 'text-green-600' : '') }}">
                    {{ ($netSelisihQty > 0 ? '+' : '') . number_format($netSelisihQty) }}
                </td>
                <td colspan="2" class="text-right font-mono" style="font-size: 10px;">
                    Net: {{ ($netNilaiSelisih >= 0 ? '+' : '') . 'Rp' . number_format($netNilaiSelisih, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Signature Section (3 Pihak) -->
    <div class="signatures">
        <div class="signature-box">
            <div class="role">Petugas Pelaksana Opname</div>
            <div class="line"></div>
            <div class="name">{{ $session->operator_name }}</div>
            <div style="font-size: 9px; color: #9ca3af; margin-top: 2px;">Auditor Lapangan</div>
        </div>
        <div class="signature-box">
            <div class="role">Kepala Gudang / PIC Lokasi</div>
            <div class="line"></div>
            <div class="name">...................................................</div>
            <div style="font-size: 9px; color: #9ca3af; margin-top: 2px;">Penanggung Jawab Lokasi</div>
        </div>
        <div class="signature-box">
            <div class="role">Manager Operasional / Direksi</div>
            <div class="line"></div>
            <div class="name">...................................................</div>
            <div style="font-size: 9px; color: #9ca3af; margin-top: 2px;">Persetujuan Penyesuaian</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 600);
        }
    </script>
</body>
</html>
