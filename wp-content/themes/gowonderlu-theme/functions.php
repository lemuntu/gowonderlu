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
