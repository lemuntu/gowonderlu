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
| Marketplace engine | HivePress core + Geolocation, Messages, Reviews extensions (live). Marketplace + Requests extensions are premium hivepress.io add-ons, not in the free WP.org directory — deferred, revisit when a phase actually needs them and the cost is justified |
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

No CI/CD:

1. Edit files locally in VS Code (Claude Code assists).
2. Commit + push to GitHub (history/backup, not auto-deploy).
3. Manually copy the new/changed files into Hostinger's File Manager at
   the matching `wp-content/themes/...` or `wp-content/plugins/...` path
   (browser upload — this plan has no SSH, unlike honeyindex's).

### Critical rules

1. **Never edit production files directly** — always edit locally and copy up.
2. **Plugin settings don't sync via Git.** API keys, configuration — set in
   WP Admin manually.
3. **Database doesn't sync.** Posts, users, settings stay where created.
4. **Third-party plugins/themes (Astra, HivePress) aren't in this repo** —
   installed and managed via WP Admin, not deployed via Git.
5. **Never connect Hostinger's GIT "Deploy from GitHub" auto-deploy tool to
   this repo.** It does a destructive mirror sync that deletes anything not
   tracked in git — it wiped the live WordPress install once already during
   Phase 0 (`wp-admin/`, `wp-includes/`, `wp-config.php` are intentionally
   gitignored, so a mirror sync removes them). Manual File Manager copy only.

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

**Phase 0 (Foundation) — complete.** Astra + `gowonderlu-theme` active on
the live site, HivePress core + Geolocation/Messages/Reviews active.
Marketplace + Requests extensions deferred (premium hivepress.io add-ons —
revisit once a phase needs them). See
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
| Manual deploy via Hostinger File Manager (no CI/CD) | Matches honeyindex's convention; Hostinger's GitHub auto-deploy was tried and abandoned after it wiped the live WP install (destructive mirror sync) |
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
