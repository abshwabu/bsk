# Bina Site Kit: Shared-Hosting Deployment & Hardening Guide

This guide details how to deploy and harden **Bina Site Kit** on shared hosting environments (such as cPanel, DirectAdmin, or Plesk) where SSH access may be restricted or unavailable, queue daemons are not running, and `public_html` serves as the web root.

---

## 1. Architecture & Deployment Layouts

Shared hosting typically designates a public folder (e.g., `public_html` or `httpdocs`) as the web root. To protect sensitive application files, credentials (`.env`), and application logic, **never place the entire Laravel project root directly into `public_html`**.

### Layout (a): App Outside `public_html` (Recommended)

In this layout, the Laravel codebase resides one directory above the web root, keeping `.env`, `storage/`, and vendor packages completely unreachable from the public internet.

```
/home/username/
├── bina-app/                         # Laravel application root (private)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                         # Protected credentials
│   └── artisan
└── public_html/                      # Web root (publicly accessible)
    ├── .htaccess                     # Security headers & rewrites
    ├── index.php                     # Modified entry point
    ├── build/                        # Vite compiled CSS/JS assets
    ├── favicon.ico
    └── storage/                      # Symlink or published uploads
```

#### Step 1: Upload Files
1. Create a directory named `bina-app` inside `/home/username/`.
2. Upload the entire project codebase into `/home/username/bina-app/` (excluding the `public/` directory).
3. Move the contents of `public/` into `/home/username/public_html/`.

#### Step 2: Update `public_html/index.php`
Edit `/home/username/public_html/index.php` to point to the `bina-app` location:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../bina-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../bina-app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bina-app/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

---

### Layout (b): Symlink-Free Fallback (`bina:publish-storage`)

Standard Laravel storage relies on `php artisan storage:link`, which creates a symbolic link from `public/storage` to `storage/app/public`.

However, many shared hosting providers disable `symlink()` via `disable_functions` in `php.ini` or configure Apache with `Options -FollowSymLinks`. In this scenario, uploaded files, logos, and product images will result in 403 or 404 errors.

Bina Site Kit includes the `bina:publish-storage` command to support symlink-free hosting:

```bash
# Copy files directly from storage/app/public to public/storage
php artisan bina:publish-storage

# If public/storage already has a broken symlink or old files, clean and republish:
php artisan bina:publish-storage --clean
```

#### Synchronizing Uploads Without Symlinks
When running in a symlink-free environment, uploads made through the Filament admin panel are saved to `storage/app/public`. To make newly uploaded files available publicly:
1. **Scheduled Cron**: Schedule `php artisan bina:publish-storage` to run periodically (e.g., hourly).
2. **Web Trigger**: Or visit the web maintenance endpoint to clear caches and trigger asset publication.

---

## 2. Environment Configuration (`.env`)

Create your `.env` file in `/home/username/bina-app/.env` with production settings:

```dotenv
APP_NAME="Bina Site Kit"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_DAILY_DAYS=14
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_bina
DB_USERNAME=cpaneluser_bina
DB_PASSWORD="your-strong-db-password"

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Super Admin Initial Credentials
ADMIN_EMAIL=admin@yourdomain.com
ADMIN_PASSWORD="StrongP@ssw0rd!2026"

# Web Maintenance Endpoint (Required for hosts without SSH)
BINA_MAINTENANCE_ENABLED=true
BINA_MAINTENANCE_TOKEN=generate-a-random-32-character-secret-token-here

# Telegram Notifications (Optional)
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```

---

## 3. Web Server & `.htaccess` Hardening

The `public_html/.htaccess` included with Bina Site Kit provides four layers of protection:

1. **Forced HTTPS**: Automatically redirects all insecure HTTP traffic to HTTPS.
2. **Security Headers**:
   - `X-Frame-Options: SAMEORIGIN` (prevents clickjacking)
   - `X-Content-Type-Options: nosniff` (prevents MIME sniffing)
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
   - `Content-Security-Policy`: Conservative policy allowing Google Fonts, Alpine.js, Livewire, and Google Tag Manager.
3. **Gzip / Deflate Compression (`mod_deflate`)**: Compresses HTML, CSS, JS, JSON, and SVG.
4. **Browser Caching (`mod_expires`)**: Caches static assets, images, and fonts for 1 year while serving dynamic responses with `0 seconds` cache.

---

## 4. Web-Triggerable Maintenance (No SSH Required)

If your shared host does not provide SSH access, managing migrations and cache clearing via terminal is impossible. Bina Site Kit includes secure web-triggerable maintenance endpoints.

### Enabling Maintenance Routes
1. Set in `.env`:
   ```dotenv
   BINA_MAINTENANCE_ENABLED=true
   BINA_MAINTENANCE_TOKEN=c34a9812e9bfa210745f9d1234abcd56
   ```
2. The endpoint uses constant-time token comparison (`hash_equals`) and rate limiting to prevent brute-force attacks. If `BINA_MAINTENANCE_ENABLED` is `false`, the routes return a `404 Not Found`.

### Available Actions

| Action | URL | Description |
|---|---|---|
| **Cache Clear** | `https://yourdomain.com/_bina/{token}/cache-clear` | Runs `optimize:clear`, flushes navigation cache, and clears sitemap cache. |
| **Run Migrations** | `https://yourdomain.com/_bina/{token}/migrate` | Executes `php artisan migrate --force` and displays the migration output in your browser. |

*Tip: Add `Accept: application/json` header for JSON responses if integrating with CI/CD webhooks.*

---

## 5. Diagnostics: `php artisan bina:doctor`

Before launching or when troubleshooting an issue, run the built-in diagnostic tool:

```bash
php artisan bina:doctor
```

It validates:
- [x] **PHP Version**: Verifies PHP >= 8.2.0.
- [x] **Required Extensions**: `gd` or `imagick`, `mbstring`, `intl`, `fileinfo`, `pdo`, `openssl`, `curl`.
- [x] **Writable Storage**: Confirms `storage/` (root, logs, framework) is writable by the web server.
- [x] **Writable Bootstrap Cache**: Confirms `bootstrap/cache/` is writable.
- [x] **Application Key**: Checks that `APP_KEY` is present and valid.
- [x] **Production Debug Mode**: Warns if `APP_DEBUG=true` in production.
- [x] **Public Storage**: Validates that `public/storage` exists as a symlink or published directory.
- [x] **Database Connection**: Tests live PDO database connection.
- [x] **Telegram Settings**: Reports status of Telegram alert credentials.

---

## 6. Security Hardening Checklist

- [x] **Admin Login Throttling**: Filament admin login is rate limited (5 attempts per minute) and logs failed attempts to `storage/logs/laravel-*.log` with IP addresses and email for fail2ban / audit trails.
- [x] **Strong Passwords**: Enforces `Password::min(8)->letters()->mixedCase()->numbers()->symbols()` for admin accounts.
- [x] **Cookie Security**: `SESSION_SECURE_COOKIE=true` ensures session cookies are only sent over HTTPS. `SESSION_SAME_SITE=lax` mitigates CSRF.
- [x] **Log Rotation**: Default log driver is set to `daily` with a 14-day retention cycle (`LOG_DAILY_DAYS=14`), preventing logs from consuming disk quota.
- [x] **Disable Directory Browsing**: `Options -Indexes` in `.htaccess` blocks public directory listing.

---

## 7. Troubleshooting: Blank 500 Error with Empty `laravel.log`

A blank white page (HTTP 500) where `storage/logs/laravel.log` is completely empty indicates that PHP crashed **before** Laravel could boot its exception handler or before it had write permissions to create the log file.

Work through this diagnostic checklist in order:

### 1. Web Server User Cannot Write to `storage/logs`
- **Cause**: PHP crashed trying to log an error because the `storage` or `storage/logs` folder is not writable.
- **Fix**: In cPanel File Manager or via terminal, update directory permissions:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```
  *(On suPHP or FastCGI hosts with strict umask, use `755` for directories and `644` for files).*

### 2. PHP Version Mismatch
- **Cause**: The server default is PHP 7.4 or 8.0, but Laravel 11 requires PHP >= 8.2.
- **Fix**: In cPanel, navigate to **MultiPHP Manager** or **Select PHP Version** and switch the domain's PHP version to **PHP 8.2** or **PHP 8.3**.

### 3. Missing PHP Extensions
- **Cause**: Required extensions such as `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `intl`, or `gd` are disabled in the PHP profile.
- **Fix**: In cPanel, open **Select PHP Version** -> **Extensions** tab and enable:
  - `pdo_mysql`
  - `mbstring`
  - `fileinfo`
  - `intl`
  - `gd` or `imagick`
  - `curl`

### 4. `.htaccess` Unsupported Directives
- **Cause**: Some Apache hosts do not permit `Options -MultiViews` or `Header` rules if `mod_headers` is disabled, triggering a 500 Internal Server Error immediately.
- **Fix**: Temporarily rename `public_html/.htaccess` to `public_html/.htaccess.bak`. If the 500 error disappears, inspect which directive in `.htaccess` is unsupported by your host.

### 5. `open_basedir` Restriction
- **Cause**: The server's `open_basedir` security setting restricts PHP scripts to paths inside `/home/username/public_html`, preventing `index.php` from accessing `/home/username/bina-app`.
- **Fix**: In cPanel or WHM, add `/home/username/bina-app/` to the allowed `open_basedir` path, or contact your hosting support to relax `open_basedir` for your account.

### 6. Memory Limit Exhaustion
- **Cause**: Shared hosting default memory limit (often 64MB or 128MB) is exhausted during Filament compilation.
- **Fix**: Add to `bina-app/.env` or `public_html/.user.ini`:
  ```ini
  memory_limit = 256M
  max_execution_time = 120
  ```

### 7. Inspect the Server's Native Error Log
- When `storage/logs/laravel.log` is empty, check the host's web server error log:
  - In cPanel: Go to **Metrics** -> **Errors**.
  - In File Manager: Check for a file named `error_log` located directly in `public_html/`.
  - Via SSH: Check `/usr/local/apache/logs/error_log` or `~/logs/`.
