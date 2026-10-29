# YBT Management Software

A PHP-rendered CRM and billing workspace built from the attached PRD v2.0. This is a **development preview**, with the remaining release requirements tracked in [docs/acceptance.md](docs/acceptance.md).

## Stack

PHP 8.3+, MariaDB/MySQL with PDO, separate CSS, vanilla JavaScript, Chart.js, Tabler Icons and Composer/Dompdf. No Node application runtime or JavaScript framework.

## Start locally

Follow [the Windows/XAMPP installation guide](docs/installation.md). Configure private database credentials and an application key; run `composer install`, then the one-time CLI installer. Serve **only `public/`**:

```text
php -S 127.0.0.1:8080 -t public bin/router.php
```

## Features in this preview

Secure shared sign-in, Owner/Manager access controls, customers, leads with conversion, products, tasks, quotations, standard invoice drafts/issuance, immutable snapshots, PDF/print, partial payments and reversals, expenses, date-filtered dashboard, reports and CSV, business settings, themes, audit logs, private encrypted provider credentials, controlled AI read context, Owner-approved task proposals, signed operational backups/restore, dependency-aware demo removal and in-product help.

## Verify

```text
composer test
composer audit --locked
```

Integration tests require a configured disposable MariaDB/MySQL database and use a rolled-back fixture. Never test on production data.

The schema version is 1.0.0. Installation refuses to reseed an existing workspace. Future schema changes require explicit migration scripts; do not rerun the installer as an upgrade command. Production deployment is a separate step after local acceptance and all PRD release gates are satisfied.
