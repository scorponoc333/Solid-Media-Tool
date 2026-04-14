# SolidTech Social Media Manager — Project Context

> **Read this file at the start of every new Claude session.**
> Also read `CLAUDE.md` for role permissions, upgrade guidelines, and roadmap.

---

## Environments

| | DEV (XAMPP) | PROD (SiteGround) |
|---|---|---|
| **Root path** | `C:\xampp\htdocs\Solid-SocialMedia\` | `/home/<user>/public_html/social-media/` |
| **Base URL** | `http://localhost/Solid-SocialMedia/public` | `https://social.solidtech.com` |
| **DB name** | `solidtech_social` | `solidtech_social_prod` |
| **DB user** | `root` (no password) | `solidtech_prod_user` |
| **Switch env** | Set `APP_ENV` to `'local'` in `config/env.php` | Set `APP_ENV` to `'production'` in `config/env.php` |

---

## Architecture Overview

- **Framework:** Custom PHP MVC (no Composer, no external framework)
- **Core:** `core/` — Router, Controller base (with RBAC), Model base, Database (PDO/MySQL)
- **Config:** `config/env.php` — all constants (DB, API keys, env toggle)
- **Public entry:** `public/index.php` — front controller, loads all models/services, runs router
- **Cron jobs:** `cron/run_scheduled_posts.php` — scheduled post publisher

### Controllers
PostController, GeneratorController, CalendarController, BrandingController, ArtDirectionController, ContentStrategyController, WizardController, UserController, SmtpController, ReviewController, AuthController, DashboardController, ReportingController, MemoryController, DocumentationController

### Models
User, Post, BrandingSetting, ContentMemory, ArtDirectionSetting, ContentTheme, ThemeSample, ThemeSchedule, SmtpSetting, ApprovalSetting, PostReview

### Services
AIService, ZernioService, BrandingService, ContentMemoryService, ArtDirectionService, ContentStrategyService, WizardService, EmailService, UserManagementService, ApprovalService, ModalService

---

## Key Integrations

### Zernio (Social Media Posting API)
- **Service:** `app/services/ZernioService.php`
- Posts to Facebook and LinkedIn via API
- `postNow()` used by "Post Now" button and cron job
- Image handling: localhost images uploaded to temp host for dev

### OpenRouter (AI Content Generation)
- **Service:** `app/services/AIService.php` — `generateWeekContent()`, `generateSinglePost()`, `regenerateText()`
- Theme-aware: accepts theme data (name, instructions, samples, required elements) per post
- Memory-aware: excludes recent topics and angles via ContentMemoryService

### Kie.ai (AI Image Generation)
- **Service:** `app/services/AIService.php` — `generateImage()`
- Art direction modifiers appended to every image prompt via ArtDirectionService
- Watermarking: logo + website + gradient overlay (configurable position, opacity, enable/disable)

---

## User Roles & Access (RBAC)

Three roles: **admin**, **editor**, **reviewer**

| Feature | Admin | Editor | Reviewer |
|---------|:-----:|:------:|:--------:|
| Dashboard | Full | Full | Read-only |
| Generator | Yes | Yes | No |
| Posts (create/edit) | Yes | Yes | No |
| Posts (view) | Yes | Yes | Yes |
| Posts (approve/reject) | Yes | No | Yes |
| Calendar | Full | Full | View only |
| Reports | Full | Full | No |
| Content Strategy | Yes | No | No |
| Art Direction | Yes | No | No |
| Branding / Wizard | Yes | No | No |
| User Management | Yes | No | No |
| SMTP Settings | Yes | No | No |
| Reviews Queue | Yes | No | Yes |
| Memory | Yes | Yes | No |
| Docs | Yes | Yes | Yes |

RBAC enforced by `Controller::requireRole()` and nav filtering in `layouts/main.php`.

---

## Content Strategy System

- **Themes** (`content_themes`): Named categories with copy instructions, required elements (phone/website/CTA/hashtags/emojis), default hashtags, image style override
- **Theme Samples** (`theme_samples`): 1-3 example posts per theme for AI to mimic
- **Schedule** (`theme_schedule`): Day-of-week → theme mapping
- **AI Critique**: Analyzes post copy and returns strengths, suggestions, revised version

## Art Direction System

- **Settings** (`art_direction_settings`): Image style, realism level, color temperature, contrast, mood, brand color bleed, illustration limit, avoid list
- **Watermark controls**: Enable/disable, website text override, logo position, gradient opacity
- **Presets**: Corporate IT, Tech Magazine, Dark & Dramatic, Clean Professional
- **Prompt modifiers**: Assembled by `ArtDirectionService::buildImagePromptModifiers()` and appended to every image generation prompt

## Approval Workflow

- **Settings** (`approval_settings`): Toggle approval required, min approvals count (1-5)
- **Flow**: Editor creates post → submits for review (`pending_review`) → Reviewers approve/request changes → When min approvals met → post moves back to `draft` for scheduling
- **Optional**: Admin can enable/disable via User Management page

---

## Post Status Lifecycle

```
draft → pending_review (if approval required) → draft (after approved) → scheduled → published
                                                                                   → failed
```

---

## User Invitation Flow

1. Admin creates user (email, name, role) on User Management page
2. System generates random 12-char temp password
3. If SMTP configured: sends branded HTML email with login URL + temp password
4. If SMTP not configured: shows temp password to admin for manual sharing
5. User logs in → forced password change lightbox (non-dismissable)
6. After password change → onboarding tour starts (role-specific)
7. Tour uses spiral favicon as guide avatar, brand-colored Next button

---

## Setup Wizard

- 5 steps: Company Basics → Website Scan → Brand Identity → Theme Suggestions → Review
- AI scans website to extract services, about text, contact info, keywords
- AI suggests tailored content themes
- Brand reveal animation on completion (typewriter text, orbiting ring, particles, 5-7 sec)
- Re-runnable from Branding page

---

## Scheduling & Posting Flow

1. User creates/edits post, selects platform(s), sets future date/time
2. If approval required: "Submit for Review" → reviewers approve → back to draft
3. Clicks "Schedule" → status = `scheduled`
4. Cron job runs every minute → queries due posts → calls ZernioService → updates status
5. Logs to `social_post_logs` table

---

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Auth, roles (admin/editor/reviewer), temp password, tour state, client_id |
| `posts` | Social media posts with full status lifecycle |
| `social_post_logs` | Log of every posting attempt per platform |
| `branding_settings` | Per-client brand identity (logo, colors, favicon, etc.) |
| `art_direction_settings` | Per-client image generation style controls |
| `content_themes` | Reusable content themes with copy instructions |
| `theme_samples` | Example posts linked to themes |
| `theme_schedule` | Day-of-week → theme mapping |
| `content_memory` | Topic/angle deduplication hashes |
| `smtp_settings` | Email provider config (SMTP/SendGrid/Mailgun) |
| `approval_settings` | Per-client approval workflow toggle + min approvals |
| `post_reviews` | Individual approval/rejection records per post |

---

## File Structure

```
Solid-SocialMedia/
  CLAUDE.md                — Project intelligence for AI sessions (READ THIS)
  PROJECT-CONTEXT.md       — This file
  config/
    env.php                — Environment config, API keys, DB credentials
    routes.php             — All GET/POST route definitions
  core/
    Router.php             — URL routing with parameterized paths
    Controller.php         — Base class: view(), json(), requireAuth(), requireRole()
    Model.php              — Base class: find(), create(), update(), delete()
    Database.php           — PDO singleton
  app/
    controllers/           — One per feature area (16 controllers)
    models/                — One per DB table (11 models)
    services/              — Business logic (11 services)
    views/
      layouts/main.php     — Master layout: role-filtered nav, password lightbox, tour
      auth/login.php       — Login page (glassmorphism, particles)
      dashboard/           — Dashboard overview
      generator/           — Content generator with Plan & Generate lightbox
      editor/              — Post editor with AI critique
      calendar/            — Calendar view
      reporting/           — Reports and analytics
      branding/            — Brand settings + wizard button
      art-direction/       — Art direction controls
      content-strategy/    — Theme management + weekly schedule
      wizard/              — Setup wizard with brand reveal animation
      users/               — User management + approval settings
      smtp/                — Email provider configuration
      reviews/             — Post review queue for approvers
      emails/              — HTML email templates (invitation)
      components/tour.php  — Onboarding tour engine
      memory/              — Content memory viewer
      documentation/       — User-facing docs
  cron/
    run_scheduled_posts.php
  database/
    migrations/            — 001 (initial), 002 (art direction + themes), 003 (users + SMTP + reviews)
  public/
    index.php              — Front controller
    css/app.css            — Main stylesheet
    js/app.js              — Modal, toast, theme toggle utilities
    uploads/               — Uploaded files
    favicon.ico            — Browser favicon (from spiral.png)
    favicon-*.png          — Favicon sizes (16, 32, 48)
    apple-touch-icon.png   — iOS icon (180x180)
  img/
    spiral.png             — SolidTech spiral logo source
  storage/
    cron.log               — Cron job execution log
```

---

## Known Issues

1. **Calendar shows single platform** — tooltip reads `post.platform` (singular). Needs update to show all platforms from `platforms` JSON column.
2. **Instagram & X/Twitter** — Checkbox UI exists but disabled. Needs Zernio account IDs.
3. **Image URL for dev** — Zernio can't reach localhost images; `resolveImageUrl()` uploads to temp host. Not needed in production.

---

*Last updated: April 13, 2026*
