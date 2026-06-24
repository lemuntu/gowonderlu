<?php
/**
 * Template Name: About Page
 */
get_header();
?>

<div class="gw-page">

<section class="gw-hero">
	<span class="gw-eyebrow">Our story</span>
	<h1>Moving shouldn't require a moving company.</h1>
	<p>GoWonderlu connects people who need something hauled with independent local drivers who already have the truck, the time, and the willingness to help.</p>
</section>

<section class="gw-about-block">
	<div class="gw-about-row">
		<div class="gw-about-copy">
			<span class="gw-eyebrow">The problem</span>
			<h2>The old options were never built for a one-time job.</h2>
			<p>Renting a truck means a deposit, insurance paperwork, and driving something you've never driven for a job that takes twenty minutes once you're there. Calling a full moving company means a sales call, a multi-hour arrival window, and a bill sized for a four-bedroom move when you just need one couch gone.</p>
			<p>Neither was built for the person who needs a single item picked up, a couch dropped off, or an apartment cleared out by Sunday.</p>
		</div>
	</div>

	<div class="gw-about-row gw-about-row-reverse">
		<div class="gw-about-copy">
			<span class="gw-eyebrow">The idea</span>
			<h2>There's already a truck three streets over.</h2>
			<p>GoWonderlu is built on a simple bet: most cities already have more pickup trucks and cargo vans than they have moving companies. What's missing is a way to connect the person with the truck to the person with the couch — directly, without a dispatcher in between.</p>
			<p>Every driver on GoWonderlu is reviewed before they're allowed to take a job. Every job stays directly between the customer and the driver, start to finish.</p>
		</div>
	</div>
</section>

<section class="gw-about-where">
	<span class="gw-eyebrow">Where we operate</span>
	<h2>Starting in Texas, built to grow.</h2>
	<p class="gw-about-where-intro">GoWonderlu is launching first in three Texas metros, where we're building the driver network city by city before expanding further.</p>
	<div class="gw-about-cities">
		<div class="gw-about-city">
			<h3>Austin</h3>
			<p>Our home base, and the first city onboarding drivers.</p>
		</div>
		<div class="gw-about-city">
			<h3>Houston</h3>
			<p>Texas's largest metro, next in line as the driver network grows.</p>
		</div>
		<div class="gw-about-city">
			<h3>Dallas</h3>
			<p>Rounding out our initial Texas footprint.</p>
		</div>
	</div>
	<p class="gw-about-where-outro">From there, we'll expand to additional states as more drivers join the network — city by city, the same way we're starting here.</p>
</section>

<section class="gw-about-trust">
	<span class="gw-eyebrow">Trust &amp; safety</span>
	<h2>Every driver is reviewed before they're visible to customers.</h2>
	<p>New driver applications don't go live automatically. Each one is checked by our team before that driver can accept a job — and customer reviews stay attached to a driver's profile permanently, so the track record is always visible.</p>
</section>

<section class="gw-panels">
	<div class="gw-panel">
		<span class="gw-eyebrow">For customers</span>
		<h2>Get it moved</h2>
		<p>Find a vetted local driver — no truck rental, no hassle.</p>
		<a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="gw-btn gw-btn-fill">Get Started</a>
	</div>
	<div class="gw-panel">
		<span class="gw-eyebrow">For drivers</span>
		<h2>Earn on your schedule</h2>
		<p>Set up your profile and start picking up jobs near you.</p>
		<a href="<?php echo esc_url( home_url( '/register-vendor/' ) ); ?>" class="gw-btn gw-btn-outline">Become a Driver</a>
	</div>
</section>

</div>

<?php
get_footer();
