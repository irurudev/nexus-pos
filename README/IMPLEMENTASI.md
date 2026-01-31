# Implementation & Documentation — POS API

## Status: ✅ COMPLETE

Centralized documentation for the backend implementation (endpoints, database, folder structure, access control, quick start, and technical notes).

---

## Summary
- Backend: Laravel 12, MySQL, Laravel Sanctum for authentication
- 25+ API endpoints (Auth, Categories, Products, Customers, Sales, Audit, Analytics)
- Key features: POS transactions (multi-item), automatic stock decrement, audit logs, analytics (profit, top categories, cashier performance)

---

## Quick Start
```bash
cd be
cp .env.example .env    # update DB_* credentials
composer install
php artisan key:generate
php artisan migrate --seed
php artisan l5-swagger:generate
php artisan serve --host=0.0.0.0 --port=8000
```

---

## API Endpoints (Overview)
- Auth: `POST /api/login`, `POST /api/logout`, `GET /api/me`
- Categories: CRUD
- Products: CRUD (pagination, search, filter)
- Customers: CRUD
- Sales: `GET /api/penjualans`, `POST /api/penjualans`, `GET /api/penjualans/{id}`, `GET /api/penjualans/summary`
- Audit & Analytics: `GET /api/audit-logs` (admin), `GET /api/analytics/{summary,top-category,cashier-performance}`

---

## Database Structure (before)
- `pelanggan`: `id_pelanggan`, `nama`, `domisili`, `jenis_kelamin`
- `barang`: `kode`, `nama`, `kategori`, `harga`
- `penjualan`: `id_nota`, `tgl`, `kode_pelanggan`, `subtotal`
- `item_penjualan`: `nota`, `kode_barang`, `qty`

## Database Structure (as defined in migrations)
- `users`: `id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`
- `kategoris`: `id`, `nama_kategori`, `created_at`, `updated_at`, `deleted_at`
- `barangs`: `kode_barang` (PK), `kategori_id` (FK → `kategoris.id`), `nama`, `harga_beli`, `harga_jual`, `stok`, `created_at`, `updated_at`, `deleted_at`
- `pelanggans`: `id_pelanggan` (PK), `nama`, `domisili`, `jenis_kelamin` (`PRIA|WANITA`), `poin`, `created_at`, `updated_at`, `deleted_at`
- `penjualans`: `id_nota` (PK), `tgl`, `kode_pelanggan` (nullable, FK → `pelanggans.id_pelanggan`), `user_id` (FK → `users.id`), `subtotal`, `diskon`, `pajak`, `total_akhir`, `created_at`, `updated_at`, `deleted_at`
- `item_penjualans`: `id`, `nota` (FK → `penjualans.id_nota`), `kode_barang` (FK → `barangs.kode_barang`), `qty`, `harga_satuan`, `jumlah`, `created_at`, `updated_at`, `deleted_at`
- `audit_logs`: `id`, `user_id` (nullable), `action` (`create|update|delete`), `auditable_type`, `auditable_id` (nullable), `old_values` (json), `new_values` (json), `ip_address`, `user_agent`, `url`, `created_at`, `updated_at`

*Note: this list follows the actual column names in the migrations; use these exact names when referencing the database.*

---

## Folder Structure & Conventions
- `app/Actions/` — Business logic (Action classes)
- `app/DTOs/` — Data Transfer Objects (SaleData, SaleItemData)
- `app/Enums/` — Typed enums (UserRole, Gender)
- `app/Http/Controllers/Api/` — API controllers (with Swagger/OpenAPI attributes)
- `app/Http/Requests/` — Form requests for validation
- `app/Models/` — Eloquent models and relations
- `app/Observers/` — Observers (AuditObserver)
- `app/Policies/` — Authorization policies
- `app/Services/` — External integrations

---

## Test Credentials (Seeder)
- Admin: `username: admin`, `password: password`
- Cashier: `username: kasir_fakhirul`, `password: password`

---

## Notes & Recommendations
- Enforce permissions server-side (Policies/Gates), not only on the client.
- Use database transactions for `POST /penjualans` to ensure data integrity (stock validation, rollback on error).
- Keep Swagger annotations up-to-date and regenerate the JSON spec after controller changes.

---

## Where to find more
- Swagger UI & Spec: `README/SWAGGER.md` and `public/api/swagger.json`
- Tests: `tests/` (`Feature` and `Unit`)
