<?php
/**
 * Favorite dashboard.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Dashboard.
 *
 * @since 1.0.0
 */
class Dashboard {

	/**
	 * Dashboard View Query Var.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $qv_view;

	/**
	 * Favorite ID Query Vars.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $qv_favorite_id;

	/**
	 * Favorite List ID Query Vars.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $qv_list_id;

	/**
	 * Additinal Data Query Vars.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $qv_data;

	/**
	 * Action.
	 *
	 * @since 1.0.0
	 * @var string|false
	 */
	public $action = false;

	/**
	 * Current Page Query Vars.
	 * This is using WordPress built in page pagination.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $qv_current_page;

	/**
	 * Returns the instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new self;
		}
		return $instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Filter registered query variables.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

		// Set variables on redirect.
		add_action( 'template_redirect', array( $this, 'set_query_vars' ), 1 );

		// Broadcast the current action.
		add_action( 'template_redirect', array( $this, 'action' ) );

		// Register shortcode.
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Add custom query var So WordPress can recognize it
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars Registered query variables.
	 * @return array $vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'af_view';        // Dashboard view.
		$vars[] = 'af_favorite_id'; // Favorite ID.
		$vars[] = 'af_list_id';     // Favorite List ID.
		$vars[] = 'af_data';        // For additional data.

		return $vars;
	}

	/**
	 * Get and Set Query Vars
	 *
	 * @since 1.0.0
	 */
	public function set_query_vars() {

		// Get dashboard page ID.
		$page_id  = astoundify_favorites_get_option( 'dashboard-page' );

		// Only set in dashboard page.
		if ( $page_id && is_page( $page_id ) ) {
			// Dashboard View.
			$this->qv_view         = get_query_var( 'af_view' );
			$this->qv_current_page = get_query_var( 'page' );

			// Actions.
			$this->qv_favorite_id  = get_query_var( 'af_favorite_id' );
			$this->qv_list_id      = get_query_var( 'af_list_id' );
			$this->qv_data         = get_query_var( 'af_data' );
		}
	}

	/**
	 * Action: Add Hook based on actions.
	 *
	 * @since 1.0.0
	 */
	public function action() {
		// Bail if viewing dashboard.
		if ( $this->qv_view ) {
			return;
		}

		$item   = false;
		$action = false;
		if ( $this->qv_favorite_id ) {
			$item   = 'favorite';
			$action = 'new' === $this->qv_favorite_id ? 'create' : 'edit';
			$action = 'remove' === $this->qv_data ? 'remove' : $action;
		} elseif ( $this->qv_list_id ) {
			$item   = 'list';
			$action = 'new' === $this->qv_list_id ? 'create' : 'edit';
			$action = 'remove' === $this->qv_data ? 'remove' : $action;
		}

		// Bail if item not set.
		if ( ! $item ) {
			return;
		}

		// Set action.
		$this->action = "{$item}_{$action}";

		// Hook to process submitted action data.
		do_action( "astoundify_favorites_dashboard_action_{$this->action}" );
	}

	/**
	 * Register Shortcodes
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'astoundify-favorites-dashboard', array( $this, 'dashboard_shortcode_callback' ) );
	}

	/**
	 * Display User favorites using shortcode
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function dashboard_shortcode_callback( $atts ) {
		if ( is_admin() ) {
			return;
		}

		ob_start();

		$defaults = array(
			'item_per_page' => '25',
			'default_view'  => 'favorites',
		);

		$atts = shortcode_atts( $defaults, $atts );
		$atts['default_view'] = 'favorites' === $atts['default_view'] ? 'favorites' : 'lists';

		// If view not set, use default.
		$this->qv_view = $this->qv_view ? $this->qv_view : $atts['default_view'];

		// Check user login.
		if ( ! is_user_logged_in() ) {

			astoundify_favorites_get_template( 'dashboard-logged-out' );
			return ob_get_clean();

		}

		/**
		 * Edit favorite form.
		 */
		if ( 'favorite_edit' === $this->action ) {

			// Get favorite object.
			$favorite = astoundify_favorites_get_favorite( $this->qv_favorite_id, get_current_user_id() );

			// Found, load edit template.
			if ( $favorite->post ) {

				astoundify_favorites_get_template( 'form-favorite-edit', array(
					'favorite' => $favorite,
					'nonce'    => 'astoundify_favorites_edit_' . $favorite->get_id(),
					'redirect' => astoundify_favorites_dashboard_url( 'favorites' ),
				) );

			} else {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s item not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorite' ) ) );

				astoundify_favorites_get_template( 'dashboard-404' );

			}

			return ob_get_clean();
		}

		/**
		 * Create list form.
		 */
		if ( 'list_create' === $this->action ) {
			astoundify_favorites_get_template( 'form-list-edit', array(
				'list'     => new Favorite_List( false ),
				'nonce'    => 'astoundify_favorites_create_list',
				'redirect' => astoundify_favorites_dashboard_url( 'lists' ),
			) );
			return ob_get_clean();
		}

		/**
		 * Edit list form.
		 */
		if ( 'list_edit' === $this->action ) {
			$list = new Favorite_List( $this->qv_list_id, get_current_user_id() );

			if ( $list->term ) {

				astoundify_favorites_get_template( 'form-list-edit', array(
					'list'     => $list,
					'nonce'    => 'astoundify_favorites_edit_' . $list->get_id(),
					'redirect' => astoundify_favorites_dashboard_url( 'lists' ),
				) );

			} else {

				// Translators: %s is List.
				Notices::add_error( sprintf( __( '%s not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ) );

				astoundify_favorites_get_template( 'dashboard-404' );

			}

			return ob_get_clean();
		}

		/**
		 * Dashboard
		 */
		if ( 'favorites' === $this->qv_view ) {
			// Maybe migrate WP Job Manager - Bookmarks.
			$this->migrate_job_manager_bookmarks();

			$args = array(
				'item_per_page' => $atts['item_per_page'],
				'current_page'  => $this->qv_current_page,
			);

			// Favorites in a list.
			if ( $this->qv_list_id ) {

				$list = new Favorite_List( $this->qv_list_id, get_current_user_id() );

				if ( $list->get_id() ) {
					$args['list_id'] = $list->get_id();

					// Translators: %1$s favorite, %2$s list name, %3$s all favorite link. %s is favorite.
					Notices::add_info( sprintf( __( 'You are currently browsing %1$s in %2$s. %3$s', 'astoundify-favorites' ), astoundify_favorites_label( 'favorites' ), $list->get_name(), '<a href="' . astoundify_favorites_dashboard_url( 'favorites' ) . '">' . sprintf( __( 'View all %s', 'astoundify-favorites' ), astoundify_favorites_label( 'favorites' ) ) . '</a>' ) );
				}
			}

			$favorite_query = new Favorite_Query( $args );

			astoundify_favorites_get_template( 'dashboard-favorites', array(
				'favorite_query' => $favorite_query,
			) );

			return ob_get_clean();

		} elseif ( 'lists' === $this->qv_view ) {

			$favorite_list_query = new Favorite_List_Query();

			astoundify_favorites_get_template( 'dashboard-lists', array(
				'favorite_list_query' => $favorite_list_query,
			) );

			return ob_get_clean();

		} else {

			astoundify_favorites_get_template( 'dashboard-404' );

			return ob_get_clean();
		} // End if().
	}

	/**
	 * Migrate Old Favorites
	 *
	 * @since 1.0.0
	 */
	public function migrate_job_manager_bookmarks() {
		global $wpdb;

		// Bail if already migrated.
		$migrated = get_user_meta( get_current_user_id(), '_job_manager_dashboard_migrated', true );
		if ( '1' === $migrated ) {
			return;
		}

		// Set as migrated if table not exists.
		$table_name = $wpdb->prefix . 'job_manager_bookmarks';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) { // Table not exists.
			update_user_meta( get_current_user_id(), '_job_manager_dashboard_migrated', '1' ); // Close the door.
			return;
		}

		// Get all bookmarks.
		$bookmarks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY date_created;", get_current_user_id() ) );

		// Loop and import all.
		foreach ( $bookmarks as $bookmark ) {
			$target_id = $bookmark->post_id;
			$note      = $bookmark->bookmark_note;

			// Not yet favorited, import.
			$is_favorited = astoundify_favorites_is_favorited( $target_id, get_current_user_id() );

			if ( ! $is_favorited ) {
				$favorite    = new Favorite( false );
				$favorite_id = $favorite->create( $target_id, get_current_user_id() );

				// Update note too.
				if ( $favorite_id && $note ) {
					$favorite->update( array(
						'note' => $note,
					) );
				}
			}
		}

		// Close the door.
		update_user_meta( get_current_user_id(), '_job_manager_dashboard_migrated', '1' );
	}

}
