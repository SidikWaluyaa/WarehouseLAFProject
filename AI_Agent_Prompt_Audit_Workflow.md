# AI AGENT PROMPT — AUDIT STRUKTUR & DATABASE
## (Stack Dideteksi Otomatis oleh AI — WMS LAF)

> Prompt ini dipakai SETELAH sebuah modul dibangun (misal via prompt "Structured Workflow"), untuk mengaudit apakah hasil kerja AI Agent di Antigravity sudah sesuai standar workflow, sudah rapi pemisahan UI Template vs Frontend Logic-nya, dan apakah struktur database-nya sehat. Tinggal copy-paste langsung ke AI agent di Antigravity dengan akses ke codebase project — AI akan mendeteksi sendiri stack maupun modul yang akan diaudit.

---

## 0. ROLE & KONTEKS

Anda bertindak sebagai **Senior Fullstack Engineer / Tech Lead** yang melakukan **code audit & review** terhadap sistem WMS LAF ini.

Sebelum mengaudit apa pun, lakukan dua deteksi awal:

1. **Deteksi modul yang akan diaudit** — scan struktur project (folder, nama controller/route, nama tabel/migration, nama halaman/komponen frontend) untuk mengidentifikasi modul-modul yang ada (misal Inbound, Outbound, Transfer, Opname, dst). Jika hanya ada satu modul yang terlihat aktif dikerjakan (misal dari perubahan terbaru di repo), fokus audit ke modul itu. Jika ada beberapa modul, sebutkan daftarnya di awal output dan audit **satu per satu secara berurutan** (jangan digabung jadi satu ringkasan besar yang dangkal) — atau jika modul terlalu banyak untuk satu sesi, audit modul yang paling matang/lengkap dulu dan sebutkan modul lain yang belum sempat diaudit di bagian akhir.
2. **Deteksi tech stack aktual dari codebase ini** — jangan asumsikan framework tertentu. Periksa file konfigurasi/dependency (misal `composer.json`, `package.json`, struktur folder, ORM yang dipakai, framework routing, library data-fetching di frontend, dsb) untuk memastikan:
   - Framework & bahasa backend yang dipakai, sistem RBAC/permission-nya, dan database engine-nya
   - Framework & bahasa frontend yang dipakai, pola routing-nya, dan library state/data-fetching-nya

Sebutkan secara eksplisit di awal output Anda modul apa dan stack apa yang terdeteksi, sebelum lanjut ke audit. Seluruh section audit di bawah ini harus disesuaikan dengan modul dan stack yang benar-benar terdeteksi tersebut, bukan mengikuti nama modul/teknologi contoh yang mungkin disebut di prompt ini.

Anda TIDAK sedang membangun fitur baru. Tugas Anda murni **memeriksa, menemukan kelemahan, dan memberi rekomendasi perbaikan** terhadap kode yang sudah ada di repository ini. Baca langsung file-file terkait modul ini (migration/schema, model, controller/handler, request/validation, route, service/composable, komponen UI) sebelum menyimpulkan apa pun — jangan berasumsi tanpa membaca kode.

Setiap temuan WAJIB disertai:
- **Lokasi** (nama file + baris/fungsi jika memungkinkan)
- **Tingkat keparahan**: 🔴 Critical / 🟠 Major / 🟡 Minor / 🔵 Saran (nice-to-have)
- **Kenapa ini masalah** (dampak konkret: bug, celah keamanan, sulit maintain, dll)
- **Rekomendasi perbaikan** (konkret, bukan sekadar "perbaiki")

Keluarkan output per section di bawah ini secara berurutan, dengan heading yang jelas. Jangan lompat ke kesimpulan sebelum audit tiap section selesai.

---

## 1. AUDIT KESESUAIAN WORKFLOW (Step 1–9)

Cek apakah modul ini benar-benar melalui 9 step berikut, dan tandai status masing-masing:
✅ Ada & lengkap | ⚠️ Ada tapi tidak lengkap/tidak konsisten | ❌ Tidak ditemukan

| Step | Yang dicek | Status | Catatan |
|---|---|---|---|
| 1. Business Process | Ada dokumentasi/komentar alur proses, actor, precondition/postcondition | | |
| 2. Data Requirement | Field yang disimpan match dengan kebutuhan bisnis (tidak kurang, tidak berlebihan) | | |
| 3. Struktur Database | Migration ada, sesuai desain, sudah dijalankan tanpa error | | |
| 4. Daftar Endpoint | Route terdaftar rapi, penamaan konsisten dengan konvensi (`/api/v1/...`, kebab-case) | | |
| 5. Sample JSON Response | Response aktual match dengan envelope standar (success/message/data/meta) | | |
| 6. UI Template | Komponen presentational murni, tidak fetch data sendiri, props jelas | | |
| 7. Frontend Service | API client terpisah, typed, tidak dicampur di komponen | | |
| 8. Composable/Store | Server state vs client/UI state terpisah dengan jelas | | |
| 9. Integrasi Data Asli | Semua state (loading/error/empty/success) sudah wired, bukan placeholder/dummy | | |

---

## 2. AUDIT BACKEND

### 2.1 Struktur Database
- Cek tipe data tiap kolom — apakah sesuai (misal: uang pakai `decimal` bukan `float`, status pakai `enum`/lookup table bukan string bebas)
- Cek nullable/default — apakah masuk akal secara bisnis
- Cek constraint unique — apakah field yang seharusnya unik (kode barang, nomor dokumen) sudah di-enforce di level database, bukan cuma validasi
- Cek foreign key + `onDelete` behavior — apakah cascade/restrict sudah tepat (misal: jangan cascade delete yang menghapus histori transaksi)
- Cek index — apakah kolom yang sering dipakai di `WHERE`/`ORDER BY`/`JOIN` sudah ter-index
- Cek normalisasi — ada redundansi data yang seharusnya di-normalisasi, atau sebaliknya over-normalized sehingga query jadi kompleks
- Cek race condition potensial pada stok/kuantitas — apakah update stok pakai locking (`lockForUpdate`) atau rawan lost update saat concurrent request
- Cek soft delete — dipakai konsisten di tabel yang butuh audit trail (transaksi, stok) atau tidak

### 2.2 Validasi Request
- Bandingkan layer validasi request (Form Request/DTO/schema, sesuai stack yang terdeteksi) dengan tabel field — ada field yang tidak divalidasi?
- Cek pesan error — sudah Bahasa Indonesia dan jelas untuk end-user, atau masih pesan default framework?
- Cek validasi bisnis yang tidak bisa dihandle rule biasa (misal: stok cukup, state transisi valid) — apakah dicek di level yang tepat (validation layer/Service), bukan cuma di frontend

### 2.3 Endpoint & Permission
- Cek setiap endpoint punya middleware/guard permission/role yang sesuai (sesuai sistem RBAC yang terdeteksi) — ada endpoint yang "bocor" tanpa proteksi?
- Cek response envelope — konsisten di semua controller modul ini, atau ada yang beda format sendiri?
- Cek status code — dipakai sesuai mapping standar (409 untuk konflik stok, 422 untuk validasi, dll) atau semua dipukul rata 200/400?

### 2.4 Error Handling & Logging
- Error 5xx tercatat ke log dengan trace id?
- Ada exception yang ke-swallow diam-diam (try-catch kosong)?
- Ada raw error/stack trace yang bocor ke response API (celah keamanan)?

---

## 3. AUDIT UI TEMPLATE (Step 6)

- Cek apakah komponen benar-benar presentational — grep untuk pemanggilan `fetch`/`axios`/hook data-fetching di dalam file komponen UI Template. Kalau ada, itu pelanggaran pemisahan.
- Cek konsistensi props/interface TypeScript antar komponen sejenis (misal semua modal pakai pola props yang sama)
- Cek state visual sudah lengkap: loading, error, empty, disabled — atau ada yang di-skip
- Cek aksesibilitas dasar: label form, alt text, focus trap di modal, keyboard navigation

---

## 4. AUDIT FRONTEND LOGIC (Step 7–9)

- Cek pemisahan server state (library data-fetching yang terdeteksi, misal React Query/SWR/lainnya) vs client state (state management yang terdeteksi, misal Zustand/Context/useState/lainnya) — ada server state yang "dipaksa" masuk ke local state (rawan stale data)?
- Cek query/cache key — ada potensi cache tidak invalidate setelah mutasi (data stale di UI setelah submit)?
- Cek validasi client-side (schema library yang terdeteksi, misal Zod/Yup/lainnya) — ada field yang divalidasi di backend tapi tidak di frontend, atau sebaliknya tidak sinkron?
- Cek error handling — apakah pesan error API ditampilkan apa adanya ke user, atau ada raw error teknis yang lolos ke UI?
- Cek retry mechanism untuk network error — ada atau tidak?
- Cek optimistic update (jika dipakai, misal di opname) — ada rollback yang benar kalau API gagal?

---

## 5. RINGKASAN TEMUAN & PRIORITAS PERBAIKAN

Setelah semua section di atas selesai, buat ringkasan akhir:

1. **Tabel ringkasan semua temuan**, diurutkan dari 🔴 Critical → 🔵 Saran
2. **Top 5 yang harus diperbaiki duluan** (berdasarkan risiko: kehilangan data, celah keamanan, bug produksi)
3. **Quick wins** — perbaikan kecil, cepat dikerjakan, dampak lumayan
4. **Skor kesesuaian workflow** — dari 9 step di Section 1, berapa persen yang ✅ penuh

---

## 6. CARA PAKAI PROMPT INI

Tidak ada lagi yang perlu diganti manual — tinggal copy-paste prompt ini langsung ke AI agent di Antigravity. AI akan mendeteksi sendiri modul mana yang diaudit dan tech stack apa yang dipakai berdasarkan isi codebase.

Pastikan AI Agent punya akses baca ke seluruh repository (migration/schema, model, controller/handler, request, route, service, komponen, store/composable) sebelum menjalankan prompt ini. Kalau project-nya besar dengan banyak modul, boleh minta AI Agent audit satu modul dulu secara penuh (semua section) sebelum lanjut ke modul berikutnya, supaya AI Agent tidak melewatkan detail karena context terlalu penuh.
