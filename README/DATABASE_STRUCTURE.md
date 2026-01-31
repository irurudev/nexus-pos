# Database Documentation - POS & Sales Analytics

Dokumentasi ini menjelaskan skema database yang dirancang untuk mendukung sistem Point of Sale (POS), manajemen stok, autentikasi kasir, dan dashboard analitik penjualan.

## 📊 Entity Relationship Diagram (ERD)

Sistem ini menggunakan relasi database yang ternormalisasi untuk memastikan integritas data dan kemudahan dalam pelaporan.



---

## 🗄️ Detail Tabel

### 1. Kelompok Autentikasi
Tabel ini menangani akses masuk dan tanggung jawab (audit trail) pada sistem.

#### **Tabel: `users`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment ID. |
| `name` | String(100) | Nama lengkap kasir atau admin. |
| `username` | String(50) | ID login unik (Unique). |
| `password` | String | Hash password (BCRYPT). |
| `role` | Enum | `'admin'`, `'kasir'`. |
| `is_active` | Boolean | Status akun (1=Aktif, 0=Nonaktif). |

---

### 2. Kelompok Master Data
Data referensi yang digunakan untuk operasional harian.

#### **Tabel: `kategoris`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | ID kategori produk. |
| `nama_kategori` | String(50) | Misal: ATK, Rumah Tangga, Masak. |
| `timestamps` | Timestamp | Created/updated timestamps. |
| `deleted_at` | Timestamp nullable | Soft delete field (softDeletes). |

#### **Tabel: `barangs`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `kode_barang` | String(20) (PK) | Barcode atau kode SKU unik. |
| `kategori_id` | BigInt (FK) | Relasi ke `kategoris.id`. |
| `nama` | String(100) | Nama produk. |
| `harga_beli` | Decimal(15,2) | Modal per unit (untuk hitung laba). |
| `harga_jual` | Decimal(15,2) | Harga jual ke pelanggan. |
| `stok` | Integer | Sisa jumlah stok di gudang. |
| `timestamps` | Timestamp | Created/updated timestamps. |
| `deleted_at` | Timestamp nullable | Soft delete field (softDeletes). |

> Note: `kode_barang` dapat dibentuk menggunakan `sequences` (prefix `BRG` + counter) saat diperlukan.

#### **Tabel: `pelanggans`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id_pelanggan` | String(20) (PK) | ID unik member. |
| `nama` | String(100) | Nama lengkap pelanggan. |
| `domisili` | String(50) | Wilayah tempat tinggal. |
| `jenis_kelamin` | Enum | `'PRIA'`, `'WANITA'`. |
| `poin` | Integer | Akumulasi poin loyalitas pelanggan. |
| `timestamps` | Timestamp | Created/updated timestamps. |
| `deleted_at` | Timestamp nullable | Soft delete field (softDeletes). |

> Note: `id_pelanggan` dapat dibuat dari `sequences` (prefix `PGN` + counter) saat diperlukan.

---

### 3. Kelompok Transaksi
Tabel yang mencatat histori aktivitas jual-beli.

#### **Tabel: `penjualans` (Header)**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id_nota` | String(20) (PK) | Nomor nota unik transaksi. |
| `tgl` | DateTime | Waktu terjadinya transaksi. |
| `kode_pelanggan`| String(20) (FK) | Relasi ke `pelanggans.id_pelanggan` (nullable). |
| `user_id` | BigInt (FK) | ID Kasir yang melayani (`users.id`). |
| `subtotal` | Decimal(15,2) | Total harga kotor. |
| `diskon` | Decimal(15,2) | Nilai diskon (default 0). |
| `pajak` | Decimal(15,2) | Nilai pajak (default 0). |
| `total_akhir` | Decimal(15,2) | Total yang dibayar (setelah diskon/pajak). |
| `timestamps` | Timestamp | Created/updated timestamps. |
| `deleted_at` | Timestamp nullable | Soft delete field (softDeletes). |

#### **Tabel: `item_penjualans` (Detail)**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment ID. |
| `nota` | String(20) (FK) | Relasi ke `penjualans.id_nota`. |
| `kode_barang` | String(20) (FK) | Relasi ke `barangs.kode_barang`. |
| `nama_barang` | String(100) nullable | Nama barang snapshot (historical data). |
| `qty` | Integer | Jumlah barang yang dibeli. |
| `harga_satuan` | Decimal(15,2) | Snapshot harga jual saat transaksi. |
| `jumlah` | Decimal(15,2) | Hasil kalkulasi `qty * harga_satuan`. |
| `timestamps` | Timestamp | Created/updated timestamps. |
| `deleted_at` | Timestamp nullable | Soft delete field (softDeletes). |

---

### 4. System & Audit
Tabel-tabel sistem pendukung dan audit.

#### **Tabel: `audit_logs`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment ID. |
| `user_id` | ForeignId nullable | FK -> `users.id` (nullable, nullOnDelete) |
| `action` | String | `create` | `update` | `delete` |
| `auditable_type` | String | Class name / model namespace yang diaudit |
| `auditable_id` | String nullable | Primary key value dari resource yang diaudit |
| `old_values` | JSON nullable | Snapshot sebelum perubahan |
| `new_values` | JSON nullable | Snapshot sesudah perubahan |
| `ip_address` | String nullable | IP address yang melakukan aksi |
| `user_agent` | Text nullable | User agent yang melakukan aksi |
| `url` | String nullable | URL request |
| `timestamps` | Timestamp | Created/updated timestamps.

#### **Tabel: `sequences`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `name` | String(100) (PK) | Nama sequence, contohnya `penjualan` atau `barang` |
| `value` | Unsigned BigInt | Nilai counter saat ini |
| `timestamps` | Timestamp | Created/updated timestamps

#### **Tabel: `personal_access_tokens`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment ID |
| `tokenable_type`/`tokenable_id` | Morphs | Relasi ke model pemilik token |
| `name` | Text | Nama token |
| `token` | String(64) (Unique) | The token value |
| `abilities` | Text nullable | Serialized ability list |
| `last_used_at` | Timestamp nullable | Waktu terakhir dipakai |
| `expires_at` | Timestamp nullable (indexed) | Waktu kadaluarsa token |
| `timestamps` | Timestamp | Created/updated timestamps |

---

## 🔗 Aturan Relasi & Integritas (Business Rules)

1. **Audit Trail**: Setiap entri di `penjualan` wajib mencantumkan `user_id`. Ini mencegah adanya transaksi gelap tanpa penanggung jawab.
2. **Snapshot Harga**: `item_penjualan` harus menyimpan `harga_satuan` secara mandiri. Hal ini penting agar laporan keuangan masa lalu tidak berubah meskipun harga di tabel `barang` naik atau turun di kemudian hari.
3. **Stok Control**: Setiap penambahan data di `item_penjualan` harus diikuti dengan pengurangan nilai `stok` di tabel `barang`.
4. **Data Purging**: Jika data `pelanggan` dihapus, maka data `penjualan` tetap dipertahankan dengan mengubah referensi menjadi `null` atau `pelanggan_umum`.

---

## 📈 Kebutuhan Dashboard Analitik

Skema ini mendukung query untuk:
- **Laba Bersih**: `SUM(item_penjualan.jumlah - (barang.harga_beli * item_penjualan.qty))`.
- **Top Kategori**: Pengelompokan `qty` terjual berdasarkan `kategori_id`.
- **Performa Kasir**: Total `subtotal` per `user_id` per bulan.