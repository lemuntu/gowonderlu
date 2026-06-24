# Phase 2: Deals Marketplace Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the customer-posts-a-deal / driver-claims-a-deal marketplace, fix the broken Sign Up/Sign In navigation, and ship a dashboard — all using HivePress's existing account/vendor plumbing rather than new auth systems.

**Architecture:** A new plugin (`gowonderlu-deals.php`) registers a `gw_deal` custom post type with custom statuses (Pending → Open → Assigned → Completed/Cancelled) and a `gw_deal_city` taxonomy. Two new theme page templates (`dashboard.php`, `post-a-deal.php`) provide the frontend UI. Status transitions happen via simple authenticated form POSTs handled on `template_redirect` — no AJAX framework, no REST API, matching this project's existing low-complexity WordPress patterns. Messaging and Reviews reuse the already-active HivePress extensions rather than building new plumbing. Payments are explicitly out of scope (Phase 3).

**Tech Stack:** WordPress, PHP, the existing `gowonderlu-theme` child theme and `gowonderlu-user-fields.php` plugin pattern, HivePress core + Messages + Reviews extensions (already active), Google Maps Places API (new).

## Global Constraints

- No AI attribution in commits or code.
- No payment processing of any kind in this phase — Stripe Connect is Phase 3.
- No SMS notifications in this phase — plain `wp_mail()` only; SMS is Phase 4.
- No distance/proximity-based matching — city-level filtering only.
- Do not use HivePress's premium Requests/Marketplace extensions — build the custom post type instead (explicit decision in the spec).
- Deploy workflow: list every created/modified file after each task for manual Hostinger File Manager upload + cache purge, matching this project's established convention (see CLAUDE.md's Deployment Workflow).
- This project has no automated test suite — verification is `php -l` linting plus manual wp-admin/browser checks, matching every prior plan in this repo.

---

### Task 1: Deal post type, statuses, taxonomy, and driver city field

**Files:**
- Create: `wp-content/plugins/gowonderlu-deals.php`
- Modify: `wp-content/plugins/gowonderlu-user-fields.php`

**Interfaces:**
- Produces: custom post type `gw_deal`; custom statuses `gw_assigned`, `gw_completed`, `gw_cancelled`; taxonomy `gw_deal_city` (seeded with Austin/Houston/Dallas terms); meta key constants `GW_DEAL_META_PICKUP`, `GW_DEAL_META_DROPOFF`, `GW_DEAL_META_DATE_WINDOW`, `GW_DEAL_META_PRICE`, `GW_DEAL_META_DRIVER_ID`; user meta key `_gw_driver_city` (stores a `gw_deal_city` term ID on the driver's WP user account). All later tasks consume these.

- [ ] **Step 1: Create the plugin file with post type, statuses, and taxonomy**

```php
<?php
/**
 * Plugin Name: GoWonderlu Deals
 * Description: Registers the Deal custom post type, statuses, and city taxonomy for the GoWonderlu marketplace.
 * Version: 0.1.0
 */

defined( 'ABSPATH' ) || exit;

define( 'GW_DEAL_META_PICKUP', '_gw_deal_pickup_address' );
define( 'GW_DEAL_META_DROPOFF', '_gw_deal_dropoff_address' );
define( 'GW_DEAL_META_DATE_WINDOW', '_gw_deal_date_window' );
define( 'GW_DEAL_META_PRICE', '_gw_deal_price' );
define( 'GW_DEAL_META_DRIVER_ID', '_gw_deal_driver_id' );

add_action( 'init', 'gowonderlu_register_deal_post_type' );

function gowonderlu_register_deal_post_type() {
	register_post_type(
		'gw_deal',
		array(
			'labels'              => array(
				'name'          => 'Deals',
				'singular_name' => 'Deal',
				'add_new_item'  => 'Add New Deal',
				'edit_item'     => 'Edit Deal',
				'all_items'     => 'All Deals',
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-car',
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail' ),
			'has_archive'         => false,
			'show_in_rest'        => false,
		)
	);
}

add_action( 'init', 'gowonderlu_register_deal_statuses' );

function gowonderlu_register_deal_statuses() {
	register_post_status(
		'gw_assigned',
		array(
			'label'                     => 'Assigned',
			'public'                    => false,
			'internal'                  => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Assigned <span class="count">(%s)</span>', 'Assigned <span class="count">(%s)</span>' ),
		)
	);

	register_post_status(
		'gw_completed',
		array(
			'label'                     => 'Completed',
			'public'                    => false,
			'internal'                  => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
		)
	);

	register_post_status(
		'gw_cancelled',
		array(
			'label'                     => 'Cancelled',
			'public'                    => false,
			'internal'                  => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>' ),
		)
	);
}

add_action( 'init', 'gowonderlu_register_deal_city_taxonomy' );

function gowonderlu_register_deal_city_taxonomy() {
	register_taxonomy(
		'gw_deal_city',
		'gw_deal',
		array(
			'labels'            => array(
				'name'          => 'Cities',
				'singular_name' => 'City',
			),
			'public'            => false,
			'show_ui'           => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => false,
		)
	);
}

register_activation_hook( __FILE__, 'gowonderlu_seed_deal_cities' );

function gowonderlu_seed_deal_cities() {
	gowonderlu_register_deal_city_taxonomy();

	foreach ( array( 'Austin', 'Houston', 'Dallas' ) as $city ) {
		if ( ! term_exists( $city, 'gw_deal_city' ) ) {
			wp_insert_term( $city, 'gw_deal_city' );
		}
	}
}
```

- [ ] **Step 2: Lint the new file**

Run: `php -l wp-content/plugins/gowonderlu-deals.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Add the driver "City" field, reusing the same taxonomy terms**

A driver's city determines which Open deals they see in Task 5's dashboard. Add this to the existing `gowonderlu-user-fields.php` (same file as the Phase 1 Phone Number field), appended at the end:

```php
add_filter(
	'hivepress/v1/forms/vendor_update',
	function ( $form ) {
		$cities = get_terms(
			array(
				'taxonomy'   => 'gw_deal_city',
				'hide_empty' => false,
			)
		);

		$options = array();

		foreach ( $cities as $city ) {
			$options[ $city->term_id ] = $city->name;
		}

		$form['fields']['gw_driver_city'] = array(
			'label'    => 'City',
			'type'     => 'select',
			'options'  => $options,
			'required' => true,
			'default'  => get_user_meta( get_current_user_id(), '_gw_driver_city', true ),
			'_order'   => 90,
		);

		return $form;
	},
	100
);

add_action(
	'hivepress/v1/models/vendor/update',
	function ( $vendor_id ) {
		if ( ! isset( $_POST['gw_driver_city'] ) ) {
			return;
		}

		$vendor = hivepress()->model->get_model( 'vendor' )->get_by_id( $vendor_id );

		if ( $vendor ) {
			update_user_meta( $vendor->get_user__id(), '_gw_driver_city', absint( $_POST['gw_driver_city'] ) );
		}
	}
);
```

> **Note for implementer:** this assumes HivePress's vendor-update hook name and model API match the pattern shown (`hivepress/v1/forms/vendor_update`, `hivepress/v1/models/vendor/update`, `get_user__id()`). Confirm these exact names against the installed HivePress version before relying on them — if `vendor_update` isn't the real filter name, check HivePress's own form-registration code (`wp-content/plugins/hivepress/includes/forms/`) for the actual vendor profile-edit form key, the way Phase 1 confirmed `user_register`/`user_update` empirically. If the model API differs, fall back to `update_user_meta( get_current_user_id(), '_gw_driver_city', absint( $_POST['gw_driver_city'] ) )` inside the same action — simpler, and correct as long as the action fires while the editing vendor's owner is the current logged-in user (true for self-service profile edits, which is the only case this field needs to handle).

- [ ] **Step 4: Lint and commit**

```bash
php -l wp-content/plugins/gowonderlu-user-fields.php
git add wp-content/plugins/gowonderlu-deals.php wp-content/plugins/gowonderlu-user-fields.php
git commit -m "Register Deal post type, statuses, city taxonomy, and driver city field"
```

- [ ] **Step 5: Deploy and verify**

Files to upload via File Manager: `wp-content/plugins/gowonderlu-deals.php` (new), `wp-content/plugins/gowonderlu-user-fields.php` (modified). Activate "GoWonderlu Deals" in Plugins. Confirm:
1. A "Deals" menu item with a car icon appears in the wp-admin sidebar.
2. Go to the existing vendor profile edit form (Account → Settings, while logged in as a vendor) and confirm a "City" dropdown now appears with Austin/Houston/Dallas options. If it doesn't appear, the hook name in Step 3's note needs adjusting — fix and re-deploy before moving to Task 5, since the dashboard depends on this.

---

### Task 2: Fix Sign Up / Get Started navigation

**Files:**
- Modify: `wp-content/themes/gowonderlu-theme/functions.php`
- Modify: `wp-content/themes/gowonderlu-theme/front-page.php`

**Interfaces:**
- Consumes: nothing new.
- Produces: a working `/account/login/?register=1` entry point that opens HivePress's Register form directly. Task 9 reuses this same URL pattern.

- [ ] **Step 1: Add the auto-open-Register script**

Append to `wp-content/themes/gowonderlu-theme/functions.php`:

```php
add_action( 'wp_footer', 'gowonderlu_auto_open_register' );

function gowonderlu_auto_open_register() {
	if ( empty( $_GET['register'] ) ) {
		return;
	}
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var links = document.querySelectorAll('a');
		for (var i = 0; i < links.length; i++) {
			if (links[i].textContent.trim() === 'Register') {
				links[i].click();
				break;
			}
		}
	});
	</script>
	<?php
}
```

This targets the link by its visible text ("Register") rather than guessing a CSS class, since that's the one thing confirmed directly from the live site (the "Don't have an account yet? Register" link).

- [ ] **Step 2: Fix the hardcoded "Get Started" link in front-page.php**

In `wp-content/themes/gowonderlu-theme/front-page.php`, find:

```php
<a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="gw-btn gw-btn-fill">Get Started</a>
```

Replace with:

```php
<a href="<?php echo esc_url( home_url( '/account/login/?register=1' ) ); ?>" class="gw-btn gw-btn-fill">Get Started</a>
```

- [ ] **Step 3: Lint and commit**

```bash
php -l wp-content/themes/gowonderlu-theme/functions.php
php -l wp-content/themes/gowonderlu-theme/front-page.php
git add wp-content/themes/gowonderlu-theme/functions.php wp-content/themes/gowonderlu-theme/front-page.php
git commit -m "Fix Get Started link and auto-open the Register form via a query flag"
```

- [ ] **Step 4: Deploy and verify**

Files to upload: `wp-content/themes/gowonderlu-theme/functions.php`, `wp-content/themes/gowonderlu-theme/front-page.php`. Visit `https://gowonderlu.com/account/login/?register=1` directly and confirm the Register form opens automatically (not Login). Click "Get Started" on the homepage and confirm the same.

- [ ] **Step 5: Fix the "Sign Up" nav menu item (wp-admin task, not code)**

Go to Appearance → Menus → edit the "Sign Up" item → change its URL to `https://gowonderlu.com/account/login/?register=1`. Save Menu.

---

### Task 3: Deal-posting form

**Files:**
- Create: `wp-content/themes/gowonderlu-theme/post-a-deal.php`
- Modify: `wp-content/plugins/gowonderlu-deals.php`
- Modify: `wp-content/themes/gowonderlu-theme/style.css`

**Interfaces:**
- Consumes: `GW_DEAL_META_*` constants and `gw_deal_city` taxonomy from Task 1.
- Produces: a published-to-Pending deal on form submission. Task 4 and Task 5 query these deals by status/meta.

- [ ] **Step 1: Add the submission handler to the plugin**

Append to `wp-content/plugins/gowonderlu-deals.php`:

```php
add_action( 'template_redirect', 'gowonderlu_handle_deal_submission' );

function gowonderlu_handle_deal_submission() {
	if ( empty( $_POST['gowonderlu_post_deal_nonce'] ) || ! wp_verify_nonce( $_POST['gowonderlu_post_deal_nonce'], 'gowonderlu_post_deal' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$title       = sanitize_text_field( wp_unslash( $_POST['gw_deal_title'] ?? '' ) );
	$description = sanitize_textarea_field( wp_unslash( $_POST['gw_deal_description'] ?? '' ) );
	$pickup      = sanitize_text_field( wp_unslash( $_POST['gw_deal_pickup'] ?? '' ) );
	$dropoff     = sanitize_text_field( wp_unslash( $_POST['gw_deal_dropoff'] ?? '' ) );
	$city_id     = absint( $_POST['gw_deal_city'] ?? 0 );
	$date_window = sanitize_text_field( wp_unslash( $_POST['gw_deal_date_window'] ?? '' ) );
	$price       = absint( $_POST['gw_deal_price'] ?? 0 );

	if ( ! $title || ! $description || ! $pickup || ! $dropoff || ! $city_id || ! $date_window || ! $price ) {
		return;
	}

	$deal_id = wp_insert_post(
		array(
			'post_type'    => 'gw_deal',
			'post_title'   => $title,
			'post_content' => $description,
			'post_status'  => 'pending',
			'post_author'  => get_current_user_id(),
		)
	);

	if ( is_wp_error( $deal_id ) || ! $deal_id ) {
		return;
	}

	wp_set_post_terms( $deal_id, array( $city_id ), 'gw_deal_city' );
	update_post_meta( $deal_id, GW_DEAL_META_PICKUP, $pickup );
	update_post_meta( $deal_id, GW_DEAL_META_DROPOFF, $dropoff );
	update_post_meta( $deal_id, GW_DEAL_META_DATE_WINDOW, $date_window );
	update_post_meta( $deal_id, GW_DEAL_META_PRICE, $price );

	if ( ! empty( $_FILES['gw_deal_photos']['name'][0] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		foreach ( $_FILES['gw_deal_photos']['name'] as $i => $name ) {
			if ( empty( $name ) ) {
				continue;
			}

			media_handle_sideload(
				array(
					'name'     => $_FILES['gw_deal_photos']['name'][ $i ],
					'type'     => $_FILES['gw_deal_photos']['type'][ $i ],
					'tmp_name' => $_FILES['gw_deal_photos']['tmp_name'][ $i ],
					'error'    => $_FILES['gw_deal_photos']['error'][ $i ],
					'size'     => $_FILES['gw_deal_photos']['size'][ $i ],
				),
				$deal_id
			);
		}
	}

	wp_safe_redirect( add_query_arg( 'posted', '1', wp_get_referer() ? wp_get_referer() : home_url( '/post-a-deal/' ) ) );
	exit;
}
```

- [ ] **Step 2: Create the form template**

```php
<?php
/**
 * Template Name: Post a Deal
 */
get_header();

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$cities = get_terms(
	array(
		'taxonomy'   => 'gw_deal_city',
		'hide_empty' => false,
	)
);
?>

<div class="gw-page">
<section class="gw-legal gw-deal-form-section">
	<span class="gw-eyebrow">Post a deal</span>
	<h1>What do you need moved?</h1>

	<?php if ( ! empty( $_GET['posted'] ) ) : ?>
		<div class="gw-legal-notice"><strong>Submitted.</strong> Your deal is pending review and will be visible to drivers once approved.</div>
	<?php endif; ?>

	<form class="gw-deal-form" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'gowonderlu_post_deal', 'gowonderlu_post_deal_nonce' ); ?>

		<label>Title
			<input type="text" name="gw_deal_title" required>
		</label>

		<label>What needs to be moved?
			<textarea name="gw_deal_description" required></textarea>
		</label>

		<label>Pickup address
			<input type="text" name="gw_deal_pickup" id="gw_deal_pickup" required>
		</label>

		<label>Dropoff address
			<input type="text" name="gw_deal_dropoff" id="gw_deal_dropoff" required>
		</label>

		<label>City
			<select name="gw_deal_city" required>
				<option value="">— Select a city —</option>
				<?php foreach ( $cities as $city ) : ?>
					<option value="<?php echo esc_attr( $city->term_id ); ?>"><?php echo esc_html( $city->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>

		<label>Date/time window
			<input type="text" name="gw_deal_date_window" placeholder="e.g. Saturday morning, June 28" required>
		</label>

		<label>Your offer ($)
			<input type="number" name="gw_deal_price" min="0" step="1" required>
		</label>

		<label>Photos (optional)
			<input type="file" name="gw_deal_photos[]" multiple accept="image/*">
		</label>

		<button type="submit" class="gw-btn gw-btn-fill">Submit Deal</button>
	</form>
</section>
</div>

<?php
get_footer();
```

- [ ] **Step 3: Add CSS for the form**

Append to `wp-content/themes/gowonderlu-theme/style.css`:

```css
.gw-deal-form-section {
	max-width: 600px;
}

.gw-deal-form {
	margin-top: 24px;
	display: flex;
	flex-direction: column;
	gap: 18px;
}

.gw-deal-form label {
	display: flex;
	flex-direction: column;
	gap: 6px;
	font-size: 14px;
	color: var(--gw-navy);
	font-weight: 600;
}

.gw-deal-form input,
.gw-deal-form select,
.gw-deal-form textarea {
	font-family: 'DM Sans', sans-serif;
	font-size: 15px;
	font-weight: 400;
	padding: 10px 14px;
	border: 0.5px solid var(--gw-border);
	border-radius: 8px;
	color: var(--gw-ink);
}

.gw-deal-form textarea {
	min-height: 100px;
	resize: vertical;
}

.gw-deal-form button {
	align-self: flex-start;
	margin-top: 8px;
}
```

- [ ] **Step 4: Lint and commit**

```bash
php -l wp-content/plugins/gowonderlu-deals.php
php -l wp-content/themes/gowonderlu-theme/post-a-deal.php
git add wp-content/plugins/gowonderlu-deals.php wp-content/themes/gowonderlu-theme/post-a-deal.php wp-content/themes/gowonderlu-theme/style.css
git commit -m "Add the deal-posting form and its submission handler"
```

- [ ] **Step 5: Deploy and verify**

Files to upload: `wp-content/plugins/gowonderlu-deals.php`, `wp-content/themes/gowonderlu-theme/post-a-deal.php` (new), `wp-content/themes/gowonderlu-theme/style.css`. In wp-admin, Pages → Add New → title "Post a Deal" → Page Attributes → Template → "Post a Deal" → Publish. Visit the live page while logged in, submit a complete deal, and confirm it appears in wp-admin → Deals as **Pending** with all fields/photos saved.

---

### Task 4: Status transition actions (claim, complete, cancel, admin-assign)

**Files:**
- Modify: `wp-content/plugins/gowonderlu-deals.php`

**Interfaces:**
- Consumes: `GW_DEAL_META_DRIVER_ID` from Task 1.
- Produces: `gowonderlu_claim_deal( $deal_id, $driver_user_id )`, `gowonderlu_complete_deal( $deal_id, $user_id )`, `gowonderlu_cancel_deal( $deal_id, $user_id )` — Task 5's dashboard forms POST to trigger these by name via the `gw_deal_action` field. Task 6 hooks into the resulting status transitions.

- [ ] **Step 1: Add the frontend action handler and the three transition functions**

Append to `wp-content/plugins/gowonderlu-deals.php`:

```php
add_action( 'template_redirect', 'gowonderlu_handle_deal_actions' );

function gowonderlu_handle_deal_actions() {
	if ( empty( $_POST['gw_deal_action'] ) || empty( $_POST['deal_id'] ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	check_admin_referer( 'gw_deal_action', 'gw_deal_nonce' );

	$deal_id = absint( $_POST['deal_id'] );
	$action  = sanitize_key( $_POST['gw_deal_action'] );
	$user_id = get_current_user_id();

	if ( 'claim' === $action ) {
		gowonderlu_claim_deal( $deal_id, $user_id );
	} elseif ( 'complete' === $action ) {
		gowonderlu_complete_deal( $deal_id, $user_id );
	} elseif ( 'cancel' === $action ) {
		gowonderlu_cancel_deal( $deal_id, $user_id );
	}

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url( '/dashboard/' ) );
	exit;
}

function gowonderlu_claim_deal( $deal_id, $driver_user_id ) {
	$deal = get_post( $deal_id );

	if ( ! $deal || 'gw_deal' !== $deal->post_type || 'publish' !== $deal->post_status ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'          => $deal_id,
			'post_status' => 'gw_assigned',
		)
	);

	update_post_meta( $deal_id, GW_DEAL_META_DRIVER_ID, $driver_user_id );

	return true;
}

function gowonderlu_complete_deal( $deal_id, $user_id ) {
	$deal      = get_post( $deal_id );
	$driver_id = (int) get_post_meta( $deal_id, GW_DEAL_META_DRIVER_ID, true );

	if ( ! $deal || 'gw_deal' !== $deal->post_type || 'gw_assigned' !== $deal->post_status ) {
		return false;
	}

	if ( $driver_id !== (int) $user_id ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'          => $deal_id,
			'post_status' => 'gw_completed',
		)
	);

	return true;
}

function gowonderlu_cancel_deal( $deal_id, $user_id ) {
	$deal      = get_post( $deal_id );
	$driver_id = (int) get_post_meta( $deal_id, GW_DEAL_META_DRIVER_ID, true );

	if ( ! $deal || 'gw_deal' !== $deal->post_type ) {
		return false;
	}

	$is_owner  = (int) $deal->post_author === (int) $user_id;
	$is_driver = $driver_id === (int) $user_id;

	if ( ! $is_owner && ! $is_driver ) {
		return false;
	}

	if ( ! in_array( $deal->post_status, array( 'pending', 'publish', 'gw_assigned' ), true ) ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'          => $deal_id,
			'post_status' => 'gw_cancelled',
		)
	);

	return true;
}

function gowonderlu_user_is_driver( $user_id ) {
	return (bool) get_user_meta( $user_id, '_gw_driver_city', true );
}
```

`gowonderlu_user_is_driver()` checks for the city field added in Task 1 Step 3 — a user only has that meta set once they've completed the vendor profile form, which is a reliable proxy for "has a driver profile" without needing to know HivePress's internal vendor-role slug.

- [ ] **Step 2: Add the admin "Assign Driver" meta box**

Append to `wp-content/plugins/gowonderlu-deals.php`:

```php
add_action( 'add_meta_boxes', 'gowonderlu_add_deal_assign_meta_box' );

function gowonderlu_add_deal_assign_meta_box() {
	add_meta_box(
		'gowonderlu_deal_assign',
		'Assign Driver',
		'gowonderlu_render_deal_assign_meta_box',
		'gw_deal',
		'side'
	);
}

function gowonderlu_render_deal_assign_meta_box( $post ) {
	if ( ! in_array( $post->post_status, array( 'publish', 'gw_assigned' ), true ) ) {
		echo '<p>Deal must be Open (Published) before a driver can be assigned.</p>';
		return;
	}

	$drivers = get_users(
		array(
			'meta_key'     => '_gw_driver_city',
			'meta_compare' => 'EXISTS',
		)
	);

	$current_driver_id = (int) get_post_meta( $post->ID, GW_DEAL_META_DRIVER_ID, true );

	wp_nonce_field( 'gowonderlu_assign_driver', 'gowonderlu_assign_driver_nonce' );

	echo '<select name="gowonderlu_assign_driver_id">';
	echo '<option value="">— Select a driver —</option>';

	foreach ( $drivers as $driver ) {
		printf(
			'<option value="%d" %s>%s</option>',
			$driver->ID,
			selected( $current_driver_id, $driver->ID, false ),
			esc_html( $driver->display_name )
		);
	}

	echo '</select>';
}

add_action( 'save_post_gw_deal', 'gowonderlu_save_deal_assign_meta_box' );

function gowonderlu_save_deal_assign_meta_box( $post_id ) {
	if ( ! isset( $_POST['gowonderlu_assign_driver_nonce'] ) || ! wp_verify_nonce( $_POST['gowonderlu_assign_driver_nonce'], 'gowonderlu_assign_driver' ) ) {
		return;
	}

	if ( empty( $_POST['gowonderlu_assign_driver_id'] ) ) {
		return;
	}

	update_post_meta( $post_id, GW_DEAL_META_DRIVER_ID, absint( $_POST['gowonderlu_assign_driver_id'] ) );

	remove_action( 'save_post_gw_deal', 'gowonderlu_save_deal_assign_meta_box' );
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_status' => 'gw_assigned',
		)
	);
	add_action( 'save_post_gw_deal', 'gowonderlu_save_deal_assign_meta_box' );
}
```

The driver list reuses the same `_gw_driver_city` meta check as `gowonderlu_user_is_driver()` — anyone who's completed the vendor profile form shows up as assignable, regardless of which city they're in (admin can use judgment; city filtering is for the driver-facing marketplace in Task 5, not this admin list).

- [ ] **Step 3: Lint and commit**

```bash
php -l wp-content/plugins/gowonderlu-deals.php
git add wp-content/plugins/gowonderlu-deals.php
git commit -m "Add deal status transition actions and admin driver-assignment meta box"
```

- [ ] **Step 4: Deploy and verify**

Files to upload: `wp-content/plugins/gowonderlu-deals.php`. Approve the Pending deal from Task 3 (set to Published in wp-admin). Open its edit screen and confirm the "Assign Driver" meta box appears with a dropdown of driver accounts (any account that's completed the vendor profile form with a City set). Assign one, update, and confirm the deal's status becomes "Assigned" in the Deals list.

---

### Task 5: Dashboard

**Files:**
- Create: `wp-content/themes/gowonderlu-theme/dashboard.php`
- Modify: `wp-content/themes/gowonderlu-theme/style.css`

**Interfaces:**
- Consumes: `gowonderlu_user_is_driver()` and the three transition functions from Task 4; `GW_DEAL_META_*` constants from Task 1.
- Produces: the `/dashboard/` page. Task 7, 8, and 9 link into this page.

- [ ] **Step 1: Create the dashboard template**

```php
<?php
/**
 * Template Name: Dashboard
 */
get_header();

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$user_id   = get_current_user_id();
$is_driver = gowonderlu_user_is_driver( $user_id );

$my_deals = get_posts(
	array(
		'post_type'      => 'gw_deal',
		'author'         => $user_id,
		'post_status'    => array( 'pending', 'publish', 'gw_assigned', 'gw_completed', 'gw_cancelled' ),
		'posts_per_page' => -1,
	)
);

$available_deals = array();
$my_jobs          = array();

if ( $is_driver ) {
	$driver_city_id = (int) get_user_meta( $user_id, '_gw_driver_city', true );

	$available_deals = get_posts(
		array(
			'post_type'      => 'gw_deal',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'gw_deal_city',
					'field'    => 'term_id',
					'terms'    => $driver_city_id,
				),
			),
		)
	);

	$my_jobs = get_posts(
		array(
			'post_type'      => 'gw_deal',
			'post_status'    => array( 'gw_assigned', 'gw_completed' ),
			'posts_per_page' => -1,
			'meta_key'       => GW_DEAL_META_DRIVER_ID,
			'meta_value'     => $user_id,
		)
	);
}

$status_labels = array(
	'pending'      => 'Pending review',
	'publish'      => 'Open',
	'gw_assigned'  => 'Assigned',
	'gw_completed' => 'Completed',
	'gw_cancelled' => 'Cancelled',
);
?>

<div class="gw-page">
<section class="gw-legal gw-dashboard">
	<span class="gw-eyebrow">Dashboard</span>
	<h1>Your deals</h1>

	<a href="<?php echo esc_url( home_url( '/post-a-deal/' ) ); ?>" class="gw-btn gw-btn-fill">Post a Deal</a>

	<h2>My Deals</h2>
	<?php if ( ! $my_deals ) : ?>
		<p>You haven't posted a deal yet.</p>
	<?php else : ?>
		<div class="gw-dashboard-list">
			<?php foreach ( $my_deals as $deal ) : ?>
				<div class="gw-dashboard-card">
					<div class="gw-dashboard-card-title"><?php echo esc_html( $deal->post_title ); ?></div>
					<div class="gw-dashboard-card-status"><?php echo esc_html( $status_labels[ $deal->post_status ] ?? $deal->post_status ); ?></div>

					<?php if ( 'gw_assigned' === $deal->post_status || 'gw_completed' === $deal->post_status ) :
						$driver = get_userdata( (int) get_post_meta( $deal->ID, GW_DEAL_META_DRIVER_ID, true ) );
						?>
						<?php if ( $driver ) : ?>
							<p>Driver: <?php echo esc_html( $driver->display_name ); ?> — <a href="<?php echo esc_url( home_url( '/account/messages/' ) ); ?>">Message</a></p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( 'gw_completed' === $deal->post_status ) : ?>
						<p><a href="<?php echo esc_url( home_url( '/account/reviews/' ) ); ?>">Leave a Review</a></p>
					<?php endif; ?>

					<?php if ( in_array( $deal->post_status, array( 'pending', 'publish', 'gw_assigned' ), true ) ) : ?>
						<form method="post">
							<?php wp_nonce_field( 'gw_deal_action', 'gw_deal_nonce' ); ?>
							<input type="hidden" name="deal_id" value="<?php echo esc_attr( $deal->ID ); ?>">
							<button type="submit" name="gw_deal_action" value="cancel" class="gw-btn gw-btn-outline">Cancel</button>
						</form>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $is_driver ) : ?>
		<h2>Available Deals</h2>
		<?php if ( ! $available_deals ) : ?>
			<p>No open deals in your city right now.</p>
		<?php else : ?>
			<div class="gw-dashboard-list">
				<?php foreach ( $available_deals as $deal ) : ?>
					<div class="gw-dashboard-card">
						<div class="gw-dashboard-card-title"><?php echo esc_html( $deal->post_title ); ?></div>
						<p><?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_PICKUP, true ) ); ?> → <?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_DROPOFF, true ) ); ?></p>
						<p>$<?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_PRICE, true ) ); ?> — <?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_DATE_WINDOW, true ) ); ?></p>
						<form method="post">
							<?php wp_nonce_field( 'gw_deal_action', 'gw_deal_nonce' ); ?>
							<input type="hidden" name="deal_id" value="<?php echo esc_attr( $deal->ID ); ?>">
							<button type="submit" name="gw_deal_action" value="claim" class="gw-btn gw-btn-fill">Claim This Job</button>
						</form>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h2>My Jobs</h2>
		<?php if ( ! $my_jobs ) : ?>
			<p>You haven't claimed a job yet.</p>
		<?php else : ?>
			<div class="gw-dashboard-list">
				<?php foreach ( $my_jobs as $deal ) : ?>
					<div class="gw-dashboard-card">
						<div class="gw-dashboard-card-title"><?php echo esc_html( $deal->post_title ); ?></div>
						<div class="gw-dashboard-card-status"><?php echo esc_html( $status_labels[ $deal->post_status ] ?? $deal->post_status ); ?></div>
						<p><a href="<?php echo esc_url( home_url( '/account/messages/' ) ); ?>">Message customer</a></p>
						<?php if ( 'gw_assigned' === $deal->post_status ) : ?>
							<form method="post">
								<?php wp_nonce_field( 'gw_deal_action', 'gw_deal_nonce' ); ?>
								<input type="hidden" name="deal_id" value="<?php echo esc_attr( $deal->ID ); ?>">
								<button type="submit" name="gw_deal_action" value="complete" class="gw-btn gw-btn-fill">Mark Completed</button>
							</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<h2>Want to drive?</h2>
		<p><a href="<?php echo esc_url( home_url( '/register-vendor/' ) ); ?>" class="gw-btn gw-btn-outline">Become a Driver</a></p>
	<?php endif; ?>
</section>
</div>

<?php
get_footer();
```

> **Note for implementer:** the `/account/messages/` and `/account/reviews/` links are placeholders for HivePress's actual Messages/Reviews URLs — confirm the real paths against the installed HivePress version (check its menu items while logged in, or its page-registration code) and correct these two `home_url()` calls if they differ. This is the same kind of "verify against the real plugin" step flagged in the spec.

- [ ] **Step 2: Add dashboard CSS**

Append to `wp-content/themes/gowonderlu-theme/style.css`:

```css
.gw-dashboard h2 {
	font-family: 'Instrument Serif', serif;
	font-style: italic;
	font-size: 24px;
	color: var(--gw-navy);
	margin: 36px 0 16px;
}

.gw-dashboard-list {
	display: flex;
	flex-direction: column;
	gap: 14px;
}

.gw-dashboard-card {
	background: var(--gw-white);
	border: 0.5px solid var(--gw-border);
	border-radius: 12px;
	padding: 20px;
}

.gw-dashboard-card-title {
	font-weight: 600;
	color: var(--gw-navy);
	margin-bottom: 4px;
}

.gw-dashboard-card-status {
	display: inline-block;
	font-size: 12.5px;
	color: var(--gw-slate);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin-bottom: 10px;
}

.gw-dashboard-card p {
	color: var(--gw-slate);
	font-size: 14.5px;
	margin: 0 0 8px;
}

.gw-dashboard-card form {
	margin-top: 10px;
}
```

- [ ] **Step 3: Lint and commit**

```bash
php -l wp-content/themes/gowonderlu-theme/dashboard.php
git add wp-content/themes/gowonderlu-theme/dashboard.php wp-content/themes/gowonderlu-theme/style.css
git commit -m "Add the customer/driver dashboard"
```

- [ ] **Step 4: Deploy and verify**

Files to upload: `wp-content/themes/gowonderlu-theme/dashboard.php` (new), `wp-content/themes/gowonderlu-theme/style.css`. In wp-admin, Pages → Add New → title "Dashboard" → Template → "Dashboard" → Publish. Visit `/dashboard/` while logged in as the customer from Task 3 — confirm "My Deals" shows the deal with status "Assigned" and the driver's name. Log in as the driver from Task 4 — confirm "My Jobs" shows the same deal with a "Mark Completed" button, and "Available Deals" is empty (since this deal is already Assigned, not Open).

---

### Task 6: Email notifications on status changes

**Files:**
- Modify: `wp-content/plugins/gowonderlu-deals.php`

**Interfaces:**
- Consumes: `GW_DEAL_META_DRIVER_ID` from Task 1; fires on the status transitions produced by Task 4.
- Produces: nothing consumed by later tasks — this is a leaf feature.

- [ ] **Step 1: Add the notification hook**

Append to `wp-content/plugins/gowonderlu-deals.php`:

```php
add_action( 'transition_post_status', 'gowonderlu_notify_on_deal_status_change', 10, 3 );

function gowonderlu_notify_on_deal_status_change( $new_status, $old_status, $post ) {
	if ( 'gw_deal' !== $post->post_type || $new_status === $old_status ) {
		return;
	}

	$customer = get_userdata( $post->post_author );

	if ( ! $customer ) {
		return;
	}

	if ( 'pending' === $old_status && 'publish' === $new_status ) {
		wp_mail(
			$customer->user_email,
			'Your GoWonderlu deal is now live',
			sprintf(
				"Hi %s,\n\nYour deal \"%s\" has been approved and is now visible to drivers.\n\nCheck your dashboard: %s",
				$customer->display_name,
				$post->post_title,
				home_url( '/dashboard/' )
			)
		);
	}

	if ( 'gw_assigned' === $new_status && 'gw_assigned' !== $old_status ) {
		$driver_id = (int) get_post_meta( $post->ID, GW_DEAL_META_DRIVER_ID, true );
		$driver    = $driver_id ? get_userdata( $driver_id ) : false;

		wp_mail(
			$customer->user_email,
			'A driver has been assigned to your deal',
			sprintf(
				"Hi %s,\n\n%s has been assigned to your deal \"%s\". Check your dashboard to coordinate.\n\n%s",
				$customer->display_name,
				$driver ? $driver->display_name : 'A driver',
				$post->post_title,
				home_url( '/dashboard/' )
			)
		);

		if ( $driver ) {
			wp_mail(
				$driver->user_email,
				'You have a new GoWonderlu job',
				sprintf(
					"Hi %s,\n\nYou've been assigned to a new job: \"%s\". Check your dashboard for details.\n\n%s",
					$driver->display_name,
					$post->post_title,
					home_url( '/dashboard/' )
				)
			);
		}
	}
}
```

- [ ] **Step 2: Lint and commit**

```bash
php -l wp-content/plugins/gowonderlu-deals.php
git add wp-content/plugins/gowonderlu-deals.php
git commit -m "Send plain email notifications on deal approval and assignment"
```

- [ ] **Step 3: Deploy and verify**

Files to upload: `wp-content/plugins/gowonderlu-deals.php`. Post a new test deal, approve it in wp-admin, and confirm the customer's email account (or Hostinger's mail log, if direct email delivery isn't confirmed working on this host) receives the "now live" email. Assign a driver and confirm both the customer and driver receive their respective emails.

---

### Task 7: Messaging integration

**Files:**
- Modify: `wp-content/themes/gowonderlu-theme/dashboard.php`

**Interfaces:**
- Consumes: the `/account/messages/` link already present from Task 5 (placeholder).
- Produces: nothing consumed by later tasks.

- [ ] **Step 1: Confirm HivePress's real Messages URL**

While logged in as any user, visit the account area and find the actual Messages link/URL HivePress provides (likely something under `/account/` rendered by the Messages extension). Note the exact path.

- [ ] **Step 2: Update the dashboard's message links to a deal-specific thread if supported**

If HivePress's Messages extension supports starting a conversation with a specific user via a URL parameter (check its templates under `wp-content/plugins/hivepress-messages/` for a pattern like `?user_id=` or a "Send Message" template tag/shortcode), update both message links in `wp-content/themes/gowonderlu-theme/dashboard.php` to pass the other party's user ID:

```php
<p>Driver: <?php echo esc_html( $driver->display_name ); ?> — <a href="<?php echo esc_url( home_url( '/account/messages/?user_id=' . $driver->ID ) ); ?>">Message</a></p>
```

and on the driver's "My Jobs" card:

```php
<?php $customer = get_userdata( (int) $deal->post_author ); ?>
<p><a href="<?php echo esc_url( home_url( '/account/messages/?user_id=' . $customer->ID ) ); ?>">Message customer</a></p>
```

If HivePress doesn't support a direct-to-user URL parameter, leave the link pointing at the general Messages inbox (already done in Task 5) — the user can find the right conversation manually. Don't build custom thread-routing logic to work around this; it's a minor UX gap, not worth new plumbing in this phase.

- [ ] **Step 2: Lint and commit**

```bash
php -l wp-content/themes/gowonderlu-theme/dashboard.php
git add wp-content/themes/gowonderlu-theme/dashboard.php
git commit -m "Link dashboard message buttons to the correct HivePress Messages URL"
```

- [ ] **Step 3: Deploy and verify**

Files to upload: `wp-content/themes/gowonderlu-theme/dashboard.php`. Click "Message" from a customer's dashboard for an assigned deal, and confirm it opens (or at minimum links to) a real, working HivePress Messages conversation with that driver — not a 404.

---

### Task 8: Reviews integration

**Files:**
- Modify: `wp-content/themes/gowonderlu-theme/dashboard.php`

**Interfaces:**
- Consumes: the `/account/reviews/` link already present from Task 5 (placeholder).
- Produces: nothing consumed by later tasks.

- [ ] **Step 1: Confirm HivePress's real review-submission URL for a Vendor**

While logged in, visit a driver's public Vendor profile page and find the actual "Leave a Review" link/form HivePress's Reviews extension renders there (likely on the vendor's own profile page, not a generic `/account/reviews/` path).

- [ ] **Step 2: Update the dashboard's review link to point at the driver's actual Vendor profile**

In `wp-content/themes/gowonderlu-theme/dashboard.php`, find:

```php
<?php if ( 'gw_completed' === $deal->post_status ) : ?>
	<p><a href="<?php echo esc_url( home_url( '/account/reviews/' ) ); ?>">Leave a Review</a></p>
<?php endif; ?>
```

Replace `home_url( '/account/reviews/' )` with a call to get the driver's Vendor profile URL (check HivePress's vendor model for the correct accessor — likely something like a `get_url()` method on the vendor object, or a `hivepress()->router->get_url( 'vendor_view_page', ... )` call). If a direct review-submission anchor/query param exists on that profile page, append it; otherwise linking to the profile page itself (where the review form lives) is sufficient.

- [ ] **Step 3: Lint and commit**

```bash
php -l wp-content/themes/gowonderlu-theme/dashboard.php
git add wp-content/themes/gowonderlu-theme/dashboard.php
git commit -m "Link dashboard review prompt to the driver's actual Vendor profile"
```

- [ ] **Step 4: Deploy and verify**

Files to upload: `wp-content/themes/gowonderlu-theme/dashboard.php`. Mark a deal Completed, click "Leave a Review" from the customer's dashboard, submit a review, and confirm it appears publicly on the driver's Vendor profile page.

---

### Task 9: Logged-in homepage CTA adaptation

**Files:**
- Modify: `wp-content/themes/gowonderlu-theme/front-page.php`

**Interfaces:**
- Consumes: `gowonderlu_user_is_driver()` from Task 4.
- Produces: nothing consumed by later tasks — leaf feature.

- [ ] **Step 1: Update the hero panel buttons to adapt for logged-in users**

In `wp-content/themes/gowonderlu-theme/front-page.php`, find the `.gw-panels` section:

```php
<section class="gw-panels">
	<div class="gw-panel">
		<span class="gw-eyebrow">For customers</span>
		<h2>Get it moved</h2>
		<p>Find a vetted local driver — no truck rental, no hassle.</p>
		<a href="<?php echo esc_url( home_url( '/account/login/?register=1' ) ); ?>" class="gw-btn gw-btn-fill">Get Started</a>
	</div>
	<div class="gw-panel">
		<span class="gw-eyebrow">For drivers</span>
		<h2>Earn on your schedule</h2>
		<p>Set up your profile and start picking up jobs near you.</p>
		<a href="<?php echo esc_url( home_url( '/register-vendor/' ) ); ?>" class="gw-btn gw-btn-outline">Become a Driver</a>
	</div>
</section>
```

Replace with:

```php
<section class="gw-panels">
	<div class="gw-panel">
		<span class="gw-eyebrow">For customers</span>
		<h2>Get it moved</h2>
		<p>Find a vetted local driver — no truck rental, no hassle.</p>
		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="gw-btn gw-btn-fill">Post a Deal</a>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/account/login/?register=1' ) ); ?>" class="gw-btn gw-btn-fill">Get Started</a>
		<?php endif; ?>
	</div>
	<div class="gw-panel">
		<span class="gw-eyebrow">For drivers</span>
		<h2>Earn on your schedule</h2>
		<p>Set up your profile and start picking up jobs near you.</p>
		<?php if ( is_user_logged_in() && gowonderlu_user_is_driver( get_current_user_id() ) ) : ?>
			<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="gw-btn gw-btn-outline">Go to Driver Dashboard</a>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/register-vendor/' ) ); ?>" class="gw-btn gw-btn-outline">Become a Driver</a>
		<?php endif; ?>
	</div>
</section>
```

- [ ] **Step 2: Lint and commit**

```bash
php -l wp-content/themes/gowonderlu-theme/front-page.php
git add wp-content/themes/gowonderlu-theme/front-page.php
git commit -m "Adapt homepage hero CTAs for logged-in customers and drivers"
```

- [ ] **Step 3: Deploy and verify**

Files to upload: `wp-content/themes/gowonderlu-theme/front-page.php`. Visit the homepage logged out — confirm "Get Started" and "Become a Driver" are unchanged. Log in as a customer-only account — confirm "Get Started" now reads "Post a Deal" and links to `/dashboard/`. Log in as a driver — confirm "Become a Driver" now reads "Go to Driver Dashboard."

---

### Task 10: Google Maps address autocomplete

**Files:**
- Modify: `wp-content/themes/gowonderlu-theme/functions.php`
- Modify: `wp-content/themes/gowonderlu-theme/post-a-deal.php`

**Interfaces:**
- Consumes: the `gw_deal_pickup`/`gw_deal_dropoff` input IDs from Task 3.
- Produces: nothing consumed by later tasks — leaf feature.

- [ ] **Step 1: Set up the Google Maps API key (wp-admin/external task, not code)**

In the Google Cloud Console, create a project, enable the "Places API," and generate an API key (restrict it to the `gowonderlu.com` domain for security). Enter it in WP Admin → HivePress → Settings → Integrations → Google Maps API Key. Save.

- [ ] **Step 2: Enqueue the Places library and wire up autocomplete**

Append to `wp-content/themes/gowonderlu-theme/functions.php`:

```php
add_action( 'wp_enqueue_scripts', 'gowonderlu_enqueue_places_autocomplete' );

function gowonderlu_enqueue_places_autocomplete() {
	if ( ! is_page_template( 'post-a-deal.php' ) ) {
		return;
	}

	$api_key = get_option( 'hp_geolocation_google_maps_api_key' );

	if ( ! $api_key ) {
		return;
	}

	wp_enqueue_script(
		'gowonderlu-places',
		'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places',
		array(),
		null,
		true
	);

	wp_add_inline_script(
		'gowonderlu-places',
		"window.addEventListener('load', function () {
			['gw_deal_pickup', 'gw_deal_dropoff'].forEach(function (id) {
				var input = document.getElementById(id);
				if (input && window.google) {
					new google.maps.places.Autocomplete(input, { componentRestrictions: { country: 'us' } });
				}
			});
		});"
	);
}
```

> **Note for implementer:** the option name `hp_geolocation_google_maps_api_key` is a best guess at HivePress's actual stored option key for this setting — confirm it against the installed HivePress Geolocation extension's settings-registration code (or just check the value directly via `var_dump( get_option( 'hp_geolocation_google_maps_api_key' ) )` temporarily after saving the key in Step 1) and correct the option name here if it differs.

- [ ] **Step 3: Lint and commit**

```bash
php -l wp-content/themes/gowonderlu-theme/functions.php
git add wp-content/themes/gowonderlu-theme/functions.php
git commit -m "Add Google Places autocomplete to the deal-posting address fields"
```

- [ ] **Step 4: Deploy and verify**

Files to upload: `wp-content/themes/gowonderlu-theme/functions.php`. Visit `/post-a-deal/`, start typing in the Pickup address field, and confirm Google's address suggestions appear.

---

### Task 11: End-to-end verification and `CLAUDE.md` update

**Files:**
- Modify: `/Users/kryskaka/codingproject2026/gowonderlu/CLAUDE.md`

**Interfaces:**
- Consumes: everything from Tasks 1–10.
- Produces: confirmation the full Phase 2 flow works, plus an updated project record.

- [ ] **Step 1: Walk through the spec's full verification list**

Run through all 10 checks from the spec's Testing/Verification section (registration flow, deal posting → pending → approval → open, driver claim, messaging, completion, review, cancellation, existing-customer-becomes-driver, admin assignment, address autocomplete). Fix any failures before proceeding — do not mark this task complete with known-broken checks.

- [ ] **Step 2: Update `CLAUDE.md`**

Add to **Current Phase Status**, after the Homepage & Site Shell entry:

```markdown
**Phase 2 (Deals Marketplace) — complete.** Customers post deals (pickup/dropoff,
date window, item description + photos, offer price, city) via a custom
`gw_deal` post type; admin reviews and approves; drivers self-claim Open
deals in their city or admin hand-assigns. Dashboard at `/dashboard/`
shows role-appropriate sections. Messaging and reviews reuse the
already-active HivePress Messages/Reviews extensions. Email notifications
on status changes (SMS comes in Phase 4). Payments are explicitly
deferred to Phase 3. See
`docs/superpowers/specs/2026-06-24-phase-2-deals-marketplace-design.md`
and `docs/superpowers/plans/2026-06-24-phase-2-deals-marketplace-implementation.md`.
```

Add to **Key Decisions**:

```markdown
| Custom `gw_deal` post type instead of HivePress's premium Requests extension | Full control, no added licensing cost, despite the extra build time — explicit decision after weighing both |
```

Update the **Tech Stack** table's Geolocation API row to reflect the key now being configured (remove the "Needed for Phase 2" blocker note, since Phase 2 is what configured it).

- [ ] **Step 3: Commit and push**

```bash
git add CLAUDE.md
git commit -m "Record Phase 2 deals marketplace completion"
git push
```

---

## Self-Review Notes

- **Spec coverage:** Account/role model + nav fix → Task 2 (and the model itself needed no new code, per the spec). Dashboard → Task 5. Deal data model + lifecycle → Tasks 1, 3, 4. Messaging/notifications/reviews → Tasks 6, 7, 8. Geolocation → Task 10. Testing/verification → Task 11. All spec sections have a task.
- **Placeholder scan:** No TBD/TODO. Three notes flag genuine third-party-plugin uncertainty (HivePress's exact vendor-update hook name, Messages URL parameter, Reviews submission URL, Geolocation option name) with a concrete primary approach plus an explicit verification step each — consistent with how the homepage plan handled the Footer Builder uncertainty, not a deferred decision.
- **Type/name consistency:** `GW_DEAL_META_*` constants and `gowonderlu_user_is_driver()`/`gowonderlu_claim_deal()`/`gowonderlu_complete_deal()`/`gowonderlu_cancel_deal()` function names are defined once in Tasks 1 and 4 and referenced identically in Tasks 3, 5, 6, and 9 — no mismatches.
