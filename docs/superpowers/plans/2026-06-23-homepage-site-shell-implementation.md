# Homepage & Site Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace Astra's demo homepage/nav/footer with a real GoWonderlu homepage (split hero, How It Works, testimonials), a configured nav and footer, and the About/Terms/Privacy pages they link to.

**Architecture:** One new theme file (`front-page.php`) holds the homepage content; `style.css` and `functions.php` get additive changes (no rewrites of Phase 0/1 code). Nav, footer, logo, and the new pages are wp-admin configuration — no code beyond the theme files. Visual design follows a navy/amber industrial-logistics direction with a diagonal amber "speed line" as the signature element (a direct extension of `brand/logo-mark.svg`'s angled-bar motion cue), confirmed by rendering a static HTML prototype before writing this plan.

**Tech Stack:** PHP (WordPress theme template), CSS (custom properties, CSS Grid), Google Fonts (Big Shoulders Display, Inter, IBM Plex Mono) loaded via `wp_enqueue_style`.

## Global Constraints

- No AI attribution anywhere: no "Co-Authored-By" lines, no AI mentions in commit messages, comments, or code.
- Brand palette: navy `#0B1F3A`, amber `#F5A623` — fixed. New supporting neutrals introduced in this plan: white `#FFFFFF`, mist `#F7F8FA`, ink `#1A1A1A`, slate `#5B6472`.
- Out of scope: any deal-posting/marketplace functionality, real legal copy, real testimonials, Stripe/payments.
- Custom CSS classes are prefixed `gw-` to avoid colliding with Astra/HivePress classes — never write CSS selectors against guessed Astra/HivePress internal class names (the Phase 1 lesson: verify against real rendered markup, or use the Customizer, don't guess).
- Placeholder legal/about copy must be visibly flagged as placeholder in the page content itself.
- Deploy is git push to GitHub (history only) + manual upload via Hostinger File Manager or WP Admin's theme upload. **Never** connect Hostinger's GitHub auto-deploy tool to this repo.

---

## File Structure

```
gowonderlu/
└── wp-content/
    └── themes/
        └── gowonderlu-theme/
            ├── style.css         ← modify: append homepage CSS
            ├── functions.php     ← modify: add font enqueue + SVG upload support
            └── front-page.php    ← new: homepage content template
```

---

### Task 1: Homepage template, styles, and supporting functions

**Files:**
- Create: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/front-page.php`
- Modify: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/style.css`
- Modify: `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/functions.php`

**Interfaces:**
- Consumes: HivePress routes confirmed working in Phase 1 (`/register/`, `/register-vendor/`, `/account/login/`), the `#how-it-works` anchor this template defines.
- Produces: the `gw-` prefixed CSS classes and the `#how-it-works` anchor ID, which Task 3's nav menu link depends on.

- [ ] **Step 1: Append the homepage CSS to `style.css`**

Add this block at the end of the existing file (after the theme header comment — don't remove or modify the existing header):

```css
:root {
	--gw-navy: #0B1F3A;
	--gw-amber: #F5A623;
	--gw-white: #FFFFFF;
	--gw-mist: #F7F8FA;
	--gw-ink: #1A1A1A;
	--gw-slate: #5B6472;
}

.gw-hero {
	position: relative;
	background: var(--gw-navy);
	overflow: hidden;
}

.gw-hero-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	position: relative;
}

.gw-hero-grid::before {
	content: '';
	position: absolute;
	top: 0;
	bottom: 0;
	left: 50%;
	width: 6px;
	background: var(--gw-amber);
	transform: skewX(-8deg) translateX(-50%);
}

.gw-hero-panel {
	padding: 88px 64px;
	color: var(--gw-white);
}

.gw-hero-panel.gw-customers {
	padding-right: 80px;
}

.gw-hero-panel.gw-drivers {
	padding-left: 80px;
}

.gw-eyebrow {
	font-family: 'IBM Plex Mono', monospace;
	font-size: 13px;
	letter-spacing: 2px;
	text-transform: uppercase;
	color: var(--gw-amber);
	font-weight: 500;
	display: block;
	margin-bottom: 10px;
}

.gw-hero-panel h1 {
	font-family: 'Big Shoulders Display', sans-serif;
	font-weight: 800;
	font-size: 42px;
	line-height: 1.05;
	margin: 0 0 18px;
}

.gw-hero-panel p {
	color: #cfd6e4;
	font-size: 16px;
	line-height: 1.6;
	max-width: 380px;
	margin: 0 0 28px;
}

.gw-btn {
	display: inline-block;
	font-family: 'Inter', sans-serif;
	font-weight: 600;
	font-size: 15px;
	padding: 14px 30px;
	border-radius: 3px;
	text-decoration: none;
}

.gw-btn-fill {
	background: var(--gw-amber);
	color: var(--gw-navy);
}

.gw-btn-outline {
	background: transparent;
	border: 2px solid var(--gw-amber);
	color: var(--gw-amber);
}

.gw-btn:focus-visible {
	outline: 3px solid var(--gw-white);
	outline-offset: 2px;
}

.gw-how {
	background: var(--gw-mist);
	padding: 90px 48px;
	text-align: center;
}

.gw-how h2,
.gw-testimonials h2 {
	font-family: 'Big Shoulders Display', sans-serif;
	font-weight: 800;
	font-size: 32px;
	color: var(--gw-navy);
	margin: 0 0 48px;
}

.gw-steps {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 40px;
	max-width: 920px;
	margin: 0 auto;
	text-align: left;
}

.gw-step-number {
	font-family: 'IBM Plex Mono', monospace;
	font-size: 13px;
	letter-spacing: 1px;
	color: var(--gw-amber);
}

.gw-step h3 {
	font-family: 'Big Shoulders Display', sans-serif;
	font-weight: 800;
	font-size: 22px;
	color: var(--gw-navy);
	margin: 8px 0 10px;
}

.gw-step p {
	color: var(--gw-slate);
	font-size: 15px;
	line-height: 1.55;
	margin: 0;
}

.gw-testimonials {
	padding: 90px 48px;
	background: var(--gw-white);
	text-align: center;
}

.gw-quotes {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 28px;
	max-width: 1040px;
	margin: 0 auto;
	text-align: left;
}

.gw-quote {
	background: var(--gw-mist);
	padding: 28px;
	border-radius: 4px;
	position: relative;
}

.gw-quote::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	width: 34px;
	height: 4px;
	background: var(--gw-amber);
	transform: skewX(-20deg);
}

.gw-quote p {
	font-size: 15px;
	line-height: 1.6;
	color: var(--gw-ink);
	margin: 0 0 16px;
}

.gw-quote .gw-attr {
	font-family: 'IBM Plex Mono', monospace;
	font-size: 12px;
	letter-spacing: .5px;
	color: var(--gw-slate);
}

.gw-signup-btn a {
	background: var(--gw-amber) !important;
	color: var(--gw-navy) !important;
	padding: 8px 18px !important;
	border-radius: 3px !important;
	font-weight: 600 !important;
}

@media (max-width: 768px) {
	.gw-hero-grid {
		grid-template-columns: 1fr;
	}

	.gw-hero-grid::before {
		display: none;
	}

	.gw-hero-panel.gw-customers,
	.gw-hero-panel.gw-drivers {
		padding: 48px 28px;
	}

	.gw-steps,
	.gw-quotes {
		grid-template-columns: 1fr;
	}
}
```

- [ ] **Step 2: Update `functions.php`**

Replace the existing `gowonderlu_enqueue_styles` function with this version (adds the Google Fonts enqueue as a dependency of the child stylesheet), and add the SVG upload filter below it:

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
		'gowonderlu-fonts',
		'https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'gowonderlu-child-style',
		get_stylesheet_uri(),
		array( 'astra-parent-style', 'gowonderlu-fonts' ),
		wp_get_theme()->get( 'Version' )
	);
}

add_filter( 'upload_mimes', 'gowonderlu_allow_svg_upload' );

function gowonderlu_allow_svg_upload( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}
```

- [ ] **Step 3: Create `front-page.php`**

```php
<?php
get_header();
?>

<section class="gw-hero">
	<div class="gw-hero-grid">
		<div class="gw-hero-panel gw-customers">
			<span class="gw-eyebrow">For customers</span>
			<h1>Need something<br>hauled?</h1>
			<p>Find a vetted local driver — no truck rental, no hassle.</p>
			<a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="gw-btn gw-btn-fill">Get Started</a>
		</div>
		<div class="gw-hero-panel gw-drivers">
			<span class="gw-eyebrow">For drivers</span>
			<h1>Have a truck?<br>Earn on your schedule.</h1>
			<p>Set up your profile and start picking up jobs near you.</p>
			<a href="<?php echo esc_url( home_url( '/register-vendor/' ) ); ?>" class="gw-btn gw-btn-outline">Become a Driver</a>
		</div>
	</div>
</section>

<section class="gw-how" id="how-it-works">
	<span class="gw-eyebrow">How it works</span>
	<h2>Three steps, start to finish</h2>
	<div class="gw-steps">
		<div class="gw-step">
			<div class="gw-step-number">STEP 01</div>
			<h3>Sign up</h3>
			<p>Create a customer or driver account in under a minute.</p>
		</div>
		<div class="gw-step">
			<div class="gw-step-number">STEP 02</div>
			<h3>Get matched</h3>
			<p>Browse vetted drivers in your area.</p>
		</div>
		<div class="gw-step">
			<div class="gw-step-number">STEP 03</div>
			<h3>Get it done</h3>
			<p>Coordinate directly and get your move handled.</p>
		</div>
	</div>
</section>

<section class="gw-testimonials">
	<span class="gw-eyebrow">What people are saying</span>
	<h2>Trusted by the neighborhood</h2>
	<!-- placeholder testimonials — replace with real ones -->
	<div class="gw-quotes">
		<div class="gw-quote">
			<p>&ldquo;Booked a driver for a same-day couch pickup. Easier than renting a van myself.&rdquo;</p>
			<div class="gw-attr">&mdash; Jane D., Boston</div>
		</div>
		<div class="gw-quote">
			<p>&ldquo;I drive on weekends between my regular job. GoWonderlu fills the gaps nicely.&rdquo;</p>
			<div class="gw-attr">&mdash; Marcus T., Cambridge</div>
		</div>
		<div class="gw-quote">
			<p>&ldquo;Straightforward signup, clear communication with the driver the whole way.&rdquo;</p>
			<div class="gw-attr">&mdash; Priya K., Somerville</div>
		</div>
	</div>
</section>

<?php
get_footer();
```

- [ ] **Step 4: Lint the PHP files**

```bash
php -l /Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/functions.php
php -l /Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/front-page.php
```

Expected: `No syntax errors detected` for both.

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/gowonderlu-theme/style.css wp-content/themes/gowonderlu-theme/functions.php wp-content/themes/gowonderlu-theme/front-page.php
git commit -m "Add homepage template, brand fonts, and SVG upload support"
```

---

### Task 2: Deploy and verify the homepage renders

**Files:** none (live-site deployment + manual verification only).

**Interfaces:**
- Consumes: `style.css`, `functions.php`, `front-page.php` from Task 1.
- Produces: a live, rendering homepage at `gowonderlu.com`. Tasks 3–5 depend on this being correct before configuring nav/footer/logo around it.

- [ ] **Step 1: Upload the three files**

Zip the `gowonderlu-theme` folder (same pattern as Phase 0) and re-upload via WP Admin → Appearance → Themes → Add New → Upload Theme (overwrites the existing theme), or copy the three changed files individually via Hostinger File Manager into `wp-content/themes/gowonderlu-theme/`.

- [ ] **Step 2: Verify it renders**

Visit `gowonderlu.com` logged out. Confirm:
- The navy split hero shows both panels with the diagonal amber divider between them.
- Big Shoulders Display renders for headlines (bold, condensed) — not a generic system font fallback.
- The "How It Works" section (light gray-blue background) shows three steps with amber "STEP 01/02/03" mono labels.
- The testimonials section shows three quote cards with the amber accent tick.

If fonts look like a system fallback (e.g. Arial) instead of the intended display/mono faces, check the browser's network tab for the Google Fonts request — common cause is a typo in the font family name or the `wp_enqueue_style` URL.

- [ ] **Step 3: Verify the links**

Click "Get Started" → lands on `/register/`. Click "Become a Driver" → redirects through login/register if logged out, or straight to the vendor profile form if already logged in as a vendor (reusing Phase 1's confirmed flow). Click into the page and confirm `#how-it-works` is a valid anchor (visiting `gowonderlu.com/#how-it-works` directly should scroll to that section).

---

### Task 3: Site logo and navigation menu

**Files:** none (wp-admin configuration only).

**Interfaces:**
- Consumes: `brand/logo-full.svg` (from Phase 0), the `upload_mimes` filter from Task 1 (required for the SVG upload to be accepted), HivePress routes (`/register/`, `/account/login/`).
- Produces: a configured site logo and primary nav menu. Task 6's verification checks both.

- [ ] **Step 1: Upload the site logo**

WP Admin → Appearance → Customize → Site Identity → Logo → upload `brand/logo-full.svg` (the `upload_mimes` filter from Task 1 must be live first, or this upload will be rejected as an unsupported file type). Publish.

- [ ] **Step 2: Build the nav menu**

WP Admin → Appearance → Menus. Create or edit the primary menu with these items, in order:
- **Home** → link to the site's front page URL
- **About** → will link to the About page once Task 5 creates it (add this item after Task 5, or add a placeholder `#` link now and fix it after)
- **How It Works** → a Custom Link to `/#how-it-works`
- **Sign In** → a Custom Link to `/account/login/`
- **Sign Up** → a Custom Link to `/register/`

- [ ] **Step 3: Style the Sign Up item as a button**

In the menu editor, click Screen Options (top right) and enable **CSS Classes**. Expand the "Sign Up" menu item and add `gw-signup-btn` in its CSS Classes field. Save the menu — this matches the `.gw-signup-btn a` rule already in `style.css` from Task 1, so no new CSS is needed.

- [ ] **Step 4: Verify**

Reload the live homepage. Confirm the nav shows Home/About/How It Works/Sign In/Sign Up, the logo image (not "ASTRA" text) appears on the left, and Sign Up renders as an amber filled button distinct from the plain text links.

---

### Task 4: Footer

**Files:** none (wp-admin configuration only).

**Interfaces:**
- Consumes: nothing.
- Produces: a site-wide footer with links and a copyright line.

- [ ] **Step 1: Try Astra's Footer Builder**

WP Admin → Appearance → Customize → Footer Builder. Add a row with: a copyright/text widget ("© 2026 GoWonderlu") and a custom-menu widget pointing at a small footer menu (create one at Appearance → Menus with Home/About/How It Works/Terms & Conditions/Privacy Policy — the last two will 404 until Task 5 creates those pages, that's expected for now).

- [ ] **Step 2: If Footer Builder isn't available on this Astra version (fallback)**

Create `/Users/kryskaka/codingproject2026/gowonderlu/wp-content/themes/gowonderlu-theme/footer.php`:

```php
<?php
/**
 * Minimal footer override — only used if Astra's free Footer Builder
 * can't produce the footer described in this plan.
 */
?>
<footer class="gw-footer">
	<div><?php echo esc_html( '© ' . date( 'Y' ) . ' GoWonderlu' ); ?></div>
	<nav>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
		<a href="<?php echo esc_url( home_url( '/#how-it-works' ) ); ?>">How It Works</a>
		<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a>
		<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a>
	</nav>
</footer>
<?php wp_footer(); ?>
</body>
</html>
```

Add this CSS to `style.css` only if this fallback file is used:

```css
.gw-footer {
	background: var(--gw-navy);
	color: #9aa6bb;
	padding: 40px 48px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 13px;
}

.gw-footer nav a {
	color: #cfd6e4;
	text-decoration: none;
	font-family: 'IBM Plex Mono', monospace;
	font-size: 12px;
	letter-spacing: .5px;
	margin-left: 22px;
	text-transform: uppercase;
}
```

Lint and deploy this file the same way as Task 1/2 if it's needed.

- [ ] **Step 3: Verify**

Reload the live homepage and scroll to the bottom. Confirm the footer shows the configured links and copyright line, on every page (not just the homepage).

---

### Task 5: About, Terms & Conditions, and Privacy Policy pages

**Files:** none (wp-admin page content only).

**Interfaces:**
- Consumes: nothing.
- Produces: three live pages the nav/footer from Tasks 3–4 link to.

- [ ] **Step 1: Create the About page**

WP Admin → Pages → Add New. Title: `About`. Content (paste as plain paragraphs, or as a single paragraph block):

```
GoWonderlu connects people who need something hauled — a couch, a move-out, a single bulky item — with independent local drivers who have the truck and the time.

We started GoWonderlu because renting a truck and doing it yourself shouldn't be the only option, and a big moving company with a call center shouldn't be the only alternative. Every driver on GoWonderlu is reviewed before they're allowed to take a job, and every job stays directly between you and your driver.

We're currently focused on the Greater Boston area, with more cities to come.

[Placeholder — replace with your own story before launch.]
```

Publish.

- [ ] **Step 2: Create the Terms & Conditions page**

WP Admin → Pages → Add New. Title: `Terms & Conditions`. Content:

```
Placeholder — this page is a structural placeholder, not reviewed legal text. Replace with attorney-reviewed terms before accepting real payments or onboarding real drivers and customers.

1. Acceptance of Terms
By creating an account on GoWonderlu, you agree to these terms.

2. Description of Service
GoWonderlu is a platform that connects customers who need items hauled or moved with independent drivers. GoWonderlu does not employ drivers and is not a party to any agreement between a customer and a driver.

3. Accounts
You must provide accurate information when registering. Driver accounts are subject to manual review and approval before becoming visible to customers.

4. Conduct
Users agree not to misuse the platform, misrepresent themselves, or engage in unlawful activity through GoWonderlu.

5. Payments
Payment processing is not yet active on GoWonderlu. This section will be updated once payments launch.

6. Limitation of Liability
GoWonderlu is not liable for the conduct of any user, the condition of any item moved, or any disputes between customers and drivers.

7. Changes to These Terms
These terms may change as GoWonderlu's features change. Continued use of the platform after a change constitutes acceptance.

8. Contact
Questions about these terms can be sent to the contact address listed on this site.
```

Publish.

- [ ] **Step 3: Update the existing Privacy Policy draft**

WP Admin → Pages → find the existing "Privacy Policy" draft page → Edit. Content:

```
Placeholder — this page is a structural placeholder, not reviewed legal text. Replace before accepting real payments or processing real personal data at scale.

What We Collect
Account information (name, email, phone number), profile information you provide (including driver vehicle details), and basic usage data.

How We Use It
To operate your account, connect customers with drivers, and improve GoWonderlu. We do not sell your personal information.

Third-Party Services
GoWonderlu uses WordPress and HivePress to operate the platform. Future phases will add payment processing (Stripe) and messaging/notification services, which will be reflected here once active.

Your Rights
You can update or delete your account information at any time from your account settings, or by contacting us.

Contact
Questions about this policy can be sent to the contact address listed on this site.
```

Publish (change status from Draft to Published).

- [ ] **Step 4: Finish Task 3's "About" nav item and the footer menu**

Go back to Appearance → Menus and point the "About" item (left as a placeholder `#` link in Task 3) at the new `/about/` page. Confirm the footer menu's Terms & Conditions and Privacy Policy links (from Task 4) now resolve instead of 404ing.

---

### Task 6: End-to-end verification and `CLAUDE.md` update

**Files:**
- Modify: `/Users/kryskaka/codingproject2026/gowonderlu/CLAUDE.md`

**Interfaces:**
- Consumes: everything from Tasks 1–5.
- Produces: confirmation the full site shell works, matching the spec's "Testing / Verification" section, plus an updated project record.

- [ ] **Step 1: Walk through the spec's verification list**

1. Visit the homepage logged out — hero, How It Works, testimonials all render with the brand palette and fonts.
2. Click "Get Started" → `/register/`. Click "Become a Driver" → login/register → vendor profile form.
3. Click "How It Works" in the nav → scrolls to the homepage section.
4. Click Home, About, Terms & Conditions, Privacy Policy in nav/footer → each loads real (placeholder, where applicable) content, not a 404.
5. Confirm the site logo (from `brand/logo-full.svg`) appears in the header.
6. Resize the browser to a mobile width (or use devtools' responsive mode) — confirm the hero stacks to one column and the diagonal divider disappears (per the `@media (max-width: 768px)` rule in Task 1).

- [ ] **Step 2: Update `CLAUDE.md`**

Add a short note under **Current Phase Status** (after the Phase 1 entry) recording that the homepage/site shell is live, and add a row to **Key Decisions** for the typography/signature-element choice:

```markdown
**Homepage & Site Shell — complete.** Real homepage (split hero, How It
Works, testimonials) replaces Astra's demo content; nav/footer configured;
About/Terms/Privacy pages live with clearly-flagged placeholder copy. See
`docs/superpowers/specs/2026-06-23-homepage-site-shell-design.md` and
`docs/superpowers/plans/2026-06-23-homepage-site-shell-implementation.md`.
```

Add to the Key Decisions table:

```markdown
| Big Shoulders Display + Inter + IBM Plex Mono, diagonal amber "speed line" as signature element | Distinct from generic AI-design defaults (cream/serif, dark/neon, broadsheet); the speed line directly extends `brand/logo-mark.svg`'s own angled-bar motion cue rather than an arbitrary decoration |
```

- [ ] **Step 3: Commit and push**

```bash
git add CLAUDE.md
git commit -m "Record homepage and site shell completion"
git push
```

---

## Self-Review Notes

- **Spec coverage:** Homepage (hero/how-it-works/testimonials) → Tasks 1–2. Site Navigation + logo → Task 3. Footer → Task 4. New Pages → Task 5. Testing/Verification → Task 6. All spec sections have a task.
- **Placeholder scan:** No TBD/TODO. All "placeholder" content (testimonials, About, Terms, Privacy) is real, complete copy explicitly flagged as placeholder per the spec's own requirement — not a deferred decision.
- **Type/name consistency:** `gw-` prefixed classes (`.gw-hero`, `.gw-eyebrow`, `.gw-btn`, `.gw-how`, `.gw-step`, `.gw-testimonials`, `.gw-quote`, `.gw-signup-btn`, `.gw-footer`) are defined once in Task 1 (or Task 4's fallback) and referenced identically in `front-page.php` and the menu configuration — no mismatches.
