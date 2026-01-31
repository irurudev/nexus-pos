# API Documentation

POS System API documentation tersedia dalam format Swagger/OpenAPI 3.0.

## Akses Dokumentasi

### Swagger UI (Interactive)
- **URL**: `http://localhost:8303/api/documentation`
- Dapat menjalankan request langsung dari browser
- Menampilkan request/response schema dengan interactive try-it-out feature

### OpenAPI Spec (JSON)
- **URL**: `http://localhost:8303/api/swagger.json`
- Format standar OpenAPI 3.0
- Dapat digunakan untuk integrasi dengan tools lain seperti Postman, Insomnia, atau Code Generator

## Cara Menggunakan Swagger UI

1. **Login terlebih dahulu**
   - Klik endpoint `/login` di section "Auth"
   - Masukkan username dan password
   - Copy token yang diterima

2. **Authorize Swagger UI**
   - Klik tombol "Authorize" di kanan atas
   - Paste token ke field Bearer token
   - Klik "Authorize"

3. **Try Endpoints**
   - Pilih endpoint yang ingin ditest
   - Klik "Try it out"
   - Masukkan parameter/body sesuai kebutuhan
   - Klik "Execute"
   - Lihat response

## Test Credentials

Gunakan credential berikut untuk testing:

### Admin User
- Username: `admin`
- Password: `password`
- Role: `admin`

### Kasir User
- Username: `kasir1` atau `kasir2`
- Password: `password`
- Role: `kasir`

## Endpoint Grouping

API diorganisir dalam beberapa kategori:

### Auth
- POST /login - Login dan dapatkan token
- POST /logout - Logout dan revoke token
- GET /me - Dapatkan info user saat ini

### Kategoris (CRUD)
- GET /kategoris - List semua kategori
- POST /kategoris - Buat kategori baru
- GET /kategoris/{id} - Detail kategori
- PUT /kategoris/{id} - Update kategori
- DELETE /kategoris/{id} - Delete kategori

### Barangs (CRUD)
- GET /barangs - List barang (dengan pagination & filter)
- POST /barangs - Buat barang baru
- GET /barangs/{kode_barang} - Detail barang
- PUT /barangs/{kode_barang} - Update barang
- DELETE /barangs/{kode_barang} - Delete barang

### Pelanggans (CRUD)
- GET /pelanggans - List pelanggan (dengan pagination)
- POST /pelanggans - Buat pelanggan baru
- GET /pelanggans/{id_pelanggan} - Detail pelanggan
- PUT /pelanggans/{id_pelanggan} - Update pelanggan
- DELETE /pelanggans/{id_pelanggan} - Delete pelanggan

### Penjualans (Sales)
- GET /penjualans - List penjualan (dengan pagination)
- POST /penjualans - Buat penjualan baru dengan items (atomic transaction)
- GET /penjualans/{id_nota} - Detail penjualan dengan items
- GET /penjualans/summary - Ringkasan penjualan hari ini

### Analytics
- GET /analytics/summary - Ringkasan penjualan keseluruhan
- GET /analytics/top-kategori - Top 10 kategori
- GET /analytics/kasir-performance - Performa kasir per bulan

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Deskripsi pesan",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Deskripsi error",
  "data": null
}
```

## HTTP Status Codes

- `200` - OK / Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (Invalid/Missing token)
- `403` - Forbidden (Insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Authentication

Semua endpoint kecuali `/login` memerlukan Bearer token authentication.

Header yang diperlukan:
```
Authorization: Bearer {token}
```

Token didapatkan dari endpoint `/login` dan memiliki masa berlaku sampai user logout atau token di-revoke.

## Pagination

Endpoint yang support pagination menerima query parameters:
- `page` - Halaman (default: 1)
- `per_page` - Item per halaman (default: 15)

Response pagination terstruktur sebagai berikut:
```json
{
  "success": true,
  "message": "...",
  "data": {
    "data": [...items...],
    "links": {
      "first": "...",
      "last": "...",
      "prev": "...",
      "next": "..."
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "path": "...",
      "per_page": 15,
      "to": 15,
      "total": 75
    }
  }
}
```

## Filter & Search

Beberapa endpoint support filtering dan searching:

### Barangs
- `search` - Cari berdasarkan nama atau kode barang
- `kategori_id` - Filter berdasarkan kategori ID

Contoh:
```
GET /api/barangs?search=mie&kategori_id=2&page=1&per_page=20
```

## Transactional Operations

Endpoint POST /penjualans menggunakan database transaction untuk memastikan data consistency:
- Validasi stok barang
- Dekrementasi stok otomatis
- Rollback jika ada error

## Integration

Swagger spec dapat diintegrasikan dengan:
- **Postman**: Import OpenAPI spec
- **Insomnia**: Import OpenAPI spec
- **Code Generators**: Menggunakan openapi-generator untuk auto-generate client SDK
- **Documentation Generators**: Menggunakan tools lain untuk generate docs

## Development Notes

- API menggunakan Laravel 12 dengan Sanctum authentication
- Database: MySQL 8.0
- Architecture: Action-Service pattern dengan DTOs
- Validation: Request validation classes
- Error handling: Centralized dengan logging
