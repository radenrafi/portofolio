# PLAN_FITUR_GURU: Manajemen Siswa oleh Guru

## Tujuan
Memungkinkan guru untuk membuat, mengelola, dan memantau akun siswa, termasuk pengelolaan dasar terkait tugas/nilai/feedback (landasan untuk fitur berikutnya).

## Lingkup
- CRUD akun siswa oleh guru.
- Listing, pencarian, filter, dan sortir siswa.
- Reset password dan deaktivasi/aktivasi akun.
- (Opsional) impor massal siswa via CSV.
- Landasan integrasi ke tugas, pengumpulan, nilai, dan feedback.

## User Stories
- Sebagai guru, saya dapat membuat akun siswa baru dengan email dan nama.
- Sebagai guru, saya dapat melihat daftar siswa dan memfilter berdasarkan kelas/status.
- Sebagai guru, saya dapat memperbarui profil siswa dan mereset password mereka.
- Sebagai guru, saya dapat menonaktifkan/megaktifkan kembali akun siswa.
- (Opsional) Sebagai guru, saya dapat mengimpor siswa secara massal dari CSV.

## Model & Skema Data (awal)
- users
  - id (PK), name, email (unik), password (hash), role enum {teacher, student}, status enum {active, inactive}
  - timestamps
- (opsional tahap ini) classes: id, name, homeroom_teacher_id
- (opsional) user_class pivot jika diperlukan

## Endpoint API (v1)
Semua endpoint privat memerlukan `Authorization: Bearer <token>` dan role `teacher`.

Catatan:
- Endpoint autentikasi didefinisikan di `plans/PLAN_FITUR_AUTH.md`.
- Super Admin juga dapat mengelola Siswa melalui namespace admin (`/api/v1/admin/students/*`), lihat `plans/PLAN_FITUR_SUPER_ADMIN.md`.

Manajemen Siswa
- POST /api/v1/students
  - Body: { name, email, password|generated, status? }
  - Buat akun siswa; email unik, role dipaksa `student`.
- GET /api/v1/students
  - Query: page, per_page, q, status, class_id?
  - Res: paginated list
- GET /api/v1/students/{id}
- PUT /api/v1/students/{id}
  - Body: { name?, email?, status? }
- PATCH /api/v1/students/{id}/reset-password
  - Body: { password? } (jika tidak disediakan, generate dan kembalikan)
- PATCH /api/v1/students/{id}/deactivate
- PATCH /api/v1/students/{id}/activate
- (Opsional) POST /api/v1/students:import
  - Content-Type: multipart/form-data (file CSV)

 

## Validasi & Aturan Bisnis
- Email unik, format valid.
- Password minimal 8, dikonfirmasi (kecuali di-generate).
- Hanya guru yang boleh akses; policy/gate wajib.
- Tidak boleh mengubah role siswa menjadi teacher melalui endpoint ini.

## Keamanan
- Sanctum token; middleware auth:sanctum.
- Rate limiting pada endpoint mutasi.
- Audit sederhana (log) untuk create/update/reset/deactivate.

## Acceptance Criteria
- Guru dapat membuat siswa baru dan siswa tampil di listing.
- Pencarian/filter/pagination berfungsi konsisten.
- Update profil siswa dan perubahan tersimpan.
- Reset password mengembalikan password baru (jika di-generate) dan menulis log.
- Deaktivasi/aktivasi mengubah status dan memblokir login saat inactive.
- Semua endpoint memiliki test (feature) dan validasi error yang jelas.

## Langkah Implementasi
1) Routing (routes/api.php): grup `/api/v1` dengan middleware `auth:sanctum` + gate teacher.
2) Model & Migration: tambahkan kolom `role` dan `status` di `users` bila belum ada; enum/konstanta di model.
3) Policy/Gate: izinkan aksi hanya untuk guru.
4) Controller `StudentController` (index, show, store, update, resetPassword, activate, deactivate, import[opsional]).
5) Request Validation (FormRequest) per aksi.
6) Resource/Transformer untuk respons konsisten.
7) Logging/Audit event penting.
8) Tests: Feature tests untuk semua endpoint + kasus gagal validasi/otorisasi.
9) Dokumentasi singkat endpoint (OpenAPI/collection Postman).

## Tugas Terperinci
- [Routing] Tambah rute students (v1) dan middleware.
- [Model] Tambah konstanta ROLE_STUDENT/ROLE_TEACHER, STATUS_ACTIVE/INACTIVE.
- [Migration] Tambah kolom `role` (default student), `status` (default active) bila belum ada.
- [Controller] Implementasi CRUD + actions (reset, activate/deactivate).
- [Validation] FormRequest untuk store/update/reset.
- [Security] Gate/policy dan rate-limit mutasi.
- [Tests] Feature tests untuk semua endpoint + pagination/filter.
- [Docs] Draft Postman/OpenAPI subset untuk fitur guru.

## Dependensi
- Fitur Auth & Role (Sanctum) harus tersedia (lihat `plans/PLAN_FITUR_AUTH.md`).
- Struktur tabel users sesuai kebutuhan (role, status).

## Di Luar Ruang Lingkup (saat ini)
- Integrasi kelas/mata pelajaran mendalam.
- Penilaian/feedback detail (akan ada plan terpisah: PLAN_FITUR_NILAI.md, PLAN_FITUR_FEEDBACK.md).
- Materi/pengumuman.

## Catatan Implementasi Laravel
- Gunakan `Route::middleware(['auth:sanctum', 'ability:teacher'])` atau policy untuk membatasi akses.
- Pertimbangkan `UserFactory` untuk membuat data uji guru/siswa.
- Gunakan `php artisan make:policy` dan `php artisan make:request` untuk scaffolding.
