<?php
get_header();
?>

<div class="gw-page">

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

</div>

<?php
get_footer();
