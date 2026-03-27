# SolidTech Social Media Manager — Project Context

> **Read this file at the start of every new Claude session.**
> It captures architecture decisions, current state, and key details
> so nothing is lost between conversations.

---

## Environments

| | DEV (XAMPP) | PROD (SiteGround) |
|---|---|---|
| **Root path** | `C:\xampp\htdocs\social-media\` | `/home/<user>/public_html/social-media/` |
| **Base URL** | `http://localhost/social-media/public` | `https://social.solidtech.com` |
| **DB name** | `solidtech_social` | `solidtech_social_prod` |
| **DB user** | `root` (no password) | `solidtech_prod_user` |
| **Switch env** | Set `APP_ENV` to `'local'` in `config/env.php` | Set `APP_ENV` to `'production'` in `config/env.php` |

**Important:** Always edit files in the XAMPP path (`C:\xampp\htdocs\social-media\`) — that's where the dev browser points. There is a separate copy on Google Drive (`G:\My Drive\Clients\Social Media\`) that is NOT served by XAMPP.

---

## Architecture Overview

- **Framework:** Custom PHP MVC (no Composer, no external framework)
- **Core:** `core/` — Router, Controller base, Model base, Database (PDO/MySQL)
- **Config:** `config/env.php` — all constants (DB, API keys, env toggle)
- **Controllers:** `app/controllers/` — PostController, GeneratorController, CalendarController, BrandingController, etc.
- **Models:** `app/models/` — Post, BrandingSetting, ContentMemory, User, Doc
- **Views:** `app/views/` — PHP templates loaded by controllers
- **Services:** `app/services/` — ZernioService, BrandingService, ContentMemoryService, OpenRouterService, KieService
- **Public entry:** `public/index.php` — front controller, all requests route through here
- **Cron jobs:** `cron/run_scheduled_posts.php` — scheduled post publisher

---

## Key Integrations

### Zernio (Social Media Posting API)
- **Service:** `app/services/ZernioService.php`
- **Purpose:** Posts content to Facebook and LinkedIn via their API
- **Methods:**
  - `postNow($platform, $content, $imageUrl)` — immediately publishes (used by "Post Now" button and cron job)
  - `schedulePost()` — exists but is NO LONGER USED (we handle scheduling ourselves via cron)
- **Account IDs (constants in env.php):**
  - Facebook: `ZERNIO_FACEBOOK_ACCOUNT_ID`
  - LinkedIn: `ZERNIO_LINKEDIN_ACCOUNT_ID`
- **Image handling:** Local images (localhost) are uploaded to a temporary host for dev; production URLs pass through directly. See `resolveImageUrl()`.

### OpenRouter (AI Content Generation)
- **Service:** `app/services/OpenRouterService.php`
- **Used by:** GeneratorController for creating post content

### Kie.ai (AI Image Generation)
- **Service:** `app/services/KieService.php`
- **Used by:** GeneratorController for creating post images

---

## Platforms

| Platform | Status | Notes |
|---|---|---|
| **Facebook** | Active | Fully connected via Zernio |
| **LinkedIn** | Active | Fully connected via Zernio |
| **Instagram** | Future | UI placeholder exists (disabled checkbox), not yet connected |
| **X / Twitter** | Future | UI placeholder exists (disabled checkbox), not yet connected |

- Users can select multiple active platforms per post
- Posts store both `platform` (legacy single, always first selected) and `platforms` (JSON array of all selected)
- The cron job and Post Now both loop through ALL selected platforms

---

## Scheduling & Posting Flow

### How scheduling works:
1. User creates/edits a post, selects platform(s), sets a future date/time
2. Clicks **"Schedule"** button
3. Post is saved with `status = 'scheduled'` and `scheduled_at` timestamp
4. **NO API call is made at this point** — the post just sits in the database
5. The **cron job** (`cron/run_scheduled_posts.php`) runs every minute
6. It queries: `WHERE status = 'scheduled' AND scheduled_at <= NOW()`
7. For each due post, it calls `ZernioService::postNow()` for each platform
8. Updates status to `published` (success) or `failed` (all platforms failed)
9. Logs everything to `social_post_logs` table and `storage/cron.log`

### Validation rules:
- Cannot schedule a post in the past (validated in both JS and PHP)
- Must select at least one platform
- Must set a date/time

### "Post Now" button:
- Immediately calls `ZernioService::postNow()` for each selected platform
- Does NOT require a scheduled date

### "Retry Failed" button:
- Checks post logs to find which platforms failed
- If scheduled time is in the future: resets to `scheduled`, lets cron retry
- If scheduled time has passed (or no schedule): calls `postNow()` immediately

### Cron setup:
- **XAMPP (dev):** Run manually or use Windows Task Scheduler
  ```
  php C:\xampp\htdocs\social-media\cron\run_scheduled_posts.php
  ```
- **SiteGround (prod):** Site Tools > Devs > Cron Jobs, set to `* * * * *`
  ```
  php /home/<user>/public_html/social-media/cron/run_scheduled_posts.php
  ```

---

## Post Statuses

| Status | Meaning |
|---|---|
| `draft` | Created but not scheduled or posted |
| `scheduled` | Has a future date/time, waiting for cron to publish |
| `published` | Successfully posted to at least one platform |
| `failed` | All platform attempts failed |

---

## Branding System

- **Settings stored in:** `branding_settings` table (per client)
- **Fields:** `logo_url`, `primary_color`, `secondary_color`, `login_bg_url`, `particles_enabled`, `company_name`, `tagline`
- **Service:** `app/services/BrandingService.php`
- **Used by:** Login page, sidebar, generator lightbox, and anywhere brand colors appear
- **Current brand:** SolidTech — reddish primary color, logo stored as uploaded file

---

## Content Memory System

- **Purpose:** Prevents repetitive AI-generated content
- **Service:** `app/services/ContentMemoryService.php`
- **Model:** `app/models/ContentMemory.php`
- **How:** Stores hashes of topic + keywords + angle for each generated post. Generator checks against memory before creating new content.

---

## Generator Lightbox

- **Location:** `app/views/generator/index.php`
- **Trigger:** Shows when "Generate Full Week" or "Generate Single Post" is clicked
- **Features:**
  - Company logo from branding (inverted to white)
  - Brand-colored background (primary → secondary gradient)
  - Orbiting animated dots around the logo
  - Pulse rings radiating outward
  - Animated progress bar (white on brand background)
  - Rotating status messages ("Analyzing your brand voice...", etc.)
  - Floating particles
  - Disappears when generation completes

---

## Database Tables (Key Ones)

- `posts` — all social media posts (title, content, image_url, platform, platforms, status, scheduled_at, post_type, topic, keywords, angle, content_hash, zernio_post_id)
- `social_post_logs` — log of every posting attempt (post_id, platform, status, account_id, zernio_post_id, response_data, error_message, created_at)
- `branding_settings` — per-client branding config
- `content_memory` — used topics/angles for deduplication
- `users` — authentication
- `docs` — documentation/knowledge base entries

---

## Known Issues / Things to Watch

1. **Two file copies:** XAMPP (`C:\xampp\htdocs\social-media\`) is the working dev copy. Google Drive (`G:\My Drive\Clients\Social Media\`) is a separate copy — edits there won't show in the browser. Always edit the XAMPP copy.
2. **Calendar shows single platform:** The calendar tooltip/modal reads `post.platform` (singular). Needs update to show all platforms from the `platforms` JSON column.
3. **Instagram & X/Twitter:** Checkbox UI exists but is disabled. Will need Zernio account IDs and enabling the checkboxes when ready.
4. **Image URL for dev:** Zernio can't reach `localhost` images. The `resolveImageUrl()` method in ZernioService uploads them to a temporary host. In production this won't be needed.

---

## File Structure (Key Paths)

```
social-media/
  config/env.php          — environment config, API keys, DB credentials
  core/                   — Router, Controller, Model, Database
  app/
    controllers/          — PostController, GeneratorController, CalendarController, etc.
    models/               — Post, User, BrandingSetting, ContentMemory, Doc
    services/             — ZernioService, BrandingService, OpenRouterService, KieService, ContentMemoryService
    views/
      editor/edit.php     — Edit post page (platform checkboxes, schedule, post now)
      editor/index.php    — All posts list
      generator/index.php — Content generator with AI lightbox
      calendar/index.php  — Calendar view
      branding/index.php  — Branding settings (logo, colors)
      memory/index.php    — Content memory viewer
      docs/index.php      — Documentation
      reports/index.php   — Performance reports
  cron/
    run_scheduled_posts.php — Cron job that publishes due posts
  public/
    index.php             — Front controller / entry point
    uploads/              — Uploaded files (logos, images)
  storage/
    cron.log              — Cron job execution log
  PROJECT-CONTEXT.md      — THIS FILE
```

---

*Last updated: March 25, 2026*
