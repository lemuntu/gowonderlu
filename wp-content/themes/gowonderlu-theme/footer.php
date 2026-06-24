<?php
/**
 * Minimal footer override — Astra's free Footer Builder only offers a
 * Copyright element (no menu widget), so this replaces it entirely.
 *
 * Astra's header.php opens #content and .ast-container as wrappers around
 * the page content — they must be closed here before our footer markup,
 * or our <footer> ends up nested inside that flex container instead of
 * sitting below it as a sibling.
 */
?>
	</div><!-- .ast-container -->
</div><!-- #content -->

<footer class="gw-footer">
	<div class="gw-footer-top">
		<div class="gw-footer-brand">
			<div class="gw-footer-logo">gowonderlu</div>
			<p>A trustworthy way to move anything.</p>
		</div>
		<div class="gw-footer-subscribe">
			<span class="gw-eyebrow">Stay in the loop</span>
			<h3>Get updates as we expand to new cities.</h3>
			<!-- Newsletter signup is cosmetic for now — not yet wired to an email service. Connect in Phase 7. -->
			<form class="gw-subscribe-form" action="#" onsubmit="return false;">
				<input type="email" placeholder="you@example.com" aria-label="Email address" required>
				<button type="submit">Subscribe</button>
			</form>
		</div>
	</div>

	<div class="gw-footer-bottom">
		<div class="gw-footer-copy"><?php echo esc_html( '© ' . gmdate( 'Y' ) . ' GoWonderlu' ); ?></div>
		<nav class="gw-footer-nav">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
			<a href="<?php echo esc_url( home_url( '/#how-it-works' ) ); ?>">How It Works</a>
			<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms &amp; Conditions</a>
			<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
		</nav>
	</div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
