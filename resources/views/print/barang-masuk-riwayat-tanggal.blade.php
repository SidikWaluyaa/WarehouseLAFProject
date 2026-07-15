<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai Transaksi Barang Masuk</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #111111;
            margin: 0;
            padding: 30px;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #222222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-title h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
            color: #111111;
        }
        .logo-title p {
            margin: 3px 0 0 0;
            font-size: 9px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #a855f7;
        }
        .invoice-title p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #666666;
        }
        .filter-info {
            background-color: #f9f9f9;
            border: 1px solid #eeeeee;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }
        .filter-item {
            margin: 0;
        }
        .filter-item strong {
            color: #555555;
            text-transform: uppercase;
            font-size: 9px;
            display: block;
            margin-bottom: 2px;
        }
        .filter-item span {
            font-size: 11px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f5f5f5;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #555555;
            border-bottom: 2px solid #dddddd;
            border-top: 1px solid #eeeeee;
            padding: 8px 10px;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eeeeee;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: monospace;
            font-size: 10.5px;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #dddddd;
            background-color: #fafafa;
            text-transform: uppercase;
        }
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .total-box {
            width: 280px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .total-row.grand-total {
            border-top: 2px solid #222222;
            border-bottom: 2px solid #222222;
            font-size: 13px;
            font-weight: 800;
            color: #111111;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            text-align: center;
            margin-top: 60px;
            page-break-inside: avoid;
        }
        .signature-block {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .signature-line {
            width: 180px;
            border-bottom: 1px solid #666666;
            margin-top: 50px;
            margin-bottom: 4px;
        }
        .signature-title {
            font-size: 9px;
            color: #666666;
            text-transform: uppercase;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #a855f7;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.1s ease;
            z-index: 1000;
        }
        .btn-print:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">Cetak / Simpan PDF</button>

    <div class="header">
        <div class="logo-title">
            <h1>WMS LAF PROJECT</h1>
            <p>Warehouse Management System</p>
        </div>
        <div class="invoice-title">
            <h2>LAPORAN NILAI TRANSAKSI BARANG MASUK</h2>
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Active Filters -->
    <div class="filter-info">
        <div class="filter-item">
            <strong>Tanggal</strong>
            <span>
                @if($tanggalStart && $tanggalEnd)
                    {{ \Carbon\Carbon::parse($tanggalStart)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tanggalEnd)->format('d/m/Y') }}
                @elseif($tanggalStart)
                    Mulai {{ \Carbon\Carbon::parse($tanggalStart)->format('d/m/Y') }}
                @elseif($tanggalEnd)
                    Sampai {{ \Carbon\Carbon::parse($tanggalEnd)->format('d/m/Y') }}
                @else
                    Semua Tanggal
                @endif
            </span>
        </div>
        <div class="filter-item">
            <strong>Tipe Nota</strong>
            <span>{{ $tipeFilter ?: 'Semua Tipe' }}</span>
        </div>
        <div class="filter-item">
            <strong>Lokasi Simpan</strong>
            <span>{{ $lokasiFilter ?: 'Semua Lokasi' }}</span>
        </div>
        @if($search)
            <div class="filter-item" style="grid-column: span 2;">
                <strong>Kata Kunci Pencarian</strong>
                <span>"{{ $search }}"</span>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%" class="text-center">No</th>
                <th style="width: 8%">Tanggal</th>
                <th style="width: 8%">Tipe</th>
                <th style="width: 12%">Supplier</th>
                <th style="width: 10%">No. Nota</th>
                <th style="width: 23%">Produk</th>
                <th style="width: 6%" class="text-center">QTY</th>
                <th style="width: 6%" class="text-center">Satuan</th>
                <th style="width: 8%">Operator</th>
                <th style="width: 8%" class="text-center">Lokasi</th>
                <th style="width: 8%" class="text-right">HPP</th>
                <th style="width: 10%" class="text-right">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @forelse($history as $index => $h)
                @php
                    $subtotal = $h->qty_masuk * $h->hpp;
                    $totalQty += $h->qty_masuk;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $h->barangMasukHeader->tanggal->format('d/m/Y') }}</td>
                    <td><span class="badge">{{ $h->barangMasukHeader->tipe_nota }}</span></td>
                    <td><strong>{{ $h->barangMasukHeader->supplier->nama_supplier ?? '-' }}</strong></td>
                    <td class="font-mono">{{ $h->barangMasukHeader->nomor_nota ?? '-' }}</td>
                    <td>
                        <strong>{{ $h->produk->nama_barang }}</strong>
                        <div style="font-size: 9px; color: #666666; margin-top: 1px;">SKU: {{ $h->produk->kode_barang }} | Varian: {{ $h->produk->variasi_barang }}</div>
                    </td>
                    <td class="text-center font-mono font-bold">{{ number_format($h->qty_masuk) }}</td>
                    <td class="text-center text-muted">{{ $h->satuan }}</td>
                    <td>{{ $h->barangMasukHeader->data_dimasukan_oleh }}</td>
                    <td class="text-center"><span class="badge">{{ $h->lokasi_simpan }}</span></td>
                    <td class="text-right font-mono">Rp{{ number_format($h->hpp, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 30px;">Tidak ada data riwayat nilai barang masuk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-box">
            <div class="total-row">
                <span>Total Baris</span>
                <span>{{ $history->count() }} baris</span>
            </div>
            <div class="total-row">
                <span>Total Kuantitas</span>
                <span>{{ number_format($totalQty) }} pasang</span>
            </div>
            <div class="total-row grand-total">
                <span>Total Nilai (HPP)</span>
                <span>Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <p class="signature-title">Disiapkan Oleh,</p>
            <div class="signature-line"></div>
            <p>Admin Gudang</p>
        </div>
        <div class="signature-block">
            <p class="signature-title">Diketahui Oleh,</p>
            <div class="signature-line"></div>
            <p>Kepala Gudang / Supervisor</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
