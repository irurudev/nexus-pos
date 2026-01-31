# React POS Frontend (Consumer of Laravel API)

Dokumentasi struktur folder untuk frontend React yang mengimplementasikan sistem POS dan Analitik.

## 🏗️ Folder Structure (Alphabetical)

* **`src/api/`** - Konfigurasi base Axios & API Interceptors (Token Handling).
* **`src/components/`** - Reusable UI components (Atomic Design).
* **`src/context/`** - Global state management (Auth Context, Theme).
* **`src/features/`** - Modul bisnis utama (Auth, Inventory, POS, Analytics).
* **`src/hooks/`** - Custom React hooks untuk logic yang reusable.
* **`src/layouts/`** - Master layouts (AdminLayout, GuestLayout).
* **`src/pages/`** - Page-level components yang terhubung ke router.
* **`src/routes/`** - Centralized route configuration (Protected & Public).
* **`src/services/`** - API service calls yang menembak endpoint Laravel.
* **`src/utils/`** - Helper functions (Currency formatter, Date parser).

---

## 🔐 Integrasi Laravel API
1. **Authentication**: Menggunakan Bearer Token yang disimpan di `localStorage` atau `Cookie`.
2. **State Management**: Data kasir yang login disimpan dalam `AuthContext`.
3. **Data Fetching**: Menggunakan `Axios` dengan interceptor untuk menangani error 401 (Unauthorized).

## 📊 Analytics Visualization
Data dari endpoint `/api/analytics` akan divisualisasikan menggunakan library **Chart.js** atau **Recharts** yang terletak di folder `features/analytics`.