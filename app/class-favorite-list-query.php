<?php
/**
 * Favorite List Query.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Favorite List Query
 *
 * @since 1.0.0
 */
class Favorite_List_Query {

	/**
	 * User ID
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $user_id;

	/**
	 * Favorite Lists
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $lists = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query args.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'user_id'  => get_current_user_id(),
		);
		$args = wp_parse_args( $args, $defaults );

		// Set var.
		$this->user_id = $args['user_id'];

		// Load query.
		$this->query();
	}

	/**
	 * Query
	 *
	 * @since 1.0.0
	 */
	public function query() {

		// Get terms args.
		$args = array(
			'taxonomy'     => 'astoundify_favorite_list',
			'hide_empty'   => false,
		);
		if ( $this->user_id ) {
			$args['meta_key']   = 'list_author';
			$args['meta_value'] = $this->user_id;
		}

		// Get terms.
		$terms = get_terms( $args );

		if ( $terms && ! is_wp_error( $terms ) ) {

			// Loop each terms and set lists.
			foreach ( $terms as $term ) {
				$this->lists[] = new Favorite_List( $term );
			}
		}
	}

	/**
	 * Create List URL
	 *
	 * @since 1.0.0
	 */
	public function get_create_list_url() {
		$dashboard_url = astoundify_favorites_dashboard_url();

		if ( ! $dashboard_url ) {
			return '';
		}

		return esc_url( add_query_arg( 'af_list_id', 'new', $dashboard_url ) );
	}

	/**
	 * Create List Link
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Anchor text.
	 */
	public function get_create_list_link( $text = '' ) {
		if ( ! $this->get_create_list_url() ) {
			return '';
		}

		// Translators: %s is list.
		$text = $text ? $text : sprintf( __( '+ Create new %s', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) );

		$attr_str = astoundify_favorites_attr( array(
			'href'             => esc_url( $this->get_create_list_url() ),
			'class'            => 'astoundify-favorites-create-list',
			'data-_nonce'      => wp_create_nonce( 'astoundify_favorites_create_list' ),
		) );

		return "<a {$attr_str}>{$text}</a>";
	}
}
