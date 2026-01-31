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

#### **Tabel: `kategori`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | ID kategori produk. |
| `nama_kategori` | String(50) | Misal: ATK, Rumah Tangga, Masak. |

#### **Tabel: `barang`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `kode_barang` | String(20) (PK) | Barcode atau kode SKU unik. |

> Note: `kode_barang` is generated automatically when not provided using a `sequences` table (prefix `BRG` + sequence number, e.g. `BRG001`, `BRG1000`). Sequence values increase indefinitely; formatting preserves numeric suffix without fixed truncation.
| `kategori_id` | BigInt (FK) | Relasi ke `kategori.id`. |
| `nama` | String(100) | Nama produk. |
| `harga_beli` | Decimal(15,2) | Modal per unit (untuk hitung laba). |
| `harga_jual` | Decimal(15,2) | Harga jual ke pelanggan. |
| `stok` | Integer | Sisa jumlah stok di gudang. |

#### **Tabel: `pelanggan`**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id_pelanggan` | String(20) (PK) | ID unik member. |

> Note: `id_pelanggan` is generated automatically when not provided using a `sequences` table (prefix `PGN` + sequence number, e.g. `PGN001`, `PGN1000`). Sequence values increase indefinitely; formatting preserves numeric suffix without fixed truncation.
| `nama` | String(100) | Nama lengkap pelanggan. |
| `domisili` | String(50) | Wilayah tempat tinggal. |
| `jenis_kelamin` | Enum | `'PRIA'`, `'WANITA'`. |
| `poin` | Integer | Akumulasi poin loyalitas pelanggan. |

---

### 3. Kelompok Transaksi
Tabel yang mencatat histori aktivitas jual-beli.

#### **Tabel: `penjualan` (Header)**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id_nota` | String(20) (PK) | Nomor nota unik transaksi. |
| `tgl` | DateTime | Waktu terjadinya transaksi. |
| `kode_pelanggan`| String(20) (FK) | Relasi ke `pelanggan.id_pelanggan`. |
| `user_id` | BigInt (FK) | ID Kasir yang melayani (`users.id`). |
| `subtotal` | Decimal(15,2) | Total harga kotor. |
| `total_akhir` | Decimal(15,2) | Total yang dibayar (setelah diskon/pajak). |

#### **Tabel: `item_penjualan` (Detail)**
| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment ID. |
| `nota` | String(20) (FK) | Relasi ke `penjualan.id_nota`. |
| `kode_barang` | String(20) (FK) | Relasi ke `barang.kode_barang`. |
| `qty` | Integer | Jumlah barang yang dibeli. |
| `harga_satuan` | Decimal(15,2) | Snapshot harga jual saat transaksi. |
| `jumlah` | Decimal(15,2) | Hasil kalkulasi `qty * harga_satuan`. |

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