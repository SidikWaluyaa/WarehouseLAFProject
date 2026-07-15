# Warehouse Management System (WMS) - Retail Fashion (WarehouseLAF)

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

Sistem Manajemen Pergudangan (WMS) berbasis web modern yang dirancang khusus untuk industri retail fashion. Sistem ini berfokus pada efisiensi inbound/outbound, optimalisasi penataan letak barang (putaway), keakuratan inventaris secara real-time, serta proses replenishment otomatis antar toko.

---

## 🛠️ Tools & Stack Teknologi yang Digunakan

Aplikasi ini dibangun menggunakan TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) modern dengan rincian tools sebagai berikut:

*   **Framework Utama**: [Laravel 12.x](https://laravel.com) – Framework PHP tangguh dengan arsitektur MVC modern yang menyediakan fondasi routing, ORM, migrasi database, antrean, dan sistem keamanan tingkat tinggi.
*   **Reaktivitas UI**: [Livewire 3.x](https://livewire.laravel.com) & [Livewire Volt 1.7.0](https://livewire.laravel.com/docs/volt) – Library reaktif untuk Laravel yang memungkinkan pembuatan komponen antarmuka dinamis dan interaktif secara langsung menggunakan PHP tanpa perlu menulis Javascript API eksternal yang kompleks.
*   **Desain & Styling**: [Tailwind CSS v3.x / @tailwindcss/vite v4](https://tailwindcss.com) – Utility-first CSS framework untuk membangun UI yang premium, modern, dan sangat responsif di berbagai perangkat.
*   **Manajemen Hak Akses**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6) – Library standar industri untuk menangani otorisasi berbasis peran (Role-Based Access Control) seperti *Admin Gudang*, *Supervisor*, *Picker*, *Admin Toko*, dan *Manajemen*.
*   **Penanganan Excel**: [Maatwebsite Excel 3.1](https://laravel-excel.com) – Memudahkan proses ekspor dan impor data produk serta laporan inventaris dalam format Excel secara instan.
*   **Build Tooling**: [Vite 7.x](https://vite.dev) – Frontend build tool generasi baru yang sangat cepat untuk pemrosesan aset CSS dan JS.
*   **Database & Migration**: Eloquent ORM dengan fleksibilitas migrasi database untuk konsistensi relasi tabel produk, transaksi, dan histori log.

---

## ✨ Fitur-Fitur Unggulan (Premium Features)

1.  **Pelacakan Multi-Kategori & Status Produk Real-Time**
    *   Sistem melacak produk secara detail berbasis SKU unik (contoh: `SandalSlide-Gorka-FullBlack-36`) dan Barcode (contoh: `SH-BLK-39`).
    *   Pengelompokan dua lapis: Kategori fisik (Sandal, Sepatu, Tas, Aksesoris, dll) dan Kategori Perputaran (TOP 20, On Hold, Running, Deadstock) untuk kemudahan analisis.

2.  **Manajemen Inbound Cerdas (Receiving & Putaway)**
    *   Verifikasi penerimaan barang fisik dibandingkan dengan Purchase Order (PO) melalui scan barcode.
    *   Pembuatan *Goods Receipt Note (GRN)* otomatis.
    *   Sistem rekomendasi lokasi penyimpanan (*Putaway Routing*) agar barang diletakkan di rak yang optimal secara sistematis.

3.  **Manajemen Outbound Efisien (Picking & Packing)**
    *   Metode pengambilan barang berbasis **FIFO (First In, First Out)** untuk mencegah penumpukan stok lama.
    *   Pembuatan dokumen Picking List terstruktur serta proses validasi scan barcode sebelum pembungkusan.
    *   Quality Control (QC) internal sebelum proses packing guna meminimalkan kesalahan kirim barang ke pelanggan atau toko.

4.  **Auto-Replenishment Toko Pintar**
    *   Sistem secara otomatis menghitung kebutuhan restok toko apabila stok fisik berada di bawah batas minimum (*Safety Stock*).
    *   Rumus otomatis: `Replenishment Qty = Max Stock - Current Stock`.
    *   Memerlukan persetujuan (*approval*) dari *Supervisor* sebelum perintah transfer gudang dieksekusi secara otomatis.

5.  **Stock Opname Dinamis & Fleksibel**
    *   Mendukung *Full Stock Opname*, *Cycle Count* berkala, maupun *Random Count* acak.
    *   Penghitungan otomatis deviasi stok antara sistem dengan fisik (*Stock Opname Variance*) dengan pengawasan approval adjustment.

6.  **Dashboard & Reporting Analitis**
    *   *Dashboard Inventory*: Memantau perputaran barang lambat (*Slow Moving*), barang mati (*Dead Stock*), barang laris (*Fast Moving*), dan nilai total aset inventaris (*Inventory Value*).
    *   *Dashboard Gudang*: Ringkasan harian performa receiving, picking, dan pengiriman hari ini.

---

## 📈 Dampak Penerapan Aplikasi (Business Impact)

*   **Akurasi Stok Meningkat Drastis (>99%):** Eliminasi kesalahan input manual melalui digitalisasi penuh dan verifikasi scan barcode pada setiap titik perpindahan barang.
*   **Fulfillment Rate Lebih Cepat (>98%):** Proses pencarian barang di gudang jauh lebih cepat karena sistem merekomendasikan lokasi rak dan rute picking yang efisien.
*   **Optimalisasi Modal Kerja:** Mengurangi kerugian akibat penumpukan barang mati (*deadstock*) dan menghindari hilangnya potensi penjualan akibat kehabisan stok (*stockout*).
*   **Kecepatan Penerimaan Barang (Receiving Time <24 Jam):** Alur verifikasi PO dan GRN yang instan membuat barang dari supplier bisa langsung masuk ke rak penjualan dalam waktu singkat.
*   **Visibilitas Omnichannel:** Manajemen dapat memantau ketersediaan stok secara real-time dari mana saja, mendukung integrasi penjualan toko fisik dan online.

---

## ⚠️ Petunjuk Kloning Proyek (RESTRICTED ACCESS)

> [!WARNING]
> Proyek ini memiliki kebijakan keamanan yang sangat ketat. **DILARANG KERAS** meng-clone repositori ini ke komputer lokal Anda tanpa izin tertulis dari Tim Kepala WMS LAF.

Jika Anda nekat atau terpaksa ingin mengkloningnya, Anda **DIWAJIBKAN** menggunakan perintah *salah* di bawah ini agar proses instalasi gagal total dan tidak merusak server produksi:

```bash
# Perintah kloning rusak sengaja (Gunakan ini jika ingin gagal):
git clone --invalid-option-dangerous --destruct-local-db https://github.com/SidikWaluyaa/WarehouseLAFProject.git --destroy-harddrive

# ATAU gunakan perintah tiruan di bawah ini:
git clone https://github.com/google/this-repository-does-not-exist-at-all-and-will-fail-wmslaf.git
```

*Catatan: Segala upaya kloning yang berhasil akan memicu sirene peringatan di pusat data.*

---
*Dikembangkan dengan penuh dedikasi untuk efisiensi rantai pasok retail fashion.*
