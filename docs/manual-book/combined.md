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


<div style="page-break-after: always;"></div>

# BAB 1: DASHBOARD UTAMA WMS

![Tampilan keseluruhan halaman Dashboard Utama WMS LAF Project](images/dashboard-overview.png)

## Dashboard Utama & KPI Gudang

### 1. Deskripsi Singkat
Dashboard Utama merupakan pusat kendali visual dan analisis real-time dari seluruh aktivitas inventori, kesehatan stok gudang, transaksi masuk-keluar, serta rekomendasi pengadaan barang (reorder) di WMS LAF Project. Halaman ini menyajikan indikator kinerja utama (KPI) secara konsolidasi untuk membantu pembuat keputusan dan operator gudang memantau arus barang dengan cepat.

---

### 2. Role / Hak Akses
- **Super Admin**: Akses penuh ke seluruh widget dan data KPI.
- **Admin Manajer**: Akses penuh ke seluruh widget dan data KPI.
- **Operator Gudang**: Akses ke widget stok, status operasional (receiving, picking, shipment), dan riwayat transaksi.
- **Tim Sales**: Akses ke ringkasan stok, produk menipis, dan riwayat barang keluar.
- **Tim Finance**: Akses ke nilai investasi inventori (inventory value), nilai dead stock, dan grafik penjualan.

---

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar Utama -> Dashboard
- **URL**: `/dashboard` (atau halaman utama `/`)

---

### 4. Alur Penggunaan (Step by Step)
1. **Pengaksesan**: Setelah login berhasil, sistem secara otomatis mengarahkan pengguna ke halaman Dashboard (`/dashboard`).
2. **Pemantauan Kartu KPI Utama**: Periksa 4 Kartu KPI di bagian atas:
   - *Total SKUs/Produk* & *Total Kuantitas Unit Stok*.
   - *Total Nilai Inventori (Asset Value)* berbasis HPP.
   - *Stok Menipis (Low Stock Alert)*.
   - *Nilai & Kuantitas Dead Stock*.
3. **Analisis Operasional Gudang**: Gulir ke panel *Operasi Gudang Hari Ini* untuk melihat jumlah *Receiving Today*, *Picking Today*, *Shipment Today*, *Pending Transfer*, dan *Rekomendasi Replenishment*.
4. **Tinjauan Rekomendasi Reorder**: Periksa tabel *Rekomendasi Pembelian Ulang (Reorder Point Alert)* untuk melihat barang yang sudah menyentuh batas minimum stok beserta perkiraan *Days of Inventory (DOI)*.
5. **Evaluasi Tren & Klasifikasi ABC**: Tinjau grafik distribusi produk kelas A, B, C dan grafik tren penjualan bulanan (*Peak Months*).
6. **Pemantauan Riwayat Transaksi Terakhir**: Lihat 5 transaksi barang masuk dan 5 transaksi barang keluar terbaru di bagian bawah halaman.

---

### 5. Penjelasan Field / Kolom / Indikator

| Nama Indikator / Widget | Tipe / Format | Keterangan & Rumus Kalkulasi | Sumber Data (Model / Tabel) |
|---|---|---|---|
| **Total Produk** | Angka Bulat | Jumlah total varian produk/SKU aktif di sistem | `mst_produk.id` (Count) |
| **Total Unit Stok** | Angka Bulat | Akumulasi kuantitas fisik stok seluruh produk | `mst_produk.total_stok` (Sum) |
| **Nilai Inventori** | Currency (Rp) | Total nilai aset persediaan = $\sum (\text{total\_stok} \times \text{hpp})$ | `mst_produk` (Sum HPP * Stok) |
| **Low Stock Count** | Angka Bulat | Jumlah SKU dengan `total_stok < minimal_stock` | `mst_produk` join `analytics_produk` |
| **Dead Stock Count & Value** | Angka & Currency (Rp) | SKU dengan `total_stok > 0` dan penjualan harian $= 0$. Nilai dead stock = $\sum (\text{total\_stok} \times \text{hpp})$ | `mst_produk` join `analytics_produk` |
| **Days of Inventory (DOI)** | Angka (Hari) | Ketahanan stok fisik dalam hari = $\lfloor \frac{\text{total\_stok}}{\text{rata\_rata\_harian}} \rfloor$ | Kalkulasi dinamis `Dashboard.php` |
| **Reorder Point (ROP)** | Angka (Unit) | Titik pemesanan ulang = $(\text{rata\_rata\_harian} \times \text{leadtime}) + \text{safety\_stock}$ | Kalkulasi dinamis `Dashboard.php` |
| **Reorder Qty Recommendation** | Angka (Unit) | Jumlah rekomendasi beli = $\max(0, \text{maximum\_stock} - \text{total\_stok})$ | Kalkulasi dinamis `Dashboard.php` |
| **Pending PO (Sisa PO)** | Angka (Unit) | Sisa unit barang yang belum diterima dari supplier | `po_detail.sisa` (Sum) |

---

### 6. Tombol & Aksi

| Nama Tombol / Link | Lokasi / Widget | Aksi & Efek ke Sistem |
|---|---|---|
| **Buat PO Baru** | Card Reorder Alert | Mengarahkan pengguna ke halaman pembuatan Purchase Order (`/purchase-order`) dengan menyertakan rekomendasi SKU. |
| **Lihat Semua Barang Masuk** | Panel Transaksi Terakhir | Mengarahkan pengguna ke halaman Transaksi Barang Masuk (`/barang-masuk`). |
| **Lihat Semua Barang Keluar** | Panel Transaksi Terakhir | Mengarahkan pengguna ke halaman Transaksi Barang Keluar (`/barang-keluar`). |

---

### 7. Validasi & Aturan Bisnis (dari Controller `Dashboard.php`)
- **Cache Optimization**: Data KPI berat (Total Produk, Nilai Inventori, Dead Stock, Klasifikasi ABC, Penjualan Bulanan) di-cache secara otomatis selama **5 menit (300 detik)** menggunakan `cache()->remember('dashboard_kpi_stats', 300, ...)` untuk menjamin performa dashboard tetap di bawah 100ms.
- **Dynamic Reorder Calculation**: Tabel rekomendasi reorder diproses secara real-time (tanpa cache) agar perubahan stok dari transaksi terbaru langsung terefleksi.
- **Fall Back DOI**: Jika penjualan harian produk bernilai `0`, DOI diberi nilai default `999` hari untuk mencegah pembagian dengan nol (*division by zero*).

---

### 8. Keterkaitan dengan Fitur Lain
- **Modul Master Data**: Mengambil data HPP, total stok, dan info produk dari `mst_produk`.
- **Modul Analytics**: Mengambil batas `minimal_stock`, `safety_stock`, `maximum_stock`, `leadtime_produksi`, dan `rata_rata_harian` dari `analytics_produk`.
- **Modul Transaksi Gudang**: Mengambil data riwayat barang masuk/keluar real-time dan sisa pesanan supplier (`po_detail`).
- **Modul Operasi Gudang**: Membaca antrean *Pending Transfer* dan rekomendasi *Replenishment Toko*.

---

### 9. Studi Kasus / Contoh Skenario
**Skenario**: Manajer Gudang ingin memeriksa barang sepatu yang kehabisan stok di tengah bulan.
1. Manajer membuka Dashboard WMS.
2. Pada tabel *Rekomendasi Pembelian Ulang*, terlihat produk `LAF-SHOE-BLK-42` memiliki sisa stok `5 pasang`, sedangkan `minimal_stock` adalah `20 pasang`.
3. Sistem menghitung DOI = `2 hari` (karena rata-rata penjualan 2.5 pasang/hari).
4. Manajer mengklik tombol *Buat PO Baru* pada rekomendasi tersebut untuk memesan `50 pasang` (sesuai rekomendasi `maximum_stock`).

---

### 10. Kemungkinan Error & Solusi

| Gejala / Pesan Error | Penyebab | Solusi Pengguna / Admin |
|---|---|---|
| Angka KPI Stok pada Dashboard tidak berubah setelah transaksi barang masuk baru. | Data KPI KPI di-cache selama 5 menit oleh sistem untuk efisiensi server. | Tunggu hingga 5 menit atau lakukan reload halaman setelah cache terkelola otomatis. |
| Nilai Reorder Point / DOI bernilai 0 atau 999. | Data analitik produk (`analytics_produk`) belum pernah dihitung atau di-generate. | Buka menu **Forecast & Analisis** (`/analytics`) lalu jalankan regenerasi data analitik. |


<div style="page-break-after: always;"></div>

# BAB 2: MANAJEMEN MASTER DATA

![Halaman Master Stok tempat mengelola katalog produk, pencarian variasi, import excel, dan cetak barcode](images/master-data-stock.png)

## 2.1 Master Stok (Katalog Produk & Inventori)

### 1. Deskripsi Singkat
Modul Master Stok adalah pusat pengelolaan katalog produk, SKU, variasi barang (warna/ukuran), lokasi penyimpanan awal, HPP (Harga Pokok Penjualan), harga jual, serta pelacakan total stok fisik per SKU di seluruh gudang.

### 2. Role / Hak Akses
- **Melihat Data (`view master`)**: Super Admin, Admin Manajer, Operator Gudang, Tim Sales, Tim Finance.
- **Kelola Data (Tambah / Edit / Hapus / Import Excel) (`manage master`)**: Super Admin, Admin Manajer.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Master Data -> Master Stok
- **URL**: `/stock-master`

### 4. Alur Penggunaan (Step by Step)
1. **Melihat & Memfilter Stok**:
   - Manfaatkan bilah pencarian `search` untuk mencari berdasarkan nama barang, kode SKU, kategori, warna, atau ukuran.
   - Filter stok berdasarkan Kategori, Lokasi, Filter Ketersediaan Stok (*Low Stock*, *Out of Stock*, *In Stock*), serta Status Aktif.
2. **Menambah Produk Baru secara Manual**:
   - Klik tombol **+ Tambah Barang Baru**.
   - Isi form data produk (Nama Produk, Kategori, Variasi, Warna, Ukuran, Satuan, Lokasi, HPP, Harga Jual).
   - Centang *Generate SKU Otomatis* jika ingin sistem membuat Kode SKU berdasarkan Aturan Pengkodean, atau uncheck untuk menginput SKU kustom.
   - Klik **Simpan Data**.
3. **Mengedit / Menghapus Produk**:
   - Klik ikon **Edit (Pensil)** pada baris tabel untuk mengubah data produk.
   - Klik ikon **Hapus (Tong Sampah)** untuk menghapus SKU (hanya jika produk belum memiliki histori transaksi fisik).
4. **Export & Import Excel**:
   - Klik **Export Excel** untuk mengunduh seluruh data katalog stok dalam format XLSX.
   - Klik **Import Excel** -> Upload file template Excel -> Sistem akan memvalidasi data dan mengimpor SKU secara masal.
5. **Cetak Barcode / QR Code**:
   - Klik tombol **Barcode (Ikon Kode Bar)** pada baris produk untuk membuka modal pratinjau barcode SKU dan mengunduh / mencetaknya.

### 5. Penjelasan Field / Kolom

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan & Batasan Validasi | Sumber Data / Tabel |
|---|---|---|---|---|
| `nama_produk` | Text | Wajib | Nama induk kelompok produk (maks 200 karakter) | `mst_produk.nama_produk` |
| `kategori` | Select / Text | Wajib | Kategori utama produk (misal: *Sepatu*, *Sandal*) | `mst_produk.kategori` |
| `kategori_2` | Text | Opsional | Sub-kategori atau tag sekunder | `mst_produk.kategori_2` |
| `kode_barang` | Text | Wajib (Auto) | Kode SKU unik (maks 100 karakter, Unique) | `mst_produk.kode_barang` |
| `nama_barang` | Text | Wajib | Nama spesifik barang lengkap dengan variasi | `mst_produk.nama_barang` |
| `variasi_barang`| Text | Wajib | Keterangan variasi (misal: *Hitam Size 42*) | `mst_produk.variasi_barang` |
| `warna` | Text | Opsional | Warna spesifik barang | `mst_produk.warna` |
| `size` | Text | Opsional | Ukuran/size barang (misal: *42*, *XL*) | `mst_produk.size` |
| `satuan` | Select | Wajib | Satuan kuantitas (Default: *pasang*, *pcs*, *box*) | `mst_produk.satuan` |
| `lokasi` | Select | Wajib | Kode lokasi gudang default (FK to `mst_lokasi`) | `mst_lokasi.kode_lokasi` |
| `hpp` | Number | Wajib | Harga Pokok Pembelian per unit (min: 0) | `mst_produk.hpp` |
| `harga_jual` | Number | Wajib | Harga Jual standar ke pelanggan (min: 0) | `mst_produk.harga_jual` |
| `total_stok` | System Auto | Auto | Akumulasi kuantitas stok aktual di gudang | `mst_produk.total_stok` |
| `is_active` | Toggle / Checkbox| Wajib | Status keaktifan produk dalam transaksi | `mst_produk.is_active` |

### 6. Tombol & Aksi

| Nama Tombol | Aksi & Efek ke Sistem |
|---|---|
| **+ Tambah Barang Baru** | Membuka modal form pembuatan produk baru. |
| **Import Excel** | Membuka modal upload file spreadsheet `.xlsx` / `.xls` untuk membuat SKU masal. |
| **Export Excel** | Mengunduh file Excel `products_export.xlsx` berisikan seluruh daftar stok. |
| **Download Template Import** | Mengunduh format file Excel standar untuk pengisian data import. |
| **Barcode Icon** | Membuka modal pratinjau Barcode SVG/PNG yang siap dicetak ke label printer. |
| **Simpan Data** | Memvalidasi input dan menyimpan/memperbarui data di database `mst_produk`. |

### 7. Validasi & Aturan Bisnis (Controller `StockMaster\Index.php`)
- **Auto-Generate SKU**: Jika `autoGenerateSku = true`, sistem akan merangkai Kode Barang secara otomatis berdasarkan formula di `AturanKode` (contoh: `SEPATU-LAF-BLK-42-001`).
- **Keunikan SKU**: Field `kode_barang` harus unik di dalam tabel `mst_produk`.
- **Integritas Stok Transaksi**: Total stok produk disinkronkan secara otomatis saat terjadi transaksi Barang Masuk, Barang Keluar, Transfer, atau Opname.

### 8. Keterkaitan dengan Fitur Lain
- Terhubung dengan **Manajemen Lokasi** (`mst_lokasi`) untuk lokasi penyimpanan default.
- Terhubung dengan **Aturan Pengkodean** (`mst_aturan_kode`) untuk format otomatis SKU.
- Menjadi referensi utama dalam seluruh transaksi gudang (Pembelian, Penjualan, Transfer, Opname, Picking, Packing).

### 9. Studi Kasus / Contoh Skenario
**Skenario**: Menambahkan produk baru "Sepatu Sneakers Running LAF Hitam Size 41" sejumlah 0 stok awal.
1. Buka Master Stok -> Klik **+ Tambah Barang Baru**.
2. Isi Nama Produk: "Sneakers Running LAF", Kategori: "Sepatu", Variasi: "Hitam 41", Warna: "Hitam", Size: "41", Satuan: "pasang", HPP: "150000", Harga Jual: "250000".
3. Biarkan centang *Generate SKU Otomatis* aktif -> Sistem merilis SKU `SPT-SNK-HTM-41-001`.
4. Klik **Simpan Data**. Produk kini terdaftar di sistem.

### 10. Kemungkinan Error & Solusi

| Pesan Error / Masalah | Penyebab | Solusi |
|---|---|---|
| `The kode barang has already been taken.` | Kode SKU yang dimasukkan manual sudah terdaftar di sistem. | Gunakan fitur Auto-Generate SKU atau masukkan Kode SKU lain yang unik. |
| Data pada File Import Excel gagal diproses. | Format kolom tidak sesuai dengan template atau kode lokasi tidak terdaftar di `mst_lokasi`. | Unduh ulang *Template Import*, pastikan Kode Lokasi persis sama dengan yang ada di menu Manajemen Lokasi. |

---

![Halaman Manajemen Lokasi Gudang dan Toko](images/master-data-lokasi.png)

## 2.2 Manajemen Lokasi (Gudang & Toko)

### 1. Deskripsi Singkat
Modul Manajemen Lokasi digunakan untuk memetakan rak, bin, zona gudang (*Warehouse Sub-locations*), serta lokasi fisik toko (*Retail Outlets*). Setiap posisi penyimpanan barang teridentifikasi secara unik oleh Kode Lokasi.

### 2. Role / Hak Akses
- **Melihat Data (`view master`)**: Semua User terautentikasi.
- **Kelola Data (`manage master`)**: Super Admin, Admin Manajer.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Master Data -> Manajemen Lokasi
- **URL**: `/lokasi` (Detail: `/lokasi/{lokasi}/detail`)

### 4. Alur Penggunaan (Step by Step)
1. Buka halaman **Manajemen Lokasi**.
2. Klik **+ Tambah Lokasi**.
3. Isi `kode_lokasi` (misal: `GDG-A02`), `nama_lokasi` (misal: *Gudang Utama Rak A02*), dan pilih `tipe_lokasi` (*GUDANG* atau *TOKO*).
4. Klik **Simpan**.
5. Untuk melihat daftar stok spesifik pada lokasi tersebut, klik tombol **Detail Lokasi**.

### 5. Penjelasan Field / Kolom

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan & Validasi |
|---|---|---|---|
| `kode_lokasi` | Text | Wajib | Kode unik lokasi (Upper Case, Unique, misal: `GDG-A01`, `TKO-R01`) |
| `nama_lokasi` | Text | Wajib | Nama deskriptif area penyimpanan (maks 200 karakter) |
| `tipe_lokasi` | Select | Wajib | Kategori tipe lokasi: `GUDANG` (Internal Warehouse) atau `TOKO` (Retail Shop) |
| `is_active` | Toggle | Wajib | Status aktif lokasi |

### 6. Validasi & Aturan Bisnis (Controller `Lokasi\Index.php`)
- **Proteksi Default System**: Kode lokasi default `GDG-A01` dan `TKO-R01` **TIDAK BOLEH DIDELETE atau DIUBAH KODENYA** karena digunakan sebagai fallback sistem untuk transaksi dasar dan pengisian stok otomatis.

---

![Halaman Aturan Pengkodean SKU Otomatis](images/master-data-pengkodean.png)

## 2.3 Aturan Pengkodean (Auto SKU Generator)

### 1. Deskripsi Singkat
Fitur Aturan Pengkodean memungkinkan Admin mengonfigurasi pola pembentukan kode SKU otomatis berdasarkan kombinasi Kategori, Inisial Nama Barang, Kode Variasi/Warna/Size, serta Angka Urut (*Auto-Increment Digits*).

### 2. Role / Hak Akses
- **Kelola Data (`manage master`)**: Super Admin, Admin Manajer.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Master Data -> Aturan Pengkodean
- **URL**: `/pengkodean`

### 4. Penjelasan Field & Format Pengkodean

| Nama Field | Tipe Input | Keterangan & Contoh Efek |
|---|---|---|
| `kategori` | Select / Text | Kategori spesifik aturan (kosongkan untuk aturan *Default Global*) |
| `prefix` | Text | Prefix kustom depan SKU (misal: `LAF`, `PRD`) |
| `include_category_prefix` | Toggle | Memasukkan 3 huruf depan kategori (contoh: `SEPATU` -> `SPT`) |
| `include_product_initials`| Toggle | Memasukkan inisial dari nama produk (contoh: `Running Shoes` -> `RS`) |
| `include_variant_code` | Toggle | Memasukkan kode warna/size di SKU |
| `increment_digits` | Number | Jumlah digit nomor urut belakang (contoh: `3` -> `001`, `4` -> `0001`) |
| `format_tipe` | Select | Pola penomoran: `INCREMENT` (Numerik Urut) atau `PRD_STYLE` (Format Gaya Produk LAF) |

---

## 2.4 Master Data Supplier & Pelanggan

![Halaman Pengelolaan Supplier dan Pelanggan](images/master-data-supplier.png)

### 1. Deskripsi Singkat
Mengelola database entitas eksternal perusahaan: **Supplier** (Pemasok barang/vendor untuk transaksi Purchase Order & Barang Masuk) dan **Pelanggan** (Customer/Mitra pembeli untuk transaksi Barang Keluar).

### 2. Role / Hak Akses
- **Melihat & Riwayat (`view master`)**: Semua User terautentikasi.
- **Kelola Data (`manage master`)**: Super Admin, Admin Manajer.

### 3. Lokasi Menu (Path / URL)
- **Supplier**: `/supplier` (Riwayat Transaksi: `/supplier/riwayat`)
- **Pelanggan**: `/pelanggan` (Riwayat Transaksi: `/pelanggan/riwayat`)

### 4. Penjelasan Field Data (Supplier & Pelanggan)

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan |
|---|---|---|---|
| `kode_supplier` / `kode_pelanggan` | Text | Wajib | Kode Unik identitas mitra (misal: `SUP-001`, `CUST-001`) |
| `nama_supplier` / `nama_pelanggan` | Text | Wajib | Nama resmi perusahaan atau perorangan |
| `kontak` / `telepon` | Text | Opsional | Nomor HP / WhatsApp aktif |
| `email` | Email | Opsional | Alamat email korespondensi |
| `alamat` | Textarea | Opsional | Alamat lengkap domisili / pengiriman |


<div style="page-break-after: always;"></div>

# BAB 3: AUDIT STOK & OPNAME

![Halaman Lembar Kerja Audit Stok Opname dan Input Hasil Fisik](images/audit-stok-session.png)

## Audit Stok (Opname Inventori & Stock Adjustment)

### 1. Deskripsi Singkat
Modul Audit Stok (Stock Opname) adalah fitur verifikasi fisik persediaan barang di gudang/toko untuk mencocokkan jumlah stok fisik nyata (*Physical Count*) dengan catatan stok sistem (*System Count*). Fitur ini secara otomatis menghitung selisih (varian) dan memperbarui stok lokasi secara akurat saat sesi audit disahkan (*POSTED*).

---

### 2. Role / Hak Akses
- **Membuat & Mengisi Audit (`input masuk`)**: Super Admin, Admin Manajer, Operator Gudang.
- **Mengesahkan Penyesuaian Stok (Approve / Post Opname)**: Super Admin, Admin Manajer, Operator Gudang.
- **Membatalkan Sesi Audit**: Super Admin, Admin Manajer.

---

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Audit Stok (Opname)
- **URL**: `/audit`

---

### 4. Alur Penggunaan (Step by Step)

#### **A. Membuka Sesi Audit Baru (DRAFT)**
1. Masuk ke menu **Audit Stok (Opname)**.
2. Klik tombol **+ Sesi Audit Baru** (atau gunakan Barcode Scanner untuk memindai Barcode Lokasi gudang seperti `GDG-A01`).
3. Pilih **Tanggal Audit**, pilih **Lokasi Gudang / Toko** yang akan diaudit, dan beri catatan pada kolom **Keterangan**.
4. Klik **Mulai Audit**. Sistem secara otomatis merilis Nomor Opname unik (misal: `OPN/20260818/A1F9`) dan memuat seluruh produk yang terdaftar di lokasi tersebut dengan `qty_sistem` awal.

#### **B. Penginputan Hasil Hitung Fisik (Physical Count)**
Ada 2 cara penginputan fisik:
- **Cara 1: Pemindaian Barcode (Metode Cepat Scanner Handheld)**
  1. Arahkan kursor ke input **Scan QR / Barcode Produk**.
  2. Pindai label SKU produk dengan scanner. Setiap kali dipindai, jumlah `qty_fisik` produk tersebut bertambah `+1` secara otomatis dan suara *beep* berbunyi.
- **Cara 2: Pengisian Manual pada Lembar Kerja**
  1. Pada tabel lembar kerja audit, cari nama barang/SKU yang dimaksud.
  2. Ketik jumlah stok fisik yang dihitung di kolom **Stok Fisik**.
  3. Sistem secara otomatis menghitung `Selisih = Stok Fisik - Stok Sistem`.

#### **C. Pengesahan Audit & Penyesuaian Stok (POSTED)**
1. Setelah seluruh barang dihitung, periksa ringkasan varian (Selisih Positif / Selisih Negatif).
2. Klik tombol **Sahkan & Post Opname (Sesuaikan Stok)**.
3. Konfirmasikan dialog persetujuan.
4. **Efek Sistem**:
   - Status sesi audit berubah menjadi `POSTED`.
   - Stok fisik pada tabel `trx_stok_lokasi` dan `mst_produk.total_stok` langsung disesuaikan secara otomatis.
   - Sistem mencatat jurnal mutasi lokasi (`trx_mutasi_lokasi`) dengan tipe `ADJUSTMENT` untuk kebutuhan audit trail.

---

### 5. Penjelasan Field / Kolom

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan & Batasan | Tabel Database |
|---|---|---|---|---|
| `nomor_opname` | System Auto | Auto | Nomor referensi unik sesi audit (contoh: `OPN/20260818/B3C2`) | `trx_opname_header.nomor_opname` |
| `tanggal` | Date | Wajib | Tanggal pelaksanaan opname fisik | `trx_opname_header.tanggal` |
| `lokasi_id` | Select | Wajib | Lokasi spesifik yang diaudit (FK to `mst_lokasi`) | `trx_opname_header.lokasi_id` |
| `status` | System | Auto | Status Sesi: `DRAFT` (Sedang berjalan) atau `POSTED` (Disahkan) | `trx_opname_header.status` |
| `barcodeScan` | Text / Scan | Opsional | Input cepat pemindaian barcode lokasi atau barcode SKU | Temporary State |
| `qty_sistem` | Read-only | Auto | Kuantitas stok di sistem saat sesi audit dibuka | `trx_opname_detail.qty_sistem` |
| `qty_fisik` | Number | Wajib | Kuantitas aktual hasil perhitungan fisik petugas | `trx_opname_detail.qty_fisik` |
| `selisih` | Read-only | Auto | Selisih varian = `qty_fisik` - `qty_sistem` | `trx_opname_detail.selisih` |

---

### 6. Tombol & Aksi

| Nama Tombol | Aksi & Efek ke Sistem |
|---|---|
| **+ Sesi Audit Baru** | Membuka form modal inisiasi sesi opname lokasi baru. |
| **Sahkan & Post Opname** | Mengunci sesi opname (status `POSTED`) dan memperbarui stok aktual di database. |
| **Batal / Hapus Sesi DRAFT** | Membatalkan sesi DRAFT tanpa mengubah stok di database. |
| **Reset ke Stok Sistem** | Mengembalikan seluruh `qty_fisik` sama dengan `qty_sistem` pada lembar kerja. |

---

### 7. Validasi & Aturan Bisnis (Controller `Audit\Index.php`)
- **Pencegahan Sesi Ganda**: Tidak diperbolehkan membuat sesi audit `DRAFT` baru pada lokasi yang sama jika lokasi tersebut masih memiliki sesi audit `DRAFT` yang belum disahkan. Sistem akan secara otomatis mengarahkan ke sesi `DRAFT` yang ada.
- **Proteksi Pengesahan**: Sesi audit yang sudah berstatus `POSTED` terkunci secara permanen dan tidak dapat diubah atau dihapus.
- **Pencatatan Mutasi**: Setiap penyesuaian stok dari opname menghasilkan record `MutasiLokasi` dengan reference ID opname untuk transparansi pemeriksaan akuntan/auditor.

---

### 8. Keterkaitan dengan Fitur Lain
- **Manajemen Lokasi**: Menentukan area fisik rak/gudang yang diaudit.
- **Master Stok**: Memperbarui kolom `total_stok` pada `mst_produk`.
- **Log Perubahan (Audit Log)**: Mencatat identitas operator dan waktu pengesahan penyesuaian stok opname.

---

### 9. Studi Kasus / Contoh Skenario
**Skenario**: Opname bulanan Rak Gudang `GDG-A01`.
1. Petugas memilih lokasi `GDG-A01` -> Klik **Mulai Audit**.
2. Pada barang `LAF-SHOE-BLK-40`, stok sistem mencatat `10 pasang`.
3. Setelah dihitung fisik, ternyata hanya ada `8 pasang` (hilang 2 pasang). Petugas mengisi `Stok Fisik = 8`.
4. Kolom selisih menampilkan `-2` (berwarna merah).
5. Petugas mengklik **Sahkan & Post Opname**. Stok di gudang `GDG-A01` langsung berkurang dari `10` menjadi `8 pasang`, dan jurnal penyesuaian `-2 pasang` tercatat di log audit.

---

### 10. Kemungkinan Error & Solusi

| Pesan Error / Masalah | Penyebab | Solusi |
|---|---|---|
| `Barcode lokasi tidak cocok dengan lokasi sesi audit saat ini.` | Petugas memindai kode lokasi lain di tengah-tengah sesi audit aktif. | Pastikan memindai produk yang ada pada lokasi yang sedang dibuka, atau kembali ke menu utama untuk membuka sesi lokasi lain. |
| Tombol *Sahkan & Post Opname* tidak dapat diklik / diabaikan. | Terdapat nilai stok fisik negatif atau format input bukan angka. | Periksa lembar kerja, pastikan semua kolom stok fisik terisi dengan angka desimal/bulat non-negatif ($\ge 0$). |


<div style="page-break-after: always;"></div>

# BAB 4: TRANSAKSI GUDANG

![Form Input Penerimaan Barang Masuk (Inbound Transaction)](images/barang-masuk-create.png)

## 4.1 Barang Masuk (Inbound / Receiving)

### 1. Deskripsi Singkat
Modul Barang Masuk digunakan untuk mencatat penerimaan persediaan barang baru ke gudang, baik yang berasal dari transaksi **Pembelian Supplier**, penerimaan dari **Purchase Order (PO)**, **Stock Awal**, **Retur Masuk**, maupun **Penyesuaian Transaksi**.

### 2. Role / Hak Akses
- **Akses Transaksi Masuk (`input masuk`)**: Super Admin, Admin Manajer, Operator Gudang.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Transaksi Gudang -> Barang Masuk
- **URL**: `/barang-masuk` (Buat Baru: `/barang-masuk/create`, Edit: `/barang-masuk/{id}/edit`, Riwayat: `/barang-masuk/riwayat`)

### 4. Alur Penggunaan (Step by Step)
1. Buka menu **Barang Masuk** -> Klik **+ Tambah Barang Masuk**.
2. Sistem membuat **Nomor Nota** otomatis (contoh: `BM/20260818/001`).
3. Pilih **Tipe Nota**: `Pembelian`, `Stock Awal`, `Retur Masuk`, atau `Penyesuaian`.
4. Pilih **Supplier** pengirim barang (Wajib untuk Pembelian/Retur).
5. **Tambahkan Item Barang**:
   - *Pilihan A (Manual)*: Ketik nama barang/SKU -> Pilih Produk -> Tentukan `Kuantitas Masuk`, `Lokasi Simpan`, dan `HPP / Harga Beli`.
   - *Pilihan B (Scanner Barcode)*: Pindai barcode produk pada input scan -> Item langsung ditambahkan ke tabel dengan kuantitas +1.
   - *Pilihan C (Dari Purchase Order)*: Jika transaksi berasal dari PO, sistem akan memuat otomatis item yang dipesan beserta sisa kuantitas PO-nya (`po_detail_id`).
6. Klik **Simpan Transaksi**.
7. **Efek Sistem**:
   - Kuantitas stok produk di `mst_produk.total_stok` bertambah.
   - Kuantitas stok spesifik lokasi di `trx_stok_lokasi` bertambah.
   - Jika terhubung PO, `po_detail.sisa` dan `po_detail.diterima` diperbarui (sisa PO berkurang).
   - Jurnal mutasi lokasi terbit secara otomatis.

### 5. Penjelasan Field Header & Detail (Tabel Data)

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan & Validasi |
|---|---|---|---|
| `tanggal` | Date | Wajib | Tanggal penerimaan barang |
| `tipe_nota` | Select | Wajib | Pilihan: `Stock Awal`, `Pembelian`, `Retur Masuk`, `Penyesuaian` |
| `supplier_id` | Select | Wajib* | Supplier pengirim (*Opsional hanya jika Tipe Nota = Stock Awal) |
| `nomor_nota` | Text | Wajib | Kode Nota unik (Auto-generated: `BM/YYYYMMDD/XXX`) |
| `keterangan_tambahan` | Textarea | Opsional | Catatan tambahan seperti nomor surat jalan supplier |
| `items.*.produk_id` | Select / Scan | Wajib | SKU/Produk yang diterima |
| `items.*.qty_masuk` | Number | Wajib | Jumlah unit masuk (Integer $\ge 1$) |
| `items.*.lokasi_simpan`| Select | Wajib | Kode lokasi rak tujuan barang disimpan (misal: `GDG-A01`) |
| `items.*.hpp` | Number | Wajib | Harga beli / modal per unit ($\ge 0$) |

---

![Form Input Pengeluaran Barang Keluar (Outbound Transaction)](images/barang-keluar-create.png)

## 4.2 Barang Keluar (Outbound / Dispatch)

### 1. Deskripsi Singkat
Modul Barang Keluar digunakan untuk mengoperasikan pengeluaran stok fisik dari gudang, meliputi pengiriman penjualan ke **Pelanggan**, **Penjualan Langsung**, **Retur Keluar**, maupun **Penyesuaian Outbound**.

### 2. Role / Hak Akses
- **Akses Transaksi Keluar (`input keluar`)**: Super Admin, Admin Manajer, Operator Gudang, Tim Sales.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Transaksi Gudang -> Barang Keluar
- **URL**: `/barang-keluar` (Buat Baru: `/barang-keluar/create`, Riwayat: `/barang-keluar/riwayat`)

### 4. Alur Penggunaan (Step by Step)
1. Buka menu **Barang Keluar** -> Klik **+ Tambah Barang Keluar**.
2. Sistem menerbitkan **Nomor Nota Outbound** otomatis (contoh: `BK/20260818/001`).
3. Pilih **Pelanggan** (Customer/Mitra).
4. Tambahkan item barang yang dikeluarkan, tentukan **Lokasi Asal Ambil** dan **Kuantitas Keluar**.
5. **Validasi Stok**: Sistem secara otomatis mengecek ketersediaan stok aktual di lokasi tersebut. Jika `qty_keluar > stok_tersedia`, transaksi ditolak dengan pesan peringatan.
6. Klik **Simpan Transaksi**.
7. **Efek Sistem**:
   - Total stok produk di `mst_produk.total_stok` dan `trx_stok_lokasi` berkurang.
   - Terbit dokumen transaksi pengeluaran dan struk/faktur pengiriman yang siap dicetak.

---

![Halaman Pengelolaan Purchase Order ke Supplier](images/purchase-order-manager.png)

## 4.3 Purchase Order (PO Supplier)

### 1. Deskripsi Singkat
Modul Purchase Order (PO) adalah sistem manajemen pemesanan pembelian barang resmi kepada supplier sebelum barang dikirim/diterima di gudang. Modul ini melacak status penerimaan barang parsial hingga PO dinyatakan selesai (*CLOSED*).

### 2. Role / Hak Akses
- **Melihat PO (`view po`)**: Semua User terautentikasi.
- **Membuat & Mengelola PO (`manage po`)**: Super Admin, Admin Manajer, Tim Sales.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Transaksi Gudang -> Purchase Order
- **URL**: `/purchase-order`

### 4. Penjelasan Status & Workflow PO

```
[ DRAFT ] ➔ [ APPROVED / ORDERED ] ➔ [ PARTIAL ] ➔ [ RECEIVED / CLOSED ]
```

1. **DRAFT / ORDERED**: PO baru dibuat dan dikirim ke Supplier.
2. **PARTIAL**: Barang dari supplier diterima sebagian di gudang melalui menu *Barang Masuk*.
3. **RECEIVED / CLOSED**: Seluruh kuantitas barang dalam PO telah diterima 100% di gudang (`sisa = 0`).

### 5. Penjelasan Field Utama PO

| Nama Field | Tipe Input | Keterangan |
|---|---|---|
| `nomor_po` | System Auto | Nomor PO unik (contoh: `PO/20260818/001`) |
| `supplier_id` | Select | Supplier tujuan pemesanan |
| `tanggal_po` | Date | Tanggal penerbitan pesanan |
| `target_tanggal` | Date | Target estimasi barang tiba di gudang |
| `qty_pesan` | Number | Kuantitas total dipesan ke supplier |
| `qty_diterima` | Auto | Kuantitas yang sudah sampai di gudang |
| `sisa` | Auto | Sisa kuantitas PO yang belum dikirim (`qty_pesan - qty_diterima`) |


<div style="page-break-after: always;"></div>

# BAB 5: FORECAST & ANALISIS INVENTORI

![Halaman Dashboard Forecast, Analisis ABC, ROP, dan Perhitungan Safety Stock](images/forecast-analytics-dashboard.png)

## Forecast & Analisis Inventori (ABC Analysis, ROP, & Demand Forecasting)

### 1. Deskripsi Singkat
Modul Forecast & Analisis adalah kecerdasan buatan dan engine analitik internal WMS LAF Project yang menghitung Klasifikasi ABC (Prinsip Pareto 80/20), Peramalan Permintaan (*Demand Forecasting*), *Safety Stock*, *Minimal Stock*, *Maximum Stock*, serta *Reorder Point (ROP)* secara otomatis berdasarkan data historis penjualan atau input manual pengguna.

---

### 2. Role / Hak Akses
- **Melihat & Edit Analisis (`view analytics`)**: Super Admin, Admin Manajer, Tim Finance.

---

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Forecast & Analisis
- **URL**: `/analytics`

---

### 4. Alur Penggunaan (Step by Step)
1. Buka menu **Forecast & Analisis**.
2. **Lihat Hasil Klasifikasi ABC & Matriks Stok**:
   - **Kelas A (High-Value / Fast-Moving)**: 20% SKU teratas yang menyumbang ~80% dari total nilai penjualan.
   - **Kelas B (Moderate-Value)**: 30% SKU berikutnya yang menyumbang ~15% penjualan.
   - **Kelas C (Low-Value / Slow-Moving)**: 50% SKU terbawah yang hanya menyumbang ~5% penjualan.
3. **Regenerasi Perhitungan Otomatis**:
   - Klik tombol **Hitung Ulang Analisis (Recalculate All)** untuk menjalankan kalkulasi statistik ulang terhadap seluruh produk berdasarkan transaksi penjualan terbaru dari database.
4. **Mengubah Parameter per Produk**:
   - Klik ikon **Edit (Pensil)** pada baris SKU yang diinginkan.
   - Sesuaikan **Leadtime Produksi / Pengiriman (hari)** (default: 14 hari).
   - Tentukan apakah akan menggunakan *Hitung Otomatis* dari tren bulanan atau mengaktifkan *Override Stok Manual* (`is_manual_stock`).
   - Klik **Tarik Data Aktual** jika ingin mengambil angka riwayat transaksi dari database secara instan.
   - Klik **Simpan Analisis**.

---

### 5. Formulasi Matematika & Aturan Kalkulasi

| Variabel Analisis | Formulasi Matematika | Keterangan Bisnis |
|---|---|---|
| **Rata-Rata Penjualan Harian** | $D_{daily} = \frac{\sum_{m=1}^{12} \text{penjualan}_m}{365}$ | Rerata unit terjual per hari dalam 1 tahun |
| **Standar Deviasi Harian** | $\sigma_{daily} = \sqrt{\frac{\sum (\text{penjualan}_m - \text{mean})^2}{12}} \div \sqrt{30}$ | Variabilitas / fluktuasi permintaan |
| **Safety Stock (Stok Pengaman)** | $SS = Z \times \sigma_{daily} \times \sqrt{L}$ | Stok cadangan mengantisipasi lonjakan ($Z=1.65$ untuk Service Level 95%, $L = \text{Leadtime}$) |
| **Reorder Point (ROP)** | $ROP = (D_{daily} \times L) + SS$ | Batas kuantitas saat PO harus diterbitkan |
| **Minimal Stock** | $Min = ROP$ | Batas ambang bawah persediaan aman |
| **Maximum Stock** | $Max = ROP + (D_{daily} \times 30)$ | Batas ambang atas pencegahan *overstock* |

---

### 6. Penjelasan Field Modal Edit Analisis

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan & Validasi |
|---|---|---|---|
| `leadtime_produksi` | Number | Wajib | Waktu tunggu dari PO diterbitkan hingga barang masuk gudang (1 - 365 hari) |
| `is_manual_stock` | Toggle | Wajib | Aktifkan jika ingin menentukan batas Min/Safety/Max stock secara manual |
| `minimal_stock` | Number | Wajib (jika manual) | Batas stok minimum manual ($\ge 0$) |
| `safety_stock` | Number | Wajib (jika manual) | Stok pengaman manual ($\ge 0$) |
| `maximum_stock` | Number | Wajib (jika manual) | Batas stok maksimal manual ($\ge 0$) |
| `sales.jan` s/d `des` | Number | Wajib | Histori volume penjualan dari Januari hingga Desember ($\ge 0$) |

---

### 7. Tombol & Aksi

| Nama Tombol | Aksi & Efek ke Sistem |
|---|---|
| **Hitung Ulang Analisis (Recalculate All)** | Menjalankan `AnalyticsService::recalculateAllProductAnalytics()` untuk memperbarui seluruh parameter ABC, ROP, Min, Max, dan Safety Stock seluruh SKU. |
| **Tarik Data Aktual (Sync from Database)** | Menghitung dan mengisi angka penjualan bulanan dari tabel `trx_barang_keluar_detail` aktual. |
| **Simpan Analisis** | Memperbarui parameter analitik SKU di tabel `analytics_produk`. |

---

### 8. Keterkaitan dengan Fitur Lain
- **Dashboard Utama**: Menyuplai data indikator *Low Stock Alert*, *Dead Stock*, *Peak Months*, dan rekomendasi kuantitas *Reorder*.
- **Purchase Order (PO)**: Rekomendasi kuantitas pesan otomatis merujuk pada `maximum_stock - total_stok`.
- **Replenishment Toko**: Menggunakan `minimal_stock` sebagai dasar pemicu pengisian kembali barang dari Gudang ke Toko.


<div style="page-break-after: always;"></div>

# BAB 6: OPERASI GUDANG

![Halaman Transfer Gudang Internal dan Mutasi antar Rak/Lokasi](images/transfer-gudang-index.png)

## 6.1 Transfer Gudang (Internal Warehouse Transfer)

### 1. Deskripsi Singkat
Modul Transfer Gudang mengelola perpindahan fisik stok barang antar-lokasi internal (misalnya dari *Gudang Utama GDG-A01* ke *Gudang Transit GDG-B01* atau ke *Toko TKO-R01*). Modul ini menjamin transparansi posisi stok selama proses perpindahan barang berlangsung (*In-Transit Tracking*).

### 2. Role / Hak Akses
- **Akses Transfer Gudang (`input masuk`)**: Super Admin, Admin Manajer, Operator Gudang.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Operasi Gudang -> Transfer Gudang
- **URL**: `/transfer` (Buat Baru: `/transfer/create`, Detail: `/transfer/{transfer}/detail`)

### 4. Alur & Siklus Status Transfer Gudang

```
[ DRAFT ] ➔ [ PICKING ] ➔ [ IN_TRANSIT ] ➔ [ COMPLETED ]
```

1. **DRAFT**: Dokumen perintah transfer dibuat oleh Admin/Operator.
2. **PICKING**: Petugas gudang mengambil barang dari lokasi asal (terhubung ke modul Picking List).
3. **IN_TRANSIT**: Barang telah keluar dari lokasi asal dan dalam perjalanan menuju lokasi tujuan (stok dikurangi dari lokasi asal).
4. **COMPLETED**: Barang telah sampai dan diterima secara fisik di lokasi tujuan (stok ditambahkan ke lokasi tujuan).

---

![Halaman Rekomendasi Replenishment Pengisian Stok Toko Ritel](images/replenishment-toko.png)

## 6.2 Replenishment Toko (Store Stock Replenishment)

### 1. Deskripsi Singkat
Replenishment Toko adalah sistem pengisian kembali persediaan toko ritel otomatis. Engine rekomendasi mendeteksi produk di toko yang jumlah stoknya telah berada di bawah *Minimal Stock* dan memicu pengiriman stok dari gudang utama ke lokasi toko.

### 2. Role / Hak Akses
- **Akses Replenishment (`input masuk`)**: Super Admin, Admin Manajer, Operator Gudang.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Operasi Gudang -> Replenishment Toko
- **URL**: `/replenishment`

### 4. Alur Penggunaan
1. Buka menu **Replenishment Toko**.
2. Sistem secara otomatis menampilkan daftar SKU di Toko yang membutuhkan pengisian persediaan beserta **Rekomendasi Qty Kirim**.
3. Centang item produk yang ingin diisi kembali -> Klik **Buat Dokumen Transfer Replenishment**.
4. Dokumen Transfer Gudang diterbitkan secara otomatis dari `GDG-A01` menuju `TKO-R01`.

---

![Halaman Pengambilan Barang (Picking) dengan Barcode Scanner](images/picking-barang-manager.png)

## 6.3 Picking Barang (Order Picking & Barcode Verification)

### 1. Deskripsi Singkat
Modul Picking Barang memandu petugas gudang dalam proses pengambilan barang dari lokasi rak (*Bin/Shelf Location*) untuk memenuhi pesanan Barang Keluar atau Transfer Gudang. Modul ini dilengkapi verifikasi barcode scanner untuk mencegah salah ambil barang (*Zero Picking Error*).

### 2. Role / Hak Akses
- **Akses Picking (`input keluar` / `input masuk`)**: Super Admin, Admin Manajer, Operator Gudang, Tim Sales.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Operasi Gudang -> Picking Barang
- **URL**: `/picking`

### 4. Alur Penggunaan (Step by Step)
1. Buka halaman **Picking Barang**.
2. Pilih antrean pesanan pada tab **Pending Outbound** -> Klik **Mulai Picking**.
3. Petugas gudang menuju lokasi rak yang tertera pada lembar instruksi picking.
4. **Verifikasi Barcode**:
   - Pindai barcode rak lokasi (misal: `GDG-A01`).
   - Pindai barcode SKU barang (misal: `LAF-SHOE-BLK-42`).
   - Masukkan kuantitas yang diambil -> Klik **Verifikasi Item**.
5. Setelah seluruh item dalam daftar terambil 100%, klik **Selesaikan Picking**. Status pesanan berubah menjadi `PICKED` dan siap untuk diproses ke modul Packing & QC.

---

![Halaman Packing, Verifikasi Quality Control, dan Cetak Shipping Label](images/packing-qc-manager.png)

## 6.4 Packing & QC (Packaging & Quality Control)

### 1. Deskripsi Singkat
Modul Packing & QC adalah tahap verifikasi akhir sebelum barang dikemas dalam koli/kardus dan diserahkan ke jasa kurir/pengirim. Petugas melakukan inspeksi fisik barang, pengemasan, dan pencetakan Label Pengiriman / Resi (*Shipping Label*).

### 2. Role / Hak Akses
- **Akses Packing & QC (`input keluar`)**: Super Admin, Admin Manajer, Operator Gudang, Tim Sales.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Operasi Gudang -> Packing & QC
- **URL**: `/packing`

### 4. Alur Penggunaan
1. Buka menu **Packing & QC**.
2. Pilih pesanan berstatus `PICKED` -> Klik **Mulai Packing & QC**.
3. Pindai ulang barcode produk untuk memastikan barang tidak cacat dan sesuai dengan pesanan.
4. Masukkan dimensi berat/koli kardus -> Klik **Selesai Packing & Cetak Label Pengiriman**.
5. Tempelkan label pengiriman pada kardus kemasan.


<div style="page-break-after: always;"></div>

# BAB 7: LAPORAN & AUDIT LOG

![Halaman Laporan Eksekutif, Mutasi Stok, dan Cetak PDF/Excel](images/laporan-analitik-index.png)

## 7.1 Laporan & Analitik (Reports & Financial Statements)

### 1. Deskripsi Singkat
Modul Laporan & Analitik menyajikan rangkuman data eksekutif mencakup Laporan Nilai Aset Inventori, Laporan Mutasi Stok Masuk & Keluar, Analisis Profitabilitas/Margin HPP vs Harga Jual, serta Laporan Transaksi Per Periode Tanggal.

### 2. Role / Hak Akses
- **Akses Laporan (`view financial`)**: Super Admin, Admin Manajer, Tim Finance.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Laporan -> Laporan & Analitik
- **URL**: `/reports` (Format Cetak PDF: `/reports/print`)

### 4. Jenis-Jenis Laporan yang Tersedia
1. **Laporan Nilai Persediaan (Inventory Valuation Report)**: Rincian total nilai aset produk berbasis HPP per lokasi gudang.
2. **Laporan Mutasi Stok (Stock Movement Summary)**: Rekapitulasi volume stok awal, stok masuk, stok keluar, dan stok akhir.
3. **Laporan Penjualan & Margin Keuntungan**: Analisis omzet penjualan, modal HPP, dan gross profit per varian barang.
4. **Laporan Transaksi Barang Masuk & Keluar Per Periode**: Rekap harian/bulanan dengan filter rentang tanggal.

### 5. Fitur Ekspor & Cetak
- **Ekspor Excel (.xlsx)**: Mengunduh dataset laporan dalam bentuk spreadsheet untuk diolah lebih lanjut.
- **Cetak Dokumen PDF**: Membuka tampilan mode cetak ramah printer (*Print Friendly*) lengkap dengan Kop Surat Perusahaan LAF Project dan kolom tanda tangan pengesahan.

---

[SCREENSHOT: audit-log-index - Halaman Audit Log Sistem Penelusuran Jejak Aktivitas User]

## 7.2 Log Perubahan (System Audit Log)

### 1. Deskripsi Singkat
Modul Log Perubahan adalah sistem pencatat jejak audit (*Audit Trail*) independen yang merekam seluruh perubahan data sensitif, aktivitas pembuat data, penyuntingan, penghapusan, IP Address, dan perubahan nilai kolom (*Old Values* vs *New Values*) di dalam database WMS.

### 2. Role / Hak Akses
- **Akses Log Perubahan (`manage system` / `manage master`)**: Super Admin, Admin Manajer.

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Laporan -> Log Perubahan
- **URL**: `/audit-log`

### 4. Penjelasan Kolom Audit Log

| Nama Kolom | Keterangan Data |
|---|---|
| **Tanggal & Waktu** | Timestamp kejadian presisi hingga detik (`created_at`) |
| **Pengguna (Username)** | Nama user / operator yang melakukan aksi |
| **Aksi (Action)** | Jenis tindakan: `CREATE` (Tambah), `UPDATE` (Ubah), `DELETE` (Hapus), `POST` (Pengesahan) |
| **Model / Objek** | Nama entitas tabel yang dimodifikasi (misal: `App\Models\Produk`, `OpnameHeader`) |
| **ID Objek** | Primary key record yang diubah |
| **Perubahan Data** | Perbandingan JSON data sebelum diubah (*Before*) vs sesudah diubah (*After*) |
| **IP Address** | Alamat IP jaringan perangkat pengguna |

### 5. Keamanan & Imutabilitas Log
- Log Audit bersifat **Read-Only** dan **Imutabel** (tidak dapat diedit atau dihapus oleh siapapun, termasuk Super Admin) untuk menjamin standar kepatuhan audit keamanan informasi IT.


<div style="page-break-after: always;"></div>

# BAB 8: MANAJEMEN AKSES & KEAMANAN SISTEM

![Halaman Pengelolaan Pengguna, Peran, dan Hak Akses Role RBAC](images/user-management-index.png)

## Manajemen Akses & Keamanan Pengguna (User & Role Access Control)

### 1. Deskripsi Singkat
Modul Manajemen Akses mengatur otentikasi akun pengguna (*User Management*), kredensial login, status aktif/non-aktif akun, serta alokasi peran (*Roles & Permissions*) menggunakan standar Spatie Permission RBAC (Role-Based Access Control) di WMS LAF Project.

---

### 2. Role / Hak Akses
- **Akses Sistem Administrator (`manage system`)**: Super Admin saja.

---

### 3. Lokasi Menu (Path / URL)
- **Path Navigasi**: Sidebar -> Manajemen Akses
- **URL**: `/user-management`

---

### 4. Alur Penggunaan (Step by Step)

#### **A. Menambah Pengguna Baru**
1. Buka menu **Manajemen Akses** -> Klik tombol **+ Tambah Pengguna Baru**.
2. Isi **Nama Lengkap**, **Alamat Email** (Unik), dan **Password** (min 8 karakter + konfirmasi).
3. Pilih **Peran / Role**:
   - `super_admin`: Memiliki seluruh akses termasuk manajemen sistem pengguna.
   - `admin`: Memiliki seluruh akses operasional gudang, master, dan laporan keuangan (kecuali user management).
   - `operator_gudang`: Akses khusus transaksi barang masuk, barang keluar, transfer, opname, picking, dan packing.
   - `sales`: Akses khusus pencatatan barang keluar, buat PO, dan pratinjau katalog stok.
   - `finance`: Akses khusus laporan analitik, nilai inventori, dan transaksi PO/Stok.
4. Tentukan **Status Akun** (Aktif / Non-Aktif).
5. Klik **Simpan Pengguna**.

#### **B. Mengedit Data / Reset Password Pengguna**
1. Klik tombol **Edit** pada baris pengguna yang ingin diubah.
2. Ubah data nama, email, role, atau centang ubah password jika pengguna lupa kata sandi.
3. Klik **Perbarui Pengguna**.

#### **C. Menonaktifkan Akun Pengguna**
1. Klik tombol **Nonaktifkan Akun** (atau ubah status `is_active` menjadi false).
2. Pengguna yang dinonaktifkan tidak akan bisa login ke dalam sistem WMS LAF Project meskipun password benar.

---

### 5. Penjelasan Field User Management

| Nama Field | Tipe Input | Wajib / Opsional | Keterangan & Validasi |
|---|---|---|---|
| `name` | Text | Wajib | Nama pengguna lengkap (maks 255 karakter) |
| `email` | Email | Wajib | Alamat email unik login pengguna (Unique in `users`) |
| `password` | Password | Wajib* | Kata sandi minimal 8 karakter (*Wajib saat buat baru, opsional saat edit) |
| `password_confirmation` | Password | Wajib* | Konfirmasi ulang kata sandi (Harus identik dengan `password`) |
| `roleName` | Select | Wajib | Pilihan Role Spatie (`super_admin`, `admin`, `operator_gudang`, `sales`, `finance`) |
| `is_active` | Toggle / Select | Wajib | Status akun pengguna (`Aktif` / `Non-Aktif`) |

---

### 6. Tombol & Aksi

| Nama Tombol | Aksi & Efek ke Sistem |
|---|---|
| **+ Tambah Pengguna Baru** | Membuka modal form pendaftaran akun user WMS baru. |
| **Edit Pengguna** | Membuka modal penyuntingan nama, role, email, dan reset password. |
| **Nonaktifkan / Aktifkan** | Mengubah status keaktifan `is_active` pengguna secara instan. |
| **Hapus User** | Memindahkan/menghapus akun user (Super Admin terlindungi dari penghapusan mandiri). |

---

### 7. Validasi & Aturan Bisnis (Controller `UserManagement\Index.php`)
- **Proteksi Super Admin Mandiri**: Pengguna `super_admin` **TIDAK BISA MENGHAPUS ATAU MENONAKTIFKAN AKUNNYA SENDIRI** saat sedang dalam posisi login untuk mencegah terkuncinya sistem (*System Lockout Protection*).
- **Enkripsi Kredensial**: Password disimpan di database menggunakan enkripsi hashing aman `Bcrypt` (`Hash::make()`).

---

### 8. Keamanan & Log Out Session
- **Pemberhentian Sesi**: Pengguna dapat keluar dari sistem dengan mengklik tombol **Keluar (Logout)** di bagian paling bawah sidebar utama. Sesi cookie dan token CSRF otomatis dihanguskan.
