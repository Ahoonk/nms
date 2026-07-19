# Deployment Guide

This project is a Laravel + Inertia.js + Vue NMS portal.

## 1. Prepare GitHub

1. Create a new empty repository on GitHub.
2. Initialize this folder as a Git repository.
3. Commit the current source code.
4. Add the GitHub remote and push the `main` branch.

Example:

```bash
git init
git branch -M main
git add .
git commit -m "Initial commit"
git remote add origin <your-github-repo-url>
git push -u origin main
```

## 2. Create the Database on VPS

Use MySQL/MariaDB on the VPS:

```sql
CREATE DATABASE nms_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nms_user'@'%' IDENTIFIED BY 'strong-password';
GRANT ALL PRIVILEGES ON nms_portal.* TO 'nms_user'@'%';
FLUSH PRIVILEGES;
```

If you prefer a tighter scope, replace `%` with the private IP or host allowed to reach MySQL.

## 3. Deploy the App

1. Clone the GitHub repository on the VPS.
2. Copy `.env.example` to `.env`.
3. Set production values:
   - `APP_URL`
   - `DB_*`
   - `CACHE_STORE`
   - `SESSION_DRIVER`
   - `QUEUE_CONNECTION`
   - `ZABBIX_*`
4. Install dependencies:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

5. Generate the app key:

```bash
php artisan key:generate
```

6. Run migrations and seeders:

```bash
php artisan migrate --force
php artisan db:seed --force
```

7. Clear and cache config/routes/views:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. WireGuard Notes

- The app does not manage WireGuard itself.
- It expects the VPS host to already have route access through the WireGuard tunnel.
- Set Zabbix `base_url` to the private address reachable through WireGuard.
- If Zabbix is not reachable, monitoring pages will show empty data until the cache expires.

## 5. Useful Checks

```bash
php artisan migrate:status
php artisan route:list
php artisan test
```

