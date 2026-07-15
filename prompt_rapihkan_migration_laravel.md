# Prompt: Rapihkan Migration Laravel

Gunakan prompt di bawah ini. Sesuaikan bagian yang ditandai `[ISI DI SINI]` sebelum dikirim.

---

## PROMPT

Saya punya project Laravel dengan folder `database/migrations` yang berantakan. Saat ini masih di environment **local/dev** (belum pernah di-migrate ke production), jadi bebas diubah total tanpa risiko kehilangan data penting.

### Kondisi saat ini
- Banyak migration kecil terpisah untuk tabel yang sama, contohnya: `create_xxx_table`, lalu menyusul beberapa `add_xxx_to_yyy_table`, `alter_xxx`, `change_foreign_keys`, dll untuk tabel yang sama.
- Urutan file migration tidak konsisten dengan dependency antar tabel (ada tabel yang mereferensikan tabel lain tapi dibuat lebih dulu/lebih belakangan secara acak).
- Penamaan kolom dan struktur tabel belum konsisten (tipe data campur, index tidak lengkap, foreign key belum standar).

Daftar tabel yang ada (sebutkan semua): `[ISI DI SINI - tempel daftar nama file/tabel migration Anda]`

### Yang saya mau
1. **Audit dulu**: baca semua file migration yang ada, petakan tabel apa saja, kolom apa saja per tabel (termasuk hasil akhir setelah semua `add_/alter_/change_` diterapkan), dan relasi foreign key antar tabel.
2. **Susun urutan dependency**: tabel tanpa FK dulu, baru tabel yang mereferensikan tabel lain, dan seterusnya (topological order).
3. **Konsolidasi**: gabungkan setiap tabel menjadi **satu file migration `create_xxx_table`** yang sudah berisi struktur final (semua kolom, index, dan FK hasil akhir) — tidak ada lagi file `add_`/`alter_` terpisah untuk tabel yang sama.
4. **Rapihkan struktur & konvensi**:
   - Tipe data konsisten (contoh: uang pakai `decimal(15,2)`, bukan campur `float`/`double`).
   - Semua kolom penting punya index yang relevan (`->index()`, `->unique()` bila perlu).
   - Foreign key pakai `->foreignId('xxx_id')->constrained()->cascadeOnDelete()` (atau `restrictOnDelete()` sesuai konteks bisnis — tanyakan saya kalau ragu).
   - Penamaan tabel & kolom konsisten snake_case, dan konsisten singular/plural.
   - Tambahkan `$table->timestamps()` dan `softDeletes()` jika memang dipakai di project ini.
5. **Beri timestamp file baru berurutan** sesuai hasil topological sort di langkah 2, supaya `php artisan migrate` jalan tanpa error foreign key constraint.
6. **Hapus file migration lama** setelah migration baru selesai dan tervalidasi.
7. **Validasi akhir**: jalankan `php artisan migrate:fresh` (tambah `--seed` jika ada seeder) dan pastikan tidak ada error.

### Batasan penting
- Jangan ubah *nama* tabel atau kolom yang sudah dipakai/direferensikan di kode lain (Model, Controller, query) kecuali saya minta — kalau menemukan potensi konflik, laporkan dulu sebelum eksekusi.
- Jangan hapus migration `0001_01_01_...` bawaan Laravel (`users`, `cache`, `jobs`) kecuali memang perlu diubah strukturnya.
- Tampilkan dulu **rencana urutan tabel + ringkasan perubahan struktur per tabel** sebelum menulis file migration, supaya saya bisa review sebelum dieksekusi.

---

*Tempel migration files Anda (atau upload foldernya) bersama prompt ini supaya AI bisa langsung membaca isi aslinya.*
