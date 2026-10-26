# Bina Site Kit (BSK)

**Bina Site Kit** is a modern, high-performance Laravel 11 starter designed for agencies, studios, and developers delivering polished client websites in under an hour. Built on **Filament 3** and **Tailwind CSS**, it combines CMS flexibility with clean Blade rendering and shared-hosting resilience.

---

## Key Highlights

- **Shared-Hosting First**: Operates seamlessly on standard cPanel, DirectAdmin, Apache, or Nginx hosts with **zero Redis or background queue daemon requirements** (sync queues, database sessions).
- **Dynamic Live Branding**: Primary and accent colors are injected as CSS variables with full Tailwind opacity support (`bg-primary/20`), alongside configurable Google Fonts.
- **Modular Block Builder**: 11 production-ready page blocks auto-discovered by `BlockRegistry` and rendered via `<x-blocks :page="$page" />`.
- **Toggleable Modules**:
  - **Portfolio Module**: Filterable project showcases, categories, image optimization (WebP/GD/Imagick), and detail views.
  - **Catalog & Order Request Module**: Product catalog, session shopping cart, and follow-up order submissions sent directly to Telegram (no payment gateway needed).
- **Instant Telegram Alerts**: Real-time notifications for contact inquiries and order requests via Telegram bot API with HTML escaping and graceful error fallback.
- **Complete SEO Suite**: Dynamic sitemaps (`/sitemap.xml`), robots.txt (`/robots.txt`), JSON-LD structured schema (Organization, LocalBusiness, Breadcrumbs, Product), and Open Graph meta tags.
- **Client Provisioning Tooling**: Spin up client sites interactively with `php artisan bina:new-client` and 4 industry-tailored starter templates.
- **Production Hardening**: Diagnostic environment scanner (`bina:doctor`), symlink-free asset publishing (`bina:publish-storage`), token-protected web maintenance routes, and security-header `.htaccess`.

---

## Requirements

- **PHP**: >= 8.2 with extensions: `pdo`, `mbstring`, `intl`, `fileinfo`, `openssl`, `curl`, and `gd` or `imagick`.
- **Database**: SQLite (default local) or MySQL / MariaDB (shared hosting & VPS).
- **Composer**: >= 2.2.
- **Node.js & npm**: Node 18+ for Vite asset compilation.

---

## Quick Start (Under 5 Minutes)

### 1. Clone & Install
```bash
git clone https://github.com/abshwabu/bsk.git my-client-site
cd my-client-site

composer install
npm install && npm run build
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

### 3. Initialize a New Client Site
Run the interactive setup command:
```bash
php artisan bina:new-client
```
*Or use scripted options for automated provisioning:*
```bash
php artisan bina:new-client \
  --template=business \
  --name="Acme Consulting" \
  --primary="#0f172a" \
  --accent="#3b82f6" \
  --admin-email="admin@acme.com" \
  --admin-password="StrongPassword!2026" \
  --force
```

### 4. Serve Locally
```bash
php artisan serve
```
- **Public Site**: [http://localhost:8000](http://localhost:8000)
- **Admin Panel**: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## Starter Templates Catalog

Bina Site Kit includes 4 purpose-built starter templates available via the `--template=` option:

| Template | Slug | Description | Enabled Modules | Included Pages |
|---|---|---|---|---|
| **Modern Business** | `business` | Clean corporate agency layout for B2B consultancies, law firms, and service businesses. | Core | Home, About, Services, Contact |
| **Creative Portfolio** | `portfolio` | Curated showcase for design agencies, architects, photographers, and studios. | `portfolio` | Home (Selected Works), About, Projects, Project Detail, Contact |
| **Charity & NGO** | `charity` | Purpose-driven starter for foundations, non-profits, and grassroots community initiatives. | Core | Home (Impact Stats), About, Donate (Giving Guide & FAQ), Contact |
| **Artisan Store** | `store` | Boutique shop and product catalog with session cart and Telegram order requests. | `catalog` | Home (Product Grid), About, Shop, Product Detail, Cart, Checkout, Contact |

To reset or switch templates in local/staging environments:
```bash
php artisan bina:reset-demo --template=store
```

---

## Artisan Commands Reference

| Command | Description |
|---|---|
| `php artisan bina:new-client` | Interactively configures site branding, creates admin user, and seeds starter pages. |
| `php artisan bina:reset-demo` | Safely wipes demo content and reseeds a selected template (refuses to run in production). |
| `php artisan bina:doctor` | Diagnoses PHP environment, database, storage permissions, extensions, and configuration. |
| `php artisan bina:publish-storage` | Copies `storage/app/public` files directly into `public/storage` for symlink-free shared hosts. |
| `php artisan bina:optimize` | Pre-compiles configuration, routes, Blade views, and navigation cache for production. |

---

## Available Page Blocks

All page blocks implement `App\Blocks\PageBlock` and are auto-discovered from `app/Blocks/`:

1. **Hero**: High-impact banner with heading, subheading, background image, overlay opacity, alignment, and two CTAs.
2. **Rich Text**: Narrative typography with rich editor, supporting narrow and wide column widths.
3. **Features Grid**: Responsive 2, 3, or 4 column feature list with Heroicon glyphs.
4. **Image Gallery**: Multi-image gallery with Alpine.js lightbox overlay.
5. **Testimonials**: Social proof quote cards with author attribution and avatars.
6. **FAQ**: Accessible Alpine.js accordion for frequently asked questions.
7. **Stats**: Key metrics counter grid (e.g. "150+ Delivered", "99.9% Uptime").
8. **Call to Action Banner**: Full-width conversion card with heading, text, and button.
9. **Contact Section**: Display site contact details, address, social links, and an inquiry form.
10. **Featured Projects**: Filtered portfolio showcase grid (active when `portfolio` module is enabled).
11. **Product Grid**: Showcase cards for highlighted shop products (active when `catalog` module is enabled).

---

## Documentation

- [Deployment Guide for Shared Hosting & cPanel](docs/DEPLOY-SHARED-HOSTING.md) — Two directory layouts, `.htaccess` security rules, symlink fallbacks, and 500 error troubleshooting.
- [New Client Onboarding Checklist](docs/NEW-CLIENT-CHECKLIST.md) — Step-by-step agency process from domain setup to client training.
- [Extending Bina Site Kit](docs/EXTENDING.md) — How to create custom page blocks and build new toggleable modules with full code examples.

---

## Testing & Quality Assurance

Run the comprehensive Pest test suite:
```bash
php artisan test
```

Format PHP code according to PSR-12 and Laravel standards:
```bash
vendor/bin/pint
```

---

## License

Bina Site Kit is open-source software licensed under the [MIT license](LICENSE).
