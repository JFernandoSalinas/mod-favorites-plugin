<?php
/**
 * Favorite Target.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * A single favorite target item.
 *
 * @since 1.3.0
 */
class Favorite_Target {

	/**
	 * Target object.
	 *
	 * @var object
	 * @since 1.3.0
	 */
	public $target = null;

	/**
	 * Target type.
	 *
	 * @var object
	 * @since 1.3.0
	 */
	public $type = 'post';

	/**
	 * Constructor
	 *
	 * @since 1.3.0
	 *
	 * @param object|int|false $target Target object.
	 */
	public function __construct( $target = null ) {
		// Bail if false.
		if ( false === $target ) {
			return;
		}

		// Is a post object/ID, use it.
		if ( is_a( $target, 'WP_Post' ) ) {
			$this->target = $target;
		} elseif ( is_numeric( $target ) ) {
			$this->target = get_post( $target );
		}

		// Bail if we can't find a WordPress object to use.
		if ( ! $this->target ) {
			return;
		}
	}

	/**
	 * Get Type
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get Object. Original WP object.
	 * Can be WP_Post, WP_User, WP_Term, etc.
	 *
	 * @since 1.3.0
	 *
	 * @return object|false
	 */
	public function get_object() {
		if ( ! $this->target ) {
			return false;
		}

		return $this->target;
	}

	/**
	 * Get ID.
	 *
	 * @since 1.3.0
	 *
	 * @return int
	 */
	public function get_id() {
		if ( ! $this->get_object() ) {
			return 0;
		}

		return absint( $this->get_object()->ID );
	}

	/**
	 * Get title
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function get_title() {
		if ( ! $this->get_object() ) {
			return '';
		}

		$title = get_the_title( $this->get_object() );
		return $title ? $title : esc_html__( 'N/A', 'astoundify-favorites' );
	}

	/**
	 * Get permalink
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function get_permalink() {
		if ( ! $this->get_object() ) {
			return '';
		}

		return ( 'publish' === get_post_status( $this->get_id() ) ) ? esc_url( get_permalink( $this->get_object() ) ) : '';
	}

	/**
	 * Get link HTML. Will return span element if object has no permalink.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function get_link() {
		if ( ! $this->get_object() ) {
			return '';
		}

		$title = $this->get_title();

		$el   = 'span';
		$attr = array(
			'class' => 'astoundify-favorites-target-link',
		);

		if ( $this->get_permalink() ) {
			$el           = 'a';
			$attr['href'] = $this->get_permalink();
		}

		$attr_str = astoundify_favorites_attr( $attr );

		$link = "<{$el} {$attr_str}>{$title}</{$el}>";

		return apply_filters( 'astoundify_favorites_get_target_link', $link, $this->get_object() );
	}

	/**
	 * Get count (cache)
	 *
	 * @since 1.3.0
	 *
	 * @param bool $recalculate Force recalculate. Default to false.
	 * @return string
	 */
	public function get_count( $recalculate = false ) {
		if ( ! $this->get_object() ) {
			return 0;
		}

		$count = absint( get_post_meta( $this->get_id(), '_astoundify_favorites_count', true ) );

		if ( ! $count || $recalculate ) {
			$count = $this->update_count();
		}

		return absint( $count );
	}

	/**
	 * Update count
	 *
	 * @since 1.3.0
	 *
	 * @return int|false
	 */
	public function update_count() {
		$args = array(
			'target_id'     => $this->get_id(),
			'target_type'   => $this->get_type(),
			'fields'        => 'ids',
			'item_per_page' => -1,
			'user_id'       => false,
		);

		$favorite_query = new Favorite_Query( $args );
		$count          = $favorite_query->total_items;

		update_post_meta( $this->get_id(), '_astoundify_favorites_count', absint( $count ) );

		return absint( $count );
	}

	/**
	 * Reset Count
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function reset_count() {
		if ( ! $this->get_object() ) {
			return false;
		}

		return delete_post_meta( $this->get_id(), '_astoundify_favorites_count' );
	}
}
