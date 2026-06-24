<?php
/**
 * Minimal footer override — Astra's free Footer Builder only offers a
 * Copyright element (no menu widget), so this replaces it entirely.
 */
?>
<footer class="gw-footer">
	<div class="gw-footer-copy"><?php echo esc_html( '© ' . gmdate( 'Y' ) . ' GoWonderlu' ); ?></div>
	<nav class="gw-footer-nav">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>
		<a href="<?php echo esc_url( home_url( '/#how-it-works' ) ); ?>">How It Works</a>
		<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms &amp; Conditions</a>
		<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy Policy</a>
	</nav>
</footer>
<?php wp_footer(); ?>
</body>
</html>
