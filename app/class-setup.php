<?php
/**
 * Plugin Setup.
 *
 * @since 1.3.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Plugin Setup.
 *
 * @since 1.3.0
 */
class Setup {

	/**
	 * Register
	 *
	 * @since 1.3.0
	 */
	public static function register() {
		// Page already set. Bail.
		if ( astoundify_favorites_get_option( 'dashboard-page' ) ) {
			return;
		}

		// Load library.
		require_once( ASTOUNDIFY_FAVORITES_PATH . 'vendor/astoundify/plugin-setup/astoundify-pluginsetup.php' );

		$config = array(
			'id'           => 'astoundify-favorites-setup',
			'capability'   => 'manage_options',
			'menu_title'   => __( 'Favorites Setup', 'astoundify-favorites' ),
			'page_title'   => __( 'Setup Favorites', 'astoundify-favorites' ),
			'redirect'     => true,
			'steps'        => array( // Steps must be using 1, 2, 3... in order, last step have no handler.
				'1' => array(
					'view'    => array( __CLASS__, 'dashboard_setup_view' ),
					'handler' => array( __CLASS__, 'dashboard_setup_handler' ),
				),
				'2' => array(
					'view'    => array( __CLASS__, 'default_lists_view' ),
					'handler' => array( __CLASS__, 'default_lists_handler' ),
				),
				'3' => array(
					'view'    => array( __CLASS__, 'thank_you_view' ),
				),
			),
			'labels'       => array(
				'next_step_button' => __( 'Next Step', 'astoundify-favorites' ),
				'skip_step_button' => __( 'Skip', 'astoundify-favorites' ),
			),
		);

		// Init setup.
		new \Astoundify_PluginSetup( $config );
	}

	/**
	 * Dashboard Setup View.
	 *
	 * @since 1.3.0
	 */
	public static function dashboard_setup_view() {
?>

<p><?php _e( '🎉 Great News! Favorites by Astoundify has been successfully installed. Visitors can view their favorites on the My Favorites dashboard page. You can change this in the Favorites Settings panel.', 'astoundify-favorites' ); ?> <?php _e( 'This setup wizard will help you get started by creating the favorites dashboard page.', 'astoundify-favorites' ); ?></p>

<p><?php printf( __( 'If you want to skip the wizard and setup the page and shortcode yourself manually, the process is still relatively simple. Refer to the %s for help.', 'astoundify-favorites' ), '<a href="http://listify.astoundify.com/article/609-how-do-i-create-a-favorites-page" target="_blank">' .  __( 'documentation page', 'astoundify-favorites' ) . '</a>' ); ?></p>

<h2 class="title"><?php esc_html_e( 'Favorites Dashboard Setup', 'astoundify-favorites' ); ?></h2>

<table class="widefat">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th><?php esc_html_e( 'Page Title', 'astoundify-favorites' ); ?></th>
			<th><?php esc_html_e( 'Page Description', 'astoundify-favorites' ); ?></th>
			<th><?php esc_html_e( 'Content Shortcode', 'astoundify-favorites' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><input type="checkbox" checked="checked" name="astoundify-favorites-create-page[dashboard]" /></td>
			<td><input type="text" placeholder="<?php echo esc_attr_x( 'My Favorites', 'Default page title (wizard)', 'astoundify-favorites' ); ?>" value="<?php echo esc_attr_x( 'My Favorites', 'Default page title (wizard)', 'astoundify-favorites' ); ?>" name="astoundify-favorites-page-title[dashboard]" /></td>
			<td>
				<p><?php esc_html_e( 'This page allows users to view and edit favorites from the front-end.', 'astoundify-favorites' ); ?></p>
			</td>
			<td><code>[astoundify-favorites-dashboard]</code></td>
		</tr>
	</tbody>
</table>

<?php
	}

	/**
	 * Dashboard Setup Handler.
	 *
	 * @since 1.3.0
	 */
	public static function dashboard_setup_handler() {
		if ( ! isset( $_POST['astoundify-favorites-create-page'] ) ) {
			return;
		}

		// Create dashboard pages.
		if ( isset( $_POST['astoundify-favorites-create-page']['dashboard'] ) ) {

			// Page Title.
			$title = isset( $_POST['astoundify-favorites-page-title']['dashboard'] ) && $_POST['astoundify-favorites-page-title']['dashboard'] ? esc_html( $_POST['astoundify-favorites-page-title']['dashboard'] ) : esc_html__( 'My Favorites', 'astoundify-favorites' );

			// Create page.
			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => get_current_user_id(),
				'post_name'      => sanitize_title( $title ),
				'post_title'     => $title,
				'post_content'   => '[astoundify-favorites-dashboard]',
				'post_parent'    => 0,
				'comment_status' => 'closed',
			);
			$page_id = wp_insert_post( $page_data );

			// Success to create page.
			if ( $page_id ) {

				// Update option.
				astoundify_favorites_update_option( 'dashboard-page', intval( $page_id ) );
			}
		}
	}
	/**
	 * Default Lists View.
	 *
	 * @since 1.3.0
	 */
	public static function default_lists_view() {
?>

<h2 class="title"><?php esc_html_e( 'Default Lists', 'astoundify-favorites' ); ?></h2>

<p><?php _e( 'Users can create "lists" to group their Favorites for easier management. These default lists will be created for each user automatically during registration.', 'astoundify-favorites' ); ?></p>

<table class="widefat">
	<thead>
		<tr>
			<th style="width:30px;"></th>
			<th><?php esc_html_e( 'List Names', 'astoundify-favorites' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><input type="checkbox" checked="checked" name="astoundify-favorites-create-default-lists" value="1"/></td>
			<td>
				<input type="text" class="widefat" value="<?php echo esc_attr_x( 'People, Things', 'Default lists (wizard)', 'astoundify-favorites' ); ?>" name="astoundify-favorites-default-lists" />
				<p><?php esc_html_e( 'Separate multiple lists with commas.', 'astoundify-favorites' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>

<?php
	}

	/**
	 * Default Lists Handler.
	 *
	 * @since 1.3.0
	 */
	public static function default_lists_handler() {
		if ( isset( $_POST['astoundify-favorites-create-default-lists'], $_POST['astoundify-favorites-default-lists'] ) && '1' === $_POST['astoundify-favorites-create-default-lists'] && $_POST['astoundify-favorites-default-lists'] ) {

			$default_lists = $_POST['astoundify-favorites-default-lists'];
			$default_lists = $default_lists ? array_map( 'trim', array_map( 'strip_tags', explode( ',', $default_lists ) ) ) : array();
			if ( ! $default_lists || ! is_array( $default_lists ) ) {
				return;
			}

			$updated = astoundify_favorites_update_option( 'default-lists', $default_lists );
			if ( $updated ) {
				add_action( 'admin_notices', function() {
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php printf( __( 'Default lists are set. You can change the settings in: %s.', 'astoundify-favorites' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=astoundify_favorites_settings' ) ) . '">' . __( 'Favorites Settings', 'astoundify-favorites' ) . '</a>' ); ?></p>
					</div>
					<?php
				} );
			}
		}
	}

	/**
	 * Thank you/Final View.
	 *
	 * @since 1.3.0
	 */
	public static function thank_you_view() {
?>
<h3><?php _e( '🎉 All Done!', 'astoundify-favorites' ); ?></h3>

<p><?php _e( 'You\'re all set to start using the plugin. In case you\'re wondering where to go next:', 'astoundify-favorites' ); ?></p>

<ul>
	<li><a href="<?php echo esc_url( admin_url( 'options-general.php?page=astoundify_favorites_settings' ) ); ?>"><?php _e( 'Adjust the plugin settings', 'astoundify-favorites' ); ?></a></li>
</ul>
<?php
	}

}
