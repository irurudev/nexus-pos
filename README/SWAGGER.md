# Swagger / OpenAPI — Implementation & Documentation

## Status: ✅ IMPLEMENTED

The Swagger/OpenAPI 3.0 documentation is implemented and available both as a JSON spec and via Swagger UI.

---

## Quick Access
- **Swagger UI (Interactive)**: `GET /api/documentation` (view: `resources/views/swagger-ui.blade.php`)
- **OpenAPI Spec (JSON)**: `GET /api/swagger.json` (file: `public/api/swagger.json`)

> Regenerate the spec after updating controller annotations:
>
> ```bash
> php artisan l5-swagger:generate
> ```

---

## Features & Implementation
- Full OpenAPI 3.0.0 compliant spec (generated with `barryvdh/laravel-l5-swagger`).
- The spec includes schemas for models (User, Category, Product, Customer, Sale, etc.), endpoints, parameters, and the security scheme (Bearer token / Laravel Sanctum).
- Swagger UI supports try-it-out, bearer token authorization, and request/response inspection.

### Files & Routes
- Spec generator route: `GET /api/swagger.json` (Controller: `app/Http/Controllers/DocumentationController.php`)
- UI view route: `GET /api/documentation` (View: `resources/views/swagger-ui.blade.php`)
- Generated file location: `public/api/swagger.json`

---

## Documented Endpoint Groups (Overview)
- Auth: `POST /login`, `POST /logout`, `GET /me`
- Categories: CRUD
- Products: CRUD (pagination, filtering)
- Customers: CRUD
- Sales: List, Create, Detail, Summary
- Audit & Analytics: `GET /audit-logs`, `GET /analytics/*`

---

## Usage
1. Login via `/api/login` and copy the returned token.
2. Click "Authorize" in Swagger UI and paste the token as a Bearer token.
3. Use "Try it out" to test endpoints.

**Local URLs (default Podman setup)**
- Swagger UI: `http://localhost:8303/api/documentation`
- OpenAPI JSON: `http://localhost:8303/api/swagger.json`

---

## Deploy (Regenerate Swagger) ✅

- Set `APP_URL` in your production environment (e.g. `APP_URL=https://api.example.com`). Optionally set `L5_SWAGGER_CONST_HOST` if you need a different public URL (e.g. `L5_SWAGGER_CONST_HOST=https://api.example.com/api`).

- After deploying, run:

```bash
php artisan config:clear && php artisan config:cache
php artisan l5-swagger:generate
```

- Verify the generated spec at `/api/swagger.json` and the UI at `/api/documentation`.

---

## Integration & Tools
- Import the OpenAPI JSON into Postman / Insomnia.
- Generate client SDKs using OpenAPI Generator.
- Use the spec in CI for contract tests and breaking-change detection.

---

## Notes & Future Enhancements
- Add example request/response bodies in controller annotations.
- Add webhook documentation if webhooks are implemented.
- Document API versioning and rate limits.
- Consider generating a TypeScript SDK for frontend integration.
