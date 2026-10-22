# Prompt: QA & Security Audit — Laravel Project (Pre-Production)

> Gunakan prompt ini untuk meminta AI (Claude, ChatGPT, Antigravity, dll) atau reviewer manusia melakukan audit menyeluruh terhadap codebase Laravel sebelum go-live. Tempel/paste isi di bawah ini sebagai instruksi, lalu lampirkan/berikan akses ke codebase yang ingin diaudit.

---

## PROMPT

Kamu bertindak sebagai **Senior QA Engineer & Security Auditor** yang berpengalaman mengaudit aplikasi Laravel untuk kesiapan production.

**Jangan berasumsi apa pun soal stack/framework yang dipakai.** Sebelum masuk ke tahap audit, lakukan dulu **Tahap 0: Identifikasi Stack** di bawah, dan gunakan hasilnya sebagai konteks untuk seluruh audit selanjutnya.

Lakukan audit menyeluruh dan berikan laporan terstruktur dengan tingkat keparahan (Critical / High / Medium / Low) untuk setiap temuan. Jangan hanya bilang "sudah bagus" — telusuri kode secara konkret, kutip file/line yang bermasalah, dan berikan rekomendasi perbaikan yang actionable.

### Tahap 0: Identifikasi Stack (wajib dilakukan dulu, sebelum audit)
- Cek `composer.json` untuk versi Laravel dan package utama yang dipakai (misal: Livewire, Inertia, Filament, Nova, Voyager, spatie/laravel-permission, dll).
- Cek `package.json` untuk frontend stack (Blade murni, Alpine.js, Vue, React, Inertia, dll) dan build tool (Vite/Mix).
- Cek struktur folder `app/` (ada `Livewire/`, `Http/Controllers/Api/`, `Filament/`, dll) untuk memastikan pola arsitektur yang dipakai.
- Cek `routes/` (web.php, api.php, admin.php jika ada) untuk memahami bagaimana area admin dan customer dipisahkan.
- Cek bagaimana admin panel dibangun — apakah pakai package admin panel siap pakai, atau custom Blade/Controller buatan sendiri.
- Cek bagaimana RBAC diimplementasikan — package tertentu (`spatie/laravel-permission`, dll), kolom `role` manual di tabel users, atau pendekatan lain.
- **Laporkan hasil identifikasi ini di awal laporan audit**, sebagai dasar konteks sebelum masuk ke temuan-temuan berikutnya. Jangan lanjut ke tahap audit berikutnya dengan asumsi stack yang tidak terverifikasi dari kode.

Setelah stack teridentifikasi, lanjutkan audit mencakup 6 area berikut (sesuaikan poin-poin di bawah dengan stack aktual yang ditemukan di Tahap 0 — lewati poin yang tidak relevan, dan tambahkan poin khusus bila ada teknologi lain yang terdeteksi):

### 1. Production Readiness (Environment & Konfigurasi)
- Pastikan `APP_DEBUG=false` dan `APP_ENV=production` di environment production.
- Cek `.env` tidak ter-commit ke repository (ada di `.gitignore`).
- Cek tidak ada credential, API key, atau secret yang hardcoded di kode.
- Cek konfigurasi cache (config, route, view) sudah di-cache dengan benar (`php artisan config:cache`, `route:cache`, `view:cache`).
- Cek queue worker & scheduler (cron) sudah dikonfigurasi dengan benar bila dipakai.
- Cek error handling — pastikan error production tidak menampilkan stack trace ke user, dan sudah terhubung ke error tracking (Sentry/Bugsnag/log channel).
- Cek storage & file permission (`storage/`, `bootstrap/cache/`) sudah benar dan `storage:link` sudah dijalankan bila pakai public disk.
- Cek strategi backup database sudah ada.
- Cek dependency (`composer.json`, `package.json`) tidak memakai versi `dev-master` atau package yang sudah deprecated/tidak maintained.

### 2. Code Quality
- Konsistensi struktur: apakah logic bisnis menumpuk di Controller (fat controller) atau sudah dipisah ke Service/Action/Repository class?
- Penggunaan Eloquent yang benar: cek N+1 query problem (kurangnya eager loading `with()`), query di dalam loop/blade view.
- Validasi input: pastikan semua form (termasuk Livewire component) memakai Form Request atau validasi eksplisit, bukan hanya validasi di frontend.
- Cek duplikasi kode yang seharusnya bisa di-refactor jadi trait/helper/service.
- Cek penamaan variabel, class, dan method konsisten dengan konvensi Laravel (PSR-12).
- Cek ada test coverage minimal untuk fitur kritis (checkout, auth, pembayaran manual/verifikasi admin) — unit test & feature test.
- Cek penggunaan job/queue untuk proses berat (kirim email, generate laporan) alih-alih blocking request.

### 3. Keamanan (Security) — OWASP-based
- **SQL Injection**: pastikan tidak ada raw query (`DB::raw`, `whereRaw`) yang menerima input user tanpa binding parameter.
- **XSS**: cek penggunaan `{!! !!}` (unescaped output) di Blade — pastikan hanya dipakai untuk konten yang sudah di-sanitize.
- **CSRF**: pastikan semua form yang render tradisional memakai `@csrf`, dan endpoint API yang stateful terlindungi.
- **Mass Assignment**: cek model Eloquent — pastikan `$fillable`/`$guarded` diset dengan benar, terutama pada field sensitif (role, is_admin, status pembayaran).
- **Authorization/IDOR**: cek setiap endpoint yang mengambil resource by ID (misal `/pesanan/{id}`) sudah memverifikasi kepemilikan data (policy/gate), bukan cuma cek login.
- **File Upload**: cek validasi tipe file, ukuran, dan penyimpanan (tidak bisa diakses/dieksekusi langsung sebagai script) untuk fitur upload (misal bukti transfer bank).
- **Rate Limiting**: cek endpoint sensitif (login, register, form checkout, verifikasi admin) sudah dibatasi (`throttle` middleware) untuk mencegah brute force.
- **Password & Auth**: cek hashing password pakai bcrypt/argon (default Laravel), ada mekanisme lockout/2FA untuk akun admin.
- **Bila memakai Livewire**: cek tidak ada data sensitif yang di-expose lewat public property Livewire tanpa disadari (public property Livewire bisa dimanipulasi dari client).
- **Bila memakai Inertia/SPA**: cek data yang dikirim ke props frontend tidak membocorkan field sensitif (password hash, token, dll), dan endpoint backend tetap divalidasi di server meski frontend sudah filter.
- **Admin panel (apa pun bentuknya)**: cek area admin tidak bisa diakses tanpa autentikasi, dan setiap resource/route admin punya otorisasi (policy/gate/middleware) — bukan cuma disembunyikan dari UI/menu.
- Cek header keamanan HTTP (CSP, X-Frame-Options, HSTS) sudah dikonfigurasi di level web server/middleware.
- Cek dependency vulnerability (`composer audit`, `npm audit`).

### 4. RBAC (Role-Based Access Control)
- Sebutkan role apa saja yang ada di sistem (misal: super admin, admin, staff gudang, customer) dan pastikan pemetaannya jelas dan terdokumentasi.
- Cek implementasi RBAC sesuai temuan Tahap 0 (package permission, kolom role manual, atau pendekatan lain). Evaluasi apakah pendekatannya konsisten di seluruh aplikasi.
- Cek setiap route/controller/halaman admin (apa pun cara pembuatannya) punya otorisasi (`authorize()`/Policy/middleware) yang sesuai role — bukan hanya disembunyikan di UI (hidden di navigasi/menu) tapi endpoint-nya tetap bisa diakses langsung kalau URL-nya diketahui.
- Cek tidak ada privilege escalation: user dengan role rendah tidak bisa mengubah role dirinya sendiri atau user lain lewat form/API.
- Cek middleware route group sudah benar memisahkan area admin dan customer.
- Cek audit trail/log untuk aksi sensitif oleh admin (misal verifikasi pembayaran, ubah status pesanan) — siapa yang melakukan apa dan kapan.

### 5. Performance & Skalabilitas
- Cek indexing database pada kolom yang sering dipakai untuk `WHERE`, `JOIN`, `ORDER BY` (terutama tabel besar seperti produk, pesanan).
- Cek penggunaan cache (Redis/file) untuk data yang jarang berubah (kategori, konfigurasi).
- Cek asset frontend (Tailwind, Alpine) sudah di-build & di-minify untuk production (`npm run build`), bukan mode dev.
- Cek pagination dipakai di semua listing data (bukan load semua data sekaligus).
- Cek image/asset produk sudah dioptimasi (ukuran, lazy loading).

### 6. Checklist Go-Live
Tutup laporan dengan checklist ringkas format tabel (✅/❌/⚠️) yang merangkum semua temuan di atas, plus rekomendasi urutan prioritas perbaikan sebelum deploy ke production.

---

## Format Output yang Diharapkan

1. **Hasil Identifikasi Stack (Tahap 0)** — ringkasan teknologi yang benar-benar terdeteksi di kode: versi Laravel, frontend stack, admin panel, cara RBAC diimplementasikan.
2. **Ringkasan Eksekutif** — kesimpulan singkat: siap production atau belum, dan alasan utama.
3. **Temuan Detail per Kategori** (1–5 di atas) — masing-masing dengan severity, lokasi kode, penjelasan risiko, dan rekomendasi fix.
4. **Checklist Go-Live** — tabel ringkas.
5. **Prioritas Perbaikan** — urutan apa yang harus dikerjakan dulu sebelum deploy.
