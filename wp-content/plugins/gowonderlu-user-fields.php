<?php
/**
 * Plugin Name: GoWonderlu User Fields
 * Description: Adds a required phone number field to HivePress registration and account settings.
 * Version: 0.1.0
 */

defined( 'ABSPATH' ) || exit;

function gowonderlu_phone_field_args() {
	return [
		'label'    => 'Phone Number',
		'type'     => 'text',
		'required' => true,
		'_order'   => 100,
	];
}

add_filter(
	'hivepress/v1/forms/user_register',
	function ( $form ) {
		$form['fields']['phone_number'] = gowonderlu_phone_field_args();

		return $form;
	},
	100
);

add_filter(
	'hivepress/v1/forms/user_update',
	function ( $form ) {
		$form['fields']['phone_number'] = array_merge(
			gowonderlu_phone_field_args(),
			[ 'default' => get_user_meta( get_current_user_id(), 'phone_number', true ) ]
		);

		return $form;
	},
	100
);

function gowonderlu_save_phone_number( $user_id, $values ) {
	if ( isset( $values['phone_number'] ) ) {
		update_user_meta( $user_id, 'phone_number', $values['phone_number'] );
	}
}

add_action( 'hivepress/v1/models/user/register', 'gowonderlu_save_phone_number', 10, 2 );
add_action( 'hivepress/v1/models/user/update', 'gowonderlu_save_phone_number', 10, 2 );

add_filter(
	'hivepress/v1/forms/vendor_update',
	function ( $form ) {
		$cities = get_terms(
			array(
				'taxonomy'   => 'gw_deal_city',
				'hide_empty' => false,
			)
		);

		$options = array();

		foreach ( $cities as $city ) {
			$options[ $city->term_id ] = $city->name;
		}

		$form['fields']['gw_driver_city'] = array(
			'label'    => 'City',
			'type'     => 'select',
			'options'  => $options,
			'required' => true,
			'default'  => get_user_meta( get_current_user_id(), '_gw_driver_city', true ),
			'_order'   => 90,
		);

		return $form;
	},
	100
);

/**
 * Saves the driver city field on vendor profile update.
 *
 * NOTE: Phase 1's proven pattern (gowonderlu_save_phone_number, above) saves
 * user meta via update_user_meta( $user_id, ... ) keyed off the hook's own
 * $user_id argument, using the hivepress/v1/models/user/update action. The
 * vendor-side equivalent of that action and its exact model API
 * (e.g. get_user__id()) are unconfirmed against the installed HivePress
 * version in this environment (no SSH/local install access to check
 * wp-content/plugins/hivepress/includes/forms/ source).
 *
 * Rather than guess at vendor model internals, this saves directly against
 * get_current_user_id() on the same vendor_update action. This is correct
 * for the only case this field needs to handle: a vendor editing their own
 * profile while logged in (self-service Account → Settings), which is the
 * sole entry point for the City dropdown registered above. If a future
 * HivePress version fires hivepress/v1/models/vendor/update for non-owner
 * edits (e.g. admin editing another vendor's profile), this would need to
 * be revisited.
 */
add_action(
	'hivepress/v1/models/vendor/update',
	function () {
		if ( ! isset( $_POST['gw_driver_city'] ) ) {
			return;
		}

		update_user_meta( get_current_user_id(), '_gw_driver_city', absint( $_POST['gw_driver_city'] ) );
	}
);
