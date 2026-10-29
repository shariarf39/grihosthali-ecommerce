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

Admin features: dashboard (sales chart, order stats, low stock), orders (status, payment, invoice print,
stock restored on cancel), sales reports, products (images, gallery, discount, badge, stock, SEO),
categories (sub-categories, menu & homepage section toggles), brands, reviews moderation, coupons,
hero sliders, testimonials, customers, contact messages, newsletter subscribers, static pages,
and settings (logo, colours, phone/WhatsApp, social links, delivery charges, free-delivery threshold).

## Before launch

- Admin → Settings: real phone, hotline, WhatsApp, email, address, social links.
- Replace the generated product illustrations (`public/uploads/products/*.svg`) with real product photos via Admin → Products.
- Testimonials are sample text — replace with real customer reviews.
- `.env`: set `APP_ENV=production`, `APP_DEBUG=false`, and the real `APP_URL`.
