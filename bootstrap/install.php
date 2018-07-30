<?php
/**
 * Plugin Install.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Bootstrap
 * @author Astoundify
 */

namespace Astoundify\Favorites;

// Register activation hook.
register_activation_hook( ASTOUNDIFY_FAVORITES_FILE, function() {

	// Bail if dashboard page is set.
	$dashboard_page = astoundify_favorites_get_option( 'dashboard-page' );
	if ( $dashboard_page ) {
		return;
	}

	// Create Page.
	$args = array(
		'post_type'    => 'page',
		'post_title'   => esc_attr__( 'My Favorites', 'astoundify-favorites' ),
		'post_content' => '[astoundify-favorites-dashboard]',
		'post_status'  => 'publish',
	);
	$post_id = wp_insert_post( $args );

	// Success.
	if ( $post_id ) {
		// Set Dashboard Page Option.
		$option = get_option( 'astoundify_favorites' );
		$option['dashboard-page'] = absint( $post_id );
		update_option( 'astoundify_favorites', $option );

		// Set transient for admin notice.
		set_transient( 'astoundify_favorites_install', $post_id );

	} else { // Fail.
		set_transient( 'astoundify_favorites_install', 'fail' );
	}
} );


// Add install notice.
add_action( 'admin_notices', function() {
	// Get transient data.
	$install = get_transient( 'astoundify_favorites_install' );

	// Bail if no transient.
	if ( false === $install ) {
		return;
	}

	// Settings Page URL.
	$settings_url = add_query_arg( 'page', 'astoundify_favorites_settings', admin_url( 'options-general.php' ) );
	$settings = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Favorites Settings', 'astoundify-favorites' ) . '</a>';

	// Notice.
	if ( 'fail' === $install ) {
		$class = 'error';
		// Translators: %s settings link.
		$notice = sprintf( __( 'Favorites by Astoundify failed to automatically install dashboard page. Please set it manually in %s.', 'astoundify-favorites' ), $settings );
	} else {
		$class = 'info';
		$edit_page = '<a href="' . esc_url( get_edit_post_link( $install ) ) . '">' . get_the_title( $install ) . '</a>';

		// Translators: %1$s edit created paged link, %2$s settings link.
		$notice = sprintf( __( 'Favorites by Astoundify has been sucessfully installed. User can view their favorites in %1$s dashboard page. You can change this in the %2$s.', 'astoundify-favorites' ), $edit_page, $settings );
	}

	?>

<div class="notice notice-<?php echo esc_attr( $class ); ?>">
	<p><?php echo wp_kses_post( $notice ); ?></p>
</div>

	<?php

	delete_transient( 'astoundify_favorites_install' ); // Clean up install notices data.
} );
