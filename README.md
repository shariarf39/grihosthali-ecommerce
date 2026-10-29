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

On an existing database, run `php artisan migrate` then `php artisan db:seed --class=RoleSeeder`
(existing admins become Super Admin).

`/public/...` URLs redirect permanently to the clean URL.

## Before launch

- Admin → Settings: real phone, hotline, WhatsApp, email, address, social links.
- Replace the generated product illustrations (`public/uploads/products/*.svg`) with real product photos via Admin → Products.
- Testimonials are sample text — replace with real customer reviews.
- `.env`: set `APP_ENV=production`, `APP_DEBUG=false`, and the real `APP_URL`.
