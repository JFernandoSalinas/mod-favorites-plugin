<?php
/**
 * Dashboard View Tabs
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 * @var string $active_tab "favorites" or "lists"
 *
 * @package Favorites
 * @category Template
 * @author Astoundify
 */
?>

<div id="astoundify-favorites-dashboard-view-tabs">

	<a class="astoundify-favorites-view-favorites astoundify-favorites-tab <?php echo ( 'favorites' === $active_tab ) ? 'astoundify-favorites-tab--active' : '' ?>" href="<?php echo esc_url( astoundify_favorites_dashboard_url( 'favorites' ) ); ?>"><?php printf( __( 'All %s', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorites' ) ); ?></a>

	<a class="astoundify-favorites-view-lists astoundify-favorites-tab <?php echo ( 'lists' === $active_tab ) ? 'astoundify-favorites-tab--active' : '' ?>" href="<?php echo esc_url( astoundify_favorites_dashboard_url( 'lists' ) ); ?>"><?php printf( __( 'All %s', 'astoundify-favorites' ), astoundify_favorites_label( 'Lists' ) ); ?></a>

</div><!-- #astoundify-favorites-dashboard-view-tabs -->
