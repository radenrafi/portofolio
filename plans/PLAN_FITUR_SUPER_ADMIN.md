# PLAN_FITUR_SUPER_ADMIN: Administrasi Sistem & Guru

## Tujuan
Menyediakan peran Super Admin yang memiliki hak administrasi sistem menyeluruh, termasuk pembuatan dan pengelolaan akun Guru. Data Super Admin bersifat statik (dibuat melalui seeder) dan tidak dapat dibuat melalui endpoint publik.

## Lingkup
- Manajemen akun Guru oleh Super Admin (CRUD, reset password, aktivasi/deaktivasi).
- Manajemen akun Siswa oleh Super Admin (CRUD, reset password, aktivasi/deaktivasi).
- Listing, pencarian, dan filter Guru & Siswa.
- Aturan perlindungan: tidak bisa menonaktifkan diri sendiri, dan minimal satu Super Admin harus selalu ada.
- (Opsional) Administrasi global lain (pengaturan sistem, impor massal Guru/Siswa) - di luar MVP.

## User Stories
- Sebagai Super Admin, saya dapat membuat akun Guru baru dengan email dan nama.
- Sebagai Super Admin, saya dapat membuat akun Siswa baru dengan email dan nama.
- Sebagai Super Admin, saya dapat melihat daftar Guru/Siswa dan memfilter berdasarkan status.
- Sebagai Super Admin, saya dapat memperbarui profil Guru/Siswa dan mereset password mereka.
- Sebagai Super Admin, saya dapat menonaktifkan/mengaktifkan kembali akun Guru/Siswa.
- Sebagai Super Admin, saya tidak dapat menonaktifkan akun saya sendiri.

## Model & Skema Data
- users
  - Kolom `role` diperluas: {`super_admin`, `teacher`, `student`}.
  - Kolom `status`: {`active`, `inactive`} (default `active`).
  - Indeks unik pada `email`.

## Endpoint API (v1)
Seluruh endpoint berikut memerlukan `Authorization: Bearer <token>` dan role `super_admin`.

Base path admin: `/api/v1/admin`

- Manajemen Guru
  - POST   `/api/v1/admin/teachers`
    - Body: { name, email, password|generated, status? }
    - Catatan: `role` dipaksa `teacher`.
  - GET    `/api/v1/admin/teachers`
    - Query: page, per_page, q, status
  - GET    `/api/v1/admin/teachers/{id}`
  - PUT    `/api/v1/admin/teachers/{id}`
    - Body: { name?, email?, status? }
  - PATCH  `/api/v1/admin/teachers/{id}/reset-password`
    - Body: { password? } (jika kosong, generate dan kembalikan)
  - PATCH  `/api/v1/admin/teachers/{id}/deactivate`
  - PATCH  `/api/v1/admin/teachers/{id}/activate`

- Manajemen Siswa
  - POST   `/api/v1/admin/students`
    - Body: { name, email, password|generated, status? }
    - Catatan: `role` dipaksa `student`.
  - GET    `/api/v1/admin/students`
    - Query: page, per_page, q, status, class_id?
  - GET    `/api/v1/admin/students/{id}`
  - PUT    `/api/v1/admin/students/{id}`
    - Body: { name?, email?, status? }
  - PATCH  `/api/v1/admin/students/{id}/reset-password`
    - Body: { password? } (jika kosong, generate dan kembalikan)
  - PATCH  `/api/v1/admin/students/{id}/deactivate`
  - PATCH  `/api/v1/admin/students/{id}/activate`

Catatan: Endpoint autentikasi ada di `plans/PLAN_FITUR_AUTH.md`.

## Validasi & Aturan Bisnis
- Email Guru/Siswa harus unik dan valid.
- Password minimal 8, konfirmasi (kecuali di-generate).
- Hanya Super Admin yang boleh mengakses route `/admin`.
- Super Admin tidak boleh menonaktifkan dirinya sendiri.
- Minimal satu Super Admin harus ada (larang mengubah/menonaktifkan satu-satunya Super Admin).

## Keamanan
- Laravel Sanctum (`auth:sanctum`).
- Middleware/Policy/Gate untuk membatasi role `super_admin`.
- Rate limiting pada operasi mutasi dan audit log untuk create/update/reset/deactivate.

## Acceptance Criteria
- Super Admin berhasil membuat akun Guru dan Siswa; keduanya muncul di listing.
- Pencarian/filter/pagination pada daftar Guru dan Siswa berfungsi.
- Update profil dan reset password Guru/Siswa berjalan sesuai validasi.
- Deaktivasi/aktivasi Guru/Siswa mengubah status dan memengaruhi akses login.
- Perlindungan self-deactivate dan minimal satu Super Admin terjaga.

## Langkah Implementasi
1) Role & Konstanta
   - Tambah konstanta `ROLE_SUPER_ADMIN` pada model User.
   - Pastikan migrasi mendukung nilai `super_admin` pada `users.role` (string/enum).
2) Seeder Super Admin (statik)
   - Tambahkan pada `DatabaseSeeder` pembuatan user Super Admin statik, contoh:
     - email: `superadmin@critispace.local`
     - password: `SuperAdmin!123` (ganti di produksi)
3) Routing
   - Grup `/api/v1/admin` dengan middleware `auth:sanctum` + gate/policy super admin.
4) Controller
   - `Admin/TeacherController` (index, show, store, update, resetPassword, activate, deactivate).
   - `Admin/StudentController` (index, show, store, update, resetPassword, activate, deactivate).
5) Request Validation
   - FormRequest per aksi (store/update/reset).
6) Resource/Transformer
   - Seragamkan payload respons (hindari mengirim field sensitif).
7) Tests
   - Feature tests untuk semua endpoint + aturan larangan self-deactivate + minimal satu super admin.

## Tugas Terperinci
- [Migration] Pastikan kolom `role` menerima `super_admin`.
- [Seeder] Tambah Super Admin statik di `DatabaseSeeder` bila belum ada.
- [Routing] Tambah grup `/api/v1/admin` dan rute manajemen Guru & Siswa.
- [Controller] Implementasi CRUD & actions untuk Guru & Siswa.
- [Validation] FormRequest, aturan email unik, password untuk keduanya.
- [Security] Gate/policy `super_admin`, rate-limit, audit log.
- [Tests] Kasus normal dan edge (self-deactivate, satu-satunya super admin; CRUD Guru & Siswa).
- [Docs] Tambah ke koleksi Postman/OpenAPI.

## Dependensi
- Fitur Auth (Sanctum) aktif: lihat `plans/PLAN_FITUR_AUTH.md`.
- Struktur tabel users (role, status) sudah tersedia.

## Di Luar Ruang Lingkup (saat ini)
- Pengaturan sistem global non-krusial, impor massal Guru, dan metrik.

## Catatan Implementasi Laravel
- Gunakan policy/gate untuk membatasi akses Super Admin, mis. `Gate::authorize('admin-only')`.
- Tambahkan helper untuk generate password (jika tidak diberikan) dan kirimkan nilai sekali pakai di respons (hanya untuk admin) atau melalui kanal aman lain.
