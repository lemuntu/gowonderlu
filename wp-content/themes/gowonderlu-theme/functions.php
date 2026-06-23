<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'gowonderlu_enqueue_styles' );

function gowonderlu_enqueue_styles() {
	wp_enqueue_style(
		'astra-parent-style',
		get_template_directory_uri() . '/style.css'
	);

	wp_enqueue_style(
		'gowonderlu-child-style',
		get_stylesheet_uri(),
		array( 'astra-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
