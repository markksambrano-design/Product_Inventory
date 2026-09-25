# Product Inventory Management System

A Laravel 11 inventory, purchasing, sales/POS, reporting, notification, backup, and security system.

## 1. Technology

- Laravel 11.41.3
- PHP 8.2+
- MySQL 8+ or SQLite
- Composer
- Node.js 20+ and npm
- Blade views with Bootstrap and Vite
- PHPUnit 11
- Database sessions, queue, and cache supported by environment configuration

## 2. Installation

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run dev
php artisan serve
```

For a production build:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Create the public storage link when product images are used:

```bash
php artisan storage:link
```

## 3. Environment Configuration

Important `.env` values:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=product_inventory
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_INACTIVITY_TIMEOUT=30
SESSION_SECURE_COOKIE=false

QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="inventory@example.com"
MAIL_FROM_NAME="Product Inventory"

DEFAULT_ADMIN_PASSWORD=
DEFAULT_STAFF_PASSWORD=

BACKUP_DISK=local
BACKUP_ENCRYPT=true
BACKUP_RETENTION_DAYS=30
```

Use real SMTP credentials before enabling password reset, 2FA email codes, verification emails, or email alerts in production. Set `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true` behind HTTPS.

## 4. Development Accounts

The seeder uses these emails:

- Admin: `admin@inventory.com`
- Staff: `staff@inventory.com`

Set `DEFAULT_ADMIN_PASSWORD` and `DEFAULT_STAFF_PASSWORD` in `.env`. If they are blank, the development fallback passwords are `Admin@12345` and `Staff@12345`. Replace them before production use.

## 5. Roles and Permissions

### Admin

Admins can manage users, products, categories, suppliers, reports, activity logs, backups, restores, stock adjustments, expired disposals, locations, stock transfers, stock returns, purchase-order approvals/receiving/cancellation, discount approvals, and daily sales closing.

### Staff

Staff can view products and inventory, perform stock-in and stock-out, count inventory, create purchase orders, and create POS sales. Staff cannot approve discounts, approve/receive/cancel purchase orders, manage catalog records, adjust stock, transfer stock, restore backups, manage users, or access admin reports.

### Authentication protections

- Login rate limit: five attempts per minute per email/IP key.
- 2FA verification rate limit.
- Password reset flow.
- Session inactivity timeout, default 30 minutes.
- Last administrator cannot be deleted or demoted.
- Email verification code and routes are available but are not enforced as a login blocker during development.
- All protected routes require authentication and session timeout middleware.

## 6. Inventory Modules

### Product Catalog

Products include product code, barcode, name, brand, category, unit, cost price, selling price, quantity, minimum stock, image, status, and description. Product codes and barcodes have database uniqueness protection. New stock should be added through Stock In rather than directly editing product quantity.

### Categories and Suppliers

Admins manage categories, hierarchical categories, suppliers, contacts, addresses, and active/inactive status.

### Stock In

Records supplier, quantity, cost, batch, expiration, date, reference, and remarks. It updates product quantity and creates a batch and signed inventory-ledger movement.

### Stock Out

Records stock depletion using FEFO: usable batches with the nearest expiration are consumed first. Expired batches are excluded. Product and batch quantities are updated atomically.

### Batch and Expiration Tracking

Batch quantities, received dates, expiration dates, FEFO allocation, expiration monitoring, and expired-product disposal are supported.

### Adjustments and Physical Counts

Admin stock adjustments can increase or decrease a selected batch. Physical counts record system quantity, actual quantity, variance, and correction movement.

### Inventory Ledger

`inventory_movements` is append-only and records signed movements for stock-in, stock-out, purchase receiving, adjustments, disposal, physical counts, POS sales, returns, transfers, and other inventory operations. The admin page is `/inventory-ledger`.

Run the historical backfill once after upgrading:

```bash
php artisan inventory:backfill-ledger
```

The backfill is idempotent. Running it again does not duplicate historical movements.

### Reconciliation

The system compares `products.quantity` with the sum of product batches. It reports mismatches without silently rewriting stock:

```bash
php artisan inventory:reconcile
```

Review mismatches before applying any manual correction.

### Locations and Transfers

The system creates a `MAIN` / Main Warehouse location. Admins can transfer stock between active locations. Transfers update source and destination balances and create paired ledger movements.

### Returns and Damaged Goods

Admins can record customer returns or damaged goods with quantity, reason, disposition, and optional batch. Restocked returns create a traceable return batch and positive ledger movement. Damaged goods create a negative ledger movement.

## 7. Purchase Orders

Purchase-order lifecycle:

```text
draft -> ordered -> partial -> received
                 \-> cancelled
```

New POs start as `draft` and `pending` approval. An admin must approve before receiving. Existing legacy `ordered` records remain compatible.

Features:

- Multiple products per PO.
- Supplier, order date, expected date, notes, quantities, unit costs, and expiration dates.
- Admin approval route.
- Locked receiving transaction to prevent double receiving.
- Partial receiving using `received_quantity`.
- `purchase_order_receipts` delivery history.
- Required cancellation reason.
- Stock-in, batch, product quantity, and ledger movement created for each receipt.

## 8. Sales / POS

POS is available at `/sales`.

Features:

- Multiple products per sale.
- Cash, card, and GCash payment methods.
- Automatic total, cash received, and change due calculation.
- Idempotency key to prevent duplicate submissions.
- FEFO batch depletion.
- Printable receipt from the sale detail page.
- Customer return/refund recording.
- Staff discounts enter `pending_discount` status and require admin approval.
- Daily sales closing by date with totals per payment method and refunds.

Sales routes:

- `GET /sales` sales list.
- `GET /sales/create` POS screen.
- `POST /sales` create a sale.
- `GET /sales/{sale}` printable receipt/detail.
- `POST /sales/{sale}/approve-discount` admin discount approval.
- `POST /sales/{sale}/return` return/refund.
- `POST /sales/close-day` admin daily closing.

## 9. Reports and Exports

Admin reports are available at `/reports` and `/reports/export`.

Available report/export types:

- Products and current stock.
- Stock-in history.
- Stock-out history.
- Expired batches.
- Inventory movements.
- Inventory valuation using quantity × cost price.
- Profit and margin from sales versus product cost.
- Fast-moving and slow-moving products.
- Supplier order and received-order performance.
- Low-stock count.
- Near-expiry count for the next 30 days.

Exports are UTF-8 CSV files compatible with Excel. The report page supports browser printing, including Print to PDF without a third-party PDF package.

## 10. Notifications

The notification page is `/notifications`.

In-app alert types:

- Low or out-of-stock products.
- Expiring or expired batches.
- Overdue purchase orders.
- Failed login attempts in the previous 24 hours.

Users can mark one or all notifications as read. Email-alert preference storage is available through the notification preference control. Actual email delivery requires a configured mail transport.

## 11. Backups and Recovery

Admin backup tools are available at `/backups`.

Backup commands:

```bash
php artisan inventory:backup
php artisan inventory:backup:prune
php artisan schedule:work
```

Backups include inventory, purchasing, sales, transfers, returns, ledger, notification, and related tables. User/security records are preserved during restore.

Protection features:

- Encryption enabled by default through `BACKUP_ENCRYPT=true`.
- SHA-256 checksum stored in `backup_histories`.
- Configurable filesystem disk through `BACKUP_DISK`; configure `s3` for cloud storage.
- Retention cleanup through `BACKUP_RETENTION_DAYS`.
- Restore preview/dry-run option.
- Safety backup is created before a live restore.
- Legacy unencrypted JSON backups remain readable.

Test restores on a separate database regularly. Never restore an untrusted file into production.

## 12. Scheduled Operations

Current scheduled tasks include:

- Daily backup at 02:00.
- Daily backup retention cleanup at 03:00.
- Daily low-stock and expiration email alert command at 08:00.

Run the scheduler in development with:

```bash
php artisan schedule:work
```

Run a queue worker when using database queues:

```bash
php artisan queue:work
```

## 13. Artisan Commands

Application-specific commands:

```bash
php artisan inventory:backfill-ledger
php artisan inventory:backup
php artisan inventory:backup:prune
php artisan inventory:reconcile
php artisan inventory:send-alerts
```

Useful Laravel commands:

```bash
php artisan migrate:status
php artisan route:list --except-vendor
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 14. Testing and CI

Run all tests:

```bash
vendor/bin/phpunit
php artisan test
```

Run formatting validation:

```bash
vendor/bin/pint --test
```

The CI workflow is `.github/workflows/ci.yml`. It installs PHP, runs migrations, executes PHPUnit, and checks Pint on pushes and pull requests. The existing feature coverage includes authentication security, inventory workflows, ledger idempotency, transfers, returns, POS sales, discount approval, and stock protection.

## 15. Production Deployment

1. Configure production `.env`, database, mail, queue, cache, and filesystem disk.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_KEY`.
3. Use HTTPS and set `SESSION_SECURE_COOKIE=true`.
4. Install dependencies with `composer install --no-dev --optimize-autoloader`.
5. Build frontend assets with `npm ci && npm run build`.
6. Run `php artisan migrate --force`.
7. Run `php artisan config:cache`, `route:cache`, and `view:cache`.
8. Run a supervised queue worker and scheduler.
9. Configure S3 or another external backup disk.
10. Verify login, permissions, POS, PO approval, backups, restore preview, and alert delivery.
11. Send Laravel logs to a monitored log aggregation or error-monitoring service.

## 16. Operational Notes

- Do not run `migrate:fresh` on production.
- Do not use the development fallback passwords in production.
- Configure real SMTP before relying on password reset, 2FA, or email notifications.
- Reconciliation reports should be reviewed before correcting historical quantities.
- CSV export is Excel-compatible; native XLSX/PDF libraries are not currently installed.
- Email verification routes are ready but intentionally not enforced while development uses non-legitimate placeholder emails.
