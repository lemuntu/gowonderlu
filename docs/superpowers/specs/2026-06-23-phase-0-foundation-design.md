# GoWonderlu — Phase 0: Foundation Design

## Context

GoWonderlu (gowonderlu.com) is a new WordPress-based trucking/hauling marketplace
connecting customers (people posting hauling/moving "deals") with independent
drivers. Competitors: Lugg, GoShare. Hosting is Hostinger (already purchased,
domain + shared/business hosting plan), fresh WordPress install, no theme or
plugins configured yet.

This is the first of several phases. The full build order (each phase gets its
own design + plan cycle):

0. **Foundation** (this doc) — branding, repo, CLAUDE.md, WP/HivePress scaffold
1. Roles + signup (driver/customer) and profiles
2. Customer "post a deal" + driver browse/accept (manual matching only)
3. Stripe Connect payments + payouts
4. Email + SMS notifications
5. Reviews + messaging
6. Auto-matching rules
7. Newsletter + marketing layer

Reference pattern: the owner's existing honeyindex.com project, which uses a
parent theme + custom child theme, custom plugins as plain PHP files in the
repo, a single authoritative `CLAUDE.md`, and a manual deploy workflow (git
push to GitHub, then copy changed files into Hostinger File Manager — no
CI/CD). Commit convention: **no AI attribution anywhere** (no
"Co-Authored-By" lines, no AI mentions in commit messages, comments, or code).

## Goals (Phase 0 only)

- Establish brand identity (logo, color palette, type direction).
- Set up local repo structure + GitHub remote (private repo
  `lemuntu/gowonderlu`).
- Write `CLAUDE.md` as the authoritative project reference (mirrors
  honeyindex's CLAUDE.md structure: what the project is, tech stack, repo
  structure, conventions, deployment workflow, current phase status).
- Scaffold the WordPress base: Astra parent theme + a new `gowonderlu-theme`
  child theme (empty/minimal — no page templates yet, that's Phase 1+).
- Install and activate HivePress core + extensions (Geolocation, Requests,
  Marketplace, Messages, Reviews) on the live site, unconfigured/unstyled.
  Configuration and theming of these happens in later phases.

**Out of scope for Phase 0:** any driver/customer signup flow, deal posting,
payments, notifications, matching logic, or visual page templates. Those are
Phase 1 onward.

## Brand Identity

- **Palette:** deep navy base (`#0B1F3A`-ish) + amber/gold accent
  (`#F5A623`-ish), generous white space, near-black body text. Chosen to read
  "trustworthy logistics network" — distinct from Lugg (orange/black,
  energetic) and GoShare (blue/green, friendly/eco).
- **Mark:** abstract geometric "W" built from angled bars that double as a
  motion/speed cue. Paired with a custom-weight "gowonderlu" wordmark.
- **Deliverables (committed under `brand/`):**
  - `brand/logo-mark.svg` — standalone mark (used as favicon / app icon)
  - `brand/logo-full.svg` — mark + wordmark lockup
  - `brand/brand-guide.md` — hex values, type direction, basic usage rules
    (min size, clear space, do/don't)

## Repository Structure

```
gowonderlu/
├── CLAUDE.md
├── README.md
├── .gitignore
├── docs/
│   └── superpowers/specs/        ← design docs (this file and future ones)
├── brand/
│   ├── logo-mark.svg
│   ├── logo-full.svg
│   └── brand-guide.md
└── wp-content/
    ├── themes/
    │   └── gowonderlu-theme/     ← Astra child theme
    │       ├── style.css
    │       └── functions.php
    └── plugins/
        └── (empty in Phase 0 — custom plugins added from Phase 1 onward,
             e.g. gowonderlu-matching.php, gowonderlu-notifications.php)
```

`.gitignore` excludes `wp-config.php`, `wp-content/uploads/`, cache
directories, and `.DS_Store` — matching honeyindex's `.gitignore`.

## GitHub

- Repo: `lemuntu/gowonderlu`, **private**.
- No `gh` CLI available in this environment, so the owner creates the empty
  repo on github.com manually; Claude then sets the `origin` remote locally
  and pushes.
- Remote URL convention matches honeyindex: SSH
  (`git@github.com:lemuntu/gowonderlu.git`).

## Deployment Workflow

Same as honeyindex — no CI/CD:

1. Edit files locally in VS Code (Claude Code assists).
2. Commit + push to GitHub (history/backup, not auto-deploy).
3. Manually copy the new/changed files into Hostinger's File Manager at the
   matching `wp-content/themes/...` or `wp-content/plugins/...` path. This
   plan has no SSH (unlike honeyindex's business plan), so the copy happens
   via the File Manager's browser-based upload, not SCP — same destination,
   different transport.

**Note:** Hostinger's hosting panel also offers a GIT "Deploy from GitHub"
auto-deploy tool. It was tried and abandoned during Phase 0 — pointed at
the WordPress install root, it does a destructive mirror sync that deletes
anything not tracked in this repo (`wp-admin/`, `wp-includes/`,
`wp-config.php`, etc. are intentionally gitignored), which wiped the live
WordPress install. Manual File Manager copy is the only deploy method this
project should use.

## WordPress Base Setup

1. Confirm fresh WP install is reachable (admin login works).
2. Install + activate **Astra** (parent theme).
3. Add **gowonderlu-theme** as an active child theme (minimal: `style.css`
   header declaring the Astra parent + `functions.php` enqueuing parent
   styles — no custom templates yet).
4. Install + activate **HivePress** core plugin.
5. Install + activate HivePress extensions: **Geolocation**, **Requests**,
   **Marketplace**, **Messages**, **Reviews**. (Bookings extension deferred —
   optional per the original tech stack notes, revisit if/when availability
   scheduling becomes a priority.)
6. Leave all of the above at default configuration/styling — Phase 1 covers
   role setup and Phase 1+ covers visual theming.

## Testing / Verification

- Local: none (no executable app code yet — Phase 0 is repo scaffolding +
  static SVG/Markdown files).
- Live site: after WP/plugin installs, verify via wp-admin that HivePress and
  all five extensions show as active with no fatal errors, and that the
  child theme is active and renders the homepage without errors.

## Open Questions / Risks

- None outstanding — domain spelling confirmed as `gowonderlu.com`, repo
  name/visibility confirmed, theme base (Astra) confirmed, brand direction
  confirmed.
