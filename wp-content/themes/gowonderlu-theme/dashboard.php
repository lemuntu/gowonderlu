<?php
/**
 * Template Name: Dashboard
 */
get_header();

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$user_id   = get_current_user_id();
$is_driver = gowonderlu_user_is_driver( $user_id );

$my_deals = get_posts(
	array(
		'post_type'      => 'gw_deal',
		'author'         => $user_id,
		'post_status'    => array( 'pending', 'publish', 'gw_assigned', 'gw_completed', 'gw_cancelled' ),
		'posts_per_page' => -1,
	)
);

$available_deals = array();
$my_jobs          = array();

if ( $is_driver ) {
	$driver_city_id = (int) get_user_meta( $user_id, '_gw_driver_city', true );

	$available_deals = get_posts(
		array(
			'post_type'      => 'gw_deal',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'gw_deal_city',
					'field'    => 'term_id',
					'terms'    => $driver_city_id,
				),
			),
		)
	);

	$my_jobs = get_posts(
		array(
			'post_type'      => 'gw_deal',
			'post_status'    => array( 'gw_assigned', 'gw_completed' ),
			'posts_per_page' => -1,
			'meta_key'       => GW_DEAL_META_DRIVER_ID,
			'meta_value'     => $user_id,
		)
	);
}

$status_labels = array(
	'pending'      => 'Pending review',
	'publish'      => 'Open',
	'gw_assigned'  => 'Assigned',
	'gw_completed' => 'Completed',
	'gw_cancelled' => 'Cancelled',
);
?>

<div class="gw-page">
<section class="gw-legal gw-dashboard">
	<span class="gw-eyebrow">Dashboard</span>
	<h1>Your deals</h1>

	<a href="<?php echo esc_url( home_url( '/post-a-deal/' ) ); ?>" class="gw-btn gw-btn-fill">Post a Deal</a>

	<h2>My Deals</h2>
	<?php if ( ! $my_deals ) : ?>
		<p>You haven't posted a deal yet.</p>
	<?php else : ?>
		<div class="gw-dashboard-list">
			<?php foreach ( $my_deals as $deal ) : ?>
				<div class="gw-dashboard-card">
					<div class="gw-dashboard-card-title"><?php echo esc_html( $deal->post_title ); ?></div>
					<div class="gw-dashboard-card-status"><?php echo esc_html( $status_labels[ $deal->post_status ] ?? $deal->post_status ); ?></div>

					<?php if ( 'gw_assigned' === $deal->post_status || 'gw_completed' === $deal->post_status ) :
						$driver = get_userdata( (int) get_post_meta( $deal->ID, GW_DEAL_META_DRIVER_ID, true ) );
						?>
						<?php if ( $driver ) : ?>
							<p>Driver: <?php echo esc_html( $driver->display_name ); ?> — <a href="<?php echo esc_url( home_url( '/account/messages/' ) ); ?>">Message</a></p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( 'gw_completed' === $deal->post_status ) : ?>
						<p><a href="<?php echo esc_url( home_url( '/account/reviews/' ) ); ?>">Leave a Review</a></p>
					<?php endif; ?>

					<?php if ( in_array( $deal->post_status, array( 'pending', 'publish', 'gw_assigned' ), true ) ) : ?>
						<form method="post">
							<?php wp_nonce_field( 'gw_deal_action', 'gw_deal_nonce' ); ?>
							<input type="hidden" name="deal_id" value="<?php echo esc_attr( $deal->ID ); ?>">
							<button type="submit" name="gw_deal_action" value="cancel" class="gw-btn gw-btn-outline">Cancel</button>
						</form>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( $is_driver ) : ?>
		<h2>Available Deals</h2>
		<?php if ( ! $available_deals ) : ?>
			<p>No open deals in your city right now.</p>
		<?php else : ?>
			<div class="gw-dashboard-list">
				<?php foreach ( $available_deals as $deal ) : ?>
					<div class="gw-dashboard-card">
						<div class="gw-dashboard-card-title"><?php echo esc_html( $deal->post_title ); ?></div>
						<p><?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_PICKUP, true ) ); ?> → <?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_DROPOFF, true ) ); ?></p>
						<p>$<?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_PRICE, true ) ); ?> — <?php echo esc_html( get_post_meta( $deal->ID, GW_DEAL_META_DATE_WINDOW, true ) ); ?></p>
						<form method="post">
							<?php wp_nonce_field( 'gw_deal_action', 'gw_deal_nonce' ); ?>
							<input type="hidden" name="deal_id" value="<?php echo esc_attr( $deal->ID ); ?>">
							<button type="submit" name="gw_deal_action" value="claim" class="gw-btn gw-btn-fill">Claim This Job</button>
						</form>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h2>My Jobs</h2>
		<?php if ( ! $my_jobs ) : ?>
			<p>You haven't claimed a job yet.</p>
		<?php else : ?>
			<div class="gw-dashboard-list">
				<?php foreach ( $my_jobs as $deal ) : ?>
					<div class="gw-dashboard-card">
						<div class="gw-dashboard-card-title"><?php echo esc_html( $deal->post_title ); ?></div>
						<div class="gw-dashboard-card-status"><?php echo esc_html( $status_labels[ $deal->post_status ] ?? $deal->post_status ); ?></div>
						<p><a href="<?php echo esc_url( home_url( '/account/messages/' ) ); ?>">Message customer</a></p>
						<?php if ( 'gw_assigned' === $deal->post_status ) : ?>
							<form method="post">
								<?php wp_nonce_field( 'gw_deal_action', 'gw_deal_nonce' ); ?>
								<input type="hidden" name="deal_id" value="<?php echo esc_attr( $deal->ID ); ?>">
								<button type="submit" name="gw_deal_action" value="complete" class="gw-btn gw-btn-fill">Mark Completed</button>
							</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<h2>Want to drive?</h2>
		<p><a href="<?php echo esc_url( home_url( '/register-vendor/' ) ); ?>" class="gw-btn gw-btn-outline">Become a Driver</a></p>
	<?php endif; ?>
</section>
</div>

<?php
get_footer();
