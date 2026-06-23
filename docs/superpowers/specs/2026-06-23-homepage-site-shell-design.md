# GoWonderlu — Homepage & Site Shell Design

## Context

Phases 0 (Foundation) and 1 (Roles + Signup) are complete: HivePress
roles/signup/listing-approval work end-to-end on the live site, but the
homepage and main navigation are still Astra's generic demo content
(Home/Services/About/Reviews/Why Us/Contact, none of it about GoWonderlu).
This is the first visible, public-facing piece of the actual product.

This work sits alongside the phased build order in `CLAUDE.md` rather than
inside it — it's the site shell (homepage, nav, footer, legal pages) that
makes the already-built signup flow from Phase 1 presentable and usable by
real visitors, not a new "marketplace feature" phase.

## Goals

- Replace Astra's demo homepage with a real GoWonderlu homepage: a split
  hero (customer/driver dual CTA), a "How It Works" section, and a
  testimonials section with clearly-placeholder content.
- Replace the demo site navigation and add a real footer.
- Add the three pages the nav/footer link to that don't have real content
  yet: About, Terms & Conditions, Privacy Policy (placeholder text,
  explicitly flagged as not a substitute for real legal review).

**Out of scope:** any actual deal-posting/marketplace functionality (that's
a future phase), real legal copy (a lawyer should write the final Terms/
Privacy text before launch), real testimonials (swapped in later once the
business has real customers), Stripe/payments.

## Homepage

Built as `front-page.php` in `gowonderlu-theme/` (matches honeyindex's
existing pattern of dedicated page templates per page — keeps content in
git rather than the database).

**1. Hero (Split, Option A from the visual review):**
Navy (`#0B1F3A`) background, two equal-weight panels:
- Left — "FOR CUSTOMERS" label, "Need something hauled?" headline, one
  line of supporting copy, amber (`#F5A623`) filled button "Get Started"
  linking to `/register`.
- Right — "FOR DRIVERS" label, "Have a truck? Earn on your schedule."
  headline, one line of supporting copy, amber outlined button "Become a
  Driver" linking to `/register-vendor` (HivePress redirects
  unauthenticated visitors through login/register first, then lands them
  on the vendor profile-completion form — no extra wiring needed beyond
  this link).

**2. How It Works (white background, 3 steps):**
Numbered amber circles (1/2/3) with: "Sign up" (create a customer or
driver account), "Get matched" (browse vetted drivers in your area),
"Get it done" (coordinate directly and get your move handled). Section
has an anchor (`id="how-it-works"`) the nav's "How It Works" link targets.

**3. Testimonials (placeholder, swappable later):**
2–3 short quote cards with a generic attribution style ("— Jane D.,
Boston") — written to read as plausible placeholder copy, not as fabricated
verified reviews. A one-line HTML comment in the template marks this
section as `<!-- placeholder testimonials — replace with real ones -->`
so it's easy to find and swap later.

## Site Navigation

Replace Astra's demo menu (Appearance → Menus, wp-admin config, no code)
with: **Home**, **About**, **How It Works** (anchor link to
`/#how-it-works`), **Sign In**, and a **Sign Up** button — matching
HivePress's existing `/account/login/` and `/register` routes.

Site logo: upload `brand/logo-full.svg` via Appearance → Customize → Site
Identity (wp-admin config, no code) — replaces the current "ASTRA" text
logo.

## Footer

Same three links (Home, About, How It Works) plus **Terms & Conditions**,
**Privacy Policy**, and a copyright line. Built via Astra's Customizer
Footer Builder (Appearance → Customize → Footer Builder — a menu widget
plus a copyright text row), wp-admin config, no code. If free Astra's
footer builder turns out too limited once we're actually configuring it,
the fallback is a `footer.php` override in `gowonderlu-theme/` — noted
here so the implementation plan has a documented Plan B instead of getting
stuck.

## New Pages

- **About** (`/about/`) — new WP Page, placeholder copy: 2–3 sentences on
  what GoWonderlu is and who it's for, written to be genuinely replaceable
  (no specific founder claims that would need rewriting, just positioning
  copy consistent with `CLAUDE.md`'s "trustworthy logistics network"
  framing).
- **Terms & Conditions** (`/terms/`) — new WP Page, standard placeholder
  marketplace-terms boilerplate (the kind any WP site needs structurally),
  with a visible note in the page content itself: "Placeholder — replace
  with reviewed legal text before accepting real payments."
- **Privacy Policy** (`/privacy-policy/`) — already exists as an
  Astra-generated draft page. Add placeholder content with the same
  "replace before launch" note, then publish it.

## Testing / Verification

No automated tests — this is theme template/page content, not application
logic. Manual verification on the live site:

1. Visit the homepage logged out — confirm the hero, How It Works, and
   testimonials sections render correctly with the brand palette.
2. Click "Get Started" → lands on `/register`. Click "Become a Driver" →
   redirects to login/register, then to the vendor profile form (reusing
   Phase 1's confirmed flow).
3. Click "How It Works" in the nav → scrolls to the homepage section.
4. Click Home, About, Terms & Conditions, Privacy Policy in nav/footer →
   each loads real (placeholder, where applicable) content, not a 404.
5. Confirm the new site logo (from `brand/logo-full.svg`) appears in the
   header in place of the Astra placeholder logo.

## Open Questions / Risks

- Footer Builder availability on free Astra is unconfirmed until we're
  actually in wp-admin configuring it — Plan B (custom `footer.php`) is
  documented above rather than left as a surprise.
- Placeholder Terms/Privacy/About content must be visibly flagged as
  placeholder so it doesn't accidentally become "the real thing" by
  default neglect before launch.
