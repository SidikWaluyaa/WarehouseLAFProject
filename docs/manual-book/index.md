# MANUAL BOOK DOKUMENTASI PENGGUNAAN WMS LAF PROJECT
## Sistem Manajemen Gudang & Inventori Terintegrasi

---

### **Daftar Isi (Table of Contents)**

1. [Bab 1: Pengenalan & Dashboard Utama](01-dashboard.md)
   - Overview Sistem WMS LAF Project
   - Ringkasan KPI Gudang & Indikator Inventori
   - Peringatan Stok Minimum (*Reorder Alert*) & *Dead Stock*
   - Grafik Tren Penjualan & Analisis Musiman (*Peak Months*)
   - Aktivitas Operasional Real-Time Gudang & Riwayat Transaksi

2. [Bab 2: Manajemen Master Data](02-master-data.md)
   - Master Stok (Katalog Produk & Inventori)
   - Manajemen Lokasi Gudang & Toko
   - Aturan Pengkodean Barang (Generator SKU Otomatis)
   - Master Data Supplier
   - Master Data Pelanggan

3. [Bab 3: Audit Stok & Opname](03-audit-stok.md)
   - Konsep & Metode Stok Opname
   - Pembuatan Sesi Audit (Full vs Partial Per Lokasi/Kategori)
   - Input Hasil Perhitungan Fisik (*Physical Count*)
   - Penyesuaian Varian Stok (*Stock Adjustment*) & Jurnal Log

4. [Bab 4: Transaksi Gudang](04-transaksi-gudang.md)
   - Barang Masuk (*Inbound / Receiving*)
   - Barang Keluar (*Outbound / Dispatch*)
   - Purchase Order (PO Supplier & Tracking Sisa PO)

5. [Bab 5: Forecast & Analisis Inventori](05-forecast-analisis.md)
   - Analisis Klasifikasi ABC (Pareto Inventory)
   - Perhitungan *Reorder Point* (ROP), *Safety Stock*, & *Maximum Stock*
   - Deteksi *Dead Stock* & Nilai Aset Tertahan
   - Peramalan Permintaan (*Demand Forecasting*) & *Days of Inventory* (DOI)

6. [Bab 6: Operasi Gudang](06-operasi-gudang.md)
   - Transfer Gudang (Internal Location Transfer)
   - Replenishment Toko (Pengisian Kembali Stok Toko Ritel)
   - Picking Barang (Daftar Pengambilan Barang & QR/Barcode Scanner)
   - Packing & Quality Control (QC & Verifikasi Pengiriman)

7. [Bab 7: Laporan & Audit Log](07-laporan-dan-audit-log.md)
   - Laporan Mutasi Stok, Keuangan, & Transaksi
   - Fitur Ekspor PDF, Excel, & Mode Cetak Struk/Faktur
   - Audit Log System (Penelusuran Jejak Perubahan Data & Pengguna)

8. [Bab 8: Manajemen Akses & Keamanan System](08-manajemen-akses.md)
   - Pengelolaan Pengguna (*User Management*)
   - Pengaturan Peran & Hak Akses (*Roles & Permissions*)
   - Profil Pengguna & Keamanan Log Out

---

### **Matriks Role & Hak Akses Sistem**

Sistem WMS LAF Project menerapkan kontrol akses berbasis peran (*Role-Based Access Control / RBAC*). Berikut adalah pemetaan hak akses untuk setiap peran pengguna di dalam sistem:

| Fitur / Modul Menu | Super Admin | Admin Manajer | Operator Gudang | Tim Sales | Tim Finance |
|---|:---:|:---:|:---:|:---:|:---:|
| **Dashboard Utama** |  |  |  |  |  |
| **Master Stok** (Lihat) |  |  |  |  |  |
| **Master Stok** (Tambah/Edit/Hapus/Import) |  |  | ❌ | ❌ | ❌ |
| **Manajemen Lokasi** (Lihat) |  |  |  |  |  |
| **Manajemen Lokasi** (Tambah/Edit/Hapus) |  |  | ❌ | ❌ | ❌ |
| **Aturan Pengkodean** |  |  | ❌ | ❌ | ❌ |
| **Supplier & Pelanggan** (Lihat & Riwayat) |  |  |  |  |  |
| **Supplier & Pelanggan** (Kelola Data) |  |  | ❌ | ❌ | ❌ |
| **Audit Stok (Opname)** |  |  |  | ❌ | ❌ |
| **Barang Masuk (Inbound)** |  |  |  | ❌ | ❌ |
| **Barang Keluar (Outbound)** |  |  |  |  | ❌ |
| **Purchase Order (Lihat)** |  |  |  |  |  |
| **Purchase Order (Buat/Edit)** |  |  | ❌ |  | ❌ |
| **Forecast & Analisis** |  |  | ❌ | ❌ |  |
| **Transfer Gudang & Replenishment** |  |  |  | ❌ | ❌ |
| **Picking & Packing/QC** |  |  |  |  | ❌ |
| **Laporan & Analitik (Keuangan)** |  |  | ❌ | ❌ |  |
| **Log Perubahan (Audit Log)** |  |  | ❌ | ❌ | ❌ |
| **Manajemen Akses User** |  | ❌ | ❌ | ❌ | ❌ |

---

### **Persyaratan Perangkat & Lingkungan Sistem**
- **Browser yang Didukung**: Google Chrome (versi 100+), Mozilla Firefox (versi 100+), Microsoft Edge, atau Safari.
- **Koneksi Jaringan**: Terhubung ke jaringan LAN gudang atau internet server WMS.
- **Perangkat Opsional**: Barcode Scanner Handheld (USB/Bluetooth), Printer Thermal / Laser untuk pencetakan label & dokumen faktur.
