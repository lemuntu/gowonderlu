# Phase 0 (Foundation) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the GoWonderlu repo, brand identity, WordPress theme scaffold, and a pushed GitHub remote, plus get HivePress installed (unconfigured) on the live Hostinger site — establishing the foundation Phase 1+ builds on.

**Architecture:** This phase produces no executable application logic — it's repo scaffolding, two SVG brand assets, one markdown brand guide, an authoritative `CLAUDE.md`, a minimal Astra child theme (parent-style passthrough only), and a manual WordPress admin checklist. There are no unit tests; "tests" below are concrete verification steps (render checks, file/byte checks, wp-admin checks) appropriate to static-asset and config work, matching the spec's own "Local: none" testing note.

**Tech Stack:** WordPress (Astra parent theme, HivePress core + 5 extensions), plain PHP child theme, SVG, Markdown, Git/GitHub, Hostinger File Manager (manual deploy, no CI/CD).

## Global Constraints

- No AI attribution anywhere: no "Co-Authored-By" lines, no AI mentions in commit messages, comments, or code.
- Repo: `lemuntu/gowonderlu`, **private**, remote `git@github.com:lemuntu/gowonderlu.git` (SSH, matches honeyindex convention).
- Deploy is git push to GitHub → Hostinger's GIT panel (OAuth-connected to `lemuntu/gowonderlu`) auto-deploys to the live site. This hosting plan has no SSH (unlike honeyindex's), so this replaces the manual File Manager copy step honeyindex uses.
- Brand palette: navy `#0B1F3A` (primary), amber/gold `#F5A623` (accent) — fixed, do not substitute other hexes.
- Out of scope for Phase 0: signup flow, deal posting, payments, notifications, matching logic, visual page templates. Do not add any of these.
- `.gitignore` must exclude `wp-config.php`, `wp-content/uploads/`, cache directories, and `.DS_Store`.

---

## File Structure

```
gowonderlu/
├── CLAUDE.md
├── README.md
├── .gitignore
├── docs/superpowers/specs/2026-06-23-phase-0-foundation-design.md   (exists)
├── docs/superpowers/plans/2026-06-23-phase-0-foundation-implementation.md  (this file)
├── brand/
│   ├── logo-mark.svg
│   ├── logo-full.svg
│   └── brand-guide.md
└── wp-content/
    ├── themes/
    │   └── gowonderlu-theme/
    │       ├── style.css
    │       └── functions.php
    └── plugins/
        └── .gitkeep
```

---

### Task 1: Repo root scaffolding

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/.gitignore`
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/README.md`

**Interfaces:**
- Consumes: nothing.
- Produces: `.gitignore` rules later tasks rely on (so `wp-config.php`, uploads, and OS cruft never get committed); `README.md` referenced by no other file.

- [ ] **Step 1: Write `.gitignore`**

```gitignore
# WordPress core (not versioned)
/wp-admin/
/wp-includes/
wp-*.php
!wp-content/
.htaccess

# Uploads & cache (not code)
wp-content/uploads/
wp-content/cache/
wp-content/litespeed/
wp-content/upgrade/
wp-content/backups/

# Third-party theme/plugins (managed via WP admin, not Git)
wp-content/themes/astra/
wp-content/plugins/hivepress/
wp-content/plugins/hivepress-*/

# Sensitive config
wp-config.php
wp-config-sample.php
.env

# OS / Editor / Logs
.DS_Store
Thumbs.db
.vscode/
.idea/
*.log
debug.log

# Backups
*.bak
*.backup
*.swp
```

- [ ] **Step 2: Write `README.md`**

```markdown
# GoWonderlu

WordPress marketplace connecting customers with independent drivers for
hauling/moving jobs, at [gowonderlu.com](https://gowonderlu.com).

## Structure
- `wp-content/themes/gowonderlu-theme/` — Astra child theme
- `wp-content/plugins/gowonderlu-*` — custom plugins (none yet — Phase 1+)
- `brand/` — logo assets, brand guide
- `docs/superpowers/` — design specs and implementation plans

## Deploy Flow
1. Edit locally in VS Code
2. `git push` → GitHub (history/backup, no auto-deploy)
3. Manually copy changed files into Hostinger File Manager

See `CLAUDE.md` for the full project reference.
```

- [ ] **Step 3: Verify**

Run: `ls -la /Users/kryskaka/codingproject2026/gowonderlu/.gitignore /Users/kryskaka/codingproject2026/gowonderlu/README.md`
Expected: both files listed, non-zero size.

- [ ] **Step 4: Commit**

```bash
git add .gitignore README.md
git commit -m "Add repo scaffolding: gitignore and README"
```

---

### Task 2: Brand mark SVG (`logo-mark.svg`)

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/brand/logo-mark.svg`

**Interfaces:**
- Consumes: palette hexes from Global Constraints (`#0B1F3A`, `#F5A623`).
- Produces: `brand/logo-mark.svg`, reused inline (mark portion) by Task 3's lockup, and referenced by filename in Task 4's brand guide and Task 7's `CLAUDE.md`.

Design: an abstract "W" built from angled bars — a zigzag stroke path (square caps, mitered joins) in navy, with the final rightmost bar in amber as the motion/speed accent. Already rendered and visually verified during design (renders as a clean two-tone W, no clipping in a `0 0 200 160` viewBox).

- [ ] **Step 1: Write the SVG**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 160">
  <path d="M30,30 L75,130 L100,60 L125,130" fill="none" stroke="#0B1F3A" stroke-width="22" stroke-linecap="square" stroke-linejoin="miter"/>
  <path d="M125,130 L170,30" fill="none" stroke="#F5A623" stroke-width="22" stroke-linecap="square"/>
</svg>
```

- [ ] **Step 2: Render and verify visually**

Run:
```bash
qlmanage -t -s 400 -o /private/tmp/claude-502/-Users-kryskaka-codingproject2026-gowonderlu/daf89530-c2ad-4fd5-9953-6041239ddee2/scratchpad /Users/kryskaka/codingproject2026/gowonderlu/brand/logo-mark.svg
```
Then read the resulting `logo-mark.svg.png` with the Read tool.
Expected: a navy "W" silhouette with the rightmost diagonal stroke in amber, no clipped edges, transparent background.

- [ ] **Step 3: Commit**

```bash
git add brand/logo-mark.svg
git commit -m "Add brand mark SVG"
```

---

### Task 3: Brand lockup SVG (`logo-full.svg`)

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/brand/logo-full.svg`

**Interfaces:**
- Consumes: same mark geometry as Task 2 (kept byte-identical for the path data so the mark reads consistently across both files).
- Produces: `brand/logo-full.svg`, referenced by Task 4's brand guide and Task 7's `CLAUDE.md`.

- [ ] **Step 1: Write the SVG**

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 160">
  <path d="M30,30 L75,130 L100,60 L125,130" fill="none" stroke="#0B1F3A" stroke-width="22" stroke-linecap="square" stroke-linejoin="miter"/>
  <path d="M125,130 L170,30" fill="none" stroke="#F5A623" stroke-width="22" stroke-linecap="square"/>
  <text x="210" y="108" font-family="Arial, Helvetica, sans-serif" font-weight="800" font-size="62" fill="#0B1F3A" letter-spacing="-1">gowonderlu</text>
</svg>
```

- [ ] **Step 2: Render and verify visually**

Run:
```bash
qlmanage -t -s 800 -o /private/tmp/claude-502/-Users-kryskaka-codingproject2026-gowonderlu/daf89530-c2ad-4fd5-9953-6041239ddee2/scratchpad /Users/kryskaka/codingproject2026/gowonderlu/brand/logo-full.svg
```
Then read the resulting `logo-full.svg.png` with the Read tool.
Expected: mark on the left, lowercase bold "gowonderlu" wordmark in navy to the right, no text clipping against the viewBox edge, comfortable spacing between mark and text.

- [ ] **Step 3: Commit**

```bash
git add brand/logo-full.svg
git commit -m "Add brand lockup SVG"
```

---

### Task 4: Brand guide (`brand-guide.md`)

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/brand/brand-guide.md`

**Interfaces:**
- Consumes: hex values from Global Constraints; filenames from Tasks 2–3.
- Produces: `brand/brand-guide.md`, referenced from `CLAUDE.md` (Task 7).

- [ ] **Step 1: Write the brand guide**

```markdown
# GoWonderlu Brand Guide

## Colors
- Navy (primary): `#0B1F3A`
- Amber/Gold (accent): `#F5A623`
- White: `#FFFFFF`
- Near-black (body text): `#1A1A1A`

## Logo
- `logo-mark.svg` — standalone abstract "W" mark, angled bars, navy with an
  amber accent stroke as the motion/speed cue. Use for favicon, app icon,
  social avatars.
- `logo-full.svg` — mark + "gowonderlu" wordmark lockup. Use for the site
  header, letterhead, and marketing materials.

## Typography
- Wordmark: bold/extra-bold sans-serif (placeholder: Arial/Helvetica at
  800 weight, until a licensed display font is chosen in a later phase).
- Body: system sans-serif stack, near-black (`#1A1A1A`) on white.

## Usage Rules
- **Minimum size:** don't render `logo-mark.svg` below 24×24px — the angled
  strokes lose legibility below that.
- **Clear space:** keep padding around the mark at least equal to its
  stroke width (22 units in the 200×160 viewBox) on all sides.
- **Do** use the mark on white or light navy-tinted backgrounds.
- **Do** keep the amber accent on the rightmost/last bar — it's the motion
  cue. Don't recolor it navy.
- **Don't** stretch or skew the lockup non-uniformly.
- **Don't** place the navy mark on dark backgrounds without an inverted
  (white) variant — none exists yet; revisit in a later phase if needed.
```

- [ ] **Step 2: Verify**

Run: `cat /Users/kryskaka/codingproject2026/gowonderlu/brand/brand-guide.md`
Expected: file prints with all four sections (Colors, Logo, Typography, Usage Rules) present.

- [ ] **Step 3: Commit**

```bash
git add brand/brand-guide.md
git commit -m "Add brand guide"
```

---

### Task 5: Astra child theme scaffold

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/style.css`
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/functions.php`

**Interfaces:**
- Consumes: nothing.
- Produces: a minimal, valid Astra child theme. No template files yet (Phase 1+). `functions.php` exposes no custom functions other than the enqueue hook — nothing for later tasks to call.

- [ ] **Step 1: Write `style.css`**

```css
/*
Theme Name: GoWonderlu
Theme URI: https://gowonderlu.com
Description: Child theme of Astra for GoWonderlu, a marketplace connecting customers with independent hauling/moving drivers.
Author: GoWonderlu
Template: astra
Version: 0.1.0
*/
```

- [ ] **Step 2: Write `functions.php`**

```php
<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'gowonderlu_enqueue_styles' );

function gowonderlu_enqueue_styles() {
	wp_enqueue_style(
		'astra-parent-style',
		get_template_directory_uri() . '/style.css'
	);

	wp_enqueue_style(
		'gowonderlu-child-style',
		get_stylesheet_uri(),
		array( 'astra-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
```

- [ ] **Step 3: Verify**

Run: `php -l /Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/functions.php`
Expected: `No syntax errors detected`.

Run: `head -8 /Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/style.css`
Expected: header block with `Template: astra` present (required by WordPress to register this as a child theme).

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/gowonderlu-theme/style.css wp-content/themes/gowonderlu-theme/functions.php
git commit -m "Add gowonderlu-theme child theme scaffold"
```

---

### Task 6: Plugins directory placeholder

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/plugins/.gitkeep`

**Interfaces:**
- Consumes: nothing.
- Produces: a tracked-but-empty `wp-content/plugins/` directory for Phase 1+ custom plugins (e.g. `gowonderlu-matching.php`, `gowonderlu-notifications.php`). Git doesn't track empty directories, hence the placeholder.

- [ ] **Step 1: Create the placeholder file**

```bash
touch /Users/kryskaka/codingproject2026/gowonderlu/wp-content/plugins/.gitkeep
```

- [ ] **Step 2: Verify**

Run: `git status --short` (from repo root)
Expected: `wp-content/plugins/.gitkeep` listed as untracked.

- [ ] **Step 3: Commit**

```bash
git add wp-content/plugins/.gitkeep
git commit -m "Add plugins directory placeholder"
```

---

### Task 7: `CLAUDE.md`

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/CLAUDE.md`

**Interfaces:**
- Consumes: tech stack and repo structure from the design spec; filenames from Tasks 1–6.
- Produces: the single authoritative project reference, read by future Claude sessions before any code change.

- [ ] **Step 1: Write `CLAUDE.md`**

```markdown
# GoWonderlu — Project Reference for Claude

> **For Claude:** This is the single authoritative reference for the
> GoWonderlu project. Read it fully before making any code changes or
> suggestions. It captures architecture, conventions, deployment workflow,
> current state, and upcoming phases.

---

## What is GoWonderlu?

A WordPress-based marketplace at [gowonderlu.com](https://gowonderlu.com)
connecting customers who need something hauled or moved with independent
drivers. Competitors: Lugg, GoShare.

Owner: Christian Kakoba (GitHub: `lemuntu`, email: `kryskaka@gmail.com`)

---

## Tech Stack

| Layer | Tech |
|---|---|
| Hosting | Hostinger (single-site plan — no SSH, unlike honeyindex's business plan) |
| CMS | WordPress |
| Parent theme | Astra |
| Child theme | `gowonderlu-theme` (in repo at `wp-content/themes/gowonderlu-theme/`) |
| Marketplace engine | HivePress core + Geolocation, Requests, Marketplace, Messages, Reviews extensions |
| Custom plugins | PHP files in `wp-content/plugins/gowonderlu-*` (none yet — Phase 1+) |
| Payments | Stripe Connect (Phase 3 — not yet configured) |
| Notifications | Email + SMS (Phase 4 — not yet configured) |
| Local dev | VS Code on macOS, project at `~/codingproject2026/gowonderlu/` |

---

## Repository Structure

```
gowonderlu/
├── CLAUDE.md
├── README.md
├── .gitignore
├── docs/
│   └── superpowers/
│       ├── specs/    ← design docs
│       └── plans/    ← implementation plans
├── brand/
│   ├── logo-mark.svg
│   ├── logo-full.svg
│   └── brand-guide.md
└── wp-content/
    ├── themes/
    │   └── gowonderlu-theme/
    │       ├── style.css
    │       └── functions.php
    └── plugins/
        └── (empty in Phase 0 — custom plugins added from Phase 1 onward)
```

---

## Deployment Workflow

This hosting plan has no SSH (unlike honeyindex's), so deploy uses
Hostinger's **GIT** panel (Websites → gowonderlu.com → Advanced → GIT →
"Deploy from GitHub", OAuth-connected to `lemuntu/gowonderlu`):

1. Edit files locally in VS Code (Claude Code assists).
2. Commit + push to GitHub.
3. Hostinger auto-deploys the pushed files to the configured target
   directory on the live site — no manual File Manager copying.

### Critical rules

1. **Never edit production files directly** — always edit locally and push.
2. **Plugin settings don't sync via Git.** API keys, configuration — set in
   WP Admin manually.
3. **Database doesn't sync.** Posts, users, settings stay where created.
4. **Third-party plugins/themes (Astra, HivePress) aren't in this repo** —
   installed and managed via WP Admin, not deployed via Git.

---

## Brand Identity

Navy (`#0B1F3A`) + amber/gold (`#F5A623`) palette. Abstract geometric "W"
mark built from angled bars, amber accent stroke as a motion/speed cue.
Full rules in `brand/brand-guide.md`.

---

## Build Order (Phases)

0. **Foundation** (this phase) — branding, repo, CLAUDE.md, WP/HivePress scaffold
1. Roles + signup (driver/customer) and profiles
2. Customer "post a deal" + driver browse/accept (manual matching only)
3. Stripe Connect payments + payouts
4. Email + SMS notifications
5. Reviews + messaging
6. Auto-matching rules
7. Newsletter + marketing layer

## Current Phase Status

**Phase 0 (Foundation) — in progress.** See
`docs/superpowers/specs/2026-06-23-phase-0-foundation-design.md` for the
design and `docs/superpowers/plans/2026-06-23-phase-0-foundation-implementation.md`
for the implementation plan.

---

## Key Decisions (Do Not Reverse Without Discussion)

| Decision | Rationale |
|---|---|
| No AI attribution in commits or code | Owner preference, matches honeyindex convention |
| Astra + child theme (not a fully custom theme) | Faster to ship, proven pattern from honeyindex |
| HivePress for marketplace core | Avoids building request/listing/messaging plumbing from scratch |
| Deploy via Hostinger's GitHub auto-deploy (GIT panel), not manual File Manager copy | This plan has no SSH, unlike honeyindex's; auto-deploy on push is the closest available equivalent |
| Navy + amber palette | Reads "trustworthy logistics network" — distinct from Lugg (orange/black) and GoShare (blue/green) |

---

## Where to Get Help

- WordPress: https://developer.wordpress.org
- Astra: https://wpastra.com/docs/
- HivePress: https://hivepress.io/docs/
- Hostinger: https://support.hostinger.com
- Repo: https://github.com/lemuntu/gowonderlu (private)

---

*Last updated: June 23, 2026. Update this file when architecture changes meaningfully.*
```

- [ ] **Step 2: Verify**

Run: `grep -c "^## " /Users/kryskaka/codingproject2026/gowonderlu/CLAUDE.md`
Expected: 8 (one per `##` section heading above).

- [ ] **Step 3: Commit**

```bash
git add CLAUDE.md
git commit -m "Add CLAUDE.md project reference"
```

---

### Task 8: GitHub remote, push, and Hostinger auto-deploy connection

**Files:** none (git config + network operation + Hostinger panel config only).

**Interfaces:**
- Consumes: all commits from Tasks 1–7.
- Produces: a pushed `main` branch on `github.com/lemuntu/gowonderlu` (private), connected to Hostinger's GIT auto-deploy so Task 9 doesn't need a manual file upload.

- [ ] **Step 1: User creates the empty repo (manual, blocking)**

No `gh` CLI is available in this environment. Ask the user to create an
empty **private** repository named `gowonderlu` under the `lemuntu` GitHub
account at github.com, with no README/license/gitignore (this repo already
has all of those locally — initializing remotely would create a conflicting
history). Wait for confirmation before continuing.

- [ ] **Step 2: Add the remote**

```bash
git remote add origin git@github.com:lemuntu/gowonderlu.git
```

- [ ] **Step 3: Verify the remote**

Run: `git remote -v`
Expected: `origin git@github.com:lemuntu/gowonderlu.git (fetch)` and `(push)`.

- [ ] **Step 4: Push (confirm with user first — this is a one-way, externally-visible action)**

```bash
git push -u origin main
```

Expected: push succeeds, branch `main` now tracks `origin/main`.

- [ ] **Step 5: Verify**

Run: `git status`
Expected: `Your branch is up to date with 'origin/main'.`

- [ ] **Step 6: User connects Hostinger's GIT panel (manual, blocking)**

In Hostinger: Websites → gowonderlu.com → Advanced → GIT → "Continue with
GitHub", authorize, select the `lemuntu/gowonderlu` repo and the `main`
branch. When it asks for a target/install directory, point it at the
WordPress install root (the same directory that already contains
`wp-config.php` and `wp-admin/`) — **not** a subdirectory — so this repo's
`wp-content/themes/...` lands at the real `wp-content/themes/...` path
rather than nested one level too deep. Report back what directory field(s)
the UI actually asks for before confirming, since the exact wording isn't
known ahead of time.

- [ ] **Step 7: Verify the first auto-deploy**

After connecting, trigger a deploy (or push a no-op commit if it doesn't
auto-trigger on connect) and confirm via Hostinger's File Manager that
`wp-content/themes/gowonderlu-theme/style.css` now exists on the live
filesystem at the correct path.

---

### Task 9: Live WordPress base setup (manual — present as a checklist, do not attempt to automate)

No browser/admin-automation tool is available in this environment, so this
task is performed by the user in wp-admin. Present it as a checklist and
wait for the user to confirm each step rather than attempting to script it.
The child theme's files arrive via Task 8's auto-deploy, not a manual
upload — this task only covers WP Admin actions.

**Checklist:**

- [ ] Confirm the fresh WordPress install is reachable and admin login works.
- [ ] Install + activate **Astra** (parent theme) from the WP Admin theme
  directory.
- [ ] Confirm `gowonderlu-theme` appears under Appearance → Themes (deployed
  by Task 8), then activate it as the active (child) theme.
- [ ] Install + activate the **HivePress** core plugin.
- [ ] Install + activate HivePress extensions: **Geolocation**, **Requests**,
  **Marketplace**, **Messages**, **Reviews**.
- [ ] Leave everything at default configuration/styling — no changes yet.

**Verification (user performs in wp-admin):**

- [ ] WP Admin → Appearance → Themes shows `GoWonderlu` active, with `Astra`
  installed as its parent.
- [ ] WP Admin → Plugins shows HivePress core + all five extensions listed
  as **Active**, no fatal-error notices.
- [ ] The homepage loads without PHP errors or a white screen.

This task has no commit — it's a live-site state change only.
