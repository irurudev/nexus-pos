# System Reference — Access Control, Database & Folder Structure 📘

Ringkasan terpusat yang menggabungkan kebijakan akses/audit, struktur database, dan struktur folder backend Laravel.

---

## 1) Access Control & Audit Policy 🔐

**Roles**
- **admin**: akses penuh ke semua resource dan pengaturan sistem.
- **kasir**: akses terbatas — melihat produk/kategori/pelanggan/penjualan dan **membuat penjualan** saja. Tidak boleh mengubah master data/users.

**Ringkasan izin per resource**
- **penjualan**: admin (C/R/U/D), kasir (C, R)
- **barang**: admin (C/R/U/D), kasir (R)
- **kategori**: admin (C/R/U/D), kasir (R)
- **pelanggan**: admin (C/R/U/D), kasir (R)
- **users/roles**: admin only
- **reports**: admin (full), kasir (view own / limited)

> **Catatan implementasi:** penegakan izin harus dilakukan di **server-side** (Policies / Gates) — jangan hanya mengandalkan UI.

**Audit**
- Sistem menulis entri audit (create/update/delete) dengan: `user_id`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `url`, `created_at`.
- Endpoint admin-only untuk melihat log: `GET /api/audit-logs` (filterable: `user_id`, `auditable_type`, `per_page`).

---

## 2) Database Structure & Analytics Requirements 🗄️

Deskripsi tabel utama dan kolom penting (ringkasan):

- **users**: `id`, `name`, `username`, `password`, `role` (`admin|kasir`), `is_active`

- **kategoris**: `id`, `nama_kategori`, timestamps, `deleted_at` (soft delete)

- **barangs**: `kode_barang` (PK), `kategori_id` (FK), `nama`, `harga_beli`, `harga_jual`, `stok`, timestamps, `deleted_at`
  - `kode_barang` dapat digenerate via `sequences` (prefix `BRG` + counter)

- **pelanggans**: `id_pelanggan` (PK), `nama`, `domisili`, `jenis_kelamin` (`PRIA|WANITA`), `poin`, timestamps, `deleted_at`
  - `id_pelanggan` dapat dibuat via `sequences` (prefix `PGN`)

- **penjualans**: `id_nota` (PK), `tgl`, `kode_pelanggan`, `user_id`, `subtotal`, `diskon`, `pajak`, `total_akhir`, timestamps, `deleted_at`

- **item_penjualans**: `id`, `nota` (FK->penjualans.id_nota), `kode_barang`, `nama_barang` (snapshot), `qty`, `harga_satuan`, `jumlah`, timestamps, `deleted_at`

- **audit_logs**: `id`, `user_id`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `url`, timestamps

- **sequences**: `name`, `value` (counter) — dipakai untuk generate kode unik (penjualan, barang, pelanggan)

- **personal_access_tokens**: standar Laravel Sanctum token storage

**Analytics queries supported**
- **Laba Bersih**: perhitungan laba diambil dari detail item dan harga beli barang:
  `SUM((ip.harga_satuan - b.harga_beli) * ip.qty) as total_laba` (implementasi di `AnalyticsController::summary`).
- **Top Kategori**: grouping `SUM(ip.qty)` per kategori (implementasi di `AnalyticsController::topKategori`).
- **Performa Kasir**: agregasi `SUM(p.total_akhir)` dan `COUNT(*)` per `user_id` per bulan (implementasi di `AnalyticsController::kasirPerformance`).

---

## 3) Backend Folder Structure & Conventions 🏗️

Tujuan: memisahkan *business logic* (Actions/Services) dari entry points (Controllers) dan menjaga konsistensi untuk testing & dokumentasi.

Layout (ringkasan):
```
app/
├─ Actions/       # Business logic (action/service classes)
├─ DTOs/          # Data Transfer Objects
├─ Enums/         # Typed enums (UserRole, JenisKelamin)
├─ Http/
│  ├─ Controllers/
│  │  ├─ Api/     # API controllers (dengan Swagger attributes)
│  │  └─ Web/     # Web controllers
│  └─ Requests/   # FormRequests (auth/validation)
├─ Models/        # Eloquent models & relations
├─ Observers/     # Model observers (AuditObserver)
├─ Policies/      # Authorization policies
├─ Services/      # External integrations (Midtrans, Firebase)
└─ Traits/        # Reusable traits (HasApiResponse)
```

**Testing**
- `tests/Feature/`: integration/feature tests (endpoint + flows)
- `tests/Unit/`: unit tests for Actions/Services

**Swagger/OpenAPI**
- Controller annotations (OpenAPI) generate `public/api/swagger.json` via `php artisan l5-swagger:generate`.