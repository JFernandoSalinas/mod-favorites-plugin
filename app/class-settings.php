<?php
/**
 * WordPress settings.
 *
 * Found in Settings > Favorites.
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Settings
 * @author Astoundify
 */

namespace Astoundify\Favorites;

/**
 * Settings
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Option group.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $options_group;

	/**
	 * Option name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $option_name;

	/**
	 * Settings slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $settings_slug;

	/**
	 * Hook.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $hook_suffix;

	/**
	 * Returns the instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new self;
		}
		return $instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set.
		$this->options_group  = 'astoundify_favorites';
		$this->settings_slug  = 'astoundify_favorites_settings';
		$this->hook_suffix    = 'settings_page_astoundify_favorites_settings';

		// Add settings.
		add_action( 'admin_menu', array( $this, 'create_settings_page' ) );

		// Register Settings and Fields.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add Settings as Favorite Post Type Sub Menu
	 *
	 * @since 1.0.0
	 */
	public function create_settings_page() {
		add_options_page(
			$page_title  = __( 'Favorites Settings', 'astoundify-favorites' ),
			$menu_title  = __( 'Favorites', 'astoundify-favorites' ),
			$capability  = 'manage_options',
			$menu_slug   = $this->settings_slug,
			$function    = array( $this, 'settings_page' )
		);
	}

	/**
	 * Settings Page
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Favorites Settings', 'astoundify-favorites' ); ?></h1>

	<form method="post" action="options.php">
		<?php $this->do_settings_sections( $this->settings_slug ); ?>
		<?php settings_fields( $this->options_group ); ?>
		<?php submit_button(); ?>
	</form>
</div><!-- wrap -->

<?php
	}

	/**
	 * Register Settings
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {

		// Settings.
		register_setting(
			$option_group      = $this->options_group,
			$option_name       = 'astoundify_favorites',
			$sanitize_callback = function( $data ) {
				$new_data = array();
				// Default list: Save as array (comma separated), no HTML allowed.
				$new_data['default-lists'] = isset( $data['default-lists'] ) ? array_map( 'trim', array_map( 'strip_tags', explode( ',', $data['default-lists'] ) ) ) : array();
				$new_data['dashboard-page'] = isset( $data['dashboard-page'] ) ? absint( $data['dashboard-page'] ) : '';
				$new_data['favorite-label-singular'] = isset( $data['favorite-label-singular'] ) ? esc_attr( $data['favorite-label-singular'] ) : __( 'Favorite', 'astoundify-favorites' );
				$new_data['favorite-label-plural'] = isset( $data['favorite-label-plural'] ) ? esc_attr( $data['favorite-label-plural'] ) : __( 'Favorites', 'astoundify-favorites' );
				$new_data['list-label-singular'] = isset( $data['list-label-singular'] ) ? esc_attr( $data['list-label-singular'] ) : __( 'List', 'astoundify-favorites' );
				$new_data['list-label-plural'] = isset( $data['list-label-plural'] ) ? esc_attr( $data['list-label-plural'] ) : __( 'Lists', 'astoundify-favorites' );
				return $new_data;
			}
		);

		// General Section.
		add_settings_section(
			$section_id        = 'astoundify_settings_section_general',
			$section_title     = __( 'General', 'astoundify-favorites' ),
			$callback_function = '__return_false',
			$settings_slug     = $this->settings_slug
		);

		add_settings_field(
			$field_id          = 'astoundify_settings_field_default_lists',
			$field_title       = __( 'Default Lists', 'astoundify-favorites' ),
			$callback_function = function() {
				$default_lists = astoundify_favorites_get_option( 'default-lists', array() );
				$default_lists = is_array( $default_lists ) ? implode( ', ', $default_lists ) : '';
				?>
				<p>
					<input type="text" name="astoundify_favorites[default-lists]" class="regular-text" value="<?php echo esc_attr( $default_lists ); ?>" />
				</p>
				<p class="description"><?php esc_attr_e( 'Separate lists with commas. This default lists will be created automatically for new users on registration.', 'astoundify-favorites' ); ?></p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_general'
		);

		// Pages Section.
		add_settings_section(
			$section_id        = 'astoundify_settings_section_pages',
			$section_title     = __( 'Pages', 'astoundify-favorites' ),
			$callback_function = '__return_false',
			$settings_slug     = $this->settings_slug
		);

		add_settings_field(
			$field_id          = 'astoundify_settings_field_dashboard_page',
			$field_title       = __( 'Dashboard Page', 'astoundify-favorites' ),
			$callback_function = function() {
				$args = array(
					'name'               => 'astoundify_favorites[dashboard-page]',
					'id'                 => 'astoundify_favorites_dashboard-page',
					'show_option_none'   => esc_html__( '&mdash; Select &mdash;', 'astoundify-favorites' ),
					'option_none_value'  => '',
					'selected'           => astoundify_favorites_get_option( 'dashboard-page' ),
				);
				?>

				<p><?php wp_dropdown_pages( $args ); // WPCS: XSS ok. ?></p>

				<p class="description"><?php esc_attr_e( 'Add [astoundify-favorites-dashboard] shortcode in selected page.', 'astoundify-favorites' ); ?></p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_pages'
		);

		// Labels/Strings Section.
		add_settings_section(
			$section_id        = 'astoundify_settings_section_labels',
			$section_title     = __( 'Labels', 'astoundify-favorites' ),
			$callback_function = '__return_false',
			$settings_slug     = $this->settings_slug
		);

		add_settings_field(
			$field_id          = 'astoundify_settings_field_favorite_singular',
			$field_title       = __( 'Favorite Singular', 'astoundify-favorites' ),
			$callback_function = function() {
				?>

				<p>
					<input type="text" name="astoundify_favorites[favorite-label-singular]" class="regular-text" value="<?php echo esc_attr( astoundify_favorites_get_option( 'favorite-label-singular', __( 'favorite', 'astoundify-favorites' ) ) ); ?>" />
				</p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_labels'
		);

		add_settings_field(
			$field_id          = 'astoundify_settings_field_favorite_plural',
			$field_title       = __( 'Favorite Plural', 'astoundify-favorites' ),
			$callback_function = function() {
				?>

				<p>
					<input type="text" name="astoundify_favorites[favorite-label-plural]" class="regular-text" value="<?php echo esc_attr( astoundify_favorites_get_option( 'favorite-label-plural', __( 'favorites', 'astoundify-favorites' ) ) ); ?>" />
				</p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_labels'
		);

		add_settings_field(
			$field_id          = 'astoundify_settings_field_list_singular',
			$field_title       = __( 'List Singular', 'astoundify-favorites' ),
			$callback_function = function() {
				?>

				<p>
					<input type="text" name="astoundify_favorites[list-label-singular]" class="regular-text" value="<?php echo esc_attr( astoundify_favorites_get_option( 'list-label-singular', __( 'list', 'astoundify-favorites' ) ) ); ?>" />
				</p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_labels'
		);

		add_settings_field(
			$field_id          = 'astoundify_settings_field_list_plural',
			$field_title       = __( 'List Plural', 'astoundify-favorites' ),
			$callback_function = function() {
				?>

				<p>
					<input type="text" name="astoundify_favorites[list-label-plural]" class="regular-text" value="<?php echo esc_attr( astoundify_favorites_get_option( 'list-label-plural', __( 'lists', 'astoundify-favorites' ) ) ); ?>" />
				</p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_labels'
		);

		// License Section.
		register_setting(
			$option_group      = $this->options_group,
			$option_name       = 'astoundify-favorites',
			$sanitize_callback = 'esc_attr'
		);

		add_settings_section(
			$section_id        = 'astoundify_settings_section_license',
			$section_title     = __( 'License', 'astoundify-favorites' ),
			$callback_function = '__return_false',
			$settings_slug     = $this->settings_slug
		);

		add_settings_field(
			$field_id          = 'astoundify-favorites',
			$field_title       = __( 'License', 'astoundify-favorites' ),
			$callback_function = function() {
				?>

				<p>
					<input type="text" name="astoundify-favorites" id="astoundify-favorites" class="regular-text" value="<?php echo esc_attr( get_option( 'astoundify-favorites', false ) ); ?>" />
				</p>

				<p class="description"><?php esc_attr_e( 'Enter your license key received during purchase to receive automatic update notifications.', 'astoundify-favorites' ); ?></p>

				<?php
			},
			$settings_slug     = $this->settings_slug,
			$section_id        = 'astoundify_settings_section_license'
		);
	}

	/**
	 * Sanitize
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Submitted data.
	 * @return array $new_data
	 */
	public function sanitize( $data ) {
		$new_data = array();
		$new_data['dashboard-page'] = isset( $data['dashboard-page'] ) ? absint( $data['dashboard-page'] ) : '';

		return $new_data;
	}

	/**
	 * Prints out all settings sections added to a particular settings page.
	 *
	 * @global $wp_settings_sections Storage array of all settings sections added to admin pages
	 * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
	 * @see \do_settings_sections() in wp-admin/includes/template.php
	 * @since 1.0.1
	 *
	 * @param string $page The slug name of the page whose settings sections you want to output.
	 */
	public function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}
		?>

<h1 class="settings-tab nav-tab-wrapper wp-clearfix">
	<?php foreach ( (array) $wp_settings_sections[ $page ] as $section ) : ?>
		<a href="#section_<?php echo esc_attr( $section['id'] ); ?>" class="nav-tab"><?php echo esc_html( $section['title'] ? $section['title'] : __( 'Tab', 'astoundify-favorites' ) ); ?></a>
	<?php endforeach; ?>
</h1><!-- .settings-tab -->

<?php foreach ( (array) $wp_settings_sections[ $page ] as $section ) : ?>
	<div id="section_<?php echo esc_attr( $section['id'] ); ?>" class="settings-section">

		<?php
		echo '<table class="form-table">';
		if ( isset( $wp_settings_fields ) && isset( $wp_settings_fields[ $page ] ) && isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
			do_settings_fields( $page, $section['id'] );
		}
		echo '</table>';
		?>

	</div><!-- .settings-section -->
<?php endforeach; ?>

<script type="text/javascript">
jQuery( document ).ready( function($) {
	// Hide all section.
	$( '.settings-section' ).hide();

	// Get active tab.
	var active_tab = '';
	if ( typeof( localStorage ) !== 'undefined' ) {
		active_tab = localStorage.getItem( pagenow + '_active_tab' );
	}
	if ( active_tab !== '' && $( active_tab ).length ) {
		$( active_tab ).fadeIn();
		$( '.nav-tab[href="' + active_tab + '"]' ).addClass( 'nav-tab-active' );
	} else {
		$( '.settings-section:first' ).fadeIn();
		$( '.settings-tab .nav-tab:first' ).addClass( 'nav-tab-active' );
	}

	// Set active tab.
	$( '.settings-tab .nav-tab' ).click( function(e) {
		e.preventDefault();
		$( '.settings-tab .nav-tab' ).removeClass( 'nav-tab-active' );
		$( this ).addClass( 'nav-tab-active' ).blur();
		$( '.settings-section' ).hide();
		$( $( this ).attr( 'href' ) ).fadeIn();
		if ( typeof( localStorage ) !== 'undefined' ) {
			localStorage.setItem( pagenow + '_active_tab', $( this ).attr( 'href' ) );
		}
	});

});
</script>

		<?php
	}

}
