<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan WMS WMSLaf - {{ ucwords(str_replace('-', ' ', $reportType)) }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #111111;
            margin: 0;
            padding: 40px;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #222222;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo-title h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0;
            color: #111111;
        }
        .logo-title p {
            margin: 4px 0 0 0;
            font-size: 9px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .report-title {
            text-align: right;
        }
        .report-title h2 {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #ff9f00;
        }
        .report-title p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #666666;
        }
        .filter-summary {
            background-color: #f9f9f9;
            border: 1px solid #eeeeee;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .filter-item {
            margin: 0;
        }
        .filter-item strong {
            color: #666666;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 2px;
        }
        .filter-item span {
            font-size: 12px;
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
            color: #666666;
            border-bottom: 2px solid #dddddd;
            padding: 8px 10px;
            text-align: left;
        }
        td {
            padding: 10px 10px;
            border-bottom: 1px solid #eeeeee;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 30px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #222222;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .card {
            border: 1px solid #eeeeee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }
        .card h3 {
            margin: 0 0 12px 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666666;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .stat-item {
            text-align: left;
        }
        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666666;
        }
        .stat-val {
            font-size: 16px;
            font-weight: 800;
            margin-top: 2px;
            color: #111111;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #cccccc;
            background-color: #fafafa;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; border-color: #a7f3d0; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .badge-info { background-color: #e0f2fe; color: #075985; border-color: #bae6fd; }

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
            background-color: #ff9f00;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
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
        <div class="report-title">
            <h2>
                @if($reportType === 'stock-card') Laporan Kartu Stok
                @elseif($reportType === 'stock-movement') Laporan Mutasi Stok
                @elseif($reportType === 'aging-stock') Laporan Usia & Dead Stock
                @elseif($reportType === 'sales-support') Laporan Analisis Penjualan & Stok
                @elseif($reportType === 'warehouse-performance') Laporan Kinerja Gudang
                @endif
            </h2>
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Filter Summary Section --}}
    <div class="filter-summary">
        <div class="filter-item">
            <strong>Tipe Laporan</strong>
            <span>{{ ucwords(str_replace('-', ' ', $reportType)) }}</span>
        </div>
        @if($reportType === 'stock-card' && isset($data['produk']))
            <div class="filter-item">
                <strong>Produk</strong>
                <span>{{ $data['produk']->kode_barang }} — {{ $data['produk']->nama_barang }}</span>
            </div>
            <div class="filter-item">
                <strong>Periode Tanggal</strong>
                <span>{{ $data['date_from'] ? Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') : 'Awal' }} s.d. {{ $data['date_to'] ? Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') : 'Sekarang' }}</span>
            </div>
        @elseif($reportType === 'stock-movement')
            <div class="filter-item">
                <strong>Tipe Mutasi</strong>
                <span>{{ $data['tipe'] ?: 'Semua' }}</span>
            </div>
            <div class="filter-item">
                <strong>Periode Tanggal</strong>
                <span>{{ $data['date_from'] ? Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') : 'Awal' }} s.d. {{ $data['date_to'] ? Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') : 'Sekarang' }}</span>
            </div>
        @elseif($reportType === 'aging-stock')
            <div class="filter-item">
                <strong>Kategori 1</strong>
                <span>{{ $data['kategori1'] ?: 'Semua' }}</span>
            </div>
            <div class="filter-item">
                <strong>Kategori 2</strong>
                <span>{{ $data['kategori2'] ?: 'Semua' }}</span>
            </div>
            <div class="filter-item">
                <strong>Status Usia</strong>
                <span>{{ $data['status'] ?: 'Semua' }}</span>
            </div>
        @elseif($reportType === 'sales-support')
            <div class="filter-item">
                <strong>Periode Tanggal</strong>
                <span>{{ $data['date_from'] ? Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') : 'Awal' }} s.d. {{ $data['date_to'] ? Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') : 'Sekarang' }}</span>
            </div>
            <div class="filter-item">
                <strong>Filter Status Ketersediaan</strong>
                <span>{{ $data['availStatus'] ?: 'Semua' }}</span>
            </div>
        @endif
    </div>

    {{-- Report Content Rendering --}}
    @if($reportType === 'stock-card')
        @if(isset($data['produk']))
            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 15px; font-weight: bold; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">
                <div>Saldo Awal Periode: {{ $data['saldo_awal'] }} pasang</div>
                <div>Saldo Akhir Periode: {{ $data['saldo_akhir'] }} pasang</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%">Tanggal</th>
                        <th style="width: 15%">Tipe</th>
                        <th style="width: 25%">Keterangan</th>
                        <th style="width: 10%">Asal</th>
                        <th style="width: 10%">Tujuan</th>
                        <th style="width: 8%" class="text-center">Masuk</th>
                        <th style="width: 8%" class="text-center">Keluar</th>
                        <th style="width: 9%" class="text-center">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            <td>{{ $row['tanggal'] }}</td>
                            <td><span class="badge">{{ $row['tipe'] }}</span></td>
                            <td>{{ $row['keterangan'] }}</td>
                            <td>{{ $row['lokasi_asal'] }}</td>
                            <td>{{ $row['lokasi_tujuan'] }}</td>
                            <td class="text-center">{{ $row['qty_masuk'] ?: '-' }}</td>
                            <td class="text-center">{{ $row['qty_keluar'] ?: '-' }}</td>
                            <td class="text-center text-bold">{{ $row['saldo'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada transaksi dalam periode ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <p class="text-center">Silakan pilih SKU Produk terlebih dahulu di halaman admin sebelum mencetak Kartu Stok.</p>
        @endif

    @elseif($reportType === 'stock-movement')
        <table>
            <thead>
                <tr>
                    <th style="width: 15%">Tanggal</th>
                    <th style="width: 12%">SKU</th>
                    <th style="width: 23%">Nama Barang</th>
                    <th style="width: 10%">Tipe Mutasi</th>
                    <th style="width: 8%" class="text-center">Qty</th>
                    <th style="width: 10%">Asal</th>
                    <th style="width: 10%">Tujuan</th>
                    <th style="width: 12%">Operator</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['rows'] as $row)
                    <tr>
                        <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-family: monospace;">{{ $row->produk->kode_barang ?? '-' }}</td>
                        <td class="text-bold">{{ $row->produk->nama_barang ?? '-' }}</td>
                        <td><span class="badge">{{ $row->tipe_mutasi }}</span></td>
                        <td class="text-center text-bold">{{ number_format($row->qty) }}</td>
                        <td>{{ $row->lokasiAsal ? $row->lokasiAsal->kode_lokasi : '-' }}</td>
                        <td>{{ $row->lokasiTujuan ? $row->lokasiTujuan->kode_lokasi : '-' }}</td>
                        <td>{{ $row->data_dimasukan_oleh }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data mutasi stok ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($reportType === 'aging-stock')
        <table>
            <thead>
                <tr>
                    <th style="width: 12%">SKU</th>
                    <th style="width: 25%">Nama Barang</th>
                    <th style="width: 12%">Kategori 1</th>
                    <th style="width: 10%">Kategori 2</th>
                    <th style="width: 8%" class="text-center">Total Stok</th>
                    <th style="width: 10%" class="text-right">HPP</th>
                    <th style="width: 10%" class="text-right">Total Nilai HPP</th>
                    <th style="width: 13%">Status Usia</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotalStok = 0; $grandTotalNilai = 0; @endphp
                @forelse($data['rows'] as $row)
                    @php
                        $grandTotalStok += $row['total_qty'];
                        $grandTotalNilai += $row['total_nilai'];
                    @endphp
                    <tr>
                        <td style="font-family: monospace;">{{ $row['kode_barang'] }}</td>
                        <td class="text-bold">{{ $row['nama_barang'] }}</td>
                        <td>{{ $row['kategori'] }}</td>
                        <td>{{ $row['kategori_2'] }}</td>
                        <td class="text-center text-bold">{{ number_format($row['total_qty']) }}</td>
                        <td class="text-right">Rp{{ number_format($row['hpp'], 0, ',', '.') }}</td>
                        <td class="text-right text-bold">Rp{{ number_format($row['total_nilai'], 0, ',', '.') }}</td>
                        <td>
                            <span class="badge 
                                {{ $row['kategori_usia'] === 'Dead Stock' ? 'badge-danger' : '' }}
                                {{ $row['kategori_usia'] === 'Slow Moving' ? 'badge-warning' : '' }}
                                {{ $row['kategori_usia'] === 'Fresh Stock' ? 'badge-success' : '' }}
                            ">
                                {{ $row['kategori_usia'] }} ({{ $row['usia_hari'] }} Hari)
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data stok ditemukan</td>
                    </tr>
                @endforelse
                @if(count($data['rows']) > 0)
                    <tr style="background-color: #fafafa; font-weight: bold; font-size: 12px; border-top: 2px solid #dddddd;">
                        <td colspan="4">GRAND TOTAL</td>
                        <td class="text-center">{{ number_format($grandTotalStok) }} pasang</td>
                        <td></td>
                        <td class="text-right">Rp{{ number_format($grandTotalNilai, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>

    @elseif($reportType === 'sales-support')
        <div class="grid-2">
            <div>
                <div class="section-title">Top 10 Best Sellers (Produk Terlaris)</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%">SKU</th>
                            <th style="width: 45%">Nama Barang</th>
                            <th style="width: 15%" class="text-center">Qty</th>
                            <th style="width: 15%" class="text-right">Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['bestSellers'] as $b)
                            <tr>
                                <td style="font-family: monospace;">{{ $b['kode_barang'] }}</td>
                                <td class="text-bold">{{ $b['nama_barang'] }}</td>
                                <td class="text-center text-bold">{{ number_format($b['total_qty']) }}</td>
                                <td class="text-right">Rp{{ number_format($b['total_sales_value'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data penjualan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                <div class="section-title">Kontribusi Kategori</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%">Kategori</th>
                            <th style="width: 20%" class="text-center">Qty</th>
                            <th style="width: 30%" class="text-right">Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['topCategories'] as $tc)
                            <tr>
                                <td class="text-bold">{{ $tc['kategori'] }}</td>
                                <td class="text-center text-bold">{{ number_format($tc['total_qty']) }}</td>
                                <td class="text-right">Rp{{ number_format($tc['total_sales_value'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data kategori</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="card" style="margin-top: 25px;">
                    <h3>Metrik Pemenuhan (Fill Rate)</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-label">Transfer Fill Rate (Toko)</div>
                            <div class="stat-val text-bold" style="color: #ff9f00;">{{ $data['transferFillRate'] }}%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">PO Fulfillment Rate</div>
                            <div class="stat-val text-bold" style="color: #10b981;">{{ $data['poFillRate'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">Ketersediaan Stok (Stock Availability)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%">SKU</th>
                    <th style="width: 25%">Nama Barang</th>
                    <th style="width: 13%">Kategori</th>
                    <th style="width: 8%" class="text-center">Stok</th>
                    <th style="width: 8%" class="text-center">Min</th>
                    <th style="width: 8%" class="text-center">Safety</th>
                    <th style="width: 8%" class="text-center">Max</th>
                    <th style="width: 10%" class="text-center">Status</th>
                    <th style="width: 8%" class="text-center">Saran Order</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['stockAvailability'] as $sa)
                    <tr>
                        <td style="font-family: monospace;">{{ $sa['kode_barang'] }}</td>
                        <td class="text-bold">{{ $sa['nama_barang'] }}</td>
                        <td>{{ $sa['kategori'] }}</td>
                        <td class="text-center text-bold">{{ number_format($sa['total_qty']) }}</td>
                        <td class="text-center">{{ number_format($sa['min_stock']) }}</td>
                        <td class="text-center">{{ number_format($sa['safety_stock']) }}</td>
                        <td class="text-center">{{ number_format($sa['max_stock']) }}</td>
                        <td class="text-center">
                            <span class="badge 
                                {{ $sa['status'] === 'Critical' ? 'badge-danger' : '' }}
                                {{ $sa['status'] === 'Warning' ? 'badge-warning' : '' }}
                                {{ $sa['status'] === 'Optimal' ? 'badge-success' : '' }}
                                {{ $sa['status'] === 'Overstock' ? 'badge-info' : '' }}
                            ">
                                {{ $sa['status'] }}
                            </span>
                        </td>
                        <td class="text-center text-bold" style="color: {{ $sa['recomm_qty'] > 0 ? '#ff9f00' : 'inherit' }}">{{ $sa['recomm_qty'] > 0 ? number_format($sa['recomm_qty']) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data ketersediaan ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($reportType === 'warehouse-performance')
        <div class="grid-2">
            <div class="card">
                <h3>Kinerja Penerimaan (Receiving Stats)</h3>
                <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="stat-item">
                        <div class="stat-label">Total Nota Masuk (GRN)</div>
                        <div class="stat-val">{{ number_format($data['receivingStats']['total_grn']) }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Total Qty Diterima</div>
                        <div class="stat-val">{{ number_format($data['receivingStats']['total_qty']) }} pasang</div>
                    </div>
                    <div class="stat-item" style="border-top: 1px solid #eeeeee; padding-top: 8px;">
                        <div class="stat-label">GRN (Bulan Ini)</div>
                        <div class="stat-val">{{ number_format($data['receivingStats']['grn_this_month']) }}</div>
                    </div>
                    <div class="stat-item" style="border-top: 1px solid #eeeeee; padding-top: 8px;">
                        <div class="stat-label">Qty (Bulan Ini)</div>
                        <div class="stat-val">{{ number_format($data['receivingStats']['qty_this_month']) }} pasang</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Kinerja Pengeluaran (Picking & Packing)</h3>
                <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="stat-item">
                        <div class="stat-label">Total Order Picking</div>
                        <div class="stat-val">{{ number_format($data['pickingStats']['total_picking']) }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Picking Selesai</div>
                        <div class="stat-val">{{ number_format($data['pickingStats']['completed_picking']) }}</div>
                    </div>
                    <div class="stat-item" style="border-top: 1px solid #eeeeee; padding-top: 8px;">
                        <div class="stat-label">Rerata Durasi Picking</div>
                        <div class="stat-val">{{ $data['pickingStats']['avg_picking_duration'] }} menit</div>
                    </div>
                    <div class="stat-item" style="border-top: 1px solid #eeeeee; padding-top: 8px;">
                        <div class="stat-label">QC Passed Rate</div>
                        <div class="stat-val" style="color: #10b981;">{{ $data['pickingStats']['qc_passed_rate'] }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 15px;">
            <h3>Akurasi Stok & Hasil Opname (Inventory Accuracy)</h3>
            <div style="display: flex; justify-content: space-around; text-align: center; margin-top: 10px;">
                <div>
                    <div class="stat-label">Total Qty Buku (Sistem)</div>
                    <div class="stat-val" style="font-size: 20px;">{{ number_format($data['inventoryAccuracy']['total_sistem']) }}</div>
                </div>
                <div>
                    <div class="stat-label">Total Qty Fisik</div>
                    <div class="stat-val" style="font-size: 20px;">{{ number_format($data['inventoryAccuracy']['total_fisik']) }}</div>
                </div>
                <div>
                    <div class="stat-label">Total Selisih (Variance)</div>
                    <div class="stat-val" style="font-size: 20px; color: {{ $data['inventoryAccuracy']['total_variance'] > 0 ? '#f43f5e' : '#10b981' }}">{{ number_format($data['inventoryAccuracy']['total_variance']) }}</div>
                </div>
                <div>
                    <div class="stat-label">Persentase Akurasi</div>
                    <div class="stat-val text-bold" style="font-size: 24px; color: #ff9f00;">{{ $data['inventoryAccuracy']['accuracy_rate'] }}%</div>
                </div>
            </div>
        </div>
    @endif

    <div style="margin-top: 100px; display: flex; justify-content: space-between; text-align: center;">
        <div style="width: 200px;">
            <p style="font-size: 10px; color: #666666; text-transform: uppercase; margin-bottom: 60px;">Disiapkan Oleh,</p>
            <div style="border-bottom: 1px solid #666666;"></div>
            <p style="margin-top: 5px;">Staff Admin Gudang</p>
        </div>
        <div style="width: 200px;">
            <p style="font-size: 10px; color: #666666; text-transform: uppercase; margin-bottom: 60px;">Disetujui Oleh,</p>
            <div style="border-bottom: 1px solid #666666;"></div>
            <p style="margin-top: 5px;">Warehouse Supervisor</p>
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
