<?php
/**
 * Setup main plugin data schema.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Register Post Type & Setup
 *
 * @since 1.0.0
 */
class Favorite_Setup {

	/**
	 * The slug of the post type to create.
	 *
	 * @var string $post_type
	 * @since 1.0.0
	 */
	public static $post_type = 'astoundify_favorite';

	/**
	 * Register
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		// Register post type.
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );

		// Favorite Title: Use target title.
		add_filter( 'the_title', array( __CLASS__, 'favorite_title' ), 10, 2 );

		// Meta Box.
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

		// Delete Post.
		add_action( 'delete_post', array( __CLASS__, 'delete_favorite_with_target' ) );
	}

	/**
	 * Register Post Type
	 *
	 * @since 1.0.0
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public static function register_post_type() {
		$args = array(
			'description'           => '',
			'public'                => false, // Private.
			'publicly_queryable'    => false,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => false,
			'exclude_from_search'   => false, // Need this for WP_Query.
			'show_ui'               => defined( 'WP_DEBUG' ) && WP_DEBUG,  // Show UI on Debug.
			'show_in_menu'          => true,
			'menu_position'         => 3,
			'menu_icon'             => 'dashicons-heart',
			'can_export'            => false,
			'delete_with_user'      => true,
			'hierarchical'          => false,
			'has_archive'           => false,
			'query_var'             => true,
			'rewrite'               => false,
			'capability_type'       => 'post',
			'supports'              => array( 'editor', 'author' ),
			'labels'                => array(
				'name'                      => __( 'Favorites', 'astoundify-favorites' ),
				'singular_name'             => __( 'Favorite', 'astoundify-favorites' ),
				'add_new'                   => __( 'Add New', 'astoundify-favorites' ),
				'add_new_item'              => __( 'Add New Item', 'astoundify-favorites' ),
				'edit_item'                 => __( 'Edit Item', 'astoundify-favorites' ),
				'new_item'                  => __( 'New Item', 'astoundify-favorites' ),
				'all_items'                 => __( 'All Items', 'astoundify-favorites' ),
				'view_item'                 => __( 'View Item', 'astoundify-favorites' ),
				'search_items'              => __( 'Search Items', 'astoundify-favorites' ),
				'not_found'                 => __( 'Not Found', 'astoundify-favorites' ),
				'not_found_in_trash'        => __( 'Not Found in Trash', 'astoundify-favorites' ),
				'menu_name'                 => __( 'Favorites', 'astoundify-favorites' ),
			),
		);

		/* Register post type */
		register_post_type( self::$post_type, apply_filters( self::$post_type . '_register_post_type_args', $args ) );
	}


	/**
	 * Favorite Title
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The title string used when creating a favorite WordPress object.
	 * @param int    $id The ID of the WordPress object.
	 * @return string $title
	 */
	public static function favorite_title( $title, $id = null ) {
		if ( ! $id || get_post_type( $id ) !== self::$post_type ) {
			return $title;
		}

		$favorite = astoundify_favorites_get_favorite( $id );
		return $favorite->get_title();
	}

	/**
	 * Add Meta Boxes
	 * For debugging. The post type will be hidden later
	 *
	 * @since 2.0.0
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			$id         = 'astoundify_favorite_meta_box',
			$title      = __( 'Favorites Info', 'astoundify-favorites' ),
			$callback   = array( __CLASS__, 'favorite_info_meta_box' ),
			$screen     = array( self::$post_type ),
			$context    = 'side'
		);
	}

	/**
	 * Meta Box Callback
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The current post obejct.
	 */
	public static function favorite_info_meta_box( $post ) {
		$post_id   = $post->ID;
		$target_id = get_post_meta( $post_id, '_target_id', true );
		$target_type = astoundify_favorites_sanitize_target_type( get_post_meta( $post_id, '_target_type', true ) );
		$target = astoundify_favorites_get_target( $target_id, $target_type );
?>

<h4><?php esc_html_e( 'Target:', 'astoundify-favorites' ); ?></h4>

<?php if ( $target_id ) : ?>
	<p><?php echo $target->get_link(); ?></p>
<?php else : ?>
	<p><?php esc_html_e( 'N/A', 'astoundify-favorites' ); ?></p>
<?php endif; ?>

<?php
	}

	/**
	 * Delete Favorite if Target Deleted
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The ID of the object that was deleted.
	 */
	public static function delete_favorite_with_target( $post_id ) {
		// Check post type to prevent loop.
		if ( get_post_type( $post_id ) === self::$post_type ) {
			return;
		}

		global $wpdb;

		// Get all favorites and delete.
		$favorite_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", '_target_id', $post_id ) );

		if ( ! $favorite_ids ) {
			return;
		}

		foreach ( $favorite_ids as $favorite_id ) {
			wp_delete_post( $favorite_id, true );
		}
	}
}
