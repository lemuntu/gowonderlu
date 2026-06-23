# GoWonderlu — Phase 1: Roles + Signup Design

## Context

Phase 0 (Foundation) is complete: Astra + `gowonderlu-theme` child theme are live,
and HivePress core + Geolocation/Messages/Reviews extensions are active and
unconfigured. HivePress's Marketplace and Requests extensions (which would
natively model a "post a job / respond with offers" two-sided marketplace)
were deferred — they're premium hivepress.io add-ons, not in the free
WP.org directory. See
`docs/superpowers/specs/2026-06-23-phase-0-foundation-design.md`.

This is Phase 1 of the build order in `CLAUDE.md`:

```
0. Foundation — done
1. Roles + signup (driver/customer) and profiles   ← this doc
2. Customer "post a deal" + driver browse/accept (manual matching only)
3. Stripe Connect payments + payouts
4. Email + SMS notifications
5. Reviews + messaging
6. Auto-matching rules
7. Newsletter + marketing layer
```

## Goals (Phase 1 only)

- Stand up two working roles — **customer** and **driver** — using
  HivePress's native concepts rather than building custom WordPress roles
  from scratch.
- Customers can register, log in, and maintain a basic profile (name,
  avatar, phone number).
- Drivers can register, apply to become a HivePress **Vendor**, fill in
  hauling-specific profile attributes, and submit a **Listing**
  representing their hauling service. New driver listings require manual
  admin approval before going live.
- All of this uses HivePress's existing Register/Login/Account/Vendor/
  Listing pages, restyled with the brand palette — no new custom PHP page
  templates.

**Out of scope for Phase 1:** posting/browsing actual hauling "deals"
(that's Phase 2 — depends on whether Marketplace/Requests extensions get
purchased or a custom alternative is built), payments, notifications
beyond what HivePress sends by default, matching logic, and reviews UI
(Reviews extension is active but its configuration is deferred to Phase 5
per the build order).

## Role & Data Model

- **Customer** = a regular WordPress user created via HivePress's default
  registration form. Profile fields: display name and avatar (WordPress
  defaults — no custom field needed) plus a new **Phone Number** field
  (see "Custom Plugin" below). Phone number matters starting Phase 2, when
  a matched driver needs to reach the customer.
- **Driver** = a WordPress user who additionally registers as a HivePress
  **Vendor**. Same Phone Number field applies (Vendors are still WP users
  underneath). Vendor profile gets two new custom attributes, configured
  in wp-admin (no code):
  - **Vehicle Type** — select: Car/SUV, Pickup Truck, Cargo Van, Box Truck
  - **Vehicle Details** — optional free text (e.g. "2021 Ford Transit,
    fits a queen mattress")
- A driver only becomes publicly visible once they submit at least one
  **Listing** (their hauling-service post — title, description, photos,
  location via the already-installed Geolocation extension) **and it's
  approved**. This is HivePress's native behavior: a Vendor isn't
  considered "live" in the directory until they have an approved Listing.
  No custom Listing attributes are added in Phase 1 — vehicle specifics
  live on the Vendor profile, not the Listing.

## Driver Approval Workflow

- wp-admin → HivePress → Settings → Listings → Submission → enable
  **Moderation**. New listings (and therefore new drivers) land as
  **Pending** instead of going live immediately.
- Admin reviews pending listings in wp-admin and approves/rejects manually.
  This is HivePress's built-in moderation queue — no custom approval UI.
- No email notification customization in Phase 1 (that's Phase 4 — Email +
  SMS notifications). The admin checks wp-admin periodically for pending
  listings until then.

## UI & Styling

- Use HivePress's built-in pages as-is: Register, Login, Account
  (dashboard), Vendor profile/onboarding, Listing submission. No new PHP
  page templates in the child theme.
- Add brand styling to `wp-content/themes/gowonderlu-theme/style.css`:
  navy (`#0B1F3A`) + amber (`#F5A623`) applied to buttons, form fields, and
  key HivePress page elements (forms, vendor cards, listing cards).

## Custom Plugin: Phone Number Field

The only new code in Phase 1. New file:
`wp-content/plugins/gowonderlu-user-fields.php` — the first file under
`wp-content/plugins/gowonderlu-*`, establishing the pattern future custom
plugins follow.

Responsibilities:
- Add a required **Phone Number** field to the registration form via the
  `hivepress/v1/forms/user_register` filter.
- Add the same field to the account-settings form (so existing users can
  add/edit their phone after registering) via the
  `hivepress/v1/forms/user_update` filter.
- Store the value as user meta and ensure it displays on the user's
  account/profile view.

Everything else (Vehicle Type/Details attributes, Moderation toggle) is
pure wp-admin configuration — no code, same pattern as Phase 0's plugin
installs.

## Testing / Verification

No automated tests — this is WP configuration plus one small PHP filter
file, not a system with a meaningful local test harness yet. Verification
is manual, end-to-end on the live site:

1. Register a test customer account → confirm the Phone Number field
   appears on registration and saves correctly; confirm it's editable from
   Account → Settings afterward.
2. Register a second test account, apply to become a Vendor, fill in
   Vehicle Type + Vehicle Details, submit one Listing with a photo and
   location.
3. Confirm the Listing shows as **Pending** and is *not* publicly visible
   yet.
4. Approve it in wp-admin → confirm the Vendor profile and Listing are now
   publicly visible, showing Vehicle Type/Details correctly, and the
   listing's location renders via the Geolocation extension.
5. Visually check the Register/Login/Account/Vendor pages against the
   brand palette (navy + amber).

## Open Questions / Risks

- None outstanding — role model (Vendor = driver, User = customer),
  approval gate (manual, via Listing moderation), UI approach (style
  HivePress's own pages, no custom templates), and the one custom field
  (phone number) are all confirmed.
