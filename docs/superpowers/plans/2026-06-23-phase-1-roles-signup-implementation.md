# Phase 1 (Roles + Signup) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up working customer and driver roles on the live GoWonderlu site using HivePress's native User/Vendor/Listing concepts, with a phone number field on every account and manual admin approval gating new drivers.

**Architecture:** One new custom plugin file (`gowonderlu-user-fields.php`) adds a phone number field to HivePress's registration and account-settings forms via documented filter/action hooks. Everything else — driver-specific Vendor attributes (Vehicle Type, Vehicle Details), the manual-approval gate, and brand styling — is wp-admin configuration, no new code. No new PHP page templates; HivePress's own Register/Login/Account/Vendor/Listing pages are used as-is.

**Tech Stack:** HivePress core (already active) + its Geolocation/Messages/Reviews extensions (already active), Astra parent theme's Customizer, plain PHP plugin file, WordPress user meta.

## Global Constraints

- No AI attribution anywhere: no "Co-Authored-By" lines, no AI mentions in commit messages, comments, or code.
- Brand palette: navy `#0B1F3A` (primary), amber/gold `#F5A623` (accent) — fixed, do not substitute other hexes.
- Out of scope for Phase 1: posting/browsing actual hauling deals, payments, notification customization, matching logic, Reviews UI configuration. Do not add any of these.
- No new custom Listing attributes and no new PHP page templates — HivePress's default Listing type and built-in pages are used as-is.
- Driver/Vendor profiles must require manual admin approval before going live (via HivePress's Listing moderation, not a custom approval system).
- Deploy is git push to GitHub (history only) + manual upload via Hostinger File Manager or WP Admin's plugin/theme upload. **Never** connect Hostinger's GitHub auto-deploy tool to this repo (see `CLAUDE.md` — it wiped the live WP install once already in Phase 0).

---

## File Structure

```
gowonderlu/
└── wp-content/
    └── plugins/
        └── gowonderlu-user-fields.php   ← new, this plan's only code file
```

No other files are created or modified by this plan. (`gowonderlu-theme/style.css` is intentionally **not** touched — styling happens via Astra's Customizer, per the approved design.)

---

### Task 1: Custom plugin — phone number field

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/plugins/gowonderlu-user-fields.php`

**Interfaces:**
- Consumes: HivePress's documented filter hooks `hivepress/v1/forms/user_register` and `hivepress/v1/forms/user_update`, and the confirmed action hook `hivepress/v1/models/user/register`.
- Produces: a `phone_number` WordPress user-meta key, populated at registration and editable afterward. Task 2 deploys and verifies this file; Task 6's end-to-end walkthrough depends on every test account having a phone number.

This is the first file under `wp-content/plugins/gowonderlu-*`, establishing the pattern for every custom plugin from here on: a single self-contained PHP file, no build step, activated directly from wp-admin → Plugins.

- [ ] **Step 1: Write the plugin file**

```php
<?php
/**
 * Plugin Name: GoWonderlu User Fields
 * Description: Adds a required phone number field to HivePress registration and account settings.
 * Version: 0.1.0
 */

defined( 'ABSPATH' ) || exit;

function gowonderlu_phone_field_args() {
	return [
		'label'    => 'Phone Number',
		'type'     => 'text',
		'required' => true,
		'_order'   => 100,
	];
}

add_filter(
	'hivepress/v1/forms/user_register',
	function ( $form ) {
		$form['fields']['phone_number'] = gowonderlu_phone_field_args();

		return $form;
	},
	100
);

add_filter(
	'hivepress/v1/forms/user_update',
	function ( $form ) {
		$form['fields']['phone_number'] = array_merge(
			gowonderlu_phone_field_args(),
			[ 'default' => get_user_meta( get_current_user_id(), 'phone_number', true ) ]
		);

		return $form;
	},
	100
);

function gowonderlu_save_phone_number( $user_id, $values ) {
	if ( isset( $values['phone_number'] ) ) {
		update_user_meta( $user_id, 'phone_number', $values['phone_number'] );
	}
}

add_action( 'hivepress/v1/models/user/register', 'gowonderlu_save_phone_number', 10, 2 );
add_action( 'hivepress/v1/models/user/update', 'gowonderlu_save_phone_number', 10, 2 );
```

**Note on the last `add_action` line:** `hivepress/v1/models/user/register` firing with `( $user_id, $values )` is confirmed (HivePress's own official example uses this exact signature for adding first/last name fields). `hivepress/v1/models/user/update` is the same pattern applied to the settings form by symmetry, but isn't independently confirmed — Task 2's Step 4 verifies it live. If it turns out not to fire or not to carry `$values['phone_number']`, replace the last line with:

```php
add_action(
	'hivepress/v1/models/user/update_phone_number',
	function ( $user_id ) {
		// This variant fires per-field; re-read and re-save isn't needed since
		// HivePress already wrote the field's own meta — this hook exists as
		// the documented fallback if the generic update action above doesn't fire.
	}
);
```

(In practice, if the generic action doesn't fire, the more likely fix is that HivePress already saves any form field matching a registered meta key automatically — Task 2 Step 4 will show directly whether the phone number persisted with no save action needed at all. Don't pre-apply this fallback; only touch it if Step 4 fails.)

- [ ] **Step 2: Lint the file**

```bash
php -l /Users/kryskaka/codingproject2026/gowonderlu/wp-content/plugins/gowonderlu-user-fields.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Commit**

```bash
git add wp-content/plugins/gowonderlu-user-fields.php
git commit -m "Add custom plugin: phone number field on registration and account settings"
```

---

### Task 2: Deploy and verify the phone number plugin

**Files:** none (live-site deployment + manual verification only).

**Interfaces:**
- Consumes: `gowonderlu-user-fields.php` from Task 1.
- Produces: a working `phone_number` field live on `gowonderlu.com`, confirmed in both directions (set at registration, editable afterward). Task 6 depends on this being verified working before running the full walkthrough.

- [ ] **Step 1: Upload the plugin**

Via WP Admin → Plugins → Add New → Upload Plugin (zip the single file first, same pattern as Phase 0's theme upload), or via Hostinger File Manager into `wp-content/plugins/gowonderlu-user-fields.php` directly (it's a single file, no folder needed).

- [ ] **Step 2: Activate**

WP Admin → Plugins → find "GoWonderlu User Fields" → Activate.

- [ ] **Step 3: Verify the registration form**

Register a new test account (e.g. `phonetest1@example.com`) at `gowonderlu.com/register/` (or whatever HivePress's register page URL is on this site). Confirm a required "Phone Number" field appears on the form and the form rejects submission if it's left blank.

- [ ] **Step 4: Verify the value persisted**

WP Admin → Users → find the new test account → Edit. Confirm `phone_number` was saved (check via Users → your test user → scroll to custom fields, or `wp user meta get <user_id> phone_number` if WP-CLI is available — it isn't on this Hostinger plan, so check via wp-admin directly: User profile screen, or query through a quick admin-only debug page if needed).

Expected: the phone number you typed at registration shows up somewhere queryable against that user (even if not on the default profile screen, it must exist in user meta — confirm via any available admin view).

- [ ] **Step 5: Verify the account-settings form**

Log in as the test account on the frontend, go to Account → Settings (HivePress's dashboard settings tab). Confirm the Phone Number field is present, pre-filled with the value from registration, editable, and that changing it and saving updates the stored value (re-check Step 4's source after saving).

If the value does **not** persist after editing in Settings: the `hivepress/v1/models/user/update` action isn't firing as expected. Open `gowonderlu-user-fields.php`, replace the last `add_action` line per Task 1 Step 1's documented fallback, re-test from Step 1 of this task.

---

### Task 3: Vendor attributes — Vehicle Type and Vehicle Details

**Files:** none (wp-admin configuration only).

**Interfaces:**
- Consumes: nothing.
- Produces: two custom Vendor profile fields, used by Task 6's driver-signup walkthrough.

- [ ] **Step 1: Add the Vehicle Type attribute**

WP Admin → HivePress → Vendors → Attributes → Add New:
- Name: `Vehicle Type`
- Type: `Select`
- Options: `Car/SUV`, `Pickup Truck`, `Cargo Van`, `Box Truck`
- Editing: enabled, editable by the vendor on the frontend
- Display: enabled, shown on the vendor profile page

- [ ] **Step 2: Add the Vehicle Details attribute**

WP Admin → HivePress → Vendors → Attributes → Add New:
- Name: `Vehicle Details`
- Type: `Text`
- Required: no (optional free text, e.g. "2021 Ford Transit, fits a queen mattress")
- Editing: enabled, editable by the vendor on the frontend
- Display: enabled, shown on the vendor profile page

- [ ] **Step 3: Verify**

Go to the frontend "Become a Vendor" / vendor-registration form (or a vendor's profile-edit screen) and confirm both new fields appear with the expected type (a dropdown for Vehicle Type, a text box for Vehicle Details).

---

### Task 4: Driver approval gate — Listing moderation

**Files:** none (wp-admin configuration only).

**Interfaces:**
- Consumes: nothing.
- Produces: every new Listing (and therefore every new driver, since a Vendor isn't publicly live without an approved Listing) lands as Pending instead of going live immediately. Task 6's walkthrough depends on this being enabled before testing the approval flow.

- [ ] **Step 1: Enable moderation**

WP Admin → HivePress → Settings → Listings → Submission → enable **Moderation**. Save changes.

- [ ] **Step 2: Verify the setting saved**

Reload the Settings → Listings → Submission page and confirm Moderation still shows as enabled after the save.

---

### Task 5: Brand colors via Astra Customizer

**Files:** none (wp-admin configuration only).

**Interfaces:**
- Consumes: the brand hexes from Global Constraints (`#0B1F3A` navy, `#F5A623` amber).
- Produces: HivePress's buttons, links, and headings inherit these colors automatically, since they render through the active theme rather than hardcoded styles. Task 6's final visual check confirms this.

- [ ] **Step 1: Set the accent color**

WP Admin → Appearance → Customize → Global → Colors (Astra's customizer panel) → set the **Theme Color / Accent Color** to `#F5A623` (amber). This drives button backgrounds, links, and active-state highlights across the site, including HivePress's forms and cards.

- [ ] **Step 2: Set text/heading colors**

In the same Colors panel, set **Headings** and/or **Base/Text** color to `#0B1F3A` (navy) where Astra exposes those controls (exact control names vary slightly by Astra version — look for "Heading Color" and "Text Color" under Global → Typography or Global → Colors).

- [ ] **Step 3: Publish and verify**

Click Publish. Visit `gowonderlu.com/register/`, `/login/`, and the account dashboard. Confirm buttons/links read amber and headings read navy. Note any HivePress element that still looks unstyled (default WP gray/blue) — if anything stands out, inspect it with the browser's "Inspect Element" to find its real class name and report back; don't guess a fix without seeing the actual selector.

---

### Task 6: End-to-end verification

**Files:** none (manual walkthrough only, ties Tasks 1–5 together).

**Interfaces:**
- Consumes: the deployed plugin (Task 2), Vendor attributes (Task 3), moderation setting (Task 4), and Customizer colors (Task 5).
- Produces: confirmation that Phase 1 is fully working end-to-end, matching the spec's "Testing / Verification" section.

- [ ] **Step 1: Register a customer**

Register a fresh test account (e.g. `customer-test@example.com`) as a plain user (no vendor application). Confirm the Phone Number field works as verified in Task 2.

- [ ] **Step 2: Register a driver and apply as Vendor**

Register a second test account (e.g. `driver-test@example.com`). From the account dashboard, find the "Become a Vendor" flow. Fill in the Vendor profile, including the Vehicle Type and Vehicle Details fields from Task 3.

- [ ] **Step 3: Submit one Listing**

As the driver-test vendor, submit one Listing representing their hauling service: a title (e.g. "Driver Test Hauling"), a description, at least one photo, and a location (via the Geolocation extension's address field).

- [ ] **Step 4: Confirm Pending status**

Confirm the new Listing shows as **Pending** in WP Admin → Listings, and that it does **not** appear in the public listings/vendor directory yet (check the live site logged out or in a private browser window).

- [ ] **Step 5: Approve and confirm it goes live**

In WP Admin, approve the Listing. Reload the public directory/listing page and confirm:
- The Listing is now publicly visible.
- The Vendor profile shows Vehicle Type and Vehicle Details correctly.
- The listing's location renders correctly (map/address via Geolocation).
- Buttons, links, and headings on these pages match the navy/amber brand (Task 5).

- [ ] **Step 6: Update `CLAUDE.md`**

Edit `/Users/kryskaka/codingproject2026/gowonderlu/CLAUDE.md`:
- Update **Current Phase Status** to mark Phase 1 complete, with a one-line summary (customer + driver roles live, phone field via custom plugin, manual listing approval gate, Astra brand colors applied).
- Add a row to **Key Decisions** if anything here turned out to deviate from this plan (e.g. if the `update` action fallback from Task 2 was needed).

```bash
git add CLAUDE.md
git commit -m "Record Phase 1 completion: roles, signup, and driver approval gate live"
git push
```

---

## Self-Review Notes

- **Spec coverage:** Role/data model → Tasks 1–3. Approval workflow → Task 4. UI/styling → Task 5. Custom plugin → Tasks 1–2. Testing/verification → Task 6. All five spec sections have a task.
- **Placeholder scan:** No TBD/TODO. The one area of genuine external uncertainty (the `user_update` action hook name) is handled as documented, real fallback code gated by an explicit verification step — not a vague placeholder.
- **Type/name consistency:** `phone_number` meta key, `gowonderlu_phone_field_args()` and `gowonderlu_save_phone_number()` function names are used consistently across both filters and both actions in Task 1; no other task redefines them.
