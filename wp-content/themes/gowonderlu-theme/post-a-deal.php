<?php
/**
 * Template Name: Post a Deal
 */
get_header();

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$cities = get_terms(
	array(
		'taxonomy'   => 'gw_deal_city',
		'hide_empty' => false,
	)
);
?>

<div class="gw-page">
<section class="gw-legal gw-deal-form-section">
	<span class="gw-eyebrow">Post a deal</span>
	<h1>What do you need moved?</h1>

	<?php if ( ! empty( $_GET['posted'] ) ) : ?>
		<div class="gw-legal-notice"><strong>Submitted.</strong> Your deal is pending review and will be visible to drivers once approved.</div>
	<?php endif; ?>

	<form class="gw-deal-form" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'gowonderlu_post_deal', 'gowonderlu_post_deal_nonce' ); ?>

		<label>Title
			<input type="text" name="gw_deal_title" required>
		</label>

		<label>What needs to be moved?
			<textarea name="gw_deal_description" required></textarea>
		</label>

		<label>Pickup address
			<input type="text" name="gw_deal_pickup" id="gw_deal_pickup" required>
		</label>

		<label>Dropoff address
			<input type="text" name="gw_deal_dropoff" id="gw_deal_dropoff" required>
		</label>

		<label>City
			<select name="gw_deal_city" required>
				<option value="">— Select a city —</option>
				<?php foreach ( $cities as $city ) : ?>
					<option value="<?php echo esc_attr( $city->term_id ); ?>"><?php echo esc_html( $city->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>

		<label>Date/time window
			<input type="text" name="gw_deal_date_window" placeholder="e.g. Saturday morning, June 28" required>
		</label>

		<label>Your offer ($)
			<input type="number" name="gw_deal_price" min="0" step="1" required>
		</label>

		<label>Photos (optional)
			<input type="file" name="gw_deal_photos[]" multiple accept="image/*">
		</label>

		<button type="submit" class="gw-btn gw-btn-fill">Submit Deal</button>
	</form>
</section>
</div>

<?php
get_footer();
