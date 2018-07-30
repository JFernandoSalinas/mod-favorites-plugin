<?php
/**
 * Actions
 * Handles all creating, editing, and removing favorites and lists.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Actions
 *
 * @since 1.0.0
 */
class Actions {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		// Create Favorite.
		add_action( 'astoundify_favorites_dashboard_action_favorite_create', array( __CLASS__, 'favorite_create' ) );
		add_action( 'wp_ajax_astoundify_favorites_favorite_create', array( __CLASS__, 'favorite_create' ) );

		// Get edit favorite form (AJAX).
		add_action( 'wp_ajax_astoundify_favorites_favorite_edit_form', array( __CLASS__, 'favorite_edit_form' ) );

		// Edit Favorite.
		add_action( 'astoundify_favorites_dashboard_action_favorite_edit', array( __CLASS__, 'favorite_edit' ) );
		add_action( 'wp_ajax_astoundify_favorites_favorite_edit', array( __CLASS__, 'favorite_edit' ) );

		// Remove Favorite.
		add_action( 'astoundify_favorites_dashboard_action_favorite_remove', array( __CLASS__, 'favorite_remove' ) );
		add_action( 'wp_ajax_astoundify_favorites_favorite_remove', array( __CLASS__, 'favorite_remove' ) );

		// Get edit/create list form (AJAX).
		add_action( 'wp_ajax_astoundify_favorites_list_create_form', array( __CLASS__, 'list_create_form' ) );
		add_action( 'wp_ajax_astoundify_favorites_list_edit_form', array( __CLASS__, 'list_edit_form' ) );

		// Create list.
		add_action( 'astoundify_favorites_dashboard_action_list_create', array( __CLASS__, 'list_create' ) );
		add_action( 'wp_ajax_astoundify_favorites_list_create', array( __CLASS__, 'list_create' ) );

		// Edit list.
		add_action( 'astoundify_favorites_dashboard_action_list_edit', array( __CLASS__, 'list_edit' ) );
		add_action( 'wp_ajax_astoundify_favorites_list_edit', array( __CLASS__, 'list_edit' ) );

		// Remove list.
		add_action( 'astoundify_favorites_dashboard_action_list_remove', array( __CLASS__, 'list_remove' ) );
		add_action( 'wp_ajax_astoundify_favorites_list_remove', array( __CLASS__, 'list_remove' ) );
	}

	/**
	 * Respond Helper Function
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $success  AJAX success/fail.
	 * @param array $data     AJAX data.
	 * @param array $request  $_REQUEST data.
	 */
	public static function respond( $success = false, $data = array(), $request = array() ) {

		// AJAX respond.
		if ( wp_doing_ajax() ) {

			// Add notices.
			$data['notices'] = Notices::display();

			// Send data.
			if ( $success ) {
				wp_send_json_success( $data );
			} else {
				wp_send_json_error( $data );
			}
		} elseif ( isset( $request['_redirect'] ) &&  $request['_redirect'] ) { // Non AJAX redirect.
			wp_safe_redirect( $request['_redirect'] );
			exit;
		}
	}

	/**
	 * Create Favorite
	 *
	 * @since 1.0.0
	 */
	public static function favorite_create() {
		$success = false;
		$data = array();

		// Check nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_create_' . $_REQUEST['af_data'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorite' ) ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Target ID & type.
		$target_id = $_REQUEST['af_data'];
		$target_type = $_REQUEST['af_type'];

		// Create Favorite.
		$favorite = new Favorite( false );
		$favorite_id = $favorite->create( $target_id, get_current_user_id(), $target_type );

		if ( $favorite_id ) {
			$success = true;
			$data['link'] = astoundify_favorites_link( $target_id, '', '', $target_type );
		} else {
			// Translators: %s is favorite.
			Notices::add_error( sprintf( __( 'Fail to create %s. Please try again.', 'astoundify-favorites' ), astoundify_favorites_label( 'favorite' ) ) );
		}

		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Get Favorite Edit Form
	 * This is only for AJAX.
	 *
	 * @since 1.0.0
	 */
	public static function favorite_edit_form() {
		$success = false;
		$data = array();

		// Check nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_create_' . $_REQUEST['af_data'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Get favorite.
		$favorite = astoundify_favorites_get_favorite( $_REQUEST['af_favorite_id'], get_current_user_id() );

		// Target ID & type.
		$target_id = $_REQUEST['af_data'];
		$target_type = $_REQUEST['af_type'];

		// Favorite not found.
		if ( ! $favorite->post ) {
			// Translators: %s is Favorite.
			Notices::add_error( sprintf( __( '%s not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorite' ) ) );
			$data['link'] = astoundify_favorites_link( $target_id, '', '', $target_type );
			self::respond( $success, $data, $_REQUEST );
		}

		// Get form, and send it.
		$success = true;
		ob_start();
		astoundify_favorites_get_template( 'form-favorite-edit', array(
			'favorite' => $favorite,
			'nonce'    => 'astoundify_favorites_edit_' . $favorite->get_id(),
			'redirect' => '',
		) );
		$data['form'] = ob_get_clean();
		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Favorite Edit
	 *
	 * @since 1.0.0
	 */
	public static function favorite_edit() {
		$success = false;
		$data = array();

		// Verify nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_edit_' . $_REQUEST['_favorite'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Get favorite.
		$favorite = astoundify_favorites_get_favorite( $_REQUEST['_favorite'], get_current_user_id() );

		// Not found.
		if ( ! $favorite->post ) {
			// Translators: %s is Favorite.
			Notices::add_error( sprintf( __( '%s not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorite' ) ) );
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Fields data.
		$note      = isset( $_REQUEST['note'] ) ? trim( $_REQUEST['note'] ) : '';
		$list_id   = isset( $_REQUEST['list_id'] ) ? trim( $_REQUEST['list_id'] ) : '';
		$list_new  = isset( $_REQUEST['list_new'] ) ? trim( $_REQUEST['list_new'] ) : '';

		// Create new list.
		if ( 'new' === $list_id && $list_new ) {
			$list = new Favorite_List( false );
			$list_id = $list->create( array(
				'list_name' => strip_tags( $list_new ),
			) );
			if ( ! $list_id ) {
				// Translators: %s is list.
				Notices::add_error( sprintf( __( 'Fail to create %s.', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ) );
			}
		}

		// Update Favorite.
		$args = array(
			'note'    => wp_kses_post( $note ),
			'list_id' => $list_id,
		);
		$updated = $favorite->update( $args );

		// Send respond.
		if ( $updated ) {
			$success = true;
			// Translators: %s is Favorite.
			Notices::add_success( sprintf( __( '%s updated.', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorite' ) ) );

			// Get favorite item template.
			ob_start();
			astoundify_favorites_get_template( 'dashboard-favorite-item', array(
				'favorite' => $favorite,
			) );
			$data['template'] = ob_get_clean();
			$data['favorite_id'] = $favorite->get_id();

		} else {
			// Translators: %s is favorite.
			Notices::add_error( sprintf( __( 'Fail to update %s.', 'astoundify-favorites' ), astoundify_favorites_label( 'favorite' ) ) );
		}
		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Favorite Remove
	 *
	 * @since 1.0.0
	 */
	public static function favorite_remove() {
		$success = false;
		$data = array();

		// Check nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_remove_' . $_REQUEST['af_favorite_id'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Favorite ID.
		$favorite_id = $_REQUEST['af_favorite_id'];

		// Remove Favorite.
		$favorite = astoundify_favorites_get_favorite( $favorite_id, get_current_user_id() );

		// Not found.
		if ( ! $favorite->post ) {
			// Translators: %s is Favorite.
			Notices::add_error( sprintf( __( '%s not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'Favorite' ) ) );
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Store ID before removing.
		$favorite_id = $favorite->get_id();
		$target_id = $favorite->get_target_id();
		$target_type = $favorite->get_target_type();

		// Remove.
		$removed = $favorite->remove();

		// Send respond.
		if ( $removed ) {
			$success = true;
			$data['link'] = astoundify_favorites_link( $target_id, '', '', $target_type );
			$data['favorite_id'] = $favorite_id;
			$data['target_id'] = $target_id;
			$data['target_type'] = $target_type;
		} else {
			// Translators: %s is favorite.
			Notices::add_error( sprintf( __( 'Fail to remove %s.', 'astoundify-favorites' ), astoundify_favorites_label( 'favorite' ) ) );
		}
		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Get Create List Form
	 * This is only for AJAX.
	 *
	 * @since 1.0.0
	 */
	public static function list_create_form() {
		$success = true;
		$data = array();

		// Check nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_create_list' ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Get form, and send it.
		ob_start();
		astoundify_favorites_get_template( 'form-list-edit', array(
			'list'     => new Favorite_List( false ),
			'nonce'    => 'astoundify_favorites_create_list',
			'redirect' => '',
		) );
		$data['form'] = ob_get_clean();
		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Get Edit List Form
	 * This is only for AJAX.
	 *
	 * @since 1.0.0
	 */
	public static function list_edit_form() {
		$success = true;
		$data = array();

		// Check nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_edit_' . $_REQUEST['af_list_id'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Get list.
		$list = new Favorite_List( $_REQUEST['af_list_id'], get_current_user_id() );

		// Not found.
		if ( ! $list->term ) {
			// Translators: %s is List.
			Notices::add_error( sprintf( __( '%s not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ) );
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Get form, and send it.
		ob_start();
		astoundify_favorites_get_template( 'form-list-edit', array(
			'list'     => $list,
			'nonce'    => 'astoundify_favorites_edit_' . $list->get_id(),
			'redirect' => '',
		) );
		$data['form'] = ob_get_clean();
		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Create List
	 *
	 * @since 1.0.0
	 */
	public static function list_create() {
		$success = false;
		$data = array();

		// Verify nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_create_list' ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Get list object.
		$list = new Favorite_List( false );

		// Create list.
		$list_id = $list->create( array(
			'list_name' => strip_tags( $_REQUEST['list_name'] ),
		) );

		// Fail.
		if ( ! $list_id ) {
			// Translators: %s is list.
			Notices::add_error( sprintf( __( 'Fail to create %s.', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ) );
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Success, get list template.
		$success = true;
		// Translators: %s is list.
		Notices::add_success( sprintf( __( 'New %s created.', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ) );
		ob_start();
		astoundify_favorites_get_template( 'dashboard-list-item', array(
			'list' => $list,
		) );
		$data['template'] = ob_get_clean();
		$data['list_id'] = $list->get_id();

		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * Edit List
	 *
	 * @since 1.0.0
	 */
	public static function list_edit() {
		$success = false;
		$data = array();

		// Verify nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_edit_' . $_REQUEST['_list'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// List ID.
		$list_id = $_REQUEST['_list'];

		// Get list.
		$list = new Favorite_List( $list_id, get_current_user_id() );
		if ( ! $list->term ) {
			// Translators: %s is List.
			Notices::add_error( sprintf( __( 'List not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ) );
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Update it.
		$args = array(
			'list_name' => strip_tags( $_REQUEST['list_name'] ),
		);
		$updated = $list->update( $args );

		if ( $updated ) {
			$success = true;
			// Translators: %s is List.
			Notices::add_success( sprintf( __( '%s updated.', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ) );

			// Get favorite item template.
			ob_start();
			astoundify_favorites_get_template( 'dashboard-list-item', array(
				'list' => $list,
			) );
			$data['template'] = ob_get_clean();
			$data['list_id'] = $list->get_id();

		} else {
			// Translators: %s is list.
			Notices::add_error( sprintf( __( 'Fail to update %s.', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ) );
		}
		self::respond( $success, $data, $_REQUEST );
	}

	/**
	 * List Remove
	 *
	 * @since 1.0.0
	 */
	public static function list_remove() {
		$success = false;
		$data = array();

		// Check nonce.
		if ( ! isset( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'astoundify_favorites_remove_' . $_REQUEST['af_list_id'] ) ) {
			if ( isset( $_REQUEST['_nonce'] ) ) {
				// Translators: %s is Favorite.
				Notices::add_error( sprintf( __( '%s nonce verification fail.', 'astoundify-favorites' ) ), astoundify_favorites_label( 'Favorite' ) );
			}
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Favorite ID.
		$list_id = $_REQUEST['af_list_id'];

		// Remove Favorite.
		$list = new Favorite_List( $list_id, get_current_user_id() );

		// Not found.
		if ( ! $list->term ) {
			// Translators: %s is List.
			Notices::add_error( sprintf( __( '%s not found.', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ) );
			self::respond( $success, $data, $_REQUEST );
			return;
		}

		// Store ID before removing.
		$list_id = $list->get_id();

		// Remove.
		$removed = $list->remove();

		// Send respond.
		if ( $removed ) {
			$success = true;
			// Translators: %s is List.
			Notices::add_success( sprintf( __( '%s removed.', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ) );
			$data['list_id'] = $list_id;
		} else {
			// Translators: %s is list.
			Notices::add_error( sprintf( __( 'Fail to remove %s. Please try again.', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ) );
		}
		self::respond( $success, $data, $_REQUEST );
	}

}
