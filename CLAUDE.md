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
| Custom plugins | PHP files in `wp-content/plugins/gowonderlu-*` — `gowonderlu-user-fields.php` adds a Phone Number field to registration/account-settings (Phase 1) |
| Geolocation API | Google Maps Places API key — set up as part of Phase 2 for address autocomplete on the deal-posting form (not yet entered in HivePress Settings → Integrations; owner action, see Phase 2 status below) |
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

**Phase 1 (Roles + Signup) — complete.** Customers are regular WP users;
drivers register via `/register-vendor` and become HivePress Vendors with
Vehicle Type/Vehicle Details attributes. New listings require manual
admin approval (HivePress Settings → Listings → Submission → Moderation)
before going live — verified end-to-end with a real test listing
(submitted → pending → approved → publicly visible). Phone Number field
added via `gowonderlu-user-fields.php`. Brand colors applied via Astra's
Customizer (amber accent, navy headings — no custom CSS needed). See
`docs/superpowers/specs/2026-06-23-phase-1-roles-signup-design.md` and
`docs/superpowers/plans/2026-06-23-phase-1-roles-signup-implementation.md`.

**Gotchas discovered during Phase 1 (relevant to future phases):**
- HivePress's frontend routes (`/register-vendor`, `/submit-listing`,
  etc.) need a **Listings** page and a **Vendors** page to actually exist
  as WP Pages, assigned in HivePress → Settings → Listings/Vendors — they
  are not auto-created. Check this first if a HivePress URL 404s or
  redirects to the homepage.
- After any fresh WordPress install/reinstall, flush permalinks
  (Settings → Permalinks → Save Changes) — custom plugin routes won't
  resolve otherwise.
- Select-type custom attributes (Vendors/Listings → Attributes) don't
  have an inline options field — publish the attribute first, then an
  "Edit Options" taxonomy-term screen becomes available for entering the
  actual choice list.

**Homepage & Site Shell — complete.** Real homepage (centered hero,
two-panel customer/driver CTAs, How It Works, testimonials) replaces
Astra's demo content; logo/favicon/nav menu configured; branded footer
with a newsletter signup placeholder (not yet wired — Phase 7) shows on
every page; About/Terms/Privacy pages live with clearly-flagged
placeholder copy. The visual direction is calm and editorial (italic
serif headlines, soft surface backgrounds, hairline-bordered cards) —
the original bold-industrial direction (Big Shoulders Display + IBM
Plex Mono + diagonal "speed line") was built first, then explicitly
replaced after live review. See
`docs/superpowers/specs/2026-06-23-homepage-site-shell-design.md` and
`docs/superpowers/plans/2026-06-23-homepage-site-shell-implementation.md`.

**Gotchas discovered during the Homepage & Site Shell phase (relevant to future phases):**
- Astra's `.ast-container` (the div wrapping all page content) is
  `display: flex`. Any custom template (`front-page.php`, `about.php`,
  etc.) that outputs multiple top-level `<section>` tags needs them
  wrapped in one `.gw-page` div with `display: block; width: 100%;` —
  otherwise the sections lay out as flex columns side-by-side instead of
  stacking.
- A custom `footer.php` must explicitly close the `#content` and
  `.ast-container` divs that Astra's `header.php` opens before outputting
  its own `<footer>` markup, or the footer ends up nested inside that
  flex container instead of sitting below it as a sibling (same visual
  symptom as the bug above, different root cause).
- Astra's Site Identity → Logo "Logo Width" field applies literally —
  if left blank, the logo renders at `0×0`. Always set an explicit pixel
  value. Relatedly, an SVG logo with only a `viewBox` (no `width`/`height`
  attributes) has no intrinsic size, so it depends entirely on that field.
- The header background color is controlled by the **first swatch** in
  Customizer → Colors → Global Palette (CSS var `--ast-global-color-0`),
  **not** the "Theme Color → Accent" field — they're separate mappings.
  Astra ships with a generic blue/magenta "Default" palette that was
  never updated to the brand colors; check this on any new Astra install.
- Astra's free-tier Footer Builder only offers a Copyright element — no
  menu/nav widget — so a footer with links requires a `footer.php`
  override (see above), not the Footer Builder.
- A misbehaving browser extension (confirmed: one injecting scripts on
  every page) can break the wp-admin Customizer's JS entirely (panels
  fail to render, controls go blank) in a way that looks identical to a
  plugin conflict. Always test flaky Customizer behavior in an
  Incognito/Private window before chasing it through WordPress plugins.
- HivePress and Hostinger's bundled plugins (Hostinger Reach, etc.) were
  both observed loading frontend script bundles into wp-admin, which can
  also crash Customizer JS independently of the above — another reason
  to verify in Incognito first rather than deactivating plugins blind.

**Phase 2 (Deals Marketplace) — code complete, pending live verification.**
Customers post deals (pickup/dropoff, date window, item description +
photos, offer price, city) via a new `gw_deal` custom post type and a
`/post-a-deal/` form; admin reviews and approves (Pending → Open, same
moderation pattern as Phase 1 Listings); drivers self-claim Open deals
in their city from `/dashboard/`, or admin hand-assigns from a meta box
on the deal's edit screen. Dashboard shows role-appropriate sections
(My Deals for everyone; Available Deals/My Jobs only for users who've
completed a driver profile). Plain email notifications fire on
approval and assignment. Messaging and reviews are intended to reuse
the already-active HivePress Messages/Reviews extensions, but the exact
integration was deferred (see gotchas below) — currently the dashboard
links to HivePress's general `/account/messages/` and `/account/reviews/`
pages rather than a deal-specific thread/review form. Payments are
explicitly out of scope (Phase 3). Built via
`docs/superpowers/specs/2026-06-24-phase-2-deals-marketplace-design.md`
and `docs/superpowers/plans/2026-06-24-phase-2-deals-marketplace-implementation.md`
using subagent-driven development — every task individually reviewed
clean, but **none of it has been deployed or exercised against the live
WordPress/HivePress install yet** (this sandbox has no live site
access). Before considering Phase 2 actually done, the owner needs to:
1. Upload all new/changed files (see the implementation plan's per-task
   "Deploy and verify" steps for the exact list) via Hostinger File
   Manager, activate the "GoWonderlu Deals" plugin, and create the
   "Post a Deal" and "Dashboard" WP Pages with their respective
   templates assigned.
2. Set up a Google Cloud Maps API key and enter it in HivePress
   Settings → Integrations (Task 10) — confirm the actual option name
   HivePress stores it under matches `hp_geolocation_google_maps_api_key`
   (a best guess; check by inspecting the saved option directly if
   autocomplete doesn't appear on `/post-a-deal/`).
3. Confirm the City field actually appears on the vendor profile-edit
   form (Account → Settings while logged in as a vendor) — this depends
   on `hivepress/v1/forms/vendor_update` being HivePress's real filter
   name for that form, which was never verified against actual plugin
   source (HivePress isn't in this repo). If the field doesn't appear,
   find the real filter name in the installed plugin's code and fix
   `gowonderlu-user-fields.php`.
4. Walk through the full verification list in the Phase 2 spec
   (registration → post deal → approve → claim → message → complete →
   review → cancel → existing customer becomes driver → admin assign →
   autocomplete) and fix anything broken. **Specifically test the
   self-claim path's emails** (driver claims an Open deal from their own
   dashboard, not admin-assigned) — a final-review pass caught and fixed
   a bug where claiming wrote the deal's status before its driver-ID
   meta, so the assignment emails fired with no driver name and the
   driver got no email at all. Fixed by reordering those two writes plus
   adding a driver-role check on the claim handler (`gowonderlu_claim_deal()`
   in `gowonderlu-deals.php`), but this exact path needs a real
   live-email test since it could only be reasoned about statically.
5. After activating the plugin and creating the new pages, flush
   permalinks (Settings → Permalinks → Save Changes) — same Phase 1
   gotcha applies to the new `gw_deal` post type and form handlers.
6. Once messaging/reviews are confirmed working through HivePress's
   general account pages, decide whether the deal-specific linking
   (Tasks 7/8 in the implementation plan) is worth a follow-up pass —
   it was intentionally left unbuilt rather than guessed at, since a
   wrong HivePress method-name guess risks a fatal PHP error on the
   live site.

---

## Key Decisions (Do Not Reverse Without Discussion)

| Decision | Rationale |
|---|---|
| No AI attribution in commits or code | Owner preference, matches honeyindex convention |
| Astra + child theme (not a fully custom theme) | Faster to ship, proven pattern from honeyindex |
| HivePress for marketplace core | Avoids building request/listing/messaging plumbing from scratch |
| Manual deploy via Hostinger File Manager (no CI/CD) | Matches honeyindex's convention; Hostinger's GitHub auto-deploy was tried and abandoned after it wiped the live WP install (destructive mirror sync) |
| Navy + amber palette | Reads "trustworthy logistics network" — distinct from Lugg (orange/black) and GoShare (blue/green) |
| Instrument Serif (italic, headlines) + DM Sans (body), soft surface backgrounds, hairline-bordered cards | Calm/editorial direction, explicitly chosen over an initial bold-industrial draft (condensed display type + diagonal "speed line" + solid color blocks) after live review — owner wanted something closer to honeyindex's restrained feel |
| Custom `gw_deal` post type instead of HivePress's premium Requests extension | Full control, no added licensing cost, despite the extra build time — explicit decision after weighing both |
| Single WP account for both roles; becoming a driver is a profile upgrade, not a separate signup | Matches how Uber/DoorDash/Turo let one account be both customer and provider — avoids forcing a second account on people who want both roles |

---

## Where to Get Help

- WordPress: https://developer.wordpress.org
- Astra: https://wpastra.com/docs/
- HivePress: https://hivepress.io/docs/
- Hostinger: https://support.hostinger.com
- Repo: https://github.com/lemuntu/gowonderlu (private)

---

*Last updated: June 24, 2026 (Phase 2 build). Update this file when architecture changes meaningfully.*
