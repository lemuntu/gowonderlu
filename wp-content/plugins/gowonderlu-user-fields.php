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
