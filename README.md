# Grihosthali (গৃহস্থালী) — E-commerce

Laravel 12 storefront + admin panel for Grihosthali's ghee, honey, oil and natural foods.
**No Node/npm/Vite needed.** CSS/JS are plain files in `public/`; Font Awesome, Swiper, Quill and Chart.js load from a CDN.

## Run on XAMPP (no `php artisan serve` needed)

1. Start **Apache** and **MySQL** in XAMPP.
2. Create the database (already done on this machine): `grihosthali` (utf8mb4).
3. From the project folder:
   ```bash
   composer install          # only if /vendor is missing
   php artisan migrate:fresh --seed
   ```
4. Open **http://localhost/Client_project/grihosthali_ecommerce**

The root `.htaccess` + `index.php` let the site run from the project folder. On a live server you can instead
point the domain's document root at `/public` (recommended). Update `APP_URL` in `.env` to match.

## Admin panel

- URL: `/admin` → http://localhost/Client_project/grihosthali_ecommerce/admin
- Email: `admin@grihosthali.com`
- Password: `admin123` ← **change this in Admin → Profile before going live**

Admin features:

- **Dashboard** – sales chart, month profit estimate, follow-ups due, low stock, pending returns.
- **Orders** – status/payment updates, invoice print, customer insight (delivery success rate), create return.
- **Returns & Refunds** – customer or admin-created requests → Approved → Product Received (resellable items
  restocked automatically, damaged ones not) → Refunded (cash/bKash/Nagad/bank + TrxID) or Replacement Sent.
  Customers request returns from *Track Order* or *My Account* within the return window (Settings).
- **CRM** – every buyer (guest or registered) keyed by phone; segments (New / Repeat / VIP / At Risk / Blocked),
  tags, notes, activity timeline (calls, WhatsApp, notes), follow-up tasks with due dates, block fake-order
  customers (blocked phones cannot check out), CSV export.
- **Inventory** – every stock change is logged (sale, cancel, return, purchase, damage, adjustment, audit);
  stock in with supplier cost, damage/loss, exact stock count, per-product low-stock alert, stock value,
  full movement history, CSV export.
- **Catalog & marketing** – products (cost price, images, gallery, discount, badge, SEO), categories, brands,
  reviews, coupons, sliders, testimonials, subscribers, static pages.
- **Settings** – logo, colours, contact & social, delivery charges/times, checkout texts, return window,
  CRM thresholds, and the whole homepage: show/hide each section, feature strip, section titles, promo banner.

- **Couriers** (Integrations → Couriers) – Steadfast, Pathao and CarryBee. Enter API keys (stored encrypted),
  *Test connection*, pick the default courier. On an order: **Send to courier** (Pathao/CarryBee city/zone/area
  dropdowns), refresh status, tracking link, cancel (CarryBee). Bulk-send from the Orders list. Webhook URLs
  are shown per courier for automatic delivery updates; optionally auto-mark orders Delivered/Returned.
- **Email** (Integrations → Email) – any SMTP account, e.g. Gmail (`smtp.gmail.com`, 587, TLS, Gmail
  *App Password*). Master on/off switch, on/off per email type (order confirmation, status updates, returns,
  welcome, admin alerts for new order / return / contact / low stock), choose which order statuses email the
  customer, send a test email, full sent/failed log. Emails are sent after the page responds, so checkout never waits.
- **Staff & Access** – multiple admins with roles. Built-in roles: Super Admin, Store Manager, Order Manager,
  Inventory Manager, Customer Support, Content Editor; create custom roles with per-section permissions.
  Deactivate an admin to block sign-in immediately. Every admin change is recorded in the Activity Log.
- **Admin UI** – grouped collapsible sidebar (remembers state), mini sidebar mode, mobile drawer,
  global search (`/` key) across orders, phones, customers, products and consignment IDs, notification bell.

**Configuration is cached** (`php artisan config:cache`) because XAMPP on Windows runs PHP multi-threaded and can
otherwise occasionally fail to read `.env`. After editing `.env`, run `php artisan config:cache` again.
Configuration caches contain machine-specific absolute paths: never copy `bootstrap/cache/config.php` from local
development to hosting. After uploading the app and setting the hosting `.env`, clear any uploaded cache and rebuild
it on the server with `php artisan config:clear` followed by `php artisan config:cache`.
If the server does not have SSH/terminal access, remove `bootstrap/cache/config.php` from the hosting file manager
and ask the host to clear Laravel's configuration cache. Ensure `storage/framework/sessions` exists and is writable
by PHP when using `SESSION_DRIVER=file`.

**Safety net:** `bootstrap/app.php` checks the cached config on every boot. If it was built for a different folder
(e.g. uploaded from XAMPP), it is deleted automatically and the server's own `.env` is used. Folders accidentally
created on Linux with Windows-path names (`C:\xampp\...\storage\logs`) are removed too.

**Brand:** logo, white logo, favicon, phone icon and social share image are uploaded in Admin → Store Settings →
Logo & Colors; homepage banners (including the brand cover) in Admin → Marketing → Sliders.

On an existing database, run `php artisan migrate` then `php artisan db:seed --class=RoleSeeder`
(existing admins become Super Admin).

`/public/...` URLs redirect permanently to the clean URL.

## Deploying to hosting (e.g. grihosthali.com)

1. Upload everything **except** `.env`, `bootstrap/cache/config.php` and the contents of `storage/logs` and
   `storage/framework/{sessions,views,cache}` (keep those folders, just empty).
2. Create the server `.env` (copy `.env.example`) and set:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://grihosthali.com
   DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
   ```
   Keep the same `APP_KEY` as local if you imported the local database (encrypted courier/SMTP keys depend on it);
   otherwise run `php artisan key:generate` and re-enter courier API keys and the SMTP password in admin.
3. Make `storage/` and `bootstrap/cache/` writable (755/775), and `public/uploads/` writable for image uploads.
4. Import the database (or run `php artisan migrate --seed --force`), then optionally `php artisan config:cache`
   **on the server**.
5. With `APP_DEBUG=false`, visitors see branded 404/500 pages — never file paths or code.

## Before launch

- Admin → Settings: real phone, hotline, WhatsApp, email, address, social links.
- Replace the generated product illustrations (`public/uploads/products/*.svg`) with real product photos via Admin → Products.
- Testimonials are sample text — replace with real customer reviews.
- `.env`: set `APP_ENV=production`, `APP_DEBUG=false`, and the real `APP_URL`.
