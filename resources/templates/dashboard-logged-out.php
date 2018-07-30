<?php
/**
 * Dashboard: Logged Out
 * This will display notice in dashboard page if user not logged in.
 * 
 * @since 1.0.0
 * @version 1.0.0
 */
?>

<div id="astoundify-favorites-dashboard-logged-out">

	<?php astoundify_favorites_notices(); ?>

	<p><?php printf( __( 'You need to be signed in to manage your %s.', 'astoundify-favorites' ), astoundify_favorites_label( 'favorites' ) ); ?></p>

</div><!-- #astoundify-favorites-dashboard-logged-out -->