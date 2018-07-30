<?php
/**
 * Plugin Name: Favorites by Astoundify
 * Plugin URI: https://astoundify.com/
 * Description: Favorites for WordPress.
 * Author: Astoundify
 * Author URI: https://astoundify.com/
 * Version: 1.3.0
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: astoundify-favorites
 * Domain Path: resources/languages/
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

/* Do not access this file directly */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Activation PHP Notice
 *
 * @since 1.0.0
 */
function astoundify_favorites_php_notice() {
	// translators: %1$s minimum PHP version, %2$s current PHP version.
	$notice = sprintf( __( 'Astoundify Favorites requires at least PHP %1$s. You are running PHP %2$s. Please upgrade and try again.', 'astoundify-favorites' ), '<code>5.4.0</code>', '<code>' . PHP_VERSION . '</code>' );
?>

<div class="notice notice-error">
	<p><?php echo wp_kses_post( $notice, array( 'code' ) ); ?></p>
</div>

<?php
}

// Check for PHP version..
if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
	add_action( 'admin_notices', 'astoundify_favorites_php_notice' );
	return;
}

// Plugin can be loaded... define some constants.
define( 'ASTOUNDIFY_FAVORITES_VERSION', '1.3.0' );
define( 'ASTOUNDIFY_FAVORITES_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ASTOUNDIFY_FAVORITES_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'ASTOUNDIFY_FAVORITES_PLUGIN', plugin_basename( __FILE__ ) );
define( 'ASTOUNDIFY_FAVORITES_FILE', __FILE__ );

/**
 * Plugin Updater.
 *
 * @since 1.0.0
 */
function astoundify_favorites_plugin_updater() {
	require_once( dirname( __FILE__ ) . '/vendor/astoundify/plugin-updater/astoundify-pluginupdater.php' );

	new Astoundify_PluginUpdater( __FILE__ );
}
add_action( 'admin_init', 'astoundify_favorites_plugin_updater', 9 );

/**
 * Load auto loader.
 *
 * @since 1.0.0
 */
require_once( __DIR__ . '/bootstrap/autoload.php' );

/**
 * Plugin install.
 *
 * @since 1.0.0
 */
require_once( __DIR__ . '/bootstrap/install.php' );

/**
 * Start the application.
 *
 * @since 1.0.0
 */
require_once( __DIR__ . '/bootstrap/app.php' );
