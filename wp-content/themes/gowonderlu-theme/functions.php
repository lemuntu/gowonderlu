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
		'https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@400;500;600&display=swap',
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

