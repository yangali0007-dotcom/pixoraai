# Run YBT on Windows 11

YBT is a PHP application with a MariaDB/MySQL database. The existing PixoraAI website remains separate; the application is in `ybt/`.

## 1. Prepare your tools

1. Install VS Code, Composer, and XAMPP. Use a supported PHP 8.3 or later runtime with `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, and `sodium` enabled. If XAMPP includes an older PHP version, use a separate supported PHP installation for the application and XAMPP for MariaDB.
2. In XAMPP Control Panel, start **MySQL**. Apache is optional for the development-server method below.
3. Open the repository folder in VS Code. Open **Terminal → New Terminal**.
4. Check `php -v` and `composer --version`. Make sure the PHP command selects the intended supported installation.
5. Run `cd ybt`, then `composer install`.

## 2. Create a database

Open XAMPP’s phpMyAdmin locally (normally `http://localhost/phpmyadmin`). On the SQL tab, run the following, replacing the password with a new strong password:

```sql
CREATE DATABASE ybt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ybt'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_A_STRONG_UNIQUE_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES
ON ybt.* TO 'ybt'@'127.0.0.1';
```

Use an administrator account only for database setup. The application should use the separate `ybt` account. After installation, a database administrator can revoke schema-changing privileges from the runtime account. Keep a separate migration account for later schema updates.

## 3. Create protected local configuration

Create `ybt/config/local.php`. This file is ignored by Git and is never served because the web document root is `ybt/public`.

```php
<?php
return [
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=ybt;charset=utf8mb4',
    'db_user' => 'ybt',
    'db_password' => 'YOUR_DATABASE_PASSWORD',
    'key' => 'YOUR_64_CHARACTER_HEX_APPLICATION_KEY',
    'storage' => 'C:/ybt-private-storage',
    'environment' => 'local',
];
```

Generate the application key once with `php -r "echo bin2hex(random_bytes(32));"`. Copy it into `key`. Keep an independent private copy. Losing this key prevents AI key decryption and backup signature verification. Never put this file or key in Git, screenshots, ordinary exports, or a public folder.

## 4. Provision your Owner account

Run from `ybt/`:

```text
php bin/install.php --email=you@example.com --name="Your Name" --demo
```

The command reads the Owner password from standard input. Use at least 12 characters. **Terminal input is visible**; run privately. The password is hashed before storage. The `--demo` option loads sample records; omit it for an empty workspace. The installer refuses to reseed an existing installation. No public registration or browser installer exists.

Demo Manager accounts are inactive and have an unknown random password. Open Managers to set a password and activate an account if needed.

## 5. Start the application

From `ybt/`, run:

```text
php -S 127.0.0.1:8080 -t public bin/router.php
```

Open `http://127.0.0.1:8080` and sign in with the account you just created. Keep the terminal open while using the app. Press Ctrl+C to stop it. Start MySQL and repeat this command next time; do not rerun installation.

For Apache, configure a virtual host with `DocumentRoot` pointing to the absolute `ybt/public` directory. Never serve the repository or `ybt/` itself. `app/`, `config/`, `vendor/`, `database/`, and private storage must remain outside public access.

### HTTPS behind a reverse proxy

For a public HTTPS deployment where a proxy terminates TLS, set `'https' => true` in private `config/local.php`. Alternatively, set `YBT_HTTPS=1` in the application environment if `https` is absent from `config/local.php`. An explicit `'https' => false` in that file overrides `YBT_HTTPS=1`; remove that setting or change it to `true` when deploying behind a TLS-terminating proxy. This marks session cookies Secure even when the proxy connects to PHP over HTTP. Leave HTTPS disabled for local HTTP development. Forwarded headers alone are not trusted.

## 6. Explore and customize

- Dashboard: try this month, previous month and custom dates.
- Customers and products: add your own records.
- Invoices: create a draft, issue it, record partial payments, print/download a PDF.
- Settings → Business profile: enter your business details.
- Settings → Appearance: select theme and density.
- Managers: grant only the operations each person needs.
- Settings → Backup & restore: create and download a backup.
- Settings → Demo data & reset: preview and safely remove samples.

## Account recovery

A server administrator can issue a 15-minute, single-use password reset token:

```text
php bin/reset-password.php you@example.com
```

Privately give the account holder the resulting relative reset URL under the application’s base URL. Never publish reset tokens. Automatic email delivery is not configured.

## Local verification

Run `composer test` from `ybt/` for database-backed integration tests. They create synthetic businesses in a transaction and roll back. Run `php tests/concurrency.php` for the two-connection sequence and backup snapshot regressions; this check commits only its synthetic fixture and removes it in a `finally` block. Never point testing at a live production database.

## Release status

Read `acceptance.md` before production use. This is a development preview with documented incomplete PRD requirements. The PHP built-in server is for local development only.
