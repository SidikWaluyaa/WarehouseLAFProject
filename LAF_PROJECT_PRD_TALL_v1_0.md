# LAF PROJECT — WAREHOUSE MANAGEMENT SYSTEM
### Product Requirements Document (PRD)
**Varian Tech Stack: Laravel 12 + Livewire (TALL Stack)**

**CONFIDENTIAL — DOKUMEN INI BERSIFAT RAHASIA**

| | |
|---|---|
| **Versi** | 1.0.0 (Initial Release — TALL Stack Variant) |
| **Tanggal** | Juni 2026 |
| **Status** | **APPROVED — Ready for Development** |
| **Tech Stack** | Laravel 12 · Livewire 3 · Alpine.js · Tailwind CSS · MySQL (Laragon) |
| **Penulis** | Tim LAF PROJECT |
| **Dokumen Acuan** | Diadaptasi dari LAF_PROJECT_PRD_v1.0.0 (Next.js/Prisma variant) |

---

## Catatan Adaptasi

Dokumen ini adalah versi alternatif dari PRD LAF PROJECT yang sebelumnya disusun menggunakan stack Next.js 14 + TypeScript + Prisma. Seluruh **kebutuhan bisnis, ruang lingkup fitur, persona pengguna, dan skema database tetap identik** dengan dokumen acuan. Perubahan hanya terjadi pada layer arsitektur teknis, yaitu migrasi dari stack JavaScript/TypeScript (Next.js + Prisma + NextAuth) menuju TALL Stack (Tailwind CSS, Alpine.js, Laravel, Livewire) dengan PHP sebagai bahasa pemrograman utama.

Skema database (nama tabel, nama kolom, tipe data, dan relasi) **tidak diubah** dari dokumen asli — hanya direpresentasikan ulang dalam bentuk migration Laravel dan Eloquent model agar konsisten dengan ekosistem framework yang baru.

---

## Daftar Isi

1. Ringkasan Eksekutif
2. Tujuan Produk & Metrik Keberhasilan
3. Lingkup Produk
4. Pengguna & Stakeholder
5. Fitur & Persyaratan Fungsional
6. Arsitektur Teknis (Laravel 12 + Livewire / TALL Stack)
7. Persyaratan Non-Fungsional
8. Rencana Implementasi
9. Risiko & Mitigasi
10. Glosarium

---

## 1. Ringkasan Eksekutif

LAF PROJECT adalah sistem manajemen gudang (Warehouse Management System / WMS) berbasis web yang dirancang untuk mendigitalisasi dan mengotomasi seluruh proses pengelolaan stok, pembelian, penjualan, dan purchase order yang sebelumnya dikelola secara manual menggunakan spreadsheet.

Pada varian ini, sistem dibangun dengan standar engineering kelas enterprise menggunakan **Laravel 12** sebagai backend framework dan **Livewire 3** sebagai layer reactive frontend tanpa perlu membangun REST API terpisah maupun SPA berbasis JavaScript framework — dirancang untuk dapat melayani kebutuhan operasional gudang LAF PROJECT secara real-time, akurat, dan dapat diaudit, dengan keuntungan tambahan berupa development cycle yang lebih cepat karena seluruh logic backend dan rendering UI berada dalam satu basis kode PHP (full-stack PHP, monolith architecture).

### 1.1 Permasalahan yang Diselesaikan

| No. | Masalah | Dampak | Prioritas |
|---|---|---|---|
| 1 | Data stok tersebar di multiple sheet spreadsheet | Stok tidak sinkron, risiko human error tinggi | KRITIS |
| 2 | Update stok manual setelah setiap transaksi | Delay informasi, double-counting, stok ghost | KRITIS |
| 3 | Tidak ada alert otomatis stok minimum | Stockout tidak terdeteksi sampai terlambat | TINGGI |
| 4 | Tidak ada audit trail siapa yang ubah data | Tidak bisa lacak kesalahan input | TINGGI |
| 5 | Kalkulasi HPP manual setiap bulan | Rawan salah hitung, time-consuming | SEDANG |
| 6 | PO tracking terpisah dari data stok | Sisa PO sering tidak diupdate ke stok aktual | SEDANG |

### 1.2 Solusi yang Diusulkan

- Satu platform terpadu: semua modul (stock, pembelian, penjualan, PO, analytics) terhubung ke satu database MySQL melalui Eloquent ORM.
- Real-time stock engine: setiap transaksi masuk/keluar langsung memperbarui total stok secara otomatis melalui Eloquent Model Events (Observer) dan/atau Database Trigger.
- Kalkulasi HPP otomatis dengan metode rata-rata tertimbang (Weighted Average Cost), dieksekusi di dalam Service Class PHP.
- Dashboard alert: notifikasi visual ketika stok menyentuh safety stock atau minimum stock, dirender secara reaktif oleh komponen Livewire tanpa reload halaman penuh.
- Audit log lengkap: setiap perubahan data tercatat dengan timestamp dan identitas pengguna menggunakan package audit (mis. `spatie/laravel-activitylog` atau audit table custom).
- Forecasting otomatis: rekomendasi reorder berdasarkan rata-rata penjualan harian dan leadtime, dihitung melalui Laravel Scheduler (Cron Job).

---

## 2. Tujuan Produk & Metrik Keberhasilan

### 2.1 Tujuan Bisnis

| Objective | Key Result | Target |
|---|---|---|
| Akurasi stok mendekati 100% | Selisih stok sistem vs fisik | < 0.5% |
| Eliminasi proses manual berulang | Waktu rekap stok harian | Dari 2 jam ke < 10 menit |
| Visibilitas stok real-time | Delay update stok pasca transaksi | < 5 detik |
| Zero stockout untuk top produk | Kejadian kehabisan stok produk TOP 20 | 0 kejadian/bulan |
| Efisiensi operasional PO | Waktu proses dari PO ke update sisa stok | Dari manual ke otomatis |

### 2.2 Success Metrics (KPI)

Sistem dinyatakan berhasil apabila dalam 3 bulan pertama setelah go-live memenuhi seluruh indikator berikut:

- Akurasi data stok: akurasi >= 99.5% dibanding stock opname fisik bulanan.
- Uptime sistem: >= 99% availability pada jam operasional (08.00 – 22.00 WIB).
- Performa: halaman utama (dashboard Livewire) load dalam < 2 detik pada koneksi local network.
- Adopsi pengguna: >= 90% transaksi diinput melalui sistem (bukan manual spreadsheet).
- Kepuasan pengguna: NPS score >= 7/10 dari operator gudang setelah 60 hari pemakaian.

---

## 3. Lingkup Produk

### 3.1 Dalam Scope (In Scope)

- Manajemen master data produk: CRUD produk, HPP, kategori, kode barang, variasi, satuan, lokasi.
- Transaksi barang masuk: input pembelian, stock awal, retur masuk beserta supplier dan HPP.
- Transaksi barang keluar: input penjualan, sample, retur keluar beserta data pelanggan.
- Manajemen Purchase Order: buat PO, tracking sisa PO (B), rekap HPP sisa PO (C).
- Dashboard stok terkini: tampilan real-time total stok, sisa PO, alert min/max — menggunakan Livewire polling atau Laravel Echo/WebSocket untuk update tanpa refresh.
- Analytics dan forecasting: rata-rata harian, safety stock, minimum stock, maximum stock.
- Manajemen pengguna: autentikasi login, role-based access control (5 role) menggunakan Laravel Breeze/Fortify + Spatie Permission.
- Audit log: pencatatan otomatis setiap mutasi data dengan user dan timestamp.

### 3.2 Di Luar Scope (Out of Scope) — Fase 1

- Integrasi dengan marketplace (Tokopedia, Shopee, Lazada) — direncanakan Fase 2.
- Aplikasi mobile native (iOS/Android) — direncanakan Fase 3. Catatan: jika dibutuhkan REST API untuk konsumsi mobile app di Fase 3, Laravel sudah menyediakan fondasi melalui Laravel Sanctum tanpa perlu migrasi stack.
- Integrasi sistem akuntansi (jurnal otomatis ke software akuntansi) — Fase 2.
- Barcode/QR scanner hardware — opsional Fase 2.
- Multi-gudang (lebih dari 2 lokasi) — Fase 2.
- Fitur laporan pajak dan perpajakan — di luar scope.

---

## 4. Pengguna & Stakeholder

### 4.1 User Personas

#### Admin — Pemilik / Manajer Operasional

| Atribut | Detail |
|---|---|
| Kebutuhan utama | Visibilitas penuh atas semua data, laporan HPP, dan performa stok |
| Hak akses | Full CRUD semua modul + manajemen user + export laporan (role `admin` via Spatie Permission) |
| Pain point saat ini | Harus rekap manual dari banyak sheet untuk dapat gambaran bisnis |
| Goal | Dashboard ringkas (komponen Livewire) yang langsung tunjukkan kondisi stok dan keuangan |

#### Operator Gudang — Staff Gudang

| Atribut | Detail |
|---|---|
| Kebutuhan utama | Input transaksi masuk/keluar dengan cepat dan akurat |
| Hak akses | Input barang masuk, barang keluar; view stok; tidak bisa hapus data (role `operator_gudang`) |
| Pain point saat ini | Sering salah formula di spreadsheet, tidak tau stok real-time |
| Goal | Form input (Livewire form component) yang simpel, cepat, dan langsung update stok otomatis tanpa reload |

#### Sales — Tim Penjualan

| Atribut | Detail |
|---|---|
| Kebutuhan utama | Cek ketersediaan stok sebelum terima order dari customer |
| Hak akses | View stok, view PO, input barang keluar (penjualan), buat PO (role `sales`) |
| Pain point saat ini | Tidak tau stok real, sering janjikan barang yang habis ke customer |
| Goal | Bisa cek stok langsung dari HP/laptop tanpa ganggu tim gudang — UI responsif Tailwind di browser mobile |

#### Finance — Tim Keuangan

| Atribut | Detail |
|---|---|
| Kebutuhan utama | Laporan HPP akurat untuk kalkulasi margin dan laporan keuangan |
| Hak akses | View semua modul, export laporan HPP dan transaksi, view audit log (role `finance`) |
| Pain point saat ini | HPP sering berbeda antara sheet stok dan catatan pembelian |
| Goal | Laporan HPP terkonsolidasi otomatis (export Excel via `maatwebsite/excel`) tanpa rekonsiliasi manual |

#### Super Admin — IT/Developer (role ke-5, implisit dari "RBAC 5 role")

| Atribut | Detail |
|---|---|
| Kebutuhan utama | Kontrol penuh sistem termasuk konfigurasi role, permission, dan maintenance |
| Hak akses | Akses penuh ke seluruh modul + manajemen role/permission + akses Laravel Telescope/log |
| Pain point saat ini | Tidak ada pemisahan akses teknis vs akses bisnis pada sistem lama |
| Goal | Mampu mengelola struktur RBAC dan memantau kesehatan sistem secara terpisah dari peran Admin bisnis |

---

## 5. Fitur & Persyaratan Fungsional

### 5.1 Peta Fitur — Prioritas Matrix

| Fitur | Prioritas | Fase | Estimasi | Modul |
|---|---|---|---|---|
| CRUD Master Produk (semua kolom) | P1 | Fase 1 | 3 hari | Stock Master |
| Database Supplier & Pelanggan | P1 | Fase 1 | 2 hari | Master |
| Input Barang Masuk + update stok auto | P1 | Fase 1 | 4 hari | Pembelian |
| Input Barang Keluar + update stok auto | P1 | Fase 1 | 4 hari | Penjualan |
| Real-time stock engine (Eloquent Observer/Trigger) | P1 | Fase 1 | 3 hari | Core Engine |
| HPP Weighted Average otomatis | P1 | Fase 1 | 2 hari | Core Engine |
| Manajemen PO — buat & track sisa | P1 | Fase 1 | 5 hari | PO |
| Sisa PO (B) — tracking per produk | P1 | Fase 1 | 2 hari | PO |
| Sisa PO (C) — rekap HPP total | P1 | Fase 1 | 2 hari | PO |
| Dashboard stok terkini + alert min/max (Livewire reactive) | P1 | Fase 1 | 3 hari | Dashboard |
| Autentikasi login + RBAC 5 role (Breeze + Spatie Permission) | P1 | Fase 1 | 3 hari | Auth |
| Audit log setiap mutasi data | P1 | Fase 1 | 2 hari | Core |
| Analytics — rata-rata harian & safety stock | P2 | Fase 1 | 4 hari | Analytics |
| Forecasting reorder otomatis (Laravel Scheduler) | P2 | Fase 1 | 3 hari | Analytics |
| Export laporan ke Excel / PDF (Maatwebsite Excel / DomPDF) | P2 | Fase 1 | 3 hari | Laporan |
| Filter & search produk advanced (Livewire `wire:model.live` + debounce) | P2 | Fase 1 | 2 hari | UX |
| Notifikasi email stok minimum (Laravel Notification + Queue) | P2 | Fase 2 | 3 hari | Notif |
| Integrasi marketplace | P3 | Fase 2 | - | Integrasi |

### 5.2 Detail Fitur per Modul

> **Catatan penting:** Seluruh nama kolom, tipe data, dan struktur tabel pada bagian ini **identik** dengan dokumen PRD acuan (variant Next.js/Prisma). Tidak ada penambahan, pengurangan, maupun perubahan nama field. Perbedaan hanya pada cara representasi teknis (Eloquent Model + Migration Laravel, bukan Prisma Schema).

#### Modul 1 — Stock Master

Merupakan pusat data produk. Setiap produk menyimpan semua kolom asli dari spreadsheet Stock Master tanpa pengurangan.

| Kolom Asli | Field Database | Tipe | Keterangan |
|---|---|---|---|
| Rp (Hpp barang /pis)* | hpp | DECIMAL(15,2) | Harga Pokok Produksi per pcs, diupdate otomatis |
| #NAMA PRODUK# | nama_produk | VARCHAR(200) | Nama lengkap produk, required |
| Kategori* | kategori | VARCHAR(100) | Misal: SANDAL, TOP 20, dll |
| Kode Barang* | kode_barang | VARCHAR(100) UNIQUE | Kode unik per produk, auto-generated bisa |
| Nama Barang* | nama_barang | VARCHAR(200) | Nama display produk |
| Variasi barang* | variasi_barang | VARCHAR(100) | Ukuran, warna, varian |
| satuan* | satuan | VARCHAR(20) | Misal: psg, pcs, box |
| #Masuk# | stok_masuk | INT | Total kumulatif barang masuk, computed |
| #Keluar# | stok_keluar | INT | Total kumulatif barang keluar, computed |
| #Total Stock# | total_stok | INT | = Masuk - Keluar, auto-update |
| Database Lokasi | lokasi | ENUM(TOKO, GUDANG) | Lokasi penyimpanan default |

Implementasi Laravel: tabel `mst_produk`, dikelola melalui `App\Models\Produk` (Eloquent), dengan kalkulasi `stok_masuk`, `stok_keluar`, dan `total_stok` di-refresh melalui Model Observer (`ProdukObserver`) atau di-trigger ulang oleh `StockEngineService` setiap kali ada insert/update pada `trx_barang_masuk` atau `trx_barang_keluar`.

#### Modul 2 — Barang Masuk / Pembelian

| Kolom Asli | Field Database | Tipe | Keterangan |
|---|---|---|---|
| Tanggal | tanggal | DATE | Date picker (Alpine.js / Flatpickr terintegrasi Livewire) |
| Tipe Nota | tipe_nota | ENUM | Stock Awal, Pembelian, Retur Masuk, Penyesuaian |
| Nama Suplier / Pengirim barang | supplier_id | FK → mst_supplier | Dropdown dari database supplier (Livewire `wire:model`) |
| Nomor Nota | nomor_nota | VARCHAR(100) | Opsional, nomor faktur/nota pembelian |
| Keterangan Tambahan | keterangan_tambahan | TEXT | Catatan bebas, opsional |
| Produk | produk_id | FK → mst_produk | Autocomplete search produk (Livewire live search) |
| QTY Masuk | qty_masuk | INT | Jumlah unit masuk, required > 0, divalidasi via Laravel Form Request rules |
| Satuan | satuan | VARCHAR(20) | Auto-fill dari master produk |
| Data di Masukan Oleh | data_dimasukan_oleh | VARCHAR(100) | Auto-fill dari `Auth::user()->name` |
| Lokasi simpan | lokasi_simpan | ENUM(TOKO, GUDANG) | Pilih lokasi masuk |
| Hpp | hpp | DECIMAL(15,2) | HPP saat transaksi, di-snapshot untuk audit |
| Database Nama Suplier / Pengirim barang | supplier_id | FK | Tersimpan di mst_supplier |

Implementasi Laravel: tabel `trx_barang_masuk`, model `BarangMasuk`, form input dikelola via Livewire component `BarangMasukForm` dengan validasi server-side menggunakan Laravel Validation Rules dan disimpan dalam DB Transaction (`DB::transaction()`).

#### Modul 3 — Barang Keluar / Penjualan

| Kolom Asli | Field Database | Tipe | Keterangan |
|---|---|---|---|
| Tanggal | tanggal | DATE | Date picker |
| Tipe Nota | tipe_nota | ENUM | Penjualan, Retur Keluar, Penyesuaian, Sample |
| Nama Langganan / Perusahaan | pelanggan_id | FK → mst_pelanggan | Dropdown dari database pelanggan |
| Nomor Nota | nomor_nota | VARCHAR(100) | Opsional |
| Keterangan Tambahan | keterangan_tambahan | TEXT | Opsional |
| Produk | produk_id | FK → mst_produk | Autocomplete search |
| QTY Keluar | qty_keluar | INT | Jumlah unit keluar, validasi <= stok tersedia (custom Validation Rule `StokCukup`) |
| Satuan (Rumus) | satuan | VARCHAR(20) | Auto-fill dari master |
| Data dimasukan Oleh / Barang dikeluarkan oleh | dikeluarkan_oleh | VARCHAR(100) | Auto-fill dari session Auth |
| Lokasi pengambilan | lokasi_pengambilan | ENUM(TOKO, GUDANG) | Dari mana barang diambil |
| HPP | hpp | DECIMAL(15,2) | Snapshot HPP saat keluar |
| Database Nama Pelanggan / Perusahaan | pelanggan_id | FK | Tersimpan di mst_pelanggan |

Implementasi Laravel: tabel `trx_barang_keluar`, model `BarangKeluar`, validasi stok mencukupi dilakukan di `StockEngineService::validasiStokKeluar()` sebelum proses simpan dieksekusi di dalam transaction.

#### Modul 4 — Purchase Order (PO A, B, C)

| Sheet Asal | Tabel Database | Kolom Asli | Keterangan |
|---|---|---|---|
| PO (A) | po_header + po_detail | TANGGAL, VENDOR, ARTIKEL, JUMLAH | Header PO + detail awal |
| SISA PO (B) | po_detail (kolom tambahan) | TANGGAL, VENDOR, ARTIKEL, JUMLAH, BARANG MASUK, SISA | Tracking sisa per produk |
| SISA PO (C) | po_detail (kolom tambahan) | VENDOR, ARTIKEL, PO, BARANG MASUK, SISA, HPP, TOTAL HPP | Rekap HPP sisa PO |

Implementasi Laravel: tabel `po_header` dan `po_detail`, model `PoHeader` dan `PoDetail` dengan relasi `hasMany`/`belongsTo`. Komponen Livewire `PurchaseOrderManager` menggabungkan ketiga tampilan (PO A, Sisa PO B, Rekap HPP C) sebagai tab/view berbeda dari satu sumber data yang sama, menghindari duplikasi tabel.

#### Modul 5 — Dashboard Stok Terkini

| Kolom Asli | Sumber Data | Logika |
|---|---|---|
| Nama Barang | mst_produk.nama_barang | Direct field |
| Stok Terkini | mst_produk.total_stok | = SUM(masuk) - SUM(keluar), real-time |
| Sisa PO | SUM(po_detail.sisa) | Total sisa PO yang belum masuk gudang |
| Stok Minimum (Input) | analytics_produk.minimal_stock | Editable per produk |
| Stok Maximum (Input) | analytics_produk.maximum_stock | Editable per produk |
| Alert Minimum | Computed | Highlight MERAH jika Stok Terkini < Stok Minimum |
| Alert Maximum | Computed | Info jika Stok Terkini > Stok Maximum (overstock) |

Implementasi Laravel: komponen Livewire `DashboardStokTerkini` dengan `wire:poll.5s` untuk refresh data secara berkala tanpa reload halaman penuh, memenuhi requirement delay update < 5 detik.

#### Modul 6 — Analytics & Forecasting

| Kolom Asli | Field Database | Logika / Rumus |
|---|---|---|
| KATEGORI | analytics_produk.kategori | Mirror dari mst_produk.kategori |
| PRODUK | mst_produk.nama_barang | FK ke master produk |
| JAN | analytics_produk.penjualan_jan | Total qty keluar bulan Januari |
| APRIL | analytics_produk.penjualan_apr | Total qty keluar bulan April |
| MEI | analytics_produk.penjualan_mei | Total qty keluar bulan Mei |
| RATA RATA HARIAN | analytics_produk.rata_rata_harian | = (JAN+APR+MEI) / jumlah hari aktif |
| LEADTIME PRODUKSI | analytics_produk.leadtime_produksi | Default 14 hari, editable per produk |
| MINIMAL STOCK | analytics_produk.minimal_stock | = RATA_RATA_HARIAN x LEADTIME |
| SAFETY STOCK | analytics_produk.safety_stock | = MINIMAL_STOCK x 1.5 (buffer 50%) |
| MAXIMUM STOCK | analytics_produk.maximum_stock | = SAFETY_STOCK x 1.5 (overstock limit) |

Implementasi Laravel: tabel `analytics_produk`, model `AnalyticsProduk`, kalkulasi bulanan dijalankan melalui Artisan Command terjadwal (`php artisan analytics:recalculate`) yang didaftarkan pada Laravel Scheduler (`routes/console.php`), dieksekusi via cron `* * * * * php artisan schedule:run`.

---

## 6. Arsitektur Teknis (Laravel 12 + Livewire / TALL Stack)

### 6.1 Tech Stack

| Layer | Teknologi | Versi | Alasan Pemilihan |
|---|---|---|---|
| Backend Framework | Laravel | 12.x | Konvensi matang, ekosistem luas, ORM bawaan kuat, ekosistem package siap pakai |
| Frontend Reactivity | Livewire | 3.x | Reaktivitas mirip SPA tanpa menulis API terpisah, full-stack PHP, real-time DOM diffing |
| Interaktivitas Ringan | Alpine.js | 3.x | Melengkapi Livewire untuk interaksi UI kecil (dropdown, modal, toggle) tanpa overhead JS framework besar |
| Bahasa Pemrograman | PHP | 8.3+ | Native dipakai Laravel, performant, strong typing opsional |
| Styling | Tailwind CSS | 3.x / 4.x | Rapid UI development, utility-first, konsisten dengan desain dokumen acuan |
| ORM | Eloquent ORM (bawaan Laravel) | Laravel 12.x | Active Record pattern, migration system bawaan, relasi model deklaratif |
| Database | MySQL via XAMPP | 8.x | Familiar, local dev, mudah backup & restore — identik dengan dokumen acuan |
| Autentikasi | Laravel Breeze / Fortify + Spatie Laravel-Permission | Latest | Session-based auth bawaan Laravel + RBAC granular berbasis role & permission |
| State Management | Livewire Component State (server-side) | 3.x | State dikelola di server, tidak perlu state management client terpisah (Redux/Context) |
| Tabel Data | Livewire + Laravel Pagination, atau package `livewire/livewire` table komponen custom | Latest | Sorting, filtering, searching dilakukan server-side dengan query Eloquent |
| Form & Validasi | Livewire Form Object + Laravel Form Request Validation | 3.x / 12.x | Validasi server-side terpusat, real-time validation feedback (`wire:model.live`) |
| Icon | Blade Icons / Heroicons (via `blade-ui-kit/blade-heroicons`) | Latest | Konsisten, ringan, terintegrasi langsung di Blade template |
| Export Laporan | Maatwebsite/Laravel-Excel, barryvdh/laravel-dompdf | Latest | Export Excel dan PDF native dari Eloquent Collection |
| Audit Log | Spatie/laravel-activitylog (atau tabel `audit_log` custom) | Latest | Mencatat setiap mutasi data dengan timestamp dan user_id otomatis |
| Job Queue | Laravel Queue (database/redis driver) | 12.x | Untuk proses async seperti notifikasi email stok minimum & recalculation analytics |
| Scheduler | Laravel Task Scheduling | 12.x | Menjalankan forecasting/analytics recalculation secara terjadwal |

**Catatan perbandingan dengan dokumen acuan:** Next.js App Router digantikan oleh Laravel Routing + Blade + Livewire Component; Prisma digantikan oleh Eloquent ORM (skema tabel tetap sama); NextAuth.js digantikan oleh Laravel Breeze/Fortify + Spatie Permission; TanStack Table dan React Hook Form + Zod digantikan oleh kombinasi Livewire reactive component dan Laravel Validation; SWR/React Context digantikan oleh server-side state Livewire (tidak memerlukan client-side cache layer karena setiap interaksi melakukan round-trip ke server melalui AJAX yang dikelola Livewire secara otomatis).

### 6.2 Struktur Database

Database terdiri dari 8 tabel utama dengan relasi sebagai berikut. **Struktur, nama tabel, dan kardinalitas ini tidak diubah dari dokumen PRD acuan.**

| Tabel | Tipe | Berelasi Dengan | Kardinalitas |
|---|---|---|---|
| mst_produk | Master | trx_barang_masuk, trx_barang_keluar, po_detail, analytics_produk | 1 produk : N transaksi |
| mst_supplier | Master | trx_barang_masuk, po_header | 1 supplier : N transaksi masuk |
| mst_pelanggan | Master | trx_barang_keluar | 1 pelanggan : N transaksi keluar |
| trx_barang_masuk | Transaksi | mst_produk, mst_supplier | Banyak-ke-satu (ke master) |
| trx_barang_keluar | Transaksi | mst_produk, mst_pelanggan | Banyak-ke-satu (ke master) |
| po_header | PO | mst_supplier, po_detail | 1 PO header : N detail |
| po_detail | PO | po_header, mst_produk | Menggabungkan PO A + B + C |
| analytics_produk | Analytics | mst_produk | 1-ke-1 dengan mst_produk |

#### Representasi Eloquent Model & Relasi

```
Produk (mst_produk)
├── hasMany(BarangMasuk::class, 'produk_id')
├── hasMany(BarangKeluar::class, 'produk_id')
├── hasMany(PoDetail::class, 'produk_id')
└── hasOne(AnalyticsProduk::class, 'produk_id')

Supplier (mst_supplier)
├── hasMany(BarangMasuk::class, 'supplier_id')
└── hasMany(PoHeader::class, 'supplier_id')

Pelanggan (mst_pelanggan)
└── hasMany(BarangKeluar::class, 'pelanggan_id')

BarangMasuk (trx_barang_masuk)
├── belongsTo(Produk::class, 'produk_id')
└── belongsTo(Supplier::class, 'supplier_id')

BarangKeluar (trx_barang_keluar)
├── belongsTo(Produk::class, 'produk_id')
└── belongsTo(Pelanggan::class, 'pelanggan_id')

PoHeader (po_header)
├── belongsTo(Supplier::class, 'supplier_id')
└── hasMany(PoDetail::class, 'po_header_id')

PoDetail (po_detail)
├── belongsTo(PoHeader::class, 'po_header_id')
└── belongsTo(Produk::class, 'produk_id')

AnalyticsProduk (analytics_produk)
└── belongsTo(Produk::class, 'produk_id')
```

#### Contoh Migration Laravel (mengikuti skema kolom asli, tanpa modifikasi)

```php
// database/migrations/xxxx_xx_xx_create_mst_produk_table.php
Schema::create('mst_produk', function (Blueprint $table) {
    $table->id();
    $table->decimal('hpp', 15, 2);
    $table->string('nama_produk', 200);
    $table->string('kategori', 100);
    $table->string('kode_barang', 100)->unique();
    $table->string('nama_barang', 200);
    $table->string('variasi_barang', 100);
    $table->string('satuan', 20);
    $table->integer('stok_masuk')->default(0);
    $table->integer('stok_keluar')->default(0);
    $table->integer('total_stok')->default(0);
    $table->enum('lokasi', ['TOKO', 'GUDANG']);
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_trx_barang_masuk_table.php
Schema::create('trx_barang_masuk', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal');
    $table->enum('tipe_nota', ['Stock Awal', 'Pembelian', 'Retur Masuk', 'Penyesuaian']);
    $table->foreignId('supplier_id')->constrained('mst_supplier');
    $table->string('nomor_nota', 100)->nullable();
    $table->text('keterangan_tambahan')->nullable();
    $table->foreignId('produk_id')->constrained('mst_produk');
    $table->integer('qty_masuk');
    $table->string('satuan', 20);
    $table->string('data_dimasukan_oleh', 100);
    $table->enum('lokasi_simpan', ['TOKO', 'GUDANG']);
    $table->decimal('hpp', 15, 2);
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_trx_barang_keluar_table.php
Schema::create('trx_barang_keluar', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal');
    $table->enum('tipe_nota', ['Penjualan', 'Retur Keluar', 'Penyesuaian', 'Sample']);
    $table->foreignId('pelanggan_id')->constrained('mst_pelanggan');
    $table->string('nomor_nota', 100)->nullable();
    $table->text('keterangan_tambahan')->nullable();
    $table->foreignId('produk_id')->constrained('mst_produk');
    $table->integer('qty_keluar');
    $table->string('satuan', 20);
    $table->string('dikeluarkan_oleh', 100);
    $table->enum('lokasi_pengambilan', ['TOKO', 'GUDANG']);
    $table->decimal('hpp', 15, 2);
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_po_header_table.php
Schema::create('po_header', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal');
    $table->foreignId('supplier_id')->constrained('mst_supplier');
    $table->string('artikel', 200)->nullable();
    $table->integer('jumlah');
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_po_detail_table.php
Schema::create('po_detail', function (Blueprint $table) {
    $table->id();
    $table->foreignId('po_header_id')->constrained('po_header');
    $table->foreignId('produk_id')->constrained('mst_produk');
    $table->integer('jumlah');
    $table->integer('barang_masuk')->default(0);
    $table->integer('sisa')->default(0);
    $table->decimal('hpp', 15, 2)->nullable();
    $table->decimal('total_hpp', 15, 2)->nullable();
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_analytics_produk_table.php
Schema::create('analytics_produk', function (Blueprint $table) {
    $table->id();
    $table->foreignId('produk_id')->constrained('mst_produk');
    $table->string('kategori', 100);
    $table->integer('penjualan_jan')->default(0);
    $table->integer('penjualan_apr')->default(0);
    $table->integer('penjualan_mei')->default(0);
    $table->decimal('rata_rata_harian', 10, 2)->default(0);
    $table->integer('leadtime_produksi')->default(14);
    $table->integer('minimal_stock')->default(0);
    $table->integer('safety_stock')->default(0);
    $table->integer('maximum_stock')->default(0);
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_mst_supplier_table.php
Schema::create('mst_supplier', function (Blueprint $table) {
    $table->id();
    $table->string('nama_supplier', 200);
    $table->string('kontak', 100)->nullable();
    $table->text('alamat')->nullable();
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_mst_pelanggan_table.php
Schema::create('mst_pelanggan', function (Blueprint $table) {
    $table->id();
    $table->string('nama_pelanggan', 200);
    $table->string('kontak', 100)->nullable();
    $table->text('alamat')->nullable();
    $table->timestamps();
});
```

> Catatan: kolom `id`, `created_at`, `updated_at` ditambahkan secara konvensional oleh Laravel sebagai bagian standar Eloquent ORM dan tidak mengubah skema bisnis yang sudah didefinisikan pada dokumen acuan.

### 6.3 Struktur Folder Laravel 12 + Livewire

| Path | Keterangan |
|---|---|
| `routes/web.php` | Definisi route utama, mengarah ke Livewire full-page component |
| `app/Http/Middleware/` | Middleware autentikasi & RBAC (role/permission check) |
| `app/Livewire/Auth/Login.php` | Komponen login — public route |
| `app/Livewire/Dashboard.php` | Dashboard KPI utama, full-page Livewire component |
| `app/Livewire/StockMaster/` | CRUD master produk (Index, Form, Detail components) |
| `app/Livewire/Pembelian/` | Input barang masuk (form component + tabel transaksi) |
| `app/Livewire/Penjualan/` | Input barang keluar (form component + tabel transaksi) |
| `app/Livewire/PurchaseOrder/` | Manajemen PO A, B, C |
| `app/Livewire/StokTerkini/` | Live stok + alert min/max (dengan `wire:poll`) |
| `app/Livewire/Analytics/` | Forecasting & safety stock |
| `app/Models/` | Eloquent Model: Produk, Supplier, Pelanggan, BarangMasuk, BarangKeluar, PoHeader, PoDetail, AnalyticsProduk, User |
| `app/Services/StockEngineService.php` | Core: kalkulasi stok & HPP (Weighted Average Cost) |
| `app/Services/AnalyticsService.php` | Core: kalkulasi rata-rata harian, safety stock, forecasting |
| `app/Observers/` | Model Observer untuk auto-update stok pasca transaksi & audit logging |
| `app/Policies/` | Laravel Policy untuk otorisasi per-model berbasis role |
| `app/Console/Commands/` | Artisan Command custom (analytics recalculation, notifikasi stok minimum) |
| `resources/views/livewire/` | Blade view untuk setiap komponen Livewire |
| `resources/views/components/` | Komponen Blade reusable (DataTable, FormModal, AlertBadge) |
| `database/migrations/` | Seluruh skema tabel (lihat 6.2) |
| `database/seeders/` | Seeder untuk role, permission, dan data dummy testing |
| `config/permission.php` | Konfigurasi Spatie Laravel-Permission untuk RBAC 5 role |

---

## 7. Persyaratan Non-Fungsional

### 7.1 Performa

| Metrik | Target | Kondisi |
|---|---|---|
| Page load time (dashboard) | < 2 detik | Koneksi local network XAMPP, termasuk render Blade + hydrate Livewire |
| Response time aksi Livewire (transaksi) | < 500ms | Single record insert/update via Livewire AJAX round-trip |
| Query stok terkini | < 1 detik | Maksimal 5.000 produk aktif, dengan eager loading relasi Eloquent untuk hindari N+1 query |
| Concurrent users | >= 10 pengguna | Operasional normal gudang |

### 7.2 Keamanan

- Semua route dilindungi middleware `auth` Laravel — tidak bisa diakses tanpa login.
- RBAC enforced melalui Spatie Laravel-Permission di level Middleware dan Laravel Policy — setiap komponen Livewire memeriksa permission sebelum eksekusi aksi (`$this->authorize()`).
- Password di-hash dengan bcrypt (default hashing driver Laravel, salt rounds >= 12 dikonfigurasi di `config/hashing.php`).
- Input sanitization: semua input user divalidasi dengan Laravel Validation Rules (baik melalui Form Request maupun Livewire `rules()` method) sebelum masuk DB.
- SQL Injection prevention: Eloquent ORM menggunakan parameterized queries (PDO prepared statements) by default.
- Session expire otomatis setelah 8 jam tidak aktif (`config/session.php` → `lifetime` disesuaikan).
- Rate limiting pada endpoint login: maksimal 5 percobaan per 15 menit menggunakan Laravel `RateLimiter` / `throttle` middleware.
- CSRF Protection bawaan Laravel aktif otomatis pada seluruh form dan request Livewire.

### 7.3 Keandalan (Reliability)

- Database: MySQL dengan daily backup otomatis ke folder lokal (dapat dijadwalkan via Laravel Scheduler memanggil `mysqldump`, atau package `spatie/laravel-backup`).
- Setiap operasi insert/update stok dibungkus dalam database transaction (`DB::transaction()`) — gagal satu, semua rollback.
- Error logging: setiap error server dicatat melalui Laravel Log (`storage/logs/laravel.log`) dengan stack trace, dapat diintegrasikan dengan Laravel Telescope untuk monitoring lebih detail di lingkungan development/staging.
- Validasi stok negatif: sistem menolak transaksi keluar jika stok tidak mencukupi, divalidasi melalui custom Validation Rule sebelum data disimpan.

### 7.4 Kemudahan Penggunaan (Usability)

- Responsive design: optimal di layar 1280px ke atas (desktop/laptop), dibangun dengan utility class Tailwind CSS yang sama persis dengan dokumen acuan.
- Form input barang masuk/keluar: maksimal 5 klik dari dashboard ke data tersimpan, didukung navigasi SPA-like dari Livewire tanpa full page reload.
- Autocomplete produk: ketik minimal 2 karakter untuk tampilkan saran produk, diimplementasikan dengan `wire:model.live.debounce.300ms` pada komponen Livewire pencarian.
- Feedback visual: toast notification (menggunakan Livewire event + Alpine.js component) untuk setiap aksi (sukses/gagal).
- Alert stok: badge merah untuk stok di bawah minimum, badge kuning untuk mendekati minimum — dirender sebagai Blade component `<x-alert-badge>` reusable.

---

## 8. Rencana Implementasi

### 8.1 Roadmap Fase 1 — MVP (Target: 8 Minggu)

| Sprint | Minggu | Deliverable | Estimasi |
|---|---|---|---|
| Sprint 0 | Minggu 1 | Setup project: Laravel 12, Livewire 3, Tailwind, MySQL, Breeze Auth, Spatie Permission, folder structure | 5 hari |
| Sprint 1 | Minggu 2-3 | Modul Stock Master: CRUD produk (Livewire component) + database supplier & pelanggan | 8 hari |
| Sprint 2 | Minggu 3-4 | Modul Barang Masuk + Core Stock Engine Service (auto-update stok & HPP via Observer) | 8 hari |
| Sprint 3 | Minggu 4-5 | Modul Barang Keluar + validasi stok tersedia (custom Validation Rule) | 7 hari |
| Sprint 4 | Minggu 5-6 | Modul Purchase Order (PO A, Sisa PO B, Sisa PO C) | 8 hari |
| Sprint 5 | Minggu 6-7 | Dashboard Stok Terkini (Livewire `wire:poll`) + alert min/max + Analytics & Forecasting (Scheduler) | 8 hari |
| Sprint 6 | Minggu 7-8 | Export laporan (Excel/PDF), audit log, UI polish, UAT, bug fixing, go-live | 7 hari |

### 8.2 Definition of Done

Setiap fitur dianggap selesai apabila memenuhi semua kriteria berikut:

- Fungsi berjalan sesuai spesifikasi PRD tanpa bug kritikal.
- Validasi input berjalan di level Livewire component (`rules()`) dan/atau Form Request Laravel.
- Data tersimpan dengan benar di MySQL dan dapat diverifikasi via Laravel Tinker atau phpMyAdmin/Adminer.
- Role-based access control berfungsi — role yang tidak berhak tidak bisa akses fitur/komponen terkait (diverifikasi melalui Policy dan Middleware).
- Stok terupdate secara otomatis pasca transaksi (max delay 5 detik), diverifikasi melalui Observer/Service Class.
- Tampilan responsif dan konsisten di browser Chrome terbaru.
- Telah di-demo ke stakeholder dan mendapat sign-off.

---

## 9. Risiko & Mitigasi

| Risiko | Kemungkinan | Dampak | Mitigasi |
|---|---|---|---|
| Data migrasi dari spreadsheet tidak akurat | Sedang | Tinggi | Buat Artisan Command import (`php artisan import:spreadsheet`) + verifikasi manual 100 baris pertama |
| Pengguna resisten pindah dari spreadsheet | Tinggi | Sedang | Training 2 jam + demo langsung + pendampingan minggu pertama |
| Performa lambat saat data > 10.000 baris | Rendah | Sedang | Tambah index pada kolom `tanggal` dan `produk_id`, gunakan eager loading dan query caching (Laravel Cache) |
| Salah input qty menyebabkan stok negatif | Sedang | Tinggi | Validasi di level Service/Livewire component: tolak jika `qty_keluar > total_stok` |
| XAMPP crash / data hilang | Rendah | Sangat Tinggi | Jadwalkan backup MySQL harian ke folder cloud (Google Drive) via Laravel Scheduler |
| Scope creep — request fitur baru saat development | Tinggi | Sedang | Freeze scope Fase 1, semua request baru masuk backlog Fase 2 |
| Tim developer kurang familiar dengan Livewire reactive pattern | Sedang | Sedang | Sesi onboarding internal + dokumentasi pattern komponen standar sebelum Sprint 1 dimulai |

---

## 10. Glosarium

| Istilah | Definisi |
|---|---|
| HPP | Harga Pokok Produksi — biaya yang dikeluarkan untuk memperoleh/memproduksi barang |
| Stok Terkini | Total stok yang tersedia secara real-time = Total Masuk - Total Keluar |
| Sisa PO | Jumlah barang yang sudah di-order ke supplier tapi belum diterima ke gudang |
| Safety Stock | Stok buffer minimum agar tidak habis saat demand tiba-tiba naik atau supplier terlambat |
| Leadtime | Waktu rata-rata dari PO dikirim ke supplier sampai barang tiba di gudang (default 14 hari) |
| Rata-rata Harian | Rata-rata unit terjual per hari berdasarkan data historis 3 bulan terakhir |
| Stock Opname | Penghitungan fisik stok secara berkala untuk dicocokkan dengan data sistem |
| RBAC | Role-Based Access Control — sistem hak akses berdasarkan peran pengguna |
| CRUD | Create, Read, Update, Delete — operasi dasar manajemen data |
| ORM | Object-Relational Mapping — Eloquent sebagai jembatan antara kode PHP dan database |
| TALL Stack | Tailwind CSS, Alpine.js, Laravel, Livewire — kombinasi stack full-stack PHP reactive |
| Livewire Component | Unit reusable berisi state PHP di server dan view Blade, ter-render reaktif tanpa reload halaman |
| PRD | Product Requirements Document — dokumen ini; spesifikasi lengkap produk yang akan dibangun |
| MVP | Minimum Viable Product — versi paling minimal yang sudah bisa digunakan secara produktif |
| TOKO | Lokasi penyimpanan / pengambilan barang di toko fisik |
| GUDANG | Lokasi penyimpanan utama / stock cadangan |

---

*— Akhir Dokumen PRD LAF PROJECT v1.0.0 (TALL Stack Variant) —*

*Dokumen ini telah disetujui untuk digunakan sebagai acuan pengembangan sistem menggunakan Laravel 12 + Livewire.*

**LAF PROJECT Warehouse OS | TALL Stack Variant | v1.0.0 | 2026**
