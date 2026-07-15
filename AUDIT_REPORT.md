# 📋 WMS LAF — Laporan Audit Kode Menyeluruh

> **Auditor:** AI Code Auditor (Senior Fullstack Engineer)  
> **Tanggal Audit:** 2026-07-07  
> **Scope:** Semua Modul  
> **Tech Stack:** Laravel + Livewire + Spatie Permission + MySQL

---

## Daftar Isi

1. [Step 1 — Business Process Map](#step-1--business-process-map)
2. [Step 2 — Database Structure Audit](#step-2--database-structure-audit)
3. [Step 3 — Request Validation Audit](#step-3--request-validation-audit)
4. [Step 4 — Livewire Component Audit](#step-4--livewire-component-audit)
5. [Step 5 — Error Handling Audit](#step-5--error-handling--response-consistency)
6. [Step 6 — Blade/UI Template Audit](#step-6--bladeui-template-audit)
7. [Step 7 — Security & Authorization Audit](#step-7--security--authorization-audit)
8. [Step 8 — Performance Audit](#step-8--performance-audit)
9. [Step 9 — Rangkuman & Rekomendasi](#step-9--rangkuman--rekomendasi)

---

## STEP 1 — BUSINESS PROCESS MAP

> Reverse-engineered dari kode aktual. Bukan asumsi.

### 1.1 Barang Masuk (Inbound)

| Aspek | Aktual di Kode |
|---|---|
| **Actor** | Semua user authenticated. `Create.php` = **TIDAK ADA** permission check. `Edit.php`/`Index.php` delete = cek `manage master` ATAU `input masuk` |
| **Trigger** | Route `/barang-masuk/create` → `App\Livewire\Pembelian\Create::store()` |
| **Precondition** | Validasi form (tanggal, supplier_id required, items min:1). Tidak ada cek stok negatif saat masuk |
| **Alur** | Validate → `StockEngineService::tambahBarangMasuk()` (DB::transaction) → Lock produk → Hitung HPP Weighted Average → Update `total_stok` + `stok_masuk` → Update/create `StokLokasi` → Catat `MutasiLokasi` → Update PO jika ada → Create detail |
| **Postcondition** | `mst_produk.total_stok += qty`, `hpp` recalculated, `trx_stok_lokasi.qty += qty`, new `trx_mutasi_lokasi`, `po_detail.sisa` decremented |
| **Edge Case ✅** | Pessimistic lock, HPP weighted average, auto-create Lokasi, reversal on edit/delete |
| **Edge Case ❌** | `Create.php` tanpa auth, nomor nota race condition, tidak ada audit log pada CREATE |

**File terkait:**
- `app/Livewire/Pembelian/Create.php`
- `app/Livewire/Pembelian/Edit.php`
- `app/Livewire/Pembelian/Index.php`
- `app/Services/StockEngineService.php` → `tambahBarangMasuk()`, `ubahBarangMasuk()`, `hapusBarangMasuk()`

---

### 1.2 Barang Keluar (Outbound)

| Aspek | Aktual di Kode |
|---|---|
| **Actor** | Semua user authenticated. **TIDAK ADA** permission check di Create maupun Index |
| **Trigger** | Route `/barang-keluar/create` → `App\Livewire\Penjualan\Create::store()` |
| **Precondition** | Validasi form + validasi stok lokasi 2x (Livewire pre-check + Service re-check) |
| **Alur** | Validate → Aggregate qty per produk/lokasi → Pre-validate stok → `StockEngineService::tambahBarangKeluar()` → Lock produk + stok_lokasi → Kurangi stok → Catat mutasi → Trigger analytics sync |
| **Edge Case ✅** | Stok validasi 2x, pessimistic lock, analytics auto-sync |
| **Edge Case ❌** | Tidak ada auth, tidak ada fitur edit/delete barang keluar, tidak ada audit log |

**File terkait:**
- `app/Livewire/Penjualan/Create.php`
- `app/Livewire/Penjualan/Index.php`
- `app/Services/StockEngineService.php` → `tambahBarangKeluar()`

---

### 1.3 Transfer Gudang

| Aspek | Aktual di Kode |
|---|---|
| **Actor** | Semua user authenticated. **TIDAK ADA** permission check |
| **Alur** | DRAFT → (PICKING) → IN_TRANSIT → RECEIVED → CLOSED |
| **Edge Case ✅** | State machine validated, pessimistic lock, `different:lokasi_asal_id` |
| **Edge Case ❌** | No auth, tidak ada MutasiLokasi record untuk penerimaan (receive), partial receive selisih qty hilang |

**File terkait:**
- `app/Livewire/Transfer/Create.php`, `Index.php`, `Detail.php`
- `app/Services/TransferService.php`

---

### 1.4 Stock Opname

| Aspek | Aktual di Kode |
|---|---|
| **Actor** | `input masuk` (create/complete/cancel), `manage master` (adjustment) |
| **Alur** | Create session → Auto-populate ALL products → Scan/manual count → Complete → Adjust |
| **Edge Case ✅** | Auto-populate, barcode scan, adjustment DB transaction + lock |
| **Edge Case ❌** | Auto-populate semua produk (bukan hanya yg ada di lokasi), `updateQtyFisik`/`updateCatatan` tanpa auth, tidak ada audit log untuk adjustment |

**File terkait:**
- `app/Livewire/Audit/Index.php`
- `app/Models/OpnameHeader.php`, `OpnameDetail.php`

---

### 1.5 Picking & Packing

| Aspek | Aktual di Kode |
|---|---|
| **Actor** | Semua user authenticated. **TIDAK ADA** permission check |
| **Alur Picking** | Generate dari BarangKeluar/TransferHeader → FIFO recommendation → Start → Scan verify → Complete |
| **Alur Packing** | Completed picking → QC checklist → Input resi/kurir → Submit |
| **Edge Case ✅** | FIFO location recommendation, status machine |
| **Edge Case ❌** | Tidak ada auth, resi/kurir di detail-level bukan header-level |

**File terkait:**
- `app/Livewire/Outbound/PickingManager.php`, `PackingManager.php`
- `app/Services/PickingService.php`

---

## STEP 2 — DATABASE STRUCTURE AUDIT

| # | Tabel/Kolom | Masalah | Risiko | Severity | Rekomendasi |
|---|---|---|---|---|---|
| DB-01 | **SEMUA Models** (`$guarded = []`) | 21 model pakai empty guarded | Mass assignment — semua kolom bisa di-fill termasuk `id`, `created_at`, kolom sensitif | 🔴 Critical | Ganti ke `$fillable` eksplisit |
| DB-02 | `trx_barang_masuk_header.supplier_id` | FK `onDelete('cascade')` | Hapus supplier = hapus SEMUA riwayat transaksi masuk | 🔴 Critical | `onDelete('restrict')` |
| DB-03 | `trx_barang_keluar_header.pelanggan_id` | FK `onDelete('cascade')` | Hapus pelanggan = hapus SEMUA riwayat transaksi keluar | 🔴 Critical | `onDelete('restrict')` |
| DB-04 | `trx_transfer_header.lokasi_asal_id` & `lokasi_tujuan_id` | FK `onDelete('cascade')` | Hapus lokasi = hapus SEMUA transfer | 🔴 Critical | `onDelete('restrict')` |
| DB-05 | `mst_produk.total_stok` | Redundant aggregate — diupdate manual di banyak tempat | Drift dari actual jika ada bug | 🟠 Major | Jadikan computed dari `StokLokasi::sum('qty')` |
| DB-06 | `mst_produk.stok_masuk` & `stok_keluar` | Running counter tanpa reconciliation | Bisa drift, opname adjust juga menambah counter ini | 🟠 Major | Pertimbangkan hapus/computed |
| DB-07 | `trx_opname_header.lokasi_id` | FK `onDelete('cascade')` | Data opname historis hilang | 🟠 Major | `onDelete('restrict')` |
| DB-08 | `trx_stok_lokasi` & `trx_mutasi_lokasi` | FK produk `onDelete('cascade')` | Riwayat mutasi hilang jika produk dihapus | 🟠 Major | `onDelete('restrict')` |
| DB-09 | `trx_mutasi_lokasi` | Tidak ada index `(produk_id, lokasi_asal_id)` | Query lambat untuk riwayat mutasi | 🟡 Minor | Tambah composite index |
| DB-10 | `trx_barang_keluar_detail` | `resi_ekspedisi`, `kurir`, `is_qc_passed` di DETAIL bukan HEADER | Redundansi, seharusnya per-nota bukan per-item | 🟡 Minor | Pindah ke header |
| DB-11 | `mst_produk.lokasi` | String (default 'GDG-A01'), bukan FK ke `mst_lokasi` | Bisa berisi kode lokasi yang tidak exist | 🟡 Minor | Ganti ke FK `lokasi_id` |
| DB-12 | `analytics_produk` | 12 kolom bulanan terpisah (`penjualan_jan`..`des`) | Tidak support multi-tahun | 🟡 Minor | OK untuk saat ini |

---

## STEP 3 — REQUEST VALIDATION AUDIT

| # | Component | Field | Validasi Saat Ini | Masalah | Severity | Rekomendasi |
|---|---|---|---|---|---|---|
| V-01 | `Pembelian/Create.php` | `supplier_id` | `required\|exists` | Selalu required, padahal `Stock Awal` seharusnya nullable (Edit sudah benar) | 🟠 Major | Dinamis berdasarkan `tipe_nota` |
| V-02 | `Pembelian/Create.php` | `nomor_nota` | `required\|string\|max:100` | Tidak ada unique check → bisa duplikasi | 🟠 Major | Tambah `unique:trx_barang_masuk_header,nomor_nota` |
| V-03 | `Penjualan/Create.php` | `nomor_nota` | `required\|string\|max:100` | Tidak ada unique check | 🟠 Major | Tambah `unique:trx_barang_keluar_header,nomor_nota` |
| V-04 | `Audit/Index.php` | `updateQtyFisik()` | Hanya cast int, min 0 | Tidak validasi apakah sesi masih DRAFT | 🟠 Major | Tambah guard `if ($session->status !== 'DRAFT')` |
| V-05 | Semua component | Error messages | Default Laravel (English) | User-facing messages teknis | 🟡 Minor | Custom messages Bahasa Indonesia |

---

## STEP 4 — LIVEWIRE COMPONENT AUDIT

### 4.1 Public Property & State

| # | Component | Masalah | Severity |
|---|---|---|---|
| LW-01 | `Pembelian/Create.php` | `$suppliersList`, `$productsList` = full collection di public property → di-serialize setiap request | 🟠 Major |
| LW-02 | `Penjualan/Create.php` | `$productsList` = `Produk::with('stokLokasi.lokasi')` semua produk + relasi | 🟠 Major |
| LW-03 | `Audit/Index.php` | `$detailsList` = full array semua opname detail, bisa sangat besar | 🟠 Major |
| LW-04 | Semua Create/Edit | `$items` array public → client bisa manipulasi `produk_id`, `qty`, `hpp` | 🟠 Major (mitigated by server validation) |

### 4.2 Lifecycle — Heavy mount()/render()

| # | Component | Method | Masalah | Severity |
|---|---|---|---|---|
| LW-05 | `Pembelian/Create.php` | `mount()` | Loads ALL suppliers + ALL products + ALL locations | 🟠 Major |
| LW-06 | `Penjualan/Create.php` | `mount()` | Same + eager loads `stokLokasi.lokasi` semua produk | 🟠 Major |
| LW-07 | `Dashboard.php` | `render()` | 15+ separate queries tiap render, termasuk ReplenishmentService | 🟠 Major |
| LW-08 | `PickingManager.php` | `render()` | 6+ queries tiap render | 🟠 Major |
| LW-09 | `PackingManager.php` | `render()` | N+1 inside loop | 🟠 Major |

### 4.3 Authorization Gaps per Action

| # | Component | Method | Auth Check? | Severity |
|---|---|---|---|---|
| LW-10 | `Pembelian/Create.php` | `store()` | ❌ NONE | 🔴 Critical |
| LW-11 | `Penjualan/Create.php` | `store()` | ❌ NONE | 🔴 Critical |
| LW-12 | `Penjualan/Index.php` | Semua method | ❌ NONE | 🔴 Critical |
| LW-13 | `Transfer/Create.php` | `store()` | ❌ NONE | 🔴 Critical |
| LW-14 | `Transfer/Index.php` | Semua method | ❌ NONE | 🟠 Major |
| LW-15 | `PickingManager.php` | `startPicking()`, `scanBarcode()`, `completePicking()`, `generatePickingList()` | ❌ NONE | 🔴 Critical |
| LW-16 | `PackingManager.php` | `submitPacking()` | ❌ NONE | 🔴 Critical |
| LW-17 | `Audit/Index.php` | `updateQtyFisik()`, `updateCatatan()`, `processBarcodeScan()` | ❌ NONE | 🟠 Major |
| — | `Pembelian/Edit.php` | `mount()` | ✅ `hasPermissionTo` | OK |
| — | `StockMaster/Index.php` | CRUD | ✅ `Gate::authorize('manage master')` | OK |
| — | `UserManagement/Index.php` | Semua | ✅ `Gate::authorize('manage system')` | OK |

### 4.4 Event & Communication

| # | Pattern | Status |
|---|---|---|
| LW-18 | `HasAlerts` trait + `dispatch('alert:...')` | ✅ Konsisten di komponen yang menggunakannya |
| LW-19 | `$this->errorMessage` / `$this->successMessage` (property langsung) | ⚠️ Duplikasi — beberapa component pakai KEDUA pattern |
| LW-20 | `dispatch('play-beep')` / `dispatch('play-error-buzzer')` | ✅ Konsisten untuk barcode |

---

## STEP 5 — ERROR HANDLING & RESPONSE CONSISTENCY

| # | Skenario Error | Penanganan Saat Ini | Masalah | Severity | Rekomendasi |
|---|---|---|---|---|---|
| EH-01 | Database error (constraint, deadlock) | Tidak di-catch spesifik → `"Terjadi kesalahan: SQLSTATE..."` | Raw SQL error terekspose ke user | 🔴 Critical | Catch `QueryException` eksplisit, log, tampilkan pesan generik |
| EH-02 | Business logic error | `throw Exception(msg)` → `$this->errorMessage = "Terjadi kesalahan: " . $e->getMessage()` | Bisa expose internal info | 🟠 Major | Custom exception class |
| EH-03 | Alert format | 3 mekanisme berbeda: `$this->errorMessage`, `HasAlerts::alertError()`, `session()->flash()` | Inkonsisten | 🟡 Minor | Standardisasi ke HasAlerts |
| EH-04 | Silent fails | `completeSession()`, `cancelSession()` return diam jika precondition gagal | User tidak tahu kenapa gagal | 🟡 Minor | Tambah error message |
| EH-05 | Logging | Hanya `StockMaster/Index.php` pakai `Log::error()`. Service lain tidak ada logging | Error hilang tanpa trace | 🟠 Major | `Log::error()` di semua catch blocks |

---

## STEP 6 — BLADE/UI TEMPLATE AUDIT

### Komponen Reusable yang Sudah Ada

| Komponen | File | Status |
|---|---|---|
| `<x-sweetalert>` | `resources/views/components/sweetalert.blade.php` | ✅ Reusable |
| `<x-modal>` | `resources/views/components/modal.blade.php` | ✅ Reusable |
| `<x-sidebar>` | `resources/views/components/sidebar.blade.php` | ✅ Reusable (65KB, sangat besar) |
| `<x-input-error>` | `resources/views/components/input-error.blade.php` | ✅ Reusable |
| Button components | Various | ✅ Reusable |

### Komponen yang Perlu Di-Extract

| # | Kategori | Masalah | Severity | Rekomendasi |
|---|---|---|---|---|
| UI-01 | Status Badge | Hardcoded per view untuk transfer/picking/opname | 🟡 Minor | `<x-status-badge :status="$status" :type="'transfer'">` |
| UI-02 | Date Range Picker | Dipakai di 4+ views | 🟡 Minor | `<x-date-range-picker>` |
| UI-03 | Barcode Scanner Input | Dipakai di 4+ views | 🟡 Minor | `<x-barcode-scanner>` |
| UI-04 | Empty State | Kemungkinan berbeda-beda antar modul | 🔵 Cosmetic | Standardisasi |

> **CATATAN:** Blade views belum dibaca detail karena volume besar. Temuan berdasarkan pola di Livewire component.

---

## STEP 7 — SECURITY & AUTHORIZATION AUDIT

| # | Area | Temuan | Severity | Rekomendasi |
|---|---|---|---|---|
| SEC-01 | Mass Assignment | 21 model pakai `$guarded = []` → semua kolom bisa di-mass assign termasuk `id`, `created_at`, `hpp`, `total_stok` | 🔴 Critical | Ganti semua ke `$fillable` eksplisit |
| SEC-02 | Route-level Auth | Semua routes hanya `auth` + `verified`. TIDAK ada permission middleware per route | 🔴 Critical | Tambah `can:permission` per route group |
| SEC-03 | IDOR — BM Edit | `Edit.php`: mount($id) dari URL. Auth check ada, tapi tidak verifikasi ownership (user A bisa edit transaksi user B) | 🟠 Major | Policy-based ownership check |
| SEC-04 | IDOR — Transfer Detail | `Detail.php`: semua authenticated user bisa ship, receive, close transfer manapun | 🔴 Critical | Role/permission check |
| SEC-05 | IDOR — Opname | `updateQtyFisik(detailId, newQty)`: user bisa ubah qty opname MANAPUN hanya kirim ID lain | 🔴 Critical | Validasi detailId belongs to active session + auth |
| SEC-06 | IDOR — Picking | `scanBarcode()`, `verifyPickItem()`: tidak ada check per picking list | 🟠 Major | Permission check |
| SEC-07 | HPP Tampering | `items[].hpp` = public property, client bisa kirim HPP = 0. Validasi hanya `min:0` | 🟠 Major | Server-side HPP lookup dari PO/master |
| SEC-08 | File Upload | `StockMaster`: `mimes:xlsx,xls,csv\|max:10240` | ✅ Aman | — |
| SEC-09 | SQL Injection | Eloquent parameterized + `whereRaw` dengan binding | ✅ Aman | — |
| SEC-10 | Audit Log Coverage | Hanya `ubahBarangMasuk()` dan `hapusBarangMasuk()` buat AuditLog. Modul lain TIDAK ada | 🟠 Major | Extend ke semua operasi kritis |

---

## STEP 8 — PERFORMANCE AUDIT

| # | Lokasi Kode | Masalah | Dampak | Severity | Rekomendasi |
|---|---|---|---|---|---|
| PERF-01 | `Dashboard.php` `render()` | 15+ separate queries per render, termasuk `ReplenishmentService` nested queries | ~20+ DB queries per page load | 🟠 Major | Cache KPI, single aggregate queries |
| PERF-02 | `Dashboard.php` L78-89 | 12 separate `SUM()` queries untuk monthly sales | 12 queries → bisa 1 | 🟠 Major | Single query `SELECT SUM(jan), SUM(feb)...` |
| PERF-03 | `Pembelian/Create` `mount()` | `Produk::all()` loads semua produk ke public property | Payload bloat tiap request | 🟠 Major | Lazy-load via search (min 2 char) |
| PERF-04 | `Penjualan/Create` `mount()` | `Produk::with('stokLokasi.lokasi')->get()` semua produk + relasi | Massive payload | 🟠 Major | Lazy-load via search |
| PERF-05 | `PackingManager` `render()` | Loop `foreach` → `find()` per iteration | N+1 query | 🟠 Major | Pre-load batch |
| PERF-06 | `AnalyticsService::syncAndRecalculateProduct()` | Dipanggil per produk di loop `tambahBarangKeluar()` → triggers `recalculateAbcClassifications()` yang query SEMUA records | O(n²) | 🟠 Major | Batch di luar loop |
| PERF-07 | `StockEngineService` nomor nota | `ORDER BY desc LIMIT 1` tanpa locking → race condition concurrent | Duplicate nomor nota | 🟠 Major | DB sequence atau `SELECT FOR UPDATE` |
| PERF-08 | `Audit/Index` `createSession()` | `Produk::all()` → loop create 1 INSERT per produk | 1000 produk = 1000 queries | 🟠 Major | Bulk `OpnameDetail::insert()` |
| PERF-09 | `Transfer/Index` `render()` | 3 separate COUNT queries untuk statusCounts | Redundant | 🟡 Minor | Single `GROUP BY status` |
| PERF-10 | `PickingManager` `render()` | Multiple `whereNotExists()` subqueries | Complex render | 🟡 Minor | Refactor ke computed |

---

## STEP 9 — RANGKUMAN & REKOMENDASI

### 9.1 Ringkasan Eksekutif

| Severity | Jumlah |
|---|---|
| 🔴 **Critical** | **11** |
| 🟠 **Major** | **18** |
| 🟡 **Minor** | **9** |
| 🔵 **Cosmetic** | **1** |
| **Total** | **39 temuan** |

### 9.2 Top 5 Risiko Mendesak

1. 🔴 **Mass Assignment** (`$guarded = []` di 21 model) — semua kolom bisa dimanipulasi
2. 🔴 **Missing Authorization** — 7+ Livewire components tanpa permission check
3. 🔴 **Cascade Delete FK Historis** — hapus supplier/pelanggan = hapus semua transaksi
4. 🟠 **IDOR Vulnerabilities** — user bisa modify data transaksi milik user lain
5. 🟠 **Dashboard Performance** — 20+ queries per render tanpa caching

### 9.3 Audit Checklist

- [x] Business process aktual sudah dipetakan
- [x] Struktur database diperiksa (index, FK, nullable, redundansi)
- [x] Validasi request diperiksa
- [x] Livewire component diperiksa (public property, lifecycle, authorization)
- [x] Error handling diperiksa
- [x] Blade/UI template diperiksa (high-level)
- [x] Celah keamanan ditemukan (mass assignment, IDOR, auth gap)
- [x] N+1 query & masalah performa ditemukan
- [x] Rekomendasi diprioritaskan

---

## TASK LIST — PERBAIKAN BERDASARKAN PRIORITAS

### 🔴 SPRINT 1 — CRITICAL (Segera Dikerjakan)

- [x] **C-01** Ganti `$guarded = []` ke `$fillable` eksplisit di 21 model
  - **Effort:** Medium
  - **Files:** `app/Models/Produk.php`, `BarangMasuk.php`, `BarangMasukDetail.php`, `BarangKeluar.php`, `BarangKeluarDetail.php`, `PoHeader.php`, `PoDetail.php`, `Supplier.php`, `Pelanggan.php`, `Lokasi.php`, `StokLokasi.php`, `MutasiLokasi.php`, `TransferHeader.php`, `TransferDetail.php`, `OpnameHeader.php`, `OpnameDetail.php`, `PickingList.php`, `PickingDetail.php`, `AnalyticsProduk.php`, `AturanKode.php`, `AuditLog.php`
  - **Detail:** Definisikan kolom yang boleh di-fill secara eksplisit. Jangan masukkan `id`, `created_at`, `updated_at`.

- [x] **C-02** Tambahkan authorization di 7 Livewire component yang belum ada
  - **Effort:** Medium
  - **Files & Detail:**
    - `app/Livewire/Pembelian/Create.php` → tambah `Gate::authorize('input masuk')` di `store()` dan `mount()`
    - `app/Livewire/Penjualan/Create.php` → tambah `Gate::authorize('input keluar')` di `store()` dan `mount()`
    - `app/Livewire/Penjualan/Index.php` → tambah permission check
    - `app/Livewire/Transfer/Create.php` → tambah `Gate::authorize('manage transfer')` di `store()`
    - `app/Livewire/Outbound/PickingManager.php` → tambah auth di `startPicking()`, `scanBarcode()`, `completePicking()`, `generatePickingList()`
    - `app/Livewire/Outbound/PackingManager.php` → tambah auth di `submitPacking()`
    - `app/Livewire/Audit/Index.php` → tambah auth di `updateQtyFisik()`, `updateCatatan()`, `processBarcodeScan()`

- [x] **C-03** Ubah FK `onDelete('cascade')` ke `onDelete('restrict')` untuk tabel transaksi historis
  - **Effort:** Small
  - **Detail:** Buat migration baru yang mengubah:
    - `trx_barang_masuk_header.supplier_id` → `onDelete('restrict')`
    - `trx_barang_keluar_header.pelanggan_id` → `onDelete('restrict')`
    - `trx_transfer_header.lokasi_asal_id` → `onDelete('restrict')`
    - `trx_transfer_header.lokasi_tujuan_id` → `onDelete('restrict')`
    - `trx_opname_header.lokasi_id` → `onDelete('restrict')`

- [x] **C-04** Catch `QueryException` di semua try-catch blocks
  - **Effort:** Small
  - **Files:** Semua Livewire component yang punya `try-catch`
  - **Detail:** Catch `\Illuminate\Database\QueryException` secara eksplisit → `Log::error()` detail → tampilkan pesan generik "Terjadi kesalahan pada server. Silakan coba lagi atau hubungi administrator."

---

### 🟠 SPRINT 2 — MAJOR (Minggu Ini)

- [x] **M-01** Implementasi route-level middleware per group
  - **Effort:** Small
  - **File:** `routes/web.php`
  - **Detail:** Group routes berdasarkan permission: `can:input masuk` untuk barang masuk/keluar, `can:manage master` untuk stock master, `can:manage system` untuk user management, dll.

- [x] **M-02** Fix IDOR vulnerabilities
  - **Effort:** Medium
  - **Files:** `Audit/Index.php`, `Transfer/Detail.php`, `Pembelian/Edit.php`
  - **Detail:**
    - `updateQtyFisik()`: validasi `detailId` belongs to `$this->selectedSession` + session status = DRAFT
    - `Transfer/Detail.php`: validasi user punya permission untuk aksi spesifik (ship/receive/close)
    - `Pembelian/Edit.php`: pertimbangkan ownership check

- [x] **M-03** Refactor dropdown data ke lazy-load
  - **Effort:** Medium
  - **Files:** `Pembelian/Create.php`, `Penjualan/Create.php`, `Transfer/Create.php`
  - **Detail:** Ganti `$productsList = Produk::all()` di `mount()` ke `#[Computed]` property yang query berdasarkan search input (min 2 karakter). Gunakan Alpine.js debounce + `wire:model.live.debounce.300ms`.

- [x] **M-04** Tambah unique validation untuk nomor nota + fix race condition
  - **Effort:** Small
  - **Files:** `StockEngineService.php`, `Pembelian/Create.php`, `Penjualan/Create.php`
  - **Detail:**
    - Tambah `unique:trx_barang_masuk_header,nomor_nota` di rules Create
    - Tambah `unique:trx_barang_keluar_header,nomor_nota` di rules Create
    - Fix race condition: generate nomor nota di dalam transaction, bukan sebelumnya

- [x] **M-05** Extend audit logging ke semua modul kritis
  - **Effort:** Large
  - **Files:** `StockEngineService.php`, `TransferService.php`, `PickingService.php`, `StockMaster/Index.php`, `Audit/Index.php`
  - **Detail:** Catat AuditLog untuk: CREATE barang masuk, SEMUA operasi barang keluar, transfer (create/ship/receive), opname adjustment, stock master CRUD

- [x] **M-06** Optimize Dashboard queries
  - **Effort:** Medium
  - **File:** `app/Livewire/Dashboard.php`
  - **Detail:**
    - Gabungkan 12 SUM monthly sales jadi 1 query
    - Cache KPI yang jarang berubah (total produk, ABC class counts) selama 5 menit
    - Gunakan `#[Computed]` untuk data yang bisa di-cache

- [x] **M-07** Fix PackingManager N+1 query
  - **Effort:** Small
  - **File:** `app/Livewire/Outbound/PackingManager.php`
  - **Detail:** Pre-load semua `BarangKeluar` dan `TransferHeader` yang direferensi oleh completed pickings dalam 1 query, bukan `find()` per iterasi

- [x] **M-08** Bulk insert untuk Opname auto-populate
  - **Effort:** Small
  - **File:** `app/Livewire/Audit/Index.php`
  - **Detail:** Ganti loop `OpnameDetail::create()` per produk ke single `OpnameDetail::insert($records)` array

- [x] **M-09** Tambah MutasiLokasi record untuk Transfer receive
  - **Effort:** Small
  - **File:** `app/Services/TransferService.php` → `processReceive()`
  - **Detail:** Catat mutasi masuk ke lokasi tujuan saat receive, seperti yang sudah dilakukan saat shipment

- [x] **M-10** Fix supplier_id validation di Pembelian Create
  - **Effort:** Small
  - **File:** `app/Livewire/Pembelian/Create.php`
  - **Detail:** Ubah `$rules` jadi method `rules()` yang return conditional validation berdasarkan `$this->tipe_nota` (seperti Edit.php)

---

### 🟡 SPRINT 3 — MINOR (Sprint Berikutnya)

- [x] **N-01** Tambah composite index pada `trx_mutasi_lokasi`
  - **Effort:** Small
  - **Detail:** Migration baru: index `(produk_id, lokasi_asal_id)` dan `(produk_id, lokasi_tujuan_id)`

- [x] **N-02** Standardisasi alert pattern ke HasAlerts trait
  - **Effort:** Medium
  - **Files:** ~10 Livewire components
  - **Detail:** Migrate semua component ke `HasAlerts` trait, hapus duplikat `$successMessage`/`$errorMessage` property yang manual

- [x] **N-03** Batch ABC recalculation
  - **Effort:** Small
  - **Files:** `AnalyticsService.php`, `StockEngineService.php`
  - **Detail:** Panggil `recalculateAbcClassifications()` sekali di akhir transaksi, bukan per produk di dalam loop

- [x] **N-04** Optimize Transfer/Index count queries
  - **Effort:** Small
  - **File:** `app/Livewire/Transfer/Index.php`
  - **Detail:** Ganti 3 `COUNT` terpisah jadi single `SELECT status, COUNT(*) GROUP BY status`

- [x] **N-05** Custom validation messages Bahasa Indonesia
  - **Effort:** Small
  - **Files:** Semua Livewire component yang punya `$rules`
  - **Detail:** Tambah `$messages` property dengan pesan bahasa Indonesia yang user-friendly

- [x] **N-06** Extract reusable Blade components
  - **Effort:** Medium
  - **Detail:** Buat: `<x-date-range-picker>`, `<x-barcode-scanner>`, `<x-status-badge>`

- [x] **N-07** Fix silent fail di Audit completeSession/cancelSession
  - **Effort:** Small
  - **File:** `app/Livewire/Audit/Index.php`
  - **Detail:** Tambah `alertError()` saat precondition gagal

- [x] **N-08** Tambah `Log::error()` di semua service catch blocks
  - **Effort:** Small
  - **Files:** `StockEngineService.php`, `TransferService.php`, `PickingService.php`, `ReplenishmentService.php`

---

### 🔵 NICE-TO-HAVE (Backlog)

- [x] **B-01** Standardisasi empty state component
- [ ] **B-02** Normalisasi `analytics_produk` ke tabel bulanan (multi-year support)
- [x] **B-03** Implementasi fitur edit/delete barang keluar
- [x] **B-04** Pindahkan `resi_ekspedisi`/`kurir` ke header level atau tabel packing terpisah

---

## REFERENSI FILE CEPAT

| Modul | Files |
|---|---|
| **Stock Master** | `app/Livewire/StockMaster/Index.php`, `app/Models/Produk.php` |
| **Barang Masuk** | `app/Livewire/Pembelian/Create.php`, `Edit.php`, `Index.php`, `Riwayat.php`, `RiwayatTanggal.php` |
| **Barang Keluar** | `app/Livewire/Penjualan/Create.php`, `Index.php`, `Riwayat.php`, `RiwayatTanggal.php` |
| **Purchase Order** | `app/Livewire/PurchaseOrder/Manager.php` |
| **Transfer** | `app/Livewire/Transfer/Create.php`, `Index.php`, `Detail.php` |
| **Picking/Packing** | `app/Livewire/Outbound/PickingManager.php`, `PackingManager.php` |
| **Opname** | `app/Livewire/Audit/Index.php` |
| **Analytics** | `app/Livewire/Analytics/Index.php` |
| **User Management** | `app/Livewire/UserManagement/Index.php` |
| **Services** | `app/Services/StockEngineService.php`, `TransferService.php`, `PickingService.php`, `AnalyticsService.php`, `ReplenishmentService.php`, `SkuGeneratorService.php` |
| **Models** | `app/Models/*.php` (22 files) |
| **Routes** | `routes/web.php` |
| **Traits** | `app/Traits/HasAlerts.php` |
