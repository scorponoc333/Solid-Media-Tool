# Deployment Guide — SolidTech Social Media Platform

## Architecture Overview

```
marketing.aiagentprojects.com/          ← Marketing landing page
    index.html                          ← Glass tiles (Social Media + Artwork Generator)

marketing.aiagentprojects.com/social/   ← Solid Social Media app
    public/                             ← Web root (point here or use .htaccess)
        index.php                       ← Front controller
        css/app.css
        js/app.js
        uploads/                        ← AI-generated images
    app/                                ← MVC application
    config/                             ← Environment config
    core/                               ← Framework
    database/                           ← Migration SQL files
    cron/                               ← Scheduled post publisher
```

---

## Step 1: Database Setup

1. Create a MySQL database on SiteGround (e.g., `solidtech_social_prod`)
2. Create a database user with full privileges
3. Run the migration SQL files in order:
   ```
   database/migrations/001_initial_schema.sql    (if exists, or import from phpMyAdmin)
   database/migrations/002_art_direction_and_themes.sql
   database/migrations/003_users_smtp_reviews.sql
   ```
4. The default admin account is:
   - Username: `admin`
   - Password: `admin123`
   - **Change this immediately after first login**

---

## Step 2: Configure Environment

Edit `config/env.php` for production:

```php
// Environment
define('APP_ENV', 'production');

// Database — update with SiteGround credentials
define('DB_HOST', 'localhost');           // Usually localhost on SiteGround
define('DB_NAME', 'solidtech_social_prod');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_PORT', '3306');

// Base URL — no trailing slash
define('BASE_URL', 'https://marketing.aiagentprojects.com/social/public');

// Keep API keys as-is (OpenRouter, Kie.ai, Zernio)
```

---

## Step 3: Upload Files via FTP

**FTP Credentials:**
- Host: `ftp.aiagentprojects.com`
- Username: `jason@aiagentprojects.com`
- Password: (rotate after deployment)

**Upload structure:**

```
/public_html/                           ← SiteGround web root
    index.html                          ← Marketing landing page (from deploy/marketing/)
    social/                             ← Create this directory
        app/
        config/
        core/
        cron/
        database/
        public/
        storage/                        ← Create manually, chmod 755
        CLAUDE.md
        PROJECT-CONTEXT.md
```

**Important:**
- Upload the entire Solid-SocialMedia project into `/public_html/social/`
- Upload `deploy/marketing/index.html` to `/public_html/index.html`
- Create `/public_html/social/storage/` directory (for cron logs)
- Ensure `/public_html/social/public/uploads/` is writable (chmod 755)

---

## Step 4: URL Rewriting

Create `/public_html/social/public/.htaccess`:

```apache
RewriteEngine On
RewriteBase /social/public/

# Redirect everything to front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

If the app is at the subdomain root instead of `/social/`, adjust `RewriteBase` accordingly.

---

## Step 5: Cron Job Setup

In SiteGround's Cron Jobs panel, add:

```
* * * * * /usr/local/bin/php /home/<user>/public_html/social/cron/run_scheduled_posts.php
```

This publishes scheduled posts every minute.

---

## Step 6: Post-Deployment Checklist

- [ ] Visit `https://marketing.aiagentprojects.com` — verify landing page loads
- [ ] Click "Solid Social Media" tile — verify transition + login page
- [ ] Login with admin/admin123 — verify dashboard loads with entrance animation
- [ ] Change admin password immediately
- [ ] Go to Branding — verify logo, colors, API keys are intact
- [ ] Go to Generator — test AI text generation
- [ ] Go to Art Direction — test image generation
- [ ] Schedule a test post — verify cron publishes it
- [ ] Go to Users — invite a test user (configure SMTP first)
- [ ] Test SMTP settings

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Blank page | Check `APP_ENV` in env.php; enable PHP error display temporarily |
| 404 on routes | Ensure `.htaccess` is uploaded and `mod_rewrite` is enabled |
| DB connection error | Verify DB_HOST, DB_NAME, DB_USER, DB_PASS in env.php |
| Images not loading | Check `public/uploads/` is writable (755) |
| Cron not publishing | Check cron job path; verify PHP CLI path with `which php` |
| Login redirect loop | Check BASE_URL matches actual URL exactly (no trailing slash) |

---

## File Permissions

```
/public_html/social/storage/           755 (cron logs)
/public_html/social/public/uploads/    755 (AI images, logos)
/public_html/social/config/env.php     644 (config — not writable by web)
```

---

## Rollback

The GitHub repo at `https://github.com/jhogan333/Solid-SocialMedia` has the full commit history:
- `1ca1202` — Version 2.0 (current production build)
- `909a6b9` — Version 1.0 Stable (fallback)

To rollback: `git checkout 909a6b9` and re-upload.
