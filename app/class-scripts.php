<?php
/**
 * Plugin scripts.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Assets
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Scripts
 *
 * @since 1.0.0
 */
class Scripts {

	/**
	 * Register
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'scripts' ) );
	}

	/**
	 * Register and Load Scripts
	 *
	 * @since 1.0.0
	 */
	public static function scripts() {
		// CSS.
		wp_enqueue_style( 'astoundify-favorites-vendor', ASTOUNDIFY_FAVORITES_URL . 'public/css/vendor.min.css', array(), ASTOUNDIFY_FAVORITES_VERSION );
		wp_enqueue_style( 'astoundify-favorites', ASTOUNDIFY_FAVORITES_URL . 'public/css/favorites.min.css', array( 'astoundify-favorites-vendor' ), ASTOUNDIFY_FAVORITES_VERSION );

		// JS.
		wp_enqueue_script( 'astoundify-favorites-vendor', ASTOUNDIFY_FAVORITES_URL . 'public/js/vendor.min.js', array( 'jquery' ), ASTOUNDIFY_FAVORITES_VERSION, true );
		wp_enqueue_script( 'astoundify-favorites', ASTOUNDIFY_FAVORITES_URL . 'public/js/favorites.min.js', array( 'astoundify-favorites-vendor', 'jquery', 'wp-util' ), ASTOUNDIFY_FAVORITES_VERSION, true );

		// i18n.
		wp_localize_script( 'astoundify-favorites', 'astoundifyFavorites', array(
			'i18n' => array(
				'confirmRemove'      => __( 'Are you sure?', 'astoundify-favorites' ),
			),
			'config' => array(
				'popupHtml'          => '<div class="astoundify-favorites-popup">%%CONTENT%%</div>',
			),
		) );
	}
}
