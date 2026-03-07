# PHP & MySQL E-Commerce / CMS Project

!!This is an amateur project; there may be significant shortcomings. You may use it free of charge.!!

## About the Project

**Mini Shop** – A dynamic e-commerce system with PDO foundation, fully responsive (mobile-friendly), and an admin panel.

**Developer:** :contentReference[oaicite:1]{index=1}

## Key Features

### Frontend
- Fully responsive design (mobile, tablet, desktop)
- Accordion-style mobile menu (hamburger menu)
- Dark Mode support
- WebP image format support
- SEO-friendly URL structure (slug: `/urun/`, `/kategori/`, `/sayfa/`)
- Add to cart via AJAX and filter/sort without page reload
- Product reviews, ratings, and AJAX comment sorting
- User profile management, addresses, and order tracking
- Homepage slider, popular categories and products (configured from admin panel)

### Backend & Security
- PDO Prepared Statements (SQL Injection protection)
- XSS protection (`htmlspecialchars`, `sanitize()`)
- CSRF protection (all forms and AJAX endpoints: login, register, checkout, profile, cart, comments, admin forms)
- Secure session management (HttpOnly, SameSite=Strict)
- Password hashing (Bcrypt)
- Password rules: min. 8 characters, 1 uppercase, 1 lowercase, 1 special character

### Admin Panel
- Product, Category, Page management (CRUD)
- Order tracking and status updates
- Comment approval/rejection
- Homepage customization (popular categories, headings)
- Slider management
- Database backup and restore (ZIP, including uploads)
- Site statistics and visit tracking
- Admin profile

### SEO
- **robots.txt:** Admin, migrations, backups and config.php blocked; Sitemap line (update with your domain in production)
- **Sitemap:** `sitemap.php` – homepage, products, categories, static pages (XML)
- **Meta:** Description, canonical URL, robots index/follow
- **Open Graph and Twitter Card:** Share preview (title, description, image)
- **Structured data (JSON-LD):** WebSite + Organization (all pages), Product (product page, price, stock, rating)
- Slug-based clean URLs (`/urun/`, `/kategori/`, `/sayfa/`)

## Quick Setup

### 1. Database Setup
- Start **Apache** and **MySQL** in XAMPP
- Create a database named mini_shop in phpMyAdmin and import the mini_shop.sql file
### 2. Settings
`config.php` uses environment variables by default; if not set, local values apply. Optional environment variables:

- `DB_HOST` – Database server (default: localhost)
- `DB_NAME` – Database name (default: mini_shop)
- `DB_USER` – Username (default: root)
- `DB_PASS` – Password (default: empty)
- `BASE_URL` – Project URL path (default: /mini_shop)

To edit directly, change the `getenv()` default values in `config.php`.

## Default Login Credentials
(for new registration/password change: min. 8 characters, 1 uppercase, 1 lowercase, 1 special character)
Default standard username: user
Default standard user password: user123
Default admin username: admin
Default admin password: admin123

- **Frontend:** `http://localhost/mini_shop`
- **Admin Panel:** `http://localhost/mini_shop/admin/dashboard.php`

### Going Live (Production)
- Use `define('IS_PRODUCTION', true);` in `config.php` or set the `APP_ENV=production` environment variable on the server
- Error messages are not shown to users
- Session cookies are set with `Secure` over HTTPS


## Screenshots

> Aşağıdaki bölüm, repo kökünde `screenshots/` klasöründe bulunan ekran görüntüleriyle entegre olacak şekilde düzenlenmiştir. Dosya isimlerini değiştirmeden doğrudan çalışır.

### Main Pages

<p align="center">
  <img src="screenshots/index.PNG" alt="Homepage" width="48%">
  <img src="screenshots/products.PNG" alt="Products Page" width="48%">
</p>

<p align="center">
  <img src="screenshots/profile.PNG" alt="User Profile" width="48%">
  <img src="screenshots/about.PNG" alt="About Page" width="48%">
</p>

---

### Responsive Views

<p align="center">
  <img src="screenshots/index_responsive.PNG" alt="Homepage Responsive" width="45%">
  <img src="screenshots/profile_responsive.PNG" alt="Profile Responsive" width="45%">
</p>

<p align="center">
  <img src="screenshots/contact_responsive.PNG" alt="Contact Responsive" width="60%">
</p>

---

### Admin Panel

<p align="center">
  <img src="screenshots/dashboard.PNG" alt="Admin Dashboard" width="48%">
  <img src="screenshots/backup.PNG" alt="Backup System" width="48%">
</p>

> Not: Eğer bazı görüntüler küçük/kırpık gözükürse, `screenshots/` klasöründeki resimlerin orijinal çözünürlüklerinin yeterli olduğundan emin ol. GitHub README görüntüleme boyutları, resim metadata'sı ve orijinal çözünürlüğe bağlı şekilde ölçeklenir.

## Project Structure


```
mini_shop/
├── index.php                 # Homepage
├── products.php              # Product listing (search, sort, pagination)
├── product.php               # Product detail, comments
├── product_comments_ajax.php  # Comment list AJAX endpoint
├── category.php              # Category page
├── cart.php                  # Cart
├── cart_remove_item.php      # Remove item from cart
├── checkout.php              # Checkout
├── order_success.php         # Order success
├── login.php                 # Login
├── register.php              # Register
├── logout.php                # Logout (closes site + admin session)
├── profile.php               # User profile
├── admin_profile.php         # Admin profile
├── my_orders.php             # My orders
├── about.php                 # About us
├── contact.php               # Contact
├── page.php                  # Static page
├── add_to_cart_ajax.php      # AJAX add to cart
├── config.php                # Database and settings
├── functions.php             # Helper functions
├── robots.txt                # Search engine rules (SEO)
├── sitemap.php               # XML sitemap (products, categories, pages)
├── .htaccess                 # URL rewrite, security headers
├── init.sql                  # Database setup
├── partials/
│   ├── header.php
│   ├── footer.php
│   ├── product_card.php           # Shared product card partial
│   ├── product_comments_list.php
│   ├── products_ajax_content.php
│   ├── index_popular_ajax_content.php
│   └── category_ajax_content.php
├── assets/
│   ├── css/
│   │   ├── styles.css
│   │   └── final_override.css
│   └── js/
│       ├── main.js
│       └── admin.js
├── admin/
│   ├── dashboard.php
│   ├── products.php
│   ├── categories.php
│   ├── pages.php
│   ├── orders.php
│   ├── users.php
│   ├── comments.php
│   ├── statistics.php
│   ├── settings.php
│   ├── homepage.php          # Homepage customization
│   ├── slider.php            # Slider management
│   ├── backup.php
│   ├── fix_products.php
│   ├── upload_image.php
│   ├── delete_temp_image.php
│   ├── page_content.php
│   ├── logout.php            # Redirects to main logout.php
│   └── partials/
├── migrations/               # SQL migration files (one-time)
│   ├── migration_admin_addresses.sql
│   ├── migrate_comments_parent_id.sql
│   ├── migrate_comments_images.sql
│   ├── migrate_slider_slides.sql
│   └── migrate_users_full_name.sql  # full_name column for admin profile
├── products_img/             # Product images
├── uploads/                  # Slider, comment images, etc.
├── img/                      # Logo, favicon
└── backups/                  # Backup ZIP files (created from panel)
```

## Technical Details

### Requirements
- PHP 7.4+ (PDO, GD, mbstring)
- MySQL 5.7+ or MariaDB 10.3+
- Apache (mod_rewrite enabled)
- PHP Zip extension (for backup)

### Zip Extension (Backup)
For the backup module to work, the `extension=zip` line in `php.ini` must be enabled (no `;` at the start). Otherwise you may get the "Class 'ZipArchive' not found" error.

### Database Tables
- `users` – Admin users
- `site_users` – Site users
- `user_addresses` – User addresses
- `admin_addresses` – Admin addresses
- `categories` – Categories
- `products` – Products
- `orders` – Orders
- `order_items` – Order line items
- `comments` – Product comments (parent_id, images, rating)
- `pages` – Static pages
- `settings` – Site settings
- `site_visits` – Visit statistics
- `slider_slides` – Homepage slider

### SEO (Going Live)
- **robots.txt:** In project root; `/admin/`, `/migrations/`, `/backups/`, `config.php` blocked. Update the `Sitemap:` line with your domain in production (e.g. `Sitemap: https://yourdomain.com/sitemap.php`).
- **sitemap.php:** XML sitemap; homepage, products, categories and static pages are listed.
- **Meta and sharing:** Canonical URL, meta description, Open Graph and Twitter Card on every page; Product JSON-LD (price, stock, rating) on product pages.
- **URL:** Clean URLs via `.htaccess`: `/urun/slug`, `/kategori/slug`, `/sayfa/slug`.
- **config.php:** If you set `$siteUrl = 'https://yourdomain.com';` in production, canonical, OG and sitemap URLs will be generated correctly.

## Screenshots

<p align="center">
  <img src="screenshots/index.PNG" width="45%" alt="Homepage">
  <img src="screenshots/index_responsive.PNG" width="45%" alt="Homepage Responsive">
</p>

<p align="center">
  <img src="screenshots/products.PNG" width="45%" alt="Products">
  <img src="screenshots/dashboard.PNG" width="45%" alt="Admin Dashboard">
</p>

<p align="center">
  <img src="screenshots/profile.PNG" width="45%" alt="User Profile">
  <img src="screenshots/profile_responsive.PNG" width="45%" alt="User Profile Responsive">
</p>

<p align="center">
  <img src="screenshots/contact_responsive.PNG" width="45%" alt="Contact Responsive">
  <img src="screenshots/about.PNG" width="45%" alt="About Page">
</p>

<p align="center">
  <img src="screenshots/backup.PNG" width="45%" alt="Backup System">
</p>
