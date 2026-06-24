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
