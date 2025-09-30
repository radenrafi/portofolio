**Critispace API — Postman Usage**
- Import `docs/Critispace.postman_collection.json` into Postman.
- Collection variables:
  - `base_url`: default `http://localhost:8000/api` (adjust if needed).
  - `token`: auto-filled after a successful Login.

**Quick Start**
- Start backend: `php artisan serve` (default `http://localhost:8000`).
- Health check: `GET {{base_url}}/health` → `{ "status": "ok" }`.
- Auth → Login with a valid user.
  - Super admin account is required for Admin endpoints.
  - After login, the test script stores the `token` in collection variables.
- Call protected endpoints; Authorization inherits Bearer `{{token}}`.

**Auth Flow**
- `POST /v1/auth/login` → gets Bearer token and abilities.
- `GET /v1/auth/me` → current user info.
- `PATCH /v1/auth/change-password` → update password (requires `current_password`, `password`, `password_confirmation`).
- `POST /v1/auth/logout` → revoke current token.
- `POST /v1/auth/logout-all` → revoke all tokens.

**Roles & Scopes**
- Super Admin: access Admin routes (`/v1/admin/...`) and has abilities `[super_admin, teacher, student]`.
- Teacher: access Teacher routes under `/v1/...` (students management) with `teacher` ability.
- Student: typically `student` ability (no routes in this collection).

**Admin Endpoints (super_admin)**
- Teachers: list/create/show/update/reset-password/activate/deactivate under `/v1/admin/teachers`.
- Students: list/create/show/update/reset-password/activate/deactivate under `/v1/admin/students`.

**Teacher Endpoints (teacher)**
- Students: list/create/show/update/reset-password/activate/deactivate under `/v1/students`.

**Notes**
- Pagination supports `per_page` (<= 100) and `page`.
- Filters: `status=active|inactive`, `q` (search name/email).
- Create/reset password: when `password` omitted or null, response `meta.temporary_password` contains generated value.
- Typical error responses: `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Validation Error`.

