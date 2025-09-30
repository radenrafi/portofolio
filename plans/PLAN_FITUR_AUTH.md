# PLAN_FITUR_AUTH: Autentikasi & Otorisasi

## Tujuan
Menyediakan mekanisme login/logout berbasis token untuk pengguna (super_admin/guru/siswa) beserta endpoint profil dasar, dengan Sanctum sebagai penyedia otentikasi.

## Lingkup
- Login (mendapatkan token Sanctum)
- Logout (revoke token saat ini)
- Logout semua sesi (revoke seluruh token pengguna)
- Profil pengguna saat ini (me)
- Ganti password

Catatan: Pendaftaran/registrasi siswa dilakukan oleh fitur Guru (lihat `plans/PLAN_FITUR_GURU.md`). Pembuatan akun Guru dan administrasi tingkat sistem berada pada fitur Super Admin (lihat `plans/PLAN_FITUR_SUPER_ADMIN.md`).

## User Stories
- Sebagai pengguna, saya dapat login dan menerima token untuk mengakses API.
- Sebagai pengguna, saya dapat logout dan mencabut token saya.
- Sebagai pengguna, saya dapat melihat profil saya saat ini.
- Sebagai pengguna, saya dapat mengganti password saya secara aman.

## Endpoint API (v1)
- POST /api/v1/auth/login
  - Body: { email, password }
  - Res: { token, user }
- POST /api/v1/auth/logout
- POST /api/v1/auth/logout-all
- GET  /api/v1/auth/me
- PATCH /api/v1/auth/change-password
  - Body: { current_password, password, password_confirmation }

Header: `Accept: application/json`, endpoint privat memakai `Authorization: Bearer <token>`.

Dokumentasi lengkap per endpoint (contoh request/response & error): lihat `docs/API_AUTH.md`.

## Validasi & Aturan Bisnis
- Email wajib dan valid, password wajib saat login.
- Ganti password: verifikasi `current_password`, minimal 8 karakter, konfirmasi sama.
- Logout-all hanya untuk pengguna yang terautentikasi.
 - User dengan `status=inactive` tidak dapat login.

## Keamanan
- Laravel Sanctum untuk token pribadi per perangkat.
- Rate limiting pada login (mis. throttle:login).
- Penyimpanan token aman; selalu kirim via HTTPS di lingkungan produksi.
 - Pertimbangkan penggunaan abilities/abilities-by-role pada token (mis. `super_admin`, `teacher`, `student`) untuk pembatasan granular.

## Acceptance Criteria
- Login berhasil mengembalikan token yang valid dan data user ringkas.
- Token yang dicabut tidak dapat digunakan lagi untuk mengakses endpoint privat.
- Endpoint `me` mengembalikan profil sesuai token aktif.
- Ganti password menolak `current_password` salah dan menerima yang benar.

## Langkah Implementasi
1) Konfigurasi Sanctum (provider, middleware `auth:sanctum`).
2) Routing (routes/api.php) dalam grup `/api/v1/auth`.
3) Controller `AuthController`:
   - `login`, `logout`, `logoutAll`, `me`, `changePassword`.
4) FormRequest untuk `login` dan `changePassword`.
5) Tests: happy path + invalid credential, rate limit, revoke token.

## Dependensi
- Tabel `users` beserta kolom yang diperlukan (email, password, role, status).
- Logika status: user `inactive` ditolak saat login.

## Di Luar Ruang Lingkup
- Registrasi publik (siswa/guru) — dikelola oleh fitur lain/seed.
- OAuth sosial.
