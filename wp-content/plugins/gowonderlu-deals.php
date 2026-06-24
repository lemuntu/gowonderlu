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
