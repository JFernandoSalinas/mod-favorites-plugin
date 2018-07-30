<?php
/**
 * Favorite Query.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Favorite Query.
 *
 * @since 1.0.0
 */
class Favorite_Query {

	/**
	 * The number of favorites to load per page.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $item_per_page;

	/**
	 * Current Page
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $current_page;

	/**
	 * List ID
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $list_id;

	/**
	 * User ID
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $user_id;

	/**
	 * Target ID
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $target_id;

	/**
	 * Fields
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $fields;

	/**
	 * Favorites
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $favorites = array();

	/**
	 * Total Pages
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $total_pages;

	/**
	 * Pages
	 *
	 * @var array
	 * @since 1.0.0
	 */
	public $pages;

	/**
	 * Total items
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $total_items;

	/**
	 * Create a new WP_Query.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Modify default arguments.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'user_id'        => get_current_user_id(),
			'item_per_page'  => 25,
			'list_id'        => '',
			'current_page'   => 1,
			'target_id'      => '',
			'target_type'    => false,
			'fields'         => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$this->user_id       = $args['user_id'];
		$this->item_per_page = $args['item_per_page'];
		$this->current_page  = absint( $args['current_page'] ) ? absint( $args['current_page'] ) : 1;
		$this->list_id       = $args['list_id'];
		$this->target_id     = $args['target_id'];
		$this->target_type   = $args['target_type'] ? astoundify_favorites_sanitize_target_type( $args['target_type'] ) : false;
		$this->fields        = $args['fields'];

		$this->query();
	}

	/**
	 * Query
	 *
	 * @since 1.0.0
	 */
	public function query() {

		$args = array(
			'post_type'      => 'astoundify_favorite',
			'posts_per_page' => intval( $this->item_per_page ),
			'paged'          => $this->current_page,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'      => '_target_id', // Need to have have target.
					'value'    => '',
					'compare'  => '!=',
				),
			),
		);

		// Target Type.
		if ( $this->target_type ) {
			if ( 'post' === $this->target_type ) {
				$args['meta_query'][] = array(
					'key'      => '_target_type',
					'compare'  => 'NOT EXISTS',
				);
			} else {
				$args['meta_query'][] = array(
					'key'      => '_target_type',
					'value'    => $this->target_type,
					'compare'  => '=',
				);
			}
		}

		// Author.
		if ( $this->user_id ) {
			$args['author'] = absint( $this->user_id );
		}

		// Target object ID.
		if ( $this->target_id ) {
			$args['meta_value'] = $this->target_id;
		}

		// List ID (WP_Term).
		if ( $this->list_id ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'astoundify_favorite_list',
					'field'    => 'term_id',
					'terms'    => $this->list_id,
				),
			);
		}

		// Fields.
		if ( $this->fields ) {
			$args['fields'] = $this->fields;
		}

		// Create the query.
		$the_query = new \WP_Query( $args );

		// Update instance properties with query data.
		$this->total_items  = $the_query->found_posts;
		$this->total_pages  = ceil( $this->total_items / $this->item_per_page );
		$this->pages        = range( 1, $this->total_pages );
		$this->favorites    = array();

		// IDs only.
		if ( 'ids' === $this->fields ) {
			$this->favorites = $the_query->posts;

			return;
		}

		// Loop: load post data as favorites.
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				global $post;

				$this->favorites[] = astoundify_favorites_get_favorite( $post );
			}

			wp_reset_postdata();
		}
	}

	/**
	 * Pagination
	 *
	 * @since 1.0.0
	 */
	public function pagination() {
		if ( 2 > $this->total_pages ) {
			return;
		}
?>

<nav class="astoundify-favorites-dashboard-pagination wp-link-pages" role="navigation">

	<?php foreach ( $this->pages as $page ) : ?>

		<?php if ( absint( $page ) === absint( $this->current_page ) ) : ?>

			<span class="page-numbers"><?php echo absint( $page ); ?></span>

		<?php else : ?>

			<a class="page-numbers" href="<?php echo esc_url( $this->pagination_item_url( absint( $page ) ) ); ?>"><?php echo absint( $page ); ?></a>

		<?php endif; ?>

	<?php endforeach; ?>

</nav><!-- .astoundify-favorites-dashboard-pagination.wp-link-pages -->

<?php
	}

	/**
	 * Pageination Item URL
	 *
	 * @since 1.0.0
	 *
	 * @param int $page The current page number.
	 */
	public function pagination_item_url( $page ) {
		$page_url = astoundify_favorites_dashboard_url();

		if ( get_option( 'permalink_structure' ) ) {
			$url = user_trailingslashit( trailingslashit( $page_url ) . $page );
		} else {
			$url = add_query_arg( 'page', $page, $page_url );
		}

		// Always view favorite.
		$url = add_query_arg( 'af_view', 'favorites', $url );

		// Display list archive.
		if ( get_query_var( 'af_list_id' ) ) {
			$url = add_query_arg( 'af_list_id', get_query_var( 'af_list_id' ), $url );
		}

		return esc_url( $url );
	}

}
