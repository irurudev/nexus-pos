# Laravel 12 API Project

Ini adalah skeleton proyek Laravel 12 yang dioptimalkan untuk skalabilitas menggunakan **Action-Service Pattern** dan dokumentasi **Swagger/OpenAPI**.

## 🏗️ Struktur Folder

Proyek ini menggunakan struktur folder yang dimodifikasi untuk memisahkan logika bisnis dari entry point aplikasi.

```text
project-root/
├── app/
│   ├── Actions/                # LOGIKA BISNIS SPESIFIK (The "How")
│   │   └── 
│   ├── DTOs/ (Data Transfer)   # STRUKTUR DATA (Type-safety antar layer)
│   │   └── 
│   ├── Enums/                  # STANDAR NILAI (Avoid Magic Numbers)
│   │   └── 
│   ├── Http/
│   │   ├── Controllers/        # TRAFFIC CONTROLLER (The "Who")
│   │   │   ├── Api/            # API Controllers (dengan Swagger Attributes)
│   │   │   │   ├── BaseController.php
│   │   │   │   └── OrderController.php
│   │   │   └── Web/            # Web Controllers
│   │   └── Requests/           # VALIDASI & SCHEMA SWAGGER
│   │       ├── Auth/
│   │       └── Order/
│   │           └── StoreOrderRequest.php
│   ├── Models/                 # DATABASE SCHEMA & RELATIONSHIP
│   ├── Observers/             # Model observers (e.g., AuditObserver)
│   ├── Policies/              # Authorization policies (PenjualanPolicy, BarangPolicy, etc.)
│   ├── Providers/             # KONFIGURASI SERVICES
│   │   └── AppServiceProvider.php
│   ├── Services/              # INTEGRASI EKSTERNAL (The "Outside World")
│   │   ├── MidtransService.php
│   │   └── FirebaseService.php
│   └── Traits/                 # REUSABLE CODE (Logika yang sering dipakai)
│       └── HasApiResponse.php
├── bootstrap/
│   └── app.php                 # PUSAT KONFIGURASI (Routing, Middleware, Exception)
├── config/                     # KONFIGURASI FRAMEWORK (Dipublish sesuai kebutuhan)
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── lang/                       # MULTI-BAHASA (Localization)
├── public/                     # ASET PUBLIK & GENERATED SWAGGER (JSON/YAML)
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── api.php                 # ENDPOINT API
│   ├── web.php                 # ENDPOINT WEB
│   └── console.php             # SCHEDULED TASKS / ARTISAN COMMANDS
├── storage/                    # LOGS & UPLOADS
└── tests/
    ├── Feature/                # INTEGRATION TEST (Endpoint & Flow)
    └── Unit/                   # LOGIC TEST (Testing Actions/Services)