# Phase 2: Deals Marketplace — Design

> Account/role model, navigation fixes, dashboard, and the deal-posting
> marketplace itself. Payments (Stripe Connect) are explicitly out of
> scope — that's Phase 3, once the owner has Stripe sandbox API keys set
> up. This phase uses manual matching only, per CLAUDE.md's build order.

## Context

Phase 1 built signup/login and HivePress Vendor (driver) profiles, but
no deal-posting mechanism exists yet. During Phase 1.5 (homepage/site
shell work), it was discovered that the homepage's "Get Started"/"Sign
Up" links pointed at a `/register/` URL that doesn't exist — HivePress
renders registration as a JS popup over `/account/login/`, not a
separate page. This phase fixes that alongside building the actual
marketplace.

## Account & Role Model

One WP user account per person. Signing up does not ask "customer or
driver" — it just creates an account. Becoming a driver is an upgrade
available at any time via "Become a Driver", using HivePress's existing
`/register-vendor/` flow, which already attaches a Vendor profile to
whatever account is currently logged in. No changes needed to this
underlying mechanic.

A single account can be both a customer (has posted deals) and a driver
(has a Vendor profile) simultaneously — mirrors how Uber/DoorDash/Turo
let one account be both a rider/customer and a driver/host.

## Navigation Fix

- **"Become a Driver"** already correctly points to
  `/account/login/?redirect=<url-encoded register-vendor URL>` — once
  logged in (or freshly registered), HivePress carries the user on to
  the vendor profile form automatically via the `redirect` param. Keep
  as-is.
- **"Get Started" / "Sign Up"** currently point to a 404 (`/register/`).
  Fix: point them at `/account/login/?register=1`. Add a small inline
  script (in `functions.php`, enqueued only when the query param is
  present) that auto-clicks HivePress's own "Register" toggle link on
  page load, so the popup opens directly into the Register form instead
  of defaulting to Login. This avoids overriding HivePress's own
  templates or JS.
- **Logged-in homepage:** renders normally (no forced redirect).
  "Get Started" becomes "Post a Deal" → `/dashboard/`. "Become a Driver"
  becomes "Go to Driver Dashboard" if the user already has a Vendor
  profile, otherwise stays "Become a Driver" → vendor signup. The nav's
  account menu (already working) continues to replace Sign In/Sign Up
  when logged in.

## Dashboard

New `/dashboard/` page template — separate from HivePress's
`/account/settings/` (profile editing stays there). Content shown
depends on which roles the logged-in user actually has:

**Everyone sees:**
- **"My Deals"** — every deal they've posted, with current status
  (Pending/Open/Assigned/Completed/Cancelled). Once Assigned, shows the
  driver's name and a link into the Messages thread with them. Once
  Completed, shows a "Leave a Review" prompt if not yet reviewed.
- **"Post a Deal"** button → deal-posting form at `/dashboard/post-a-deal/`.

**Users with a Vendor (driver) profile additionally see:**
- **"Available Deals"** — Open deals in their city, each with a "Claim
  This Job" button (self-assign).
- **"My Jobs"** — deals assigned to them, with a Messages link to the
  customer and a "Mark as Completed" button.

**Users without a Vendor profile** see a "Want to drive? Become a
Driver" prompt linking to `/register-vendor/`.

## Deal Data Model

New custom post type `gw_deal`, registered in a new plugin file
`gowonderlu-deals.php` (same pattern as `gowonderlu-user-fields.php`).

**Fields:**
- Title — short customer-given label (e.g. "Couch from apartment to storage unit")
- Item description + photos — description uses the native post content field; photos attach via the standard WP media uploader
- Pickup address & dropoff address — plain text fields, enhanced with Google Maps Places autocomplete for clean entry (autocomplete only — not used for distance-based matching in this phase)
- Date/time window
- City — taxonomy `gw_deal_city`, seeded with Austin / Houston / Dallas (a taxonomy, not a hardcoded list, so adding a city later is a wp-admin action, not a code change)
- Customer's offer price

**Status** uses WordPress's native post-status system, mirroring the
existing Listing moderation pattern from Phase 1:
- **Pending** (native) — submitted, awaiting admin review, not visible to drivers
- **Publish** = "Open" (native, reused) — approved, visible in the driver marketplace
- **`gw_assigned`**, **`gw_completed`**, **`gw_cancelled`** — three new custom statuses registered via `register_post_status()`

**Assignment:** whichever happens first wins — a driver clicking "Claim
This Job," or an admin assigning a specific driver from a meta box in
wp-admin. Both require the deal to still be Open at that moment (a
simple status check, not heavy locking — acceptable at expected
low-volume MVP scale). Claiming sets status to `gw_assigned` and
records the driver's user ID in post meta.

**Completion/cancellation:** the assigned driver marks a job
"Completed" from their dashboard (→ `gw_completed`). Either party can
cancel while Pending/Open/Assigned (→ `gw_cancelled`).

## Messaging, Notifications & Reviews

**Messaging:** once a deal is Assigned, both dashboard views show a
"Message [other party]" link opening a HivePress Messages conversation
(the already-active extension) between customer and driver, linked to
that deal for context. The exact HivePress function/template for
starting a conversation outside its normal Listing context is confirmed
against the installed plugin's code during implementation.

**Notifications** (plain `wp_mail()` for now — SMS is Phase 4):
- Customer: email when their deal moves Pending → Open, and Open → Assigned.
- Driver: email when assigned a deal.
- New chat messages: HivePress Messages may already send its own
  notification email by default — confirm during implementation and
  only add custom notification code if it doesn't.

**Reviews:** when a deal reaches Completed, the customer's dashboard
shows "Leave a Review" for that driver, using the already-active
HivePress Reviews extension. The review attaches to the driver's public
Vendor profile — the trust/track-record signal the About page already
promises.

## Geolocation Setup

wp-admin/external task, not code: create a Google Cloud project, enable
the Places API, generate an API key, enter it in HivePress Settings →
Integrations. Used only for address autocomplete on the deal-posting
form's pickup/dropoff fields — not for distance-based matching (the City
taxonomy handles coarse filtering for now).

## Explicitly Out of Scope (This Phase)

- Stripe Connect / any real payment processing — Phase 3.
- SMS notifications — Phase 4.
- Distance/proximity-based driver matching — future work once there's
  real usage data to justify it.
- HivePress's premium Requests/Marketplace extensions — decided against
  in favor of a custom post type for full control.

## Testing / Verification

1. Logged-out visitor clicks "Get Started" → Register popup opens directly (not Login) → account created → lands on dashboard.
2. Customer posts a deal → appears as Pending in wp-admin.
3. Admin approves (Publish) → deal becomes Open, visible on drivers' dashboards filtered to their city.
4. A driver clicks "Claim This Job" → status → Assigned, customer's dashboard shows the driver's name + a message link.
5. Customer and driver exchange a message via HivePress Messages.
6. Driver marks "Completed" → customer's dashboard shows "Leave a Review" → review appears on the driver's public Vendor profile.
7. Cancellation path works from both customer and driver sides.
8. An existing logged-in customer clicks "Become a Driver" → keeps their same account, gains a Vendor profile, dashboard now also shows "Available Deals" and "My Jobs."
9. Admin can directly assign a driver to an Open deal from a wp-admin meta box, as an alternative to a driver self-claiming.
10. Google Maps autocomplete works on the pickup/dropoff address fields.
