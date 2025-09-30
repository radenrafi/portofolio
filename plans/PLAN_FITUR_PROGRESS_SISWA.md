# PLAN_FITUR_PROGRESS_SISWA: Progres Siswa

## Tujuan
Menyediakan fitur pelacakan progres belajar siswa yang tercatat otomatis saat pertama kali mengakses suatu fitur (idempotent), dan dapat dilihat oleh siswa (myProgress) serta guru.

## Lingkup
- Fitur yang dilacak: `exploreInAR`, `geogebraLab`, `problemChallenge`, dan ringkasan `myProgress`.
- Pencatatan progres melalui endpoint "hit" generik per fitur.
- Ringkasan progres siswa (self) dan tampilan progres siswa oleh guru.
- Pengayaan metrik dasar: `started_at`, `last_accessed_at`, `access_count`, `percent`, `state`, `meta`.

## User Stories
- Sebagai siswa, saat pertama kali mengakses fitur X, progres saya tercatat otomatis (create-on-first-hit), dan pada akses berikutnya diperbarui (update-on-hit).
- Sebagai siswa, saya dapat melihat ringkasan seluruh progres saya (myProgress) dan detail per fitur.
- Sebagai guru, saya dapat melihat ringkasan dan detail progres siswa.
- Sebagai sistem, saya menghindari duplikasi (idempotent) dan mendukung pembaruan persen/keadaan sesuai payload.

## Model & Skema Data
Tabel utama (wajib):
- `student_feature_progress`
  - `id`
  - `user_id` (FK ke `users.id`)
  - `feature` (string/enum: `exploreInAR|geogebraLab|problemChallenge`)
  - `started_at` (datetime)
  - `last_accessed_at` (datetime)
  - `access_count` (integer, default 0)
  - `percent` (unsigned tinyint 0..100, default 0)
  - `state` (string, mis. `active|completed|paused`, default `active`)
  - `meta` (json, nullable) — untuk data spesifik fitur; contoh: `{"solved": 3, "total": 10}`
  - timestamps
  - Unique constraint: (`user_id`, `feature`)

Tabel opsional (jejak granular, jika dibutuhkan):
- `student_progress_events`
  - `id`, `user_id`, `feature`, `action` (mis. `open`, `complete`, `solve`), `payload` (json), `occurred_at` (datetime), timestamps.

Catatan:
- `myProgress` adalah ringkasan komputasi dari baris-baris pada `student_feature_progress` (tidak perlu disimpan tersendiri kecuali ingin cache).

## Endpoint API (v1)
Header: `Accept: application/json`. Endpoint privat menggunakan `Authorization: Bearer <token>`.

Siswa (role: `student` — dan juga dapat diakses oleh `teacher/super_admin` yang bertindak sebagai siswa sendiri):
- POST `/api/v1/progress/hit`
  - Body: `{ "feature": "exploreInAR|geogebraLab|problemChallenge", "payload": { ... }? }`
  - Efek: create-on-first-hit (set `started_at`, `last_accessed_at`, `access_count=1`), atau update (`last_accessed_at`, `access_count++`, `percent/state` dari payload jika ada).
  - Res: `ProgressResource` (baris terkini).
- GET `/api/v1/my/progress`
  - Ringkasan seluruh fitur milik user saat ini.
  - Query (opsional): `features[]=...` untuk subset, `with=events` untuk lampirkan jejak (jika tabel events dipakai).
- GET `/api/v1/my/progress/{feature}`
  - Detail progres satu fitur milik user saat ini.

Guru (role: `teacher`, juga `super_admin`):
- GET `/api/v1/students/{student}/progress`
  - Ringkasan seluruh fitur untuk siswa tertentu.
- GET `/api/v1/students/{student}/progress/{feature}`
  - Detail progres satu fitur untuk siswa tertentu.

## Validasi & Aturan Bisnis
- `feature` wajib dan harus salah satu dari enum yang didukung.
- `payload` opsional, berbentuk object; di-merge ke `meta` atau dipakai menghitung `percent/state`.
- Idempotensi: kombinasi (`user_id`, `feature`) unik — "hit" pertama membuat baris; selanjutnya update.
- Rate limiting untuk endpoint hit guna mencegah spam.
- Siswa hanya dapat mengakses progres miliknya; guru dapat mengakses progres siswa yang dikelolanya (gunakan policy/gate). Super admin boleh semuanya.

## Keamanan
- `auth:sanctum` untuk semua endpoint progres.
- Policy: `viewSelf` (siswa), `viewStudentProgress` (guru/super_admin), `hitSelf` (siswa untuk dirinya sendiri).
- Logging opsional via event `ProgressHitRecorded`.

## Acceptance Criteria
- Saat siswa pertama kali memanggil `POST /v1/progress/hit` untuk suatu fitur, record baru dibuat dengan `started_at` terisi.
- Panggilan hit berikutnya pada fitur yang sama menaikkan `access_count` dan memperbarui `last_accessed_at`.
- Jika payload menyediakan data kemajuan (mis. `solved`/`total` untuk `problemChallenge`), `percent` dihitung benar.
- `GET /v1/my/progress` mengembalikan ringkasan semua fitur milik user.
- Guru dapat mengambil progres siswa via endpoint guru dan ditolak jika tidak berizin.

## Langkah Implementasi
1) Konstanta & Enum
   - Tambahkan konstanta fitur di tempat terpusat (mis. `App\Enums\ProgressFeature` atau konstanta pada service), gunakan untuk validasi.
2) Migrasi
   - Buat tabel `student_feature_progress` (unique `user_id+feature`), dan opsional `student_progress_events`.
3) Model & Relasi
   - `StudentFeatureProgress` (belongsTo `User`).
   - (Opsional) `StudentProgressEvent`.
4) Service
   - `ProgressService` dengan metode `hit(User $user, string $feature, array $payload = [])` yang menangani create/update, perhitungan percent/state, dan emit event.
5) Request & Resource
   - `HitProgressRequest` (feature enum, payload array nullable).
   - `ProgressResource` dan `ProgressCollection` untuk output seragam.
6) Policy/Authorization
   - Policy untuk view self, view student (guru), dan hit self.
7) Controller & Routes
   - `POST /v1/progress/hit` (siswa), `GET /v1/my/progress`, `GET /v1/my/progress/{feature}`.
   - `GET /v1/students/{student}/progress`, `GET /v1/students/{student}/progress/{feature}` (guru/super_admin).
8) Tests
   - Feature tests: create-on-first-hit, update-on-hit, percent calculation, myProgress summary, authZ guru vs siswa.
9) Dokumentasi
   - Tambahkan ke koleksi Postman (folder Progress) dengan contoh request/respons.
10) Observability (opsional)
   - Event/listener, logging, dan rate limiting.

## Tugas Terperinci
- [Enum] Buat enum/konstanta `ProgressFeature` (`exploreInAR`, `geogebraLab`, `problemChallenge`).
- [Migration] `student_feature_progress` + unique index + kolom yang disebut di atas.
- [Model] `StudentFeatureProgress` (+ factory).
- [Service] `ProgressService::hit()` dengan logika merge `meta` dan hitung `percent` (mis. untuk `problemChallenge`: `percent = floor(solved/total*100)`).
- [Request] `HitProgressRequest` (rules: feature in enum, payload array|nullable).
- [Resource] `ProgressResource` (id, feature, started_at, last_accessed_at, access_count, percent, state, meta).
- [Policy] Batasi akses berdasarkan role (self vs teacher/super_admin).
- [Controller] Endpoint siswa dan guru sesuai daftar.
- [Routes] Grup `auth:sanctum` + role middleware untuk guru/super_admin bila diperlukan.
- [Tests] Kasus pertama kali hit, update, invalid feature, akses tanpa izin, ringkasan.
- [Docs] Update Postman: contoh hit untuk tiap fitur, dan GET summary/detail.

## Dependensi
- Fitur Auth & role sudah tersedia (`auth:sanctum`, role `student|teacher|super_admin`).
- Model `User` aktif dengan `status=active` agar bisa login/hit.

## Di Luar Ruang Lingkup (saat ini)
- Analitik lanjutan (time-on-task, heatmap), badge/achievement, leaderboard.
- Sinkronisasi ke sistem eksternal.

## Catatan Implementasi Laravel
- Gunakan transaksi pada `hit` jika meng-update beberapa tabel sekaligus.
- Terapkan rate limiting pada route `progress/hit`.
- Pertimbangkan cache untuk `myProgress` jika ringkasan kompleks; invalidasi pada hit.

