# API Progres Siswa (Student Only)

Dokumentasi lengkap endpoint progres siswa pada Critispace. Fitur ini mencatat progres saat pertama kali diakses (create-on-first-hit), lalu memperbarui progres pada akses berikutnya (update-on-hit).

- Base URL: `http://localhost:8000/api`
- Versi: `v1`
- Autentikasi: `Authorization: Bearer <token>` (role: `student`)
- Header umum: `Accept: application/json`

## Tipe Fitur (feature)
Gunakan salah satu nilai berikut pada field `feature`:
- `exploreInAR`
- `geogebraLab`
- `problemChallenge`

Catatan: Ringkasan `myProgress` adalah hasil komputasi dari data progres; tidak perlu mengirim `feature = myProgress`.

## Skema Data Progres (Respons)
Setiap item progres memiliki struktur berikut:

```
{
  "id": 1,
  "user_id": 10,
  "feature": "problemChallenge",
  "started_at": "2025-09-28T10:00:00.000000Z",
  "last_accessed_at": "2025-09-28T10:05:12.000000Z",
  "access_count": 3,
  "percent": 30,
  "state": "active",
  "meta": { "solved": 3, "total": 10 },
  "created_at": "2025-09-28T10:00:00.000000Z",
  "updated_at": "2025-09-28T10:05:12.000000Z"
}
```

- `percent`: 0..100. Untuk `problemChallenge`, jika `meta.total > 0`, dihitung dari `floor(solved/total*100)`.
- `state`: default `active`. Menjadi `completed` bila `percent = 100`.
- `meta`: bebas untuk menyimpan data spesifik fitur (mis. `{ solved, total }`).

## Endpoint

### 1) Hit Progres (buat/perbarui)
- Method & URL: `POST /api/v1/progress/hit`
- Auth: Bearer token (role: student)
- Body:

```
{
  "feature": "exploreInAR | geogebraLab | problemChallenge",
  "payload": { ... } // opsional, object
}
```

- Perilaku:
  - Jika record belum ada untuk kombinasi (user, feature) → dibuat dengan `started_at` = sekarang, `access_count = 1`.
  - Jika sudah ada → `access_count` bertambah, `last_accessed_at` diperbarui, `percent/state/meta` diperbarui dari payload bila ada.
  - `problemChallenge`: jika `payload` berisi `{ solved, total }`, sistem menghitung `percent` otomatis; `state` menjadi `completed` bila 100%.

- Contoh Request (problemChallenge):

```
POST /api/v1/progress/hit
Authorization: Bearer <token>
Content-Type: application/json

{
  "feature": "problemChallenge",
  "payload": { "solved": 3, "total": 10 }
}
```

- Contoh Respons 200:

```
{
  "data": {
    "id": 1,
    "user_id": 10,
    "feature": "problemChallenge",
    "started_at": "2025-09-28T10:00:00.000000Z",
    "last_accessed_at": "2025-09-28T10:05:12.000000Z",
    "access_count": 3,
    "percent": 30,
    "state": "active",
    "meta": { "solved": 3, "total": 10 },
    "created_at": "2025-09-28T10:00:00.000000Z",
    "updated_at": "2025-09-28T10:05:12.000000Z"
  }
}
```

- Kesalahan Umum:
  - 401 Unauthorized (tanpa/invalid token)
  - 403 Forbidden (token tidak memiliki role `student`)
  - 422 Unprocessable Entity (feature tidak valid, payload bukan object)

### 2) Ringkasan Progres Saya
- Method & URL: `GET /api/v1/my/progress`
- Auth: Bearer token (role: student)
- Deskripsi: Mengembalikan daftar progres untuk semua fitur milik siswa saat ini.

- Contoh Respons 200:

```
{
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "feature": "exploreInAR",
      "started_at": "2025-09-28T10:00:00.000000Z",
      "last_accessed_at": "2025-09-28T10:02:00.000000Z",
      "access_count": 1,
      "percent": 0,
      "state": "active",
      "meta": {},
      "created_at": "2025-09-28T10:00:00.000000Z",
      "updated_at": "2025-09-28T10:02:00.000000Z"
    },
    {
      "id": 2,
      "user_id": 10,
      "feature": "problemChallenge",
      "started_at": "2025-09-28T10:00:00.000000Z",
      "last_accessed_at": "2025-09-28T10:05:12.000000Z",
      "access_count": 3,
      "percent": 30,
      "state": "active",
      "meta": { "solved": 3, "total": 10 },
      "created_at": "2025-09-28T10:00:00.000000Z",
      "updated_at": "2025-09-28T10:05:12.000000Z"
    }
  ]
}
```

- Kesalahan Umum:
  - 401 Unauthorized (tanpa/invalid token)
  - 403 Forbidden (token tidak memiliki role `student`)

### 3) Detail Progres Saya per Fitur
- Method & URL: `GET /api/v1/my/progress/{feature}`
- Auth: Bearer token (role: student)
- Path param: `feature` → salah satu dari enum di atas.

- Contoh:

```
GET /api/v1/my/progress/problemChallenge
Authorization: Bearer <token>
```

- Contoh Respons 200:

```
{
  "data": {
    "id": 2,
    "user_id": 10,
    "feature": "problemChallenge",
    "started_at": "2025-09-28T10:00:00.000000Z",
    "last_accessed_at": "2025-09-28T10:05:12.000000Z",
    "access_count": 3,
    "percent": 30,
    "state": "active",
    "meta": { "solved": 3, "total": 10 },
    "created_at": "2025-09-28T10:00:00.000000Z",
    "updated_at": "2025-09-28T10:05:12.000000Z"
  }
}
```

- Kesalahan Umum:
  - 401 Unauthorized (tanpa/invalid token)
  - 403 Forbidden (token tidak memiliki role `student`)
  - 404 Not Found (belum pernah hit fitur tsb, sehingga record belum ada)
  - 422 Unprocessable Entity (nilai `feature` tidak valid)

## Catatan & Rekomendasi
- Idempotensi: kombinasi `user_id + feature` unik. Panggilan pertama membuat record; berikutnya memperbarui.
- Penentuan `percent`:
  - `problemChallenge`: gunakan `payload` `{ solved, total }` untuk perhitungan otomatis.
  - Fitur lain: Anda bisa mengirim `payload.percent` secara eksplisit (opsional). Nilai akan di-clamp 0..100.
- `state` default `active` dan berubah `completed` saat `percent = 100`.
- `timestamps` dan tanggal di respons dalam format ISO 8601 (UTC).
- Disarankan rate limiting pada endpoint hit untuk mengurangi spam (diatur via middleware/konfigurasi Laravel).

## Contoh cURL

```
# Login (dapatkan token)
curl -X POST \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"student@example.com","password":"secret"}' \
  http://localhost:8000/api/v1/auth/login

# Hit progres (problemChallenge)
curl -X POST \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"feature":"problemChallenge","payload":{"solved":3,"total":10}}' \
  http://localhost:8000/api/v1/progress/hit

# Ringkasan progres saya
curl -H "Accept: application/json" -H "Authorization: Bearer <token>" \
  http://localhost:8000/api/v1/my/progress

# Detail progres saya (fitur tertentu)
curl -H "Accept: application/json" -H "Authorization: Bearer <token>" \
  http://localhost:8000/api/v1/my/progress/problemChallenge
```

--
Lihat juga koleksi Postman: `docs/Critispace.postman_collection.json` (folder "Progress").
