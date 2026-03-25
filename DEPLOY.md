# TicketMaster.LT — Deployment Guide
## Hostinger Shared Hosting (PHP 8.1 / MySQL)

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.1+ (with `pdo_mysql`, `mbstring`, `intl`, `fileinfo`, `gd` extensions) |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Composer | 2.x |
| SSH access | Recommended (Hostinger Business plan+) |

---

## 1. Local Preparation

```bash
# Clone / download the repo
git clone <repo-url> ticketmaster
cd ticketmaster

# Install production dependencies (no dev packages, optimized autoloader)
composer install --no-dev --optimize-autoloader

# Copy and configure environment file
cp .env.example .env
```

Edit `.env`:

```ini
CI_ENVIRONMENT = production

app.baseURL   = 'https://yourdomain.com/ticketmaster/'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = your_db_name
database.default.username = your_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.charset  = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
```

> **Note:** On Hostinger, database host is typically `localhost`.
> Create the database in hPanel → Databases → MySQL Databases.

---

## 2. Upload Files

### Option A — FTP / File Manager

Upload the entire project to `public_html/ticketmaster/` **except** these directories (generate them on the server):
- `vendor/` — run `composer install` on the server instead
- `writable/` — created automatically on first run

Minimum files to upload:
```
app/
database/
public/
composer.json
composer.lock
spark
.htaccess
```

### Option B — Git (SSH)

```bash
# On the server via SSH
cd public_html
git clone <repo-url> ticketmaster
cd ticketmaster
composer install --no-dev --optimize-autoloader
```

---

## 3. Document Root Configuration

Hostinger must serve from the `public/` subdirectory.

**In hPanel → Websites → Manage → Advanced → PHP Configuration:**

Set document root to:
```
public_html/ticketmaster/public
```

If document root cannot be changed (shared plan), the included `.htaccess` at the
project root will redirect all traffic to `public/` automatically.

---

## 4. File Permissions

```bash
# Via SSH
chmod -R 755 public_html/ticketmaster/writable/
chmod -R 755 public_html/ticketmaster/writable/uploads/
```

Ensure these directories are writable by the web server:
- `writable/cache/`
- `writable/logs/`
- `writable/session/`
- `writable/uploads/invoices/`

---

## 5. Database Setup

### Option A — Spark Migrations (recommended)

```bash
# Via SSH in project root
php spark migrate
php spark db:seed DatabaseSeeder
```

This creates all tables and inserts the two default users:
- **Admin:** `admin@ticketmaster.lt` / `Admin@1234`
- **User:**  `user@ticketmaster.lt`  / `User@1234`

### Option B — Direct SQL Import

```bash
# Import schema first
mysql -u <user> -p <database> < database/schema.sql

# Then run seeder for users (required — handles bcrypt hashing)
php spark db:seed DatabaseSeeder

# Optional: load demo/test data
mysql -u <user> -p <database> < database/seed_test_data.sql
```

> **Change default passwords immediately after first login.**

---

## 6. Production Optimization

Run after every deployment:

```bash
# Cache config, routes, and services (significant performance boost)
php spark optimize

# If you need to clear the cache (e.g. after config changes)
php spark cache:clear
php spark optimize
```

`php spark optimize` writes:
- `writable/cache/config_cache.php`
- `writable/cache/routes_cache.php`
- `writable/cache/services_cache.php`

---

## 7. PHP Version (Hostinger)

The `public/.htaccess` already contains:

```apache
AddHandler application/x-httpd-php81 .php
```

This forces PHP 8.1. If your Hostinger plan uses a different handler name, adjust it:
- PHP 8.2: `application/x-httpd-php82`
- PHP 8.3: `application/x-httpd-php83`

You can verify the active PHP version at `/ping` → should return `{"status":"ok","app":"TicketMaster.LT"}` without errors.

---

## 8. Verify Deployment

| Check | URL / Command |
|---|---|
| App loads | `https://yourdomain.com/ticketmaster/` |
| Health ping | `https://yourdomain.com/ticketmaster/ping` |
| Login works | Use default credentials above |
| PDF upload | Create an invoice, attach a PDF |
| Excel export | Reports → Export Excel |
| PDF export | Reports → Export PDF |
| Unmatched tickets | Load test data, check `/unmatched-tickets` |

---

## 9. Security Checklist

- [ ] Change default admin and user passwords
- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Verify `app.baseURL` ends with a trailing slash
- [ ] Confirm `writable/` is NOT web-accessible (`.htaccess` blocks it)
- [ ] Confirm `app/` is NOT web-accessible (`.htaccess` blocks it)
- [ ] Confirm `vendor/` is NOT web-accessible (`.htaccess` blocks it)
- [ ] Enable HTTPS on your domain (Hostinger hPanel → SSL)
- [ ] Add `app.forceGlobalSecureRequests = true` to `.env` once HTTPS is active
- [ ] Review session cookie settings in `app/Config/Cookie.php`

---

## 10. Updating the Application

```bash
# Via SSH
cd public_html/ticketmaster

git pull origin main

composer install --no-dev --optimize-autoloader

php spark migrate          # run any new migrations
php spark cache:clear
php spark optimize
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| Blank page / 500 error | Check `writable/logs/` for errors; set `CI_ENVIRONMENT = development` temporarily |
| "Unable to connect to database" | Verify `.env` DB credentials; check MySQL host is `localhost` |
| Sessions not persisting | Ensure `writable/session/` is writable (`chmod 755`) |
| PDF/Excel not downloading | Ensure `dompdf` and `phpspreadsheet` installed via Composer |
| File upload fails | Check `writable/uploads/invoices/` exists and is writable |
| Wrong base URL | Set `app.baseURL` exactly — include trailing slash |
| `php spark` not found | Run as `php8.1 spark` or check Hostinger's PHP CLI path |

---

## Directory Structure Summary

```
ticketmaster/
├── app/                    # Application code (not web-accessible)
│   ├── Config/
│   ├── Controllers/
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeds/
│   ├── Filters/
│   ├── Language/
│   ├── Models/
│   └── Views/
├── database/               # SQL scripts (not web-accessible)
│   ├── schema.sql
│   └── seed_test_data.sql
├── public/                 # Document root ← point server here
│   ├── assets/
│   │   ├── css/app.css
│   │   └── js/app.js
│   ├── index.php
│   └── .htaccess
├── vendor/                 # Composer packages (not web-accessible)
├── writable/               # Runtime files (not web-accessible)
│   ├── cache/
│   ├── logs/
│   ├── session/
│   └── uploads/invoices/
├── .env                    # Environment config (never commit)
├── .htaccess               # Root redirect to public/
├── composer.json
└── spark                   # CLI runner
```

---

*TicketMaster.LT — Ing: Jorge Luis Oliva Matos*
