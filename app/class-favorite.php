<?php
/**
 * Favorite
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * A single favorite item.
 *
 * @since 1.0.0
 */
class Favorite {

	/**
	 * The WordPress object this favorite is based on.
	 *
	 * @since 1.0.0
	 * @var $post null|WP_Post
	 */
	public $post = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param object|int|false $object       Supported WordPress object.
	 * @param int              $check_author User ID.
	 */
	public function __construct( $post = null, $check_author = false ) {
		// Bail if false.
		if ( false === $post ) {
			return;
		}

		// Is a post object/ID, use it.
		if ( is_a( $post, 'WP_Post' ) ) {
			$this->post = $post;
		} elseif ( is_numeric( $post ) ) {
			$this->post = get_post( $post );
		}

		// Bail if we can't find a WordPress object to use.
		if ( ! $this->post ) {
			return;
		}

		// If the post has been set but it is not valid (wrong post type), reset it.
		if ( 'astoundify_favorite' !== $this->post->post_type ) {
			$this->post = null;
		}

		// If the post has been set and the author is not valid, reset it.
		if ( $check_author && ( (int) $check_author !== (int) $this->post->post_author ) ) {
			$this->post = null;
		}
	}

	/**
	 * Favorite ID
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		if ( ! $this->post ) {
			return 0;
		}

		return $this->post->ID;
	}

	/**
	 * Favorite Title
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		if ( ! $this->post ) {
			return '';
		}

		// Translators: %1$s Favorite display name. %2$s Favorite name.
		return sprintf( __( '%1$s Favorited %2$s', 'astoundify-favorites' ), $this->get_author()->display_name, $this->get_target_title() );
	}

	/**
	 * Favorite Note
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_note() {
		if ( ! $this->post ) {
			return '';
		}

		return apply_filters( 'astoundify_favorite_note', wp_kses_post( $this->post->post_content ), $this->post );
	}


	/**
	 * Favorite Note HTML
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_note_html() {
		return '<div class="astoundify-favorite-note">' . wpautop( wp_kses_post( $this->get_note() ) ) . '</div>';
	}

	/**
	 * List
	 *
	 * @since 1.0.0
	 *
	 * @return Favorite_List|false
	 */
	public function get_list() {
		if ( ! $this->post ) {
			return false;
		}

		$list  = null;
		$lists = get_the_terms( $this->post, 'astoundify_favorite_list' );

		if ( $lists && ! is_wp_error( $lists ) && isset( $lists[0] ) ) {
			$list = $lists[0];
		}

		return new Favorite_List( $list );
	}

	/**
	 * List ID
	 *
	 * @since 1.0.0
	 *
	 * @return int|false
	 */
	public function get_list_id() {
		if ( ! $this->get_list() ) {
			return false;
		}

		return $this->get_list()->get_id();
	}

	/**
	 * List Link
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_list_html() {
		$before = '<div class="astoundify-favorite-list">';
		$after  = '</div>';

		if ( ! $this->get_list() ) {
			return '';
		}

		$text = $this->get_list()->get_name();

		$attr = array(
			'href'  => $this->get_list()->get_url(),
			'class' => 'astoundify-favorites-list-link',
		);

		$attr_str = astoundify_favorites_attr( $attr );

		return "{$before}<a {$attr_str}>{$text}</a>{$after}";
	}
      /**
	 * Target Gas Price
	 *
	 */
	public function get_gas_field() {

		if ( ! $this->get_target_id() ) {
			return false;
		}
		$listing = listify_get_listing( $this->get_target_id() );
		$text =  $listing->get_object()->_gas_price;
		if (!empty($text)) { $text = $text;}
		return  "<p>{$text}</p>";
	}
    /**
	 * Target Diesel Price
	 *
	 */
	public function get_diesel_field() {
		if ( ! $this->get_target_id()) {
			return false;
		}
		$listing = listify_get_listing( $this->get_target_id() );
		$text =  $listing->get_object()->_diesel_price;
		if (!empty($text)) { $text = $text;}
		return  "<p>{$text}</p>";
	}
	 /**
	 * Target Last Updated
	 *
	 */
	public function get_last_updated_field() {
		if ( ! $this->get_target_id()) {
			return false;
		}
		$listing = listify_get_listing( $this->get_target_id() );
		$text =  $listing->get_object()->_last_updated;
		if (!empty($text)) { $text = $text;}
		return  "<p>{$text}</p>";
	}
	/**
	 * Author ID
	 *
	 * @since 1.0.0
	 *
	 * @return WP_User|false
	 */
	public function get_author_id() {
		if ( ! $this->post ) {
			return false;
		}

		return $this->post->post_author;
	}

	/**
	 * Author
	 *
	 * Useful WP_User Vars:
	 * - ID
	 * - user_login
	 * - display_name
	 * - user_email
	 *
	 * @since 1.0.0
	 *
	 * @return WP_User|false
	 */
	public function get_author() {
		if ( ! $this->post ) {
			return false;
		}

		return get_user_by( 'id', $this->get_author_id() );
	}

	/**
	 * Edit URL
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_edit_url() {
		$dashboard_url = astoundify_favorites_dashboard_url();

		if ( ! $this->post || ! $dashboard_url ) {
			return '';
		}

		return esc_url( add_query_arg( 'af_favorite_id', $this->get_id(), $dashboard_url ) );
	}

	/**
	 * Edit URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Change the default "Edit" text.
	 * @return string
	 */
	public function get_edit_link( $text = '' ) {
		// Don't output a URL that does nothing.
		if ( ! $this->get_edit_url() ) {
			return '';
		}

		$text = $text ? $text : __( 'Edit', 'astoundify-favorites' );

		$attr_str = astoundify_favorites_attr( array(
			'href'                => esc_url( $this->get_edit_url() ),
			'class'               => 'astoundify-favorites-edit-favorite',
			'data-af_favorite_id' => absint( $this->get_id() ),
			'data-af_data'        => absint( $this->get_target_id() ),
			'data-_nonce'         => wp_create_nonce( 'astoundify_favorites_create_' . $this->get_target_id() ),
		) );

		return "<a {$attr_str}>{$text}</a>";
	}

	/**
	 * Remove URL
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_remove_url() {
		$dashboard_id = astoundify_favorites_get_option( 'dashboard-page' );

		if ( ! $this->post || ! $dashboard_id ) {
			return '';
		}

		// Redirect URL.
		if ( is_page( $dashboard_id ) ) {
			$redirect_url = astoundify_favorites_dashboard_url( 'favorites' );
		} else {
			$redirect_url = wp_doing_ajax() ? wp_get_referer() : $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		$url = add_query_arg( array(
			'af_favorite_id' => $this->get_id(),
			'af_data'        => 'remove',
			'_nonce'         => wp_create_nonce( 'astoundify_favorites_remove_' . $this->get_id() ),
			'_redirect'      => rawurlencode( esc_url( $redirect_url ) ),
		), astoundify_favorites_dashboard_url() );

		return esc_url( $url );
	}

	/**
	 * Remove Link
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Change the default text.
	 * @return string
	 */
	public function get_remove_link( $text = '' ) {
		if ( ! $this->get_remove_url() ) {
			return '';
		}

		$text = $text ? $text : __( 'Remove', 'astoundify-favorites' );

		$attr = array(
			'href'                => esc_url( $this->get_remove_url() ),
			'class'               => 'astoundify-favorites-remove-favorite',
			'data-_nonce'         => wp_create_nonce( 'astoundify_favorites_remove_' . $this->get_id() ),
			'data-af_favorite_id' => absint( $this->get_id() ),
			'data-af_type'        => esc_attr( $this->get_target_type() ),
		);

		$attr_str = astoundify_favorites_attr( $attr );

		return "<a {$attr_str}>{$text}</a>";
	}

	/**
	 * Target Type.
	 *
	 * @since 1.3.0
	 * @uses astoundify_favorites_sanitize_target_type().
	 *
	 * @return string Possible value "post", "term", "user", etc. Default to post.
	 */
	public function get_target_type() {
		if ( ! $this->post ) {
			return false;
		}

		$type = $this->post->_target_type;

		// Update as post if no type specified.
		if ( 'post' === $type ) {
			delete_post_meta( $this->get_id(), '_target_type' );
		}

		return astoundify_favorites_sanitize_target_type( $type );
	}

	/**
	 * Target
	 *
	 * @since 1.0.0
	 *
	 * @return object|false
	 */
	public function get_target() {
		if ( ! $this->post || ! $this->post->_target_id ) {
			return false;
		}

		$target = astoundify_favorites_get_target( $this->post->_target_id, $this->get_target_type() );
		return $target->get_object() ? $target : false;
	}

	/**
	 * Target ID
	 *
	 * @since 1.0.0
	 *
	 * @return int|false
	 */
	public function get_target_id() {
		if ( ! $this->get_target() ) {
			return 0;
		}

		return absint( $this->get_target()->get_id() );
	}

	/**
	 * Target Title
	 *
	 * @since 1.0.0
	 */
	public function get_target_title() {
		if ( ! $this->get_target() ) {
			return false;
		}

		return $this->get_target()->get_title();
	}

	/**
	 * Target Permalink
	 *
	 * @since 1.0.0
	 *
	 * @return string|false
	 */
	public function get_target_permalink() {
		if ( ! $this->get_target() ) {
			return false;
		}

		return esc_url( $this->get_target()->get_permalink() );
	}

	/**
	 * Target Link
	 * Link to target if status is published
	 *
	 * @since 1.0.0
	 *
	 * @return string|false
	 */
	public function get_target_link() {
		if ( ! $this->get_target() ) {
			return false;
		}
		return $this->get_target()->get_link();
	}

	/**
	 * Create
	 *
	 * @since 1.0.0
	 *
	 * @param int    $target_id   The ID of the object to create.
	 * @param int    $user_id     The owner of the object.
	 * @param string $target_type Target type.
	 * @return int|false ID of created favorite or false.
	 */
	public function create( $target_id, $user_id = 0, $target_type = 'post' ) {
		// Already set, bail.
		if ( $this->post ) {
			return $this->get_id();
		}

		// Sanitize target type.
		$target_type = astoundify_favorites_sanitize_target_type( $target_type );

		// Validate supported post type.
		if ( 'post' === $target_type && ! in_array( get_post_type( $target_id ), astoundify_favorites_post_types(), true ) ) {
			return false;
		}

		// Validate user.
		$user_id = $user_id ? $user_id : get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		// Bail if already favorited.
		$is_favorited = astoundify_favorites_is_favorited( $target_id, $user_id, $target_type );

		if ( $is_favorited ) {
			return $is_favorited;
		}

		// Create favorite.
		$create_args = array(
			'post_type'   => 'astoundify_favorite',
			'post_status' => 'publish',
			'post_author' => $user_id,
		);

		$favorite_id = wp_insert_post( $create_args );

		if ( $favorite_id ) {
			update_post_meta( $favorite_id, '_target_id', $target_id );

			// For query, "post" type need to be removed.
			if ( 'post' === $target_type ) {
				delete_post_meta( $favorite_id, '_target_type' );
			} else {
				update_post_meta( $favorite_id, '_target_type', $target_type );
			}
		}

		// Update current post object.
		$this->post = get_post( $favorite_id );

		// Reset counter.
		delete_post_meta( $this->get_target_id(), '_astoundify_favorites_count' );
		delete_user_meta( $user_id, '_astoundify_favorites_count' );

		return $favorite_id;
	}

	/**
	 * Update
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Modify the default args.
	 * @return int|false ID of updated favorite or false.
	 */
	public function update( $args = array() ) {
		$defaults = array(
			'note'      => '',
			'list_id'   => '',
			'user_id'   => $this->get_author_id() ? $this->get_author_id() : get_current_user_id(),
			'target_id' => $this->get_target_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Fav not set, maybe create.
		$this->create( $args['target_id'], $args['user_id'] );

		if ( ! $this->post ) {
			return false;
		}

		// Validate list.
		if ( $args['list_id'] ) {
			$list = new Favorite_List( $args['list_id'], $args['user_id'] );

			if ( ! $list->term ) {
				$args['list_id'] = false;
			}
		}

		// Update favorite with new information.
		$update_args = array(
			'ID'           => $this->get_id(),
			'post_type'    => 'astoundify_favorite',
			'post_content' => wp_kses_post( $args['note'] ),
		);

		$updated = wp_update_post( $update_args );

		// Update current post object.
		if ( ! is_wp_error( $updated ) && $updated ) {
			$this->post = get_post( $updated );
		}

		// Update assigned list.
		if ( false !== $args['list_id'] ) {
			wp_set_object_terms( $this->get_id(), absint( $args['list_id'] ), 'astoundify_favorite_list', false );
		}

		return $updated;
	}

	/**
	 * Remove
	 *
	 * @since 1.0.0
	 *
	 * @return int|false ID of deleted favorite or false.
	 */
	public function remove() {
		if ( ! $this->post ) {
			return false;
		}

		// Reset counter.
		if ( $this->get_target() ) {
			$this->get_target()->reset_count();
		}
		delete_user_meta( $this->get_author_id(), '_astoundify_favorites_user_count' );

		$removed = wp_delete_post( $this->get_id(), true );

		if ( ! $removed ) {
			return false;
		}

		// Reset current post object.
		$this->post = null;

		return $removed;
	}
}
