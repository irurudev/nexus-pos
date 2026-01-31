# Swagger / OpenAPI — Implementation & Documentation

## Status: ✅ IMPLEMENTED

Swagger/OpenAPI 3.0 documentation telah diimplementasikan dan tersedia sebagai JSON serta Swagger UI.

---

## Quick Access
- **Swagger UI (Interactive)**: `GET /api/documentation` (view: `resources/views/swagger-ui.blade.php`)
- **OpenAPI Spec (JSON)**: `GET /api/swagger.json` (file: `public/api/swagger.json`)

> Regenerate spec after changing controller annotations:
>
> ```bash
> php artisan l5-swagger:generate
> ```

---

## Features & Implementation
- Full OpenAPI 3.0.0 compliant spec (generated with `barryvdh/laravel-l5-swagger`).
- Spec contains schemas for models (User, Kategori, Barang, Pelanggan, Penjualan, dll), endpoints, parameters, and security scheme (Bearer token / Sanctum).
- Swagger UI supports try-it-out, token authorization, and request/response inspection.

### Files & Routes
- Spec generator endpoint: `GET /api/swagger.json` (Controller: `app/Http/Controllers/DocumentationController.php`)
- UI view endpoint: `GET /api/documentation` (View: `resources/views/swagger-ui.blade.php`)
- Generated file: `public/api/swagger.json`

---

## Endpoints Documented (High level)
- Auth: `POST /login`, `POST /logout`, `GET /me`
- Kategoris: CRUD
- Barangs: CRUD (pagination, filtering)
- Pelanggans: CRUD
- Penjualans: List, Create, Detail, Summary
- Audit & Analytics: `GET /audit-logs`, `GET /analytics/*`

---

## Usage / Access
1. Login via `/api/login` and copy token.
2. Click "Authorize" in Swagger UI and paste token as Bearer token.
3. Try endpoints using "Try it out".

**Local URLs (default Podman setup)**
- Swagger UI: `http://localhost:8303/api/documentation`
- OpenAPI JSON: `http://localhost:8303/api/swagger.json`

---

## Integration & Tools
- Import OpenAPI JSON into Postman / Insomnia.
- Generate client SDKs with OpenAPI Generator.
- Use spec in CI for contract testing and breaking-change detection.

---

## Notes & Future Enhancements
- Add more example request/response bodies in controller annotations.
- Add webhook docs if implemented.
- Add API versioning and rate-limit documentation.
- Consider generating TypeScript SDK (frontend integration).
