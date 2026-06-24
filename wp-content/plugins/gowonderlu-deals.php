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

add_action( 'template_redirect', 'gowonderlu_handle_deal_actions' );

function gowonderlu_handle_deal_actions() {
	if ( empty( $_POST['gw_deal_action'] ) || empty( $_POST['deal_id'] ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	check_admin_referer( 'gw_deal_action', 'gw_deal_nonce' );

	$deal_id = absint( $_POST['deal_id'] );
	$action  = sanitize_key( $_POST['gw_deal_action'] );
	$user_id = get_current_user_id();

	if ( 'claim' === $action ) {
		gowonderlu_claim_deal( $deal_id, $user_id );
	} elseif ( 'complete' === $action ) {
		gowonderlu_complete_deal( $deal_id, $user_id );
	} elseif ( 'cancel' === $action ) {
		gowonderlu_cancel_deal( $deal_id, $user_id );
	}

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url( '/dashboard/' ) );
	exit;
}

function gowonderlu_claim_deal( $deal_id, $driver_user_id ) {
	$deal = get_post( $deal_id );

	if ( ! $deal || 'gw_deal' !== $deal->post_type || 'publish' !== $deal->post_status ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'          => $deal_id,
			'post_status' => 'gw_assigned',
		)
	);

	update_post_meta( $deal_id, GW_DEAL_META_DRIVER_ID, $driver_user_id );

	return true;
}

function gowonderlu_complete_deal( $deal_id, $user_id ) {
	$deal      = get_post( $deal_id );
	$driver_id = (int) get_post_meta( $deal_id, GW_DEAL_META_DRIVER_ID, true );

	if ( ! $deal || 'gw_deal' !== $deal->post_type || 'gw_assigned' !== $deal->post_status ) {
		return false;
	}

	if ( $driver_id !== (int) $user_id ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'          => $deal_id,
			'post_status' => 'gw_completed',
		)
	);

	return true;
}

function gowonderlu_cancel_deal( $deal_id, $user_id ) {
	$deal      = get_post( $deal_id );
	$driver_id = (int) get_post_meta( $deal_id, GW_DEAL_META_DRIVER_ID, true );

	if ( ! $deal || 'gw_deal' !== $deal->post_type ) {
		return false;
	}

	$is_owner  = (int) $deal->post_author === (int) $user_id;
	$is_driver = $driver_id === (int) $user_id;

	if ( ! $is_owner && ! $is_driver ) {
		return false;
	}

	if ( ! in_array( $deal->post_status, array( 'pending', 'publish', 'gw_assigned' ), true ) ) {
		return false;
	}

	wp_update_post(
		array(
			'ID'          => $deal_id,
			'post_status' => 'gw_cancelled',
		)
	);

	return true;
}

function gowonderlu_user_is_driver( $user_id ) {
	return (bool) get_user_meta( $user_id, '_gw_driver_city', true );
}

add_action( 'add_meta_boxes', 'gowonderlu_add_deal_assign_meta_box' );

function gowonderlu_add_deal_assign_meta_box() {
	add_meta_box(
		'gowonderlu_deal_assign',
		'Assign Driver',
		'gowonderlu_render_deal_assign_meta_box',
		'gw_deal',
		'side'
	);
}

function gowonderlu_render_deal_assign_meta_box( $post ) {
	if ( ! in_array( $post->post_status, array( 'publish', 'gw_assigned' ), true ) ) {
		echo '<p>Deal must be Open (Published) before a driver can be assigned.</p>';
		return;
	}

	$drivers = get_users(
		array(
			'meta_key'     => '_gw_driver_city',
			'meta_compare' => 'EXISTS',
		)
	);

	$current_driver_id = (int) get_post_meta( $post->ID, GW_DEAL_META_DRIVER_ID, true );

	wp_nonce_field( 'gowonderlu_assign_driver', 'gowonderlu_assign_driver_nonce' );

	echo '<select name="gowonderlu_assign_driver_id">';
	echo '<option value="">— Select a driver —</option>';

	foreach ( $drivers as $driver ) {
		printf(
			'<option value="%d" %s>%s</option>',
			$driver->ID,
			selected( $current_driver_id, $driver->ID, false ),
			esc_html( $driver->display_name )
		);
	}

	echo '</select>';
}

add_action( 'save_post_gw_deal', 'gowonderlu_save_deal_assign_meta_box' );

function gowonderlu_save_deal_assign_meta_box( $post_id ) {
	if ( ! isset( $_POST['gowonderlu_assign_driver_nonce'] ) || ! wp_verify_nonce( $_POST['gowonderlu_assign_driver_nonce'], 'gowonderlu_assign_driver' ) ) {
		return;
	}

	if ( empty( $_POST['gowonderlu_assign_driver_id'] ) ) {
		return;
	}

	update_post_meta( $post_id, GW_DEAL_META_DRIVER_ID, absint( $_POST['gowonderlu_assign_driver_id'] ) );

	remove_action( 'save_post_gw_deal', 'gowonderlu_save_deal_assign_meta_box' );
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_status' => 'gw_assigned',
		)
	);
	add_action( 'save_post_gw_deal', 'gowonderlu_save_deal_assign_meta_box' );
}

add_action( 'transition_post_status', 'gowonderlu_notify_on_deal_status_change', 10, 3 );

function gowonderlu_notify_on_deal_status_change( $new_status, $old_status, $post ) {
	if ( 'gw_deal' !== $post->post_type || $new_status === $old_status ) {
		return;
	}

	$customer = get_userdata( $post->post_author );

	if ( ! $customer ) {
		return;
	}

	if ( 'pending' === $old_status && 'publish' === $new_status ) {
		wp_mail(
			$customer->user_email,
			'Your GoWonderlu deal is now live',
			sprintf(
				"Hi %s,\n\nYour deal \"%s\" has been approved and is now visible to drivers.\n\nCheck your dashboard: %s",
				$customer->display_name,
				$post->post_title,
				home_url( '/dashboard/' )
			)
		);
	}

	if ( 'gw_assigned' === $new_status && 'gw_assigned' !== $old_status ) {
		$driver_id = (int) get_post_meta( $post->ID, GW_DEAL_META_DRIVER_ID, true );
		$driver    = $driver_id ? get_userdata( $driver_id ) : false;

		wp_mail(
			$customer->user_email,
			'A driver has been assigned to your deal',
			sprintf(
				"Hi %s,\n\n%s has been assigned to your deal \"%s\". Check your dashboard to coordinate.\n\n%s",
				$customer->display_name,
				$driver ? $driver->display_name : 'A driver',
				$post->post_title,
				home_url( '/dashboard/' )
			)
		);

		if ( $driver ) {
			wp_mail(
				$driver->user_email,
				'You have a new GoWonderlu job',
				sprintf(
					"Hi %s,\n\nYou've been assigned to a new job: \"%s\". Check your dashboard for details.\n\n%s",
					$driver->display_name,
					$post->post_title,
					home_url( '/dashboard/' )
				)
			);
		}
	}
}
