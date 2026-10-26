# New Client Onboarding & Launch Checklist

Use this checklist to streamline setting up, customizing, hardening, and delivering a client website with **Bina Site Kit** in under an hour.

---

## Phase 1: Pre-Launch Asset Gathering

Collect these items from the client prior to provisioning:

- [ ] **Domain & DNS Access**:
  - Registered domain name (e.g. `clientname.com`).
  - Access to DNS management (Cloudflare, Namecheap, GoDaddy, or cPanel DNS Zone Editor).
- [ ] **Brand Identity Assets**:
  - Logo: Vector SVG (preferred) or high-resolution PNG with transparent background.
  - Favicon: Square PNG (min 512x512) or ICO.
  - Brand Palette: Primary Hex Color (e.g. `#0f172a`) and Accent Hex Color (e.g. `#3b82f6`).
  - Typography: Desired Google Font pairing (e.g. `Inter` for headings and body, or `Playfair Display` + `Plus Jakarta Sans`).
- [ ] **Business Contact Information**:
  - Primary contact email (for public display and inquiry notifications).
  - Business phone number and WhatsApp number with international country codes.
  - Physical office address or operating location.
  - Social media profile URLs (LinkedIn, Instagram, X/Twitter, Facebook, GitHub).
- [ ] **Feature Scope & Modules**:
  - Does the client need a portfolio or project showcase? (Enable `portfolio` module).
  - Does the client sell physical or digital goods with order requests? (Enable `catalog` module).
  - Do they want instant Telegram alerts on their phone?

---

## Phase 2: Server & Hosting Provisioning

- [ ] **Database Setup**:
  - Log into cPanel / DirectAdmin / Plesk.
  - Create a new MySQL database (e.g. `client_db`).
  - Create a database user with a strong password and assign `ALL PRIVILEGES` to the database.
- [ ] **PHP Configuration**:
  - Open **MultiPHP Manager** or **Select PHP Version**.
  - Ensure PHP is set to **PHP 8.2** or **PHP 8.3**.
  - Enable required extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `intl`, `curl`, `openssl`, `gd` (or `imagick`).
  - Set `memory_limit = 256M` or higher.
- [ ] **File Layout Setup (Layout a - Recommended)**:
  - Upload application files to `/home/username/bina-app/` (above web root).
  - Upload `public/` files to `/home/username/public_html/`.
  - Update `public_html/index.php` paths pointing to `../bina-app/`.
  - Verify directory permissions: `chmod -R 775 storage bootstrap/cache`.

---

## Phase 3: Project Initialization

Run the client setup command via SSH (or local development before uploading):

```bash
php artisan bina:new-client
```

Select the appropriate template:
- `business` for professional services, agencies, and B2B consultancies.
- `portfolio` for design studios, architects, photographers, and freelancers.
- `charity` for foundations, non-profits, and fundraising initiatives.
- `store` for boutique makers and catalogs with shopping cart and order requests.

The command will automatically:
1. Save brand colors, contact information, and enabled modules to `site_settings`.
2. Seed starter pages (`Home`, `About`, `Services` or `Donate`, `Contact`) with responsive blocks.
3. Create the super-administrator account with strong password validation.

---

## Phase 4: Telegram Bot Setup (Instant Notifications)

Set up a dedicated Telegram bot to notify the client when a visitor submits an inquiry or places a catalog order:

### Step 1: Create the Bot via @BotFather
1. In Telegram, search for `@BotFather` (verified bot with blue checkmark).
2. Send `/newbot`.
3. Enter a friendly name (e.g. `Acme Inquiries Bot`).
4. Enter a unique username ending in `bot` (e.g. `acme_inquiries_bot`).
5. Copy the generated **HTTP API Token** (e.g. `7123456789:AAH...`).

### Step 2: Get the Client's Chat ID
- **For a Single User**:
  1. Ask the client to open Telegram and search for `@userinfobot`.
  2. Send `/start`.
  3. The bot replies with their numerical **Id** (e.g. `987654321`).
  4. **Important**: Have the client start a chat with your new bot and press **Start** so the bot has permission to message them.
- **For a Team Group**:
  1. Create a Telegram group with the client's team.
  2. Add your new bot to the group as a member.
  3. Add `@userinfobot` or `@RawDataBot` to the group to get the group's numerical chat ID (usually prefixed with `-100`, e.g. `-1001234567890`).

### Step 3: Configure in Bina Site Kit
1. Log into `/admin` -> **Site Settings** -> **Integrations** tab.
2. Enter the **Telegram Bot Token** (saved using encrypted database casting).
3. Enter the **Telegram Chat ID**.
4. Click **Test Telegram Notification** to send an instant test ping to the client's device.

---

## Phase 5: Content Customization & Pre-Flight QA

- [ ] **Branding & Visuals**:
  - In `/admin` -> **Site Settings**, upload client's Logo and Favicon.
  - Review Google Fonts rendering across mobile and desktop.
- [ ] **Pages & Blocks**:
  - Review `Home`, `About`, and custom pages in `/admin/pages`.
  - Customize Hero text, CTA URLs, and Feature cards.
  - Add client testimonials, stats, and real team photography.
- [ ] **SEO & Metadata**:
  - Check `/admin` dashboard for the **Missing SEO Information** widget.
  - Fill in SEO Title, SEO Description, and Open Graph image on every page.
  - Verify `/sitemap.xml` generates dynamically with all published pages.
  - Verify `/robots.txt` is accessible and points to the sitemap.
- [ ] **Test Forms & Checkout**:
  - Submit a test inquiry on `/contact` or `/inquiry`. Verify validation, honeypot spam protection, and Telegram notification delivery.
  - If using the store module, add items to `/cart` and submit a test order on `/checkout`.

---

## Phase 6: Production Diagnostics & Optimization

Run the built-in diagnostic and optimization commands:

```bash
# 1. Run doctor checks (database, PHP extensions, writable permissions)
php artisan bina:doctor

# 2. Pre-compile configuration, routes, Blade views, and navigation cache
php artisan bina:optimize
```

If symlinks are blocked on the shared host, publish assets directly:
```bash
php artisan bina:publish-storage --clean
```

---

## Phase 7: Client Handover & Training

Schedule a 20-minute Zoom or Loom recording covering:

1. **Access**:
   - Provide client login URL (`https://clientdomain.com/admin`).
   - Deliver super-admin credentials securely via password manager (e.g. 1Password / Bitwarden).
2. **Managing Content**:
   - How to edit text inside page blocks (Rich Text, Hero, Features).
   - How to reorder blocks by dragging.
   - How to upload portfolio projects or catalog products.
3. **Inquiries & Order Requests**:
   - Viewing received inquiries in `/admin/inquiries` and updating statuses (New -> Read -> Replied -> Archived).
   - Viewing and printing customer order requests in `/admin/order-requests`.
4. **Maintenance & Support**:
   - Provide client with emergency agency contact info.
   - Bookmark web maintenance URL (`/_bina/{token}/cache-clear`) if SSH is unavailable.
