<?php
/**
 * Favorite List.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * A single favorite list item.
 *
 * @since 1.0.0
 * @link https://developer.wordpress.org/reference/classes/wp_term/
 */
class Favorite_List {

	/**
	 * WP_Term object.
	 *
	 * @var WP_Term
	 * @since 1.0.0
	 */
	public $term = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param object|int|false $term WP_Term.
	 * @param int              $check_author User ID.
	 */
	public function __construct( $term = null, $check_author = null ) {
		// Bail if false.
		if ( false === $term ) {
			return;
		}

		// Is a term object/ID, use it.
		if ( $term && is_a( $term, 'WP_Term' ) ) {
			$this->term = $term;
		} elseif ( is_numeric( $term ) ) {
			$this->term = get_term( $term, 'astoundify_favorite_list' );
		}

		// Check WP Error / empty.
		if ( is_wp_error( $this->term ) || ! $this->term ) {
			$this->term = null;
			return;
		}

		// Check taxonomy.
		if ( 'astoundify_favorite_list' !== $this->term->taxonomy ) {
			$this->term = null;
		}

		// Check author.
		if ( $check_author && ( (int) get_term_meta( $this->term->term_id, 'list_author', true ) !== (int) $check_author ) ) {
			$this->term = null;
		}
	}

	/**
	 * List ID
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		if ( ! $this->term ) {
			return 0;
		}

		return $this->term->term_id;
	}

	/**
	 * List Name
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		if ( ! $this->term ) {
			return '';
		}

		return $this->term->name;
	}

	/**
	 * List Description
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		if ( ! $this->term ) {
			return '';
		}

		return $this->term->description;
	}

	/**
	 * List Count
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_count() {
		if ( ! $this->term ) {
			return 0;
		}
		return $this->term->count;
	}

	/**
	 * Author ID
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_author_id() {
		if ( ! $this->term ) {
			return 0;
		}
		return get_term_meta( $this->get_id(), 'list_author', true );
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
	 * @return object|false WP_User
	 */
	public function get_author() {
		if ( ! $this->term ) {
			return false;
		}
		return get_user_by( 'id', $this->get_author_id() );
	}

	/**
	 * List Archive URL
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_url() {
		$dashboard_url = astoundify_favorites_dashboard_url( 'favorites' );

		if ( ! $this->term || ! $dashboard_url ) {
			return '';
		}

		return esc_url( add_query_arg( 'af_list_id', $this->get_id(), $dashboard_url ) );
	}

	/**
	 * List Edit URL
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_edit_url() {
		$dashboard_url = astoundify_favorites_dashboard_url();

		if ( ! $this->term || ! $dashboard_url ) {
			return '';
		}

		return esc_url( add_query_arg( 'af_list_id', $this->get_id(), $dashboard_url ) );
	}

	/**
	 * List Edit Link
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Anchor text.
	 * @return string
	 */
	public function get_edit_link( $text = '' ) {
		// Don't output a URL that does nothing.
		if ( ! $this->get_edit_url() ) {
			return '';
		}

		$text = $text ? $text : __( 'Edit', 'astoundify-favorites' );

		$attr_str = astoundify_favorites_attr( array(
			'href'             => esc_url( $this->get_edit_url() ),
			'class'            => 'astoundify-favorites-edit-list',
			'data-af_list_id'  => absint( $this->get_id() ),
			'data-_nonce'      => wp_create_nonce( 'astoundify_favorites_edit_' . $this->get_id() ),
		) );

		return "<a {$attr_str}>{$text}</a>";
	}

	/**
	 * List Remove URL
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_remove_url() {
		$dashboard_url = astoundify_favorites_dashboard_url();

		if ( ! $this->term || ! $dashboard_url ) {
			return '';
		}

		$url = add_query_arg( array(
			'af_list_id' => $this->get_id(),
			'af_data'    => 'remove',
			'_nonce'     => wp_create_nonce( 'astoundify_favorites_remove_' . $this->get_id() ),
		), $dashboard_url );

		return esc_url( $url );
	}

	/**
	 * List Remove Link
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Anchor text.
	 * @return string
	 */
	public function get_remove_link( $text = '' ) {
		if ( ! $this->get_remove_url() ) {
			return '';
		}

		$text = $text ? $text : __( 'Remove', 'astoundify-favorites' );

		$attr = array(
			'href'             => esc_url( $this->get_remove_url() ),
			'class'            => 'astoundify-favorites-remove-list',
			'data-_nonce'      => wp_create_nonce( 'astoundify_favorites_remove_' . $this->get_id() ),
			'data-af_list_id'  => absint( $this->get_id() ),
		);

		$attr_str = astoundify_favorites_attr( $attr );

		return "<a {$attr_str}>{$text}</a>";
	}

	/**
	 * Create
	 * Term author added via hook in Favorite_Setup()
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments used to create a list.
	 * @return int|false Term ID on success. False if fail.
	 */
	public function create( $args = array() ) {
		$defaults = array(
			'list_name'        => '',
			'list_description' => '',
			'list_author'      => get_current_user_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Bail if no list name or no author.
		if ( ! $args['list_name'] || ! $args['list_author'] ) {
			return false;
		}

		// Insert term.
		$term_args = array(
			'slug'        => sanitize_title( md5( absint( $args['list_author'] ) ) . '_' . $args['list_name'] ),
			'description' => wp_kses_post( $args['list_description'] ),
		);

		$term_data = wp_insert_term( strip_tags( $args['list_name'] ), 'astoundify_favorite_list', $term_args );

		if ( is_wp_error( $term_data ) ) {
			return false;
		} elseif ( isset( $term_data['term_id'] ) ) {

			// Set term for this object.
			$this->term = get_term( absint( $term_data['term_id'] ), 'astoundify_favorite_list' );

			if ( $this->term && ! is_wp_error( $this->term ) ) {
				// Set author.
				add_term_meta( absint( $term_data['term_id'] ), 'list_author', absint( $args['list_author'] ), true );

				return absint( $term_data['term_id'] );
			}
		}

		return false;
	}

	/**
	 * Update
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments used to update a list.
	 */
	public function update( $args = array() ) {
		$defaults = array(
			'list_name'        => '',
			'list_description' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		// Bail if no list name (list name required).
		if ( ! $args['list_name'] ) {
			return false;
		}

		$term_args = array(
			'name'        => strip_tags( $args['list_name'] ),
			'description' => wp_kses_post( $list_description ),
		);

		$term_data = wp_update_term( absint( $this->get_id() ), 'astoundify_favorite_list', $term_args );

		if ( is_wp_error( $term_data ) ) {
			return false;
		} elseif ( isset( $term_data['term_id'] ) ) {
			$this->term = get_term( $term_data['term_id'], 'astoundify_favorite_list' );

			return $term_data['term_id'];
		}

		return false;
	}

	/**
	 * Remove
	 *
	 * @since 1.0.0
	 */
	public function remove() {
		$removed = wp_delete_term( $this->get_id(), 'astoundify_favorite_list' );

		if ( is_wp_error( $removed ) ) {
			return false;
		}

		$this->term = null;

		return $removed;
	}

}
