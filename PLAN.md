# PLAN: Backend Critispace (Laravel)

## Ringkasan
- Tujuan: membangun API untuk platform dashboard manajemen siswa (guru) dan aplikasi media pembelajaran (siswa).
- Peran utama: Super Admin, Guru, dan Siswa.
- Fokus awal (MVP): autentikasi + otorisasi berbasis peran, manajemen akun siswa oleh guru, administrasi guru oleh super admin, dan dasar-dasar penilaian/umpan balik.

## Tujuan Produk
- Guru dapat membuat dan mengelola akun siswa.
- Guru dapat melihat, mengelola, dan menilai pengumpulan tugas siswa.
- Guru dapat memberi umpan balik kepada siswa.
- Siswa dapat mengakses materi/pengumuman dan melihat nilai serta umpan balik.

## Ruang Lingkup Tahap 1 (MVP)
- Autentikasi (Sanctum) + Role/Permission (Super Admin, Guru, Siswa).
- Super Admin: CRUD Guru (dan dapat CRUD Siswa lewat namespace admin).
- Guru: CRUD Siswa, listing/filter, reset/deaktivasi, (opsional) impor massal.
- Dasar Tugas & Pengumpulan: definisi tugas, pengumpulan oleh siswa, peninjauan oleh guru.
- Dasar Penilaian & Feedback: pemberian nilai + catatan feedback per pengumpulan.
- Observability dasar: logging, rate limit, format error konsisten.

## Peran & Hak Akses
- Super Admin: administrasi sistem, kelola Guru dan (opsional) Siswa melalui `/api/v1/admin/*`.
- Guru: kelola siswa, kelola tugas, menilai dan memberi feedback.
- Siswa: akses materi, kumpulkan tugas, lihat nilai dan feedback.

## Arsitektur & Teknologi
- Backend: Laravel 12 (PHP ^8.2), Sanctum untuk token-based auth.
- DB: MySQL (default) atau SQLite untuk dev.
- Konvensi versi API: prefix `/api/v1`.
- Format: JSON; pagination standar (limit, page), sorting, filtering sederhana.

## Konvensi API
- Base path: `/api/v1`.
- Header: `Accept: application/json`, `Authorization: Bearer <token>` untuk endpoint privat.
- Error format: `{ "message": string, "errors": object|null, "code": string|null }`.
- Pagination: `{ data: [...], meta: { current_page, per_page, total, last_page }, links: {...} }`.

## Milestone & Deliverables
1) Auth & Role (Sanctum)
- Registrasi/seed awal peran, login, refresh/revoke token, middleware kebijakan akses.
2) Super Admin: Administrasi Guru & Siswa (admin namespace)
- CRUD Guru, reset/aktivasi, listing/filter; CRUD Siswa bila diperlukan.
3) Guru: Manajemen Siswa
- CRUD siswa, listing + filter, reset password/deaktivasi, (opsional) impor CSV.
4) Tugas & Pengumpulan
- CRUD tugas, upload/pengumpulan oleh siswa, listing status.
5) Penilaian & Feedback
- Pemberian nilai, catatan feedback per pengumpulan, revisi.
6) Observability & Hardening
- Logging terstruktur, rate-limit, validasi menyeluruh, dokumentasi API.

## Struktur Rencana Fitur
- Setiap fitur turunan memiliki dokumen detail: `plans/PLAN_FITUR_<NAMA>.md`.
- Contoh: Guru (manajemen siswa) → `plans/PLAN_FITUR_GURU.md`.

## Rencana Fitur yang Tersedia
- Auth & Role → lihat `plans/PLAN_FITUR_AUTH.md`.
- Super Admin: Administrasi Guru & Siswa → lihat `plans/PLAN_FITUR_SUPER_ADMIN.md`.
- Guru: Manajemen Siswa → lihat `plans/PLAN_FITUR_GURU.md`.
- Tambahan fitur akan menyusul (Tugas, Pengumpulan, Penilaian, Feedback, Materi, Pengumuman, dsb.).

## Definisi Data (awal)
- users: id, name, email (unik), password, role (enum: super_admin, teacher, student), status (active/inactive), metadata.
- classes (opsional tahap 1): id, name, homeroom_teacher_id.
- assignments: id, title, description, due_at, created_by (guru).
- submissions: id, assignment_id, student_id, submitted_at, file/url, status.
- grades: id, submission_id, score, graded_by (guru), graded_at.
- feedbacks: id, submission_id, comment, created_by (guru), created_at.

## Kualitas & Testing
- Unit + Feature test untuk auth, role, dan endpoint guru.
- Validasi ketat (FormRequest), Policy/Gate untuk otorisasi.
- E2E koleksi Postman atau HTTP CLI (pintasan artisan jika perlu).

## Risiko & Keputusan Terbuka
- Penentuan skema kelas/mata pelajaran (tahap berikutnya).
- Media penyimpanan (local/S3) untuk lampiran tugas.
- Mekanisme impor massal (CSV/XLSX) dan audit trail.

## Langkah Berikutnya
- Finalisasi kebutuhan detail di `plans/PLAN_FITUR_GURU.md` dan mulai implementasi.
- Tambahkan plan untuk fitur lain (assignments, submissions, grading, feedback) saat siap.
