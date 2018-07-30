<?php
/**
 * Favorite List Setup.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Register List Taxonomy & Setup
 *
 * @since 1.0.0
 */
class Favorite_List_Setup {

	/**
	 * Favorite Post Type
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $post_type = 'astoundify_favorite';

	/**
	 * Favorite List Taxonomy
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public static $taxonomy  = 'astoundify_favorite_list';

	/**
	 * Register
	 *
	 * @since 1.0.0
	 */
	public static function register() {

		// Register taxonomy.
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );

		// Display author in term edit screen.
		add_action( self::$taxonomy . '_edit_form_fields', array( __CLASS__, 'author_field' ), 10, 2 );

		// Delete lists with user or reassign to other user.
		add_action( 'deleted_user', array( __CLASS__, 'delete_user' ), 10, 2 );

		// Create new lists on user registration.
		add_action( 'user_register', array( __CLASS__, 'create_default_lists' ) );
	}

	/**
	 * Register Taxonomy
	 *
	 * @since 1.0.0
	 * @link https://codex.wordpress.org/Function_Reference/register_taxonomy
	 */
	public static function register_taxonomy() {
		$args = array(
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => false,
			'labels'            => array(
				'name'                => __( 'Lists', 'astoundify-favorites' ),
				'singular_name'       => __( 'List', 'astoundify-favorites' ),
				'search_items'        => __( 'Search Items', 'astoundify-favorites' ),
				'all_items'           => __( 'All Items', 'astoundify-favorites' ),
				'parent_item'         => __( 'Parent Item', 'astoundify-favorites' ),
				'parent_item_colon'   => __( 'Parent Item Colon:', 'astoundify-favorites' ),
				'edit_item'           => __( 'Edit Item', 'astoundify-favorites' ),
				'update_item'         => __( 'Update Item', 'astoundify-favorites' ),
				'add_new_item'        => __( 'Add New Item', 'astoundify-favorites' ),
				'new_item_name'       => __( 'New Item Name', 'astoundify-favorites' ),
				'menu_name'           => __( 'List', 'astoundify-favorites' ),
			),
		);

		register_taxonomy( self::$taxonomy, self::$post_type, apply_filters( 'astoundify_favorite_list_register_taxonomy_args', $args ) );
	}

	/**
	 * Display Author
	 *
	 * @since 1.0.0
	 *
	 * @param object $tag      WP_Term object.
	 * @param string $taxonomy Term Taxonomy.
	 */
	public static function author_field( $tag, $taxonomy ) {
		$author_id = get_term_meta( $tag->term_id, 'list_author', true );
		$edit_url  = add_query_arg( 'user_id', $author_id, admin_url( 'user-edit.php' ) );
?>
<tr class="form-field">
	<th scope="row"><label for="list_author"><?php esc_attr_e( 'List Author', 'astoundify-favorites' ); ?></label></th>
	<td>
		<p><?php esc_attr_e( 'User ID:', 'astoundify-favorites' ); ?> <a target="_blank" href="<?php echo esc_url( $edit_url ); ?>"><?php echo absint( $author_id ); ?></a></p>
	</td>
</tr>
<?php
	}

	/**
	 * Delete Taxonomy With User
	 * Currently It's not reassign to another user for simplicity.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id       User ID.
	 * @param int $reassign User ID to reassign posts.
	 * @return void
	 */
	public static function delete_user( $id, $reassign ) {

		// Query all list owned by deleted user.
		$args = array(
			'taxonomy'     => self::$taxonomy,
			'hide_empty'   => false,
			'meta_key'     => 'list_author',
			'meta_value'   => $id,
		);
		$terms = get_terms( $args );
		if ( $terms && ! is_wp_error( $terms ) ) {

			// Loop each list.
			foreach ( $terms as $term ) {

				// Reassign to another user.
				if ( $reassign ) {
					update_term_meta( $term->term_id, 'list_author', $reassign );
				} else {
					wp_delete_term( $term->term_id, 'astoundify_favorite_list' );
				}
			}
		}
	}


	/**
	 * Create Default Favorite Lists on user registration.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 */
	public static function create_default_lists( $user_id ) {
		$default_lists = astoundify_favorites_get_option( 'default-lists', array() );

		// Bail if not set.
		if ( ! $default_lists || ! is_array( $default_lists ) ) {
			return;
		}

		// Insert terms.
		foreach ( $default_lists as $list_name ) {

			// Create list.
			$list = new Favorite_List( false );
			$list_id = $list->create( array(
				'list_name'        => $list_name,
				'list_description' => '',
				'list_author'      => $user_id,
			) );
		}
	}
}
