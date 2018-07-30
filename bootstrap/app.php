<?php
/**
 * Load the application.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Bootstrap
 * @author Astoundify
 */

namespace Astoundify\Favorites;

// Load helper functions.
require_once( ASTOUNDIFY_FAVORITES_PATH . 'app/functions.php' );

/**
 * Plugin Init
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', function() {
	// Load text domain.
	load_plugin_textdomain( dirname( ASTOUNDIFY_FAVORITES_PLUGIN ), false, dirname( ASTOUNDIFY_FAVORITES_PLUGIN ) . '/resources/languages/' );

	// Plugin setup.
	Setup::register();

	// Register favorite post type.
	Favorite_Setup::register();

	// Register list taxonomy.
	Favorite_List_Setup::register();

	// Filter content.
	Content::register();

	// Add, update, remove actions.
	Actions::register();

	// Load scripts.
	Scripts::register();

	// Register settings page.
	Settings::get_instance();

	// Setup dashboard shortcode.
	Dashboard::get_instance();
} );
