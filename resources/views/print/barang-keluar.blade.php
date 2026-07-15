<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Barang Keluar - {{ $transaction->nomor_nota ?? '-' }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #111111;
            margin: 0;
            padding: 40px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #222222;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-title h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
            color: #111111;
        }
        .logo-title p {
            margin: 4px 0 0 0;
            font-size: 10px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #f43f5e;
        }
        .invoice-title p {
            margin: 4px 0 0 0;
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .details-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666666;
            margin: 0 0 10px 0;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 5px;
        }
        .details-block p {
            margin: 4px 0;
        }
        .details-block .name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th {
            background-color: #f5f5f5;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666666;
            border-bottom: 2px solid #dddddd;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #eeeeee;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 60px;
        }
        .total-box {
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .total-row.grand-total {
            border-top: 2px solid #222222;
            border-bottom: 2px solid #222222;
            font-size: 16px;
            font-weight: 800;
            color: #111111;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            text-align: center;
            margin-top: 80px;
        }
        .signature-block {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #666666;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 10px;
            color: #666666;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #cccccc;
            background-color: #fafafa;
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
            background-color: #f43f5e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.1s ease;
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
            <h2>SURAT JALAN / BARANG KELUAR</h2>
            <p>{{ $transaction->nomor_nota ?? '-' }}</p>
        </div>
    </div>

    <div class="details-grid">
        <div class="details-block">
            <h3>Pelanggan / Penerima</h3>
            <p class="name">{{ $transaction->pelanggan->nama_pelanggan }}</p>
            @if($transaction->pelanggan->kontak)
                <p><strong>Kontak:</strong> {{ $transaction->pelanggan->kontak }}</p>
            @endif
            @if($transaction->pelanggan->alamat)
                <p><strong>Alamat:</strong> {{ $transaction->pelanggan->alamat }}</p>
            @endif
        </div>
        <div class="details-block">
            <h3>Informasi Transaksi</h3>
            <p><strong>Tanggal Keluar:</strong> {{ $transaction->tanggal->format('d F Y') }}</p>
            <p><strong>Tipe Nota:</strong> <span class="badge">{{ $transaction->tipe_nota }}</span></p>
            <p><strong>Dikeluarkan Oleh:</strong> {{ $transaction->dikeluarkan_oleh }}</p>
            @if($transaction->keterangan_tambahan)
                <p><strong>Keterangan:</strong> {{ $transaction->keterangan_tambahan }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Kode Barang (SKU)</th>
                <th style="width: 35%">Nama Barang / Produk</th>
                <th style="width: 15%" class="text-center">QTY</th>
                <th style="width: 12%" class="text-right">Harga Modal (HPP)</th>
                <th style="width: 13%" class="text-right">Total Nilai HPP</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; $totalValue = 0; @endphp
            @foreach($transaction->details as $index => $detail)
                @php
                    $subtotal = $detail->qty_keluar * $detail->hpp;
                    $totalQty += $detail->qty_keluar;
                    $totalValue += $subtotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $detail->produk->kode_barang }}</td>
                    <td>
                        <strong>{{ $detail->produk->nama_barang }}</strong>
                        <div style="font-size: 10px; color: #666666; margin-top: 2px;">Varian: {{ $detail->produk->variasi_barang }} | Asal: {{ $detail->lokasi_pengambilan }}</div>
                    </td>
                    <td class="text-center"><strong>{{ number_format($detail->qty_keluar) }}</strong> {{ $detail->satuan }}</td>
                    <td class="text-right">Rp{{ number_format($detail->hpp, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: 600;">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-box">
            <div class="total-row">
                <span>Total Item</span>
                <span>{{ $transaction->details->count() }} item</span>
            </div>
            <div class="total-row">
                <span>Total Kuantitas</span>
                <span><strong>{{ number_format($totalQty) }} pasang</strong></span>
            </div>
            <div class="total-row grand-total">
                <span>Total Nilai HPP</span>
                <span>Rp{{ number_format($totalValue, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <p class="signature-title">Disiapkan Oleh,</p>
            <div class="signature-line"></div>
            <p>{{ $transaction->dikeluarkan_oleh }}</p>
        </div>
        <div class="signature-block">
            <p class="signature-title">Diterima Oleh,</p>
            <div class="signature-line"></div>
            <p>Pelanggan / Penerima</p>
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
