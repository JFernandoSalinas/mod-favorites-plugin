<?php
/**
 * Automatic output.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Content
 *
 * @since 1.0.0
 */
class Content {

	/**
	 * Register
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		add_action( 'wp_head', array( __CLASS__, 'wp_head' ), 999 );
	}

	/**
	 * WP Head Action.
	 * The content filters is added here (late), so it will not filter meta description.
	 *
	 * @since 1.2.0
	 */
	public static function wp_head() {

		// Content filter.
		add_filter( 'the_content', array( __CLASS__, 'the_content' ) );
	}

	/**
	 * Add favorite link before post content
	 * To disable this filter simply set "astoundify_favorites_content_filter" to false.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content The object content.
	 * @return string $content
	 */
	public static function the_content( $content ) {

		/* Do not load in admin. */
		if ( is_admin() ) {
			return $content;
		}

		/* Filter to disable this */
		if ( is_singular() && apply_filters( 'astoundify_favorites_content_filter', true, $content ) ) {

			/* Link */
			$link = astoundify_favorites_link( get_the_ID() , '<p>', '</p>' );

			/* Add link before content */
			return $link . $content;
		}

		return $content;
	}

}
