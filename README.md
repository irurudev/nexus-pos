# Backend README — POS API (Laravel)

This README is the canonical documentation entry for the backend (the `be/` folder). It focuses on backend-specific docs, developer notes, common commands, and how to push only the `be` code to a remote repository (if you want to publish the backend as its own repo).

---

## Quick links (backend docs)

- **`README/IMPLEMENTASI.md`** — Complete API reference: endpoints, DB schema, access control, folder structure, and implementation notes.
- **`README/SWAGGER.md`** — Swagger/OpenAPI docs & usage (UI & spec).
- **`public/api/swagger.json`** — Generated OpenAPI JSON (regenerate with `php artisan l5-swagger:generate`).

---

## Quick commands

# Prepare local environment (Linux / Laravel)

**Prerequisites (examples on Linux):**
- PHP 8.x and required extensions: `pdo_mysql`, `mbstring`, `bcmath`, `xml`, `json`, `tokenizer`, `fileinfo`, `zip`
- Composer (https://getcomposer.org)
- MySQL 8.x or compatible (or MariaDB)
- Git

**Setup steps:**
```bash
# from repo root, go to backend folder
cd be

# copy environment file and edit DB credentials
cp .env.example .env
# edit .env DB_* values to match your local database

# install PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# generate app key
php artisan key:generate

# run migrations and seeders
php artisan migrate --seed --force

# generate swagger JSON
php artisan l5-swagger:generate

# set permissions (if needed)
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache || true

# serve locally (development)
php artisan serve --host=0.0.0.0 --port=8000
```

**Run tests:**
```bash
# if using Pest
vendor/bin/pest --colors
# or
php artisan test
```

**Logs & troubleshooting:**
- Laravel logs: `tail -n 200 storage/logs/laravel.log`
- Database status (Linux): `systemctl status mysql` or `journalctl -u mysql --since "1 hour ago"`

---

> Note: This README intentionally provides local Laravel/Linux setup steps only.