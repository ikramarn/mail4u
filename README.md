# Mail4U — WordPress Plugin

B2B Mail Outreach SaaS. Sends cold outreach emails to newly launched businesses on behalf of your customers.

---

## Plugin Folder Structure

```
mail4u/
├── mail4u.php                  ← Main plugin file (plugin header + init)
├── README.md
├── assets/
│   ├── style.css               ← All frontend CSS
│   └── script.js               ← Minimal JS (password validation, UX)
├── includes/
│   ├── activation.php          ← DB table creation, default options, page setup
│   ├── mail.php                ← wp_mail() wrappers
│   ├── stripe.php              ← Stripe Checkout Session + webhook verification
│   ├── admin-settings.php      ← WP admin settings page + campaigns view
│   └── shortcodes.php          ← All shortcode callbacks + form processing
└── templates/
    ├── homepage.php            ← [mail4u_homepage]
    ├── pricing.php             ← [mail4u_pricing]
    ├── register.php            ← [mail4u_register]
    ├── dashboard.php           ← [mail4u_dashboard]
    └── contact.php             ← [mail4u_contact]
```

---

## Installation (Hostinger / hPanel)

1. Zip the entire `mail4u/` folder → `mail4u.zip`
2. In hPanel → **WordPress** → **File Manager** (or use FTP), navigate to:
   `public_html/wp-content/plugins/`
3. Upload and extract `mail4u.zip` there.
4. In your WordPress admin, go to **Plugins** → **Installed Plugins** → activate **Mail4U**.
5. On activation the plugin automatically:
   - Creates the `wp_mail4u_campaigns` database table
   - Creates five pages and assigns their shortcodes
   - Sets default option values

> **Important:** You only upload the `wp-content/plugins/mail4u/` folder — never the full WordPress core. The database is already present on Hostinger; no `.sql` file import is needed for a fresh install.

---

## Pages Created on Activation

| Page Title     | Slug                  | Shortcode              |
|----------------|-----------------------|------------------------|
| Mail4U Home    | `/mail4u-home`        | `[mail4u_homepage]`    |
| Pricing        | `/mail4u-pricing`     | `[mail4u_pricing]`     |
| Get Started    | `/mail4u-register`    | `[mail4u_register]`    |
| My Dashboard   | `/mail4u-dashboard`   | `[mail4u_dashboard]`   |
| Contact Us     | `/mail4u-contact`     | `[mail4u_contact]`     |

After activation, add these pages to your theme's navigation menu via **Appearance → Menus**.

---

## Admin Area

Navigate to **Mail4U** in the WP sidebar for:

- **Settings** — Stripe keys, admin email, plan prices
- **Campaigns** — View all submitted campaigns and update their status

---

## Stripe Setup

1. Create a free account at [stripe.com](https://stripe.com).
2. Copy your **Publishable Key** and **Secret Key** from the Stripe Dashboard (use Test keys first).
3. Paste them into **WP Admin → Mail4U → Settings**.
4. In the Stripe Dashboard, go to **Developers → Webhooks → Add endpoint**.
   - Endpoint URL: `https://yourdomain.com/?mail4u_webhook=1`
   - Event to listen for: `checkout.session.completed`
5. Copy the **Signing Secret** shown and paste it into the Stripe Webhook Secret field in settings.

### Payment Flow

1. User registers → lands on Pricing page
2. Clicks "Get Started" → POST to Pricing shortcode → Stripe Checkout Session is created
3. User is redirected to Stripe's hosted checkout page
4. After payment, Stripe redirects to `/mail4u-dashboard?mail4u_action=stripe_success&...`
5. Plugin verifies the session with the Stripe API and updates the user's plan in `wp_usermeta`
6. Webhook (`checkout.session.completed`) provides a second confirmation as a fallback

---

## Database

Only one custom table is created:

```sql
wp_mail4u_campaigns (
    id          BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id     BIGINT NOT NULL,
    industry    VARCHAR(255),
    deal_type   VARCHAR(100),
    message     TEXT,
    status      VARCHAR(50) DEFAULT 'pending',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

User data (plan, plan activation date) is stored as WordPress user meta:

| Meta Key                  | Values                                      |
|---------------------------|---------------------------------------------|
| `mail4u_plan`             | `free` \| `starter` \| `pro` \| `enterprise` |
| `mail4u_plan_activated`   | MySQL datetime string                       |

---

## Plan Limits Reference

| Plan       | Emails / month | Campaigns | Delivery  | Price  |
|------------|---------------|-----------|-----------|--------|
| Free       | 10            | 1         | Standard  | $0     |
| Starter    | 500           | 5         | 48 hrs    | $29/mo |
| Pro        | 2,000         | 20        | 24 hrs    | $79/mo |
| Enterprise | Unlimited     | Unlimited | Same day  | $199/mo|

Prices are configurable in WP Admin → Mail4U → Settings (stored in cents).

---

## No External Dependencies

This plugin uses only:
- Native WordPress APIs (`wp_mail`, `wpdb`, `wp_remote_post`, shortcodes, nonces)
- Stripe's REST API via `wp_remote_post()` — no Stripe PHP SDK required
- jQuery (already bundled with WordPress) for the minimal frontend script
