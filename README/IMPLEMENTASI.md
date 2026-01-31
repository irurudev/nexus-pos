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

## Database Structure (after)
- `users`: `id`, `name`, `username`, `password`, `role (admin|cashier)`, `is_active`
- `categories`: `id`, `name`, timestamps, `deleted_at`
- `products`: `kode_barang` (PK), `category_id`, `name`, `purchase_price`, `sale_price`, `stock`, `deleted_at`
- `customers`: `id_pelanggan`, `name`, `address`, `gender`, `points`
- `sales`: `id_nota`, `date`, `customer_code`, `user_id`, `subtotal`, `discount`, `tax`, `total`
- `sale_items`: `id`, `nota`, `product_code`, `product_name_snapshot`, `qty`, `unit_price`, `amount`
- `audit_logs`: `id`, `user_id`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `url`

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

## Analytics (Implemented Queries)
- Net Profit: `SUM((si.unit_price - p.purchase_price) * si.qty) as total_profit`
- Top Categories: `SUM(si.qty)` grouped by category
- Cashier Performance: `SUM(s.total)` and `COUNT(*)` per `user_id` per month

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
- Docker/Podman setup: `podman/docker-compose.yml` and `podman/backend/Dockerfile`
