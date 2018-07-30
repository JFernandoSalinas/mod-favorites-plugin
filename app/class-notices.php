<?php
/**
 * Persistent notifications.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Notices
 *
 * @since 1.0.0
 */
class Notices {

	/**
	 * Get Notices
	 *
	 * @since 1.0.0
	 */
	public static function get() {
		if ( ! is_user_logged_in() ) {
			return array();
		}

		$notices = get_transient( 'astfav_notices_' . get_current_user_id() );
		$notices = is_array( $notices ) ? $notices : array();

		return $notices;
	}

	/**
	 * Display Notices & Clear
	 *
	 * @since 1.0.0
	 */
	public static function display() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		ob_start();
?>

<div class="astoundify-favorites-notices">
	<?php echo self::messages(); // WPCS: xss ok. ?>
	<?php self::clear(); ?>
</div><!-- .astoundify-favorites-notices -->

<?php
		return ob_get_clean();
	}

	/**
	 * Get Messages
	 *
	 * @since 1.0.0
	 */
	public static function messages() {
		ob_start();
		$notices = self::get();

		foreach ( $notices as $notice ) {
?>

	<p class="astoundify-favorites-notice astoundify-favorites-notice-<?php echo esc_attr( $notice['type'] ); ?>"><?php echo wp_kses_post( $notice['message'] ); ?></p>

<?php
		}

		return ob_get_clean();
	}

	/**
	 * Add Notices
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The notice text.
	 * @param string $type The type of notice.
	 * @param string $id A unique notice ID.
	 */
	public static function add( $message, $type = 'error', $id = '' ) {
		if ( ! is_user_logged_in() || ! $message ) {
			return array();
		}

		$notices = self::get();

		if ( $id ) {
			$notices[ $id ] = array(
				'message' => wp_kses_post( $message ),
				'type'    => esc_attr( $type ),
			);
		} else {
			$notices[] = array(
				'message' => wp_kses_post( $message ),
				'type'    => esc_attr( $type ),
			);
		}

		return set_transient( 'astfav_notices_' . get_current_user_id(), $notices );
	}

	/**
	 * Add Error Notices
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The notice text.
	 * @param string $id A unique notice ID.
	 */
	public static function add_error( $message, $id = '' ) {
		self::add( $message, 'error' );
	}

	/**
	 * Add Success Notices
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The notice text.
	 * @param string $id A unique notice ID.
	 */
	public static function add_success( $message, $id = '' ) {
		self::add( $message, 'success' );
	}

	/**
	 * Add Info Notices
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The notice text.
	 * @param string $id A unique notice ID.
	 */
	public static function add_info( $message, $id = '' ) {
		self::add( $message, 'info' );
	}

	/**
	 * Clear Notices
	 *
	 * @since 1.0.0
	 */
	public static function clear() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return delete_transient( 'astfav_notices_' . get_current_user_id() );
	}

}
