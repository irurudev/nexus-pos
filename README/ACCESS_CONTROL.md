# Access Control & Audit Policy

This document describes the recommended role-based access control (RBAC) and auditing rules for the POS backend.

## Roles
- admin: full access to all resources and system settings.
- kasir (cashier): limited access: can view products, categories, customers and sales, and can create sales. Cannot add/update/delete master data or users.

## Resource permissions (summary)
- penjualan: admin (C/R/U/D), kasir (C, R)
- barang: admin (C/R/U/D), kasir (R)
- kategori: admin (C/R/U/D), kasir (R)
- pelanggan: admin (C/R/U/D), kasir (R)
- users/roles: admin only
- reports: admin (full), kasir (view own shift / limited)

> Implementation note: Permissions should be enforced on the **server-side** (Policies / Gates), not only the UI.

## Audit Trail (minimum requirements)
- Audited actions: create/update/delete on critical resources (penjualan, barang, kategori, pelanggan, users, stock adjustments).
- Each audit log entry must include: `user_id` (nullable for system actions), `action` (create|update|delete), `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `url`, `created_at`.
- Penjualan must have `user_id` set (the operator who performed the sale).
- Soft-deletes should be used for master data so audit/recovery is possible.

## Implementation plan (backend)
1. Add Policies (Laravel) for resources, including `PenjualanPolicy` (allow `create` for `admin` and `kasir`).
2. Register policies in `AuthServiceProvider`.
3. Add audit logs table and `AuditLog` model.
4. Add a flexible `AuditObserver` or `Auditable` trait that writes audit entries on `created`, `updated`, `deleted` model events for the selected models. An admin-only endpoint `/api/audit-logs` is provided to inspect logs.
5. Enforce policy checks in controllers (e.g. `authorize('create', Penjualan::class)`), and in API endpoints that modify master data.
6. Add lightweight integration tests verifying unauthorized users cannot create/update/delete protected resources.

## Security notes
- Always validate and sanitize inputs.
- Token-based authentication (Bearer) should be required for all API endpoints that mutate data.
- Logging should not record sensitive PII (e.g., full card numbers).

## Follow-ups
- Add role management UI for admins.
- Add audit log viewer with filters (date range, user, resource type, action).

---
Generated and maintained by the engineering team. Adjust as business rules change.