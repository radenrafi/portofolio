# Dokumentasi API AUTH (v1)

Dokumentasi endpoint autentikasi/otorisasi untuk Critispace (Laravel Sanctum).
Semua path berada di bawah prefix `/api/v1`.

- Base URL lokal: `http://localhost:8000`
- Header umum: `Accept: application/json`
- Autentikasi: Bearer Token (`Authorization: Bearer <token>`)

## Ringkasan Endpoint
- POST `/auth/login` — Login dan terima token
- POST `/auth/logout` — Logout dari sesi/token saat ini
- POST `/auth/logout-all` — Cabut seluruh token (semua perangkat)
- GET `/auth/me` — Dapatkan profil user saat ini
- PATCH `/auth/change-password` — Ganti password akun

Catatan: Token berisi `abilities` sesuai role user: `super_admin`, `teacher`, `student`.

---

## POST /auth/login
Login menggunakan email dan password untuk mendapatkan token Sanctum.

- Auth: Tidak perlu
- Header: `Accept: application/json`
- Body (JSON):
  - `email` (string, required, format email)
  - `password` (string, required)
  - `device_name` (string, optional, default: user-agent atau `api-token`)

Contoh cURL:
```
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "secret123",
    "device_name": "my-laptop"
  }'
```

Respon 200:
```
{
  "token_type": "Bearer",
  "token": "<plain_text_token>",
  "abilities": ["super_admin", "teacher", "student"],
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@example.com",
    "role": "super_admin",
    "status": "active",
    "created_at": "2025-09-26T00:00:00.000000Z",
    "updated_at": "2025-09-26T00:00:00.000000Z"
  }
}
```

Galat umum:
- 422 Validation Error (credential salah):
```
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```
- 403 Akun tidak aktif:
```
{
  "message": "Account is inactive."
}
```

---

## POST /auth/logout
Mencabut token akses saat ini.

- Auth: Wajib (`Authorization: Bearer <token>`)
- Header: `Accept: application/json`
- Body: (tidak ada)

Contoh cURL:
```
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Respon 200:
```
{
  "message": "Logged out successfully."
}
```

---

## POST /auth/logout-all
Mencabut seluruh token user (keluar dari semua perangkat/sesi).

- Auth: Wajib (`Authorization: Bearer <token>`)
- Header: `Accept: application/json`
- Body: (tidak ada)

Contoh cURL:
```
curl -X POST http://localhost:8000/api/v1/auth/logout-all \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Respon 200:
```
{
  "message": "All sessions have been revoked."
}
```

---

## GET /auth/me
Mengambil profil user terkait token saat ini.

- Auth: Wajib (`Authorization: Bearer <token>`)
- Header: `Accept: application/json`

Contoh cURL:
```
curl http://localhost:8000/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Respon 200:
```
{
  "id": 1,
  "name": "Super Admin",
  "email": "admin@example.com",
  "role": "super_admin",
  "status": "active",
  "created_at": "2025-09-26T00:00:00.000000Z",
  "updated_at": "2025-09-26T00:00:00.000000Z"
}
```

---

## PATCH /auth/change-password
Mengganti password user. Akan mencabut semua token lain kecuali token saat ini (jika ada); jika tidak ada token aktif yang terdeteksi, seluruh token dicabut.

- Auth: Wajib (`Authorization: Bearer <token>`)
- Header: `Accept: application/json`
- Body (JSON):
  - `current_password` (string, required)
  - `password` (string, required, min:8, confirmed)
  - `password_confirmation` (string, required; harus sama dengan `password`)

Contoh cURL:
```
curl -X PATCH http://localhost:8000/api/v1/auth/change-password \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "current_password": "secret123",
    "password": "newsecret123",
    "password_confirmation": "newsecret123"
  }'
```

Respon 200:
```
{
  "message": "Password updated successfully."
}
```

Galat umum:
- 422 Validation Error (password konfirmasi tidak cocok / kurang dari 8 karakter):
```
{
  "message": "The given data was invalid.",
  "errors": {
    "password": ["The password confirmation does not match."]
  }
}
```
- 422 Password saat ini salah:
```
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": ["The provided password is incorrect."]
  }
}
```

---

## Abilities & Role
Token Sanctum disertai daftar `abilities` sesuai role user (lihat `abilitiesFor()` pada `AuthController`).
- `super_admin` → abilities: ["super_admin", "teacher", "student"]
- `teacher` → abilities: ["teacher", "student"]
- `student` → abilities: ["student"]

Endpoint non-AUTH dapat memakai middleware/cek abilities/role untuk pembatasan akses.

## Status Akun
User dengan `status = inactive` akan ditolak pada login (HTTP 403). Pastikan status aktif agar bisa mengakses API privat.

## Praktik Keamanan
- Gunakan HTTPS di produksi.
- Simpan token di penyimpanan aman (jangan commit/ekspos token).
- Pertimbangkan rate limiting pada login.

