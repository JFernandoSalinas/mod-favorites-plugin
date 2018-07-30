<?php
/**
 * Functions
 *
 * @since 1.0.0
 *
 * @package Favorites
 * @category Functions
 * @author Astoundify
 */

/**
 * Get Favorite Object
 *
 * @since 1.3.0
 *
 * @param object|int|false $post The WordPress object.
 * @param int              $check_author User ID.
 */
function astoundify_favorites_get_favorite( $post, $check_author = false ) {
	if ( is_a( $post, 'WP_Post' ) ) {
		$post = $post;
	} elseif ( is_numeric( $post ) ) {
		$post = get_post( $post );
	} else {
		$post = false;
	}

	if ( ! $post ) {
		return false;
	}

	// Base classname.
	$classname = '\Astoundify\Favorites\Favorite';

	// Filter.
	$classname = apply_filters( 'astoundify_favorites_get_favorite_classname', $classname, $post );

	try {
		return new $classname( $post, $check_author );
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Favorite Query.
 *
 * @since 1.2.0
 *
 * @param array $args Query Args.
 * @return array Array of favorite object.
 */
function astoundify_favorites_get_favorites( $args = array() ) {
	$_favorites = array();
	$favorites = new \Astoundify\Favorites\Favorite_Query( $args );
	foreach ( $favorites->favorites as $favorite ) {
		$_favorites["{$favorite->get_id()}_{$favorite->get_target_type()}"] = $favorite;
	}
	return $_favorites;
}

/**
 * Get Target
 *
 * @since 1.3.0
 *
 * @param int    $target_id   Target ID.
 * @param string $target_type Target Type. Default to "post".
 * @return object|false
 */
function astoundify_favorites_get_target( $target_id, $target_type = 'post' ) {

	// Target type.
	$target_type = astoundify_favorites_sanitize_target_type( $target_type );

	// Base classname.
	$classname = '\Astoundify\Favorites\Favorite_Target';

	// Filter.
	$classname = apply_filters( 'astoundify_favorites_get_favorite_target_classname', $classname, $target_type, $target_id );

	try {
		return new $classname( $target_id );
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Supported Post Types
 *
 * @since 1.0.0
 */
function astoundify_favorites_post_types() {
	return apply_filters( 'astoundify_favorites_post_types', array(
		'post',
	) );
}

/**
 * Get Options
 *
 * @since 1.0.0
 *
 * @param string $option Required.
 * @param string $default Optional.
 * @param string $option_name Optional default to `astoundify_favorites`.
 * @return mixed
 */
function astoundify_favorites_get_option( $option, $default = '', $option_name = 'astoundify_favorites' ) {
	if ( ! $option ) {
		return false;
	}

	$get_option = get_option( $option_name );

	if ( ! is_array( $get_option ) ) {
		return $default;
	}

	if ( isset( $get_option[ $option ] ) ) {
		return $get_option[ $option ];
	} else {
		return $default;
	}
}

/**
 * Update Option
 *
 * @since 1.3.0
 *
 * @param string       $key         Option key.
 * @param string|array $value       Option value.
 * @param string       $option_name Option name.
 * @return bool
 */
function astoundify_favorites_update_option( $key, $value, $option_name = 'astoundify_favorites' ) {
	// Sanitize Key.
	$key = sanitize_key( $key );
	if ( ! $key ) {
		return false;
	}

	// Filter to sanitize value.
	$value = apply_filters( "astoundify_favorites_update_option_{$key}", $value, $option_name );

	// Add to option data.
	$data = get_option( $option_name, array() );
	$data = is_array( $data ) ? $data : array();
	$data[ $key ] = $value;

	// Update it.
	return update_option( $option_name,  $data );
}


/**
 * Get Template File : For PHP File
 *
 * @since 1.0.0
 * @link https://developer.wordpress.org/reference/functions/locate_template/
 * @link https://developer.wordpress.org/reference/functions/load_template/
 *
 * @param string $template_name The filename of the template to load.
 * @param array  $data Variable to use in template.
 * @return void
 */
function astoundify_favorites_get_template( $template_name, $data = array() ) {
	// Get theme template file.
	$template = locate_template( "astoundify-favorites/{$template_name}.php", false, false );

	// Theme template file not found, use default plugin template.
	if ( ! $template ) {
		$template = trailingslashit( ASTOUNDIFY_FAVORITES_PATH . 'resources/templates' ) . $template_name . '.php';
	}

	$template = apply_filters( 'astoundify_favorites_get_template', $template, $template_name );

	// Check file exists.
	if ( file_exists( $template ) ) {

		// Filter data.
		$data = apply_filters( 'astoundify_favorite_template_data', $data, $template_name );

		// Extract vars for easy use.
		if ( $data && is_array( $data ) ) {
			extract( $data );
		}

		// Load template.
		include( $template );
	}
}

/**
 * See if a post is favorited by a user
 *
 * @since 1.0.0
 * @link https://developer.wordpress.org/reference/functions/get_posts/
 *
 * @param  int $target_id Target Post ID.
 * @param  int $user_id Favorite Author User ID.
 * @return bool|int False if not favorited, Fav ID if already favorited
 */
function astoundify_favorites_is_favorited( $target_id, $user_id, $target_type = 'post' ) {
	// User need to logged in.
	if ( ! $target_id || ! $user_id ) {
		return false;
	}

	$args = array(
		'target_id'      => $target_id,
		'target_type'    => astoundify_favorites_sanitize_target_type( $target_type ),
		'user_id'        => $user_id,
		'item_per_page'  => 1,
		'fields'         => 'ids',
	);

	$favorite_query = new \Astoundify\Favorites\Favorite_Query( $args );

	return $favorite_query->favorites ? $favorite_query->favorites[0] : false;
}

/**
 * Favorite Count
 *
 * @todo: Need target object with add/update count.
 *
 * @since 1.0.0
 *
 * @param int    $target_id   ID of the WordPress object to query.
 * @param string $target_type Target type.
 * @return int
 */
function astoundify_favorites_count( $target_id, $target_type = 'post' ) {
	$target = astoundify_favorites_get_target( $target_id, $target_type );
	return absint( $target->get_count() );
}

/**
 * Favorite Count HTML
 *
 * @since 1.0.0
 *
 * @param int    $target_id   ID of the WordPress object to query.
 * @param string $target_type Taget type.
 * @return string
 */
function astoundify_favorites_count_html( $target_id, $target_type = 'post' ) {
	return '<span class="astoundify-favorites-count">' . astoundify_favorites_count( $target_id, $target_type ) . '</span>';
}

/**
 * User Favorites Count
 *
 * @since 1.0.0
 *
 * @param int $user_id ID of user.
 * @return int
 */
function astoundify_favorites_user_favorites_count( $user_id = 0 ) {

	// Use current user if not set.
	$user_id = $user_id ? $user_id : get_current_user_id();

	// If user not logged in, bail.
	if ( ! $user_id ) {
		return 0;
	}

	// Get count cache in user meta.
	$count = get_user_meta( $user_id, '_astoundify_favorites_user_count', true );

	if ( $count ) {
		return $count;
	}

	// Get user favorites data.
	$args = array(
		'user_id' => $user_id,
		'item_per_page' => 1, // minimal.
		'fields' => 'ids',
	);
	$favorite_query = new \Astoundify\Favorites\Favorite_Query( $args );
	$count = intval( $favorite_query->total_items );

	// Update user meta.
	update_user_meta( $user_id, '_astoundify_favorites_user_count', $count );

	return $count;
}

/**
 * List Drop Down
 *
 * @since 1.0.0
 *
 * @param array $args Modify default arguments.
 */
function astoundify_favorites_list_field( $args = array() ) {
	$defaults = array(
		'selected'          => '',
		// Translators: %s is list.
		'show_option_none'  => sprintf( __( '- Select %s -', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ),
		// Translators: %s is list.
		'show_option_new'   => sprintf( __( '+ Create new %s', 'astoundify-favorites' ), astoundify_favorites_label( 'list' ) ),
		// Translators: %s is List.
		'placeholder_new'   => sprintf( __( '%s name', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ),
	);

	$args = wp_parse_args( $args, $defaults );

	$favorite_list_query = new \Astoundify\Favorites\Favorite_List_Query();
?>

	<?php if ( empty( $favorite_list_query->lists ) && $args['show_option_new'] ) : ?>

		<div class="astoundify-favorites-list-field">

			<div class="astoundify_favorites_list_new" style="display:block;">
				<input type="text" name="list_new" value="" placeholder="<?php echo esc_attr( $args['show_option_new'] ); ?>" autocomplete="off"/>
				<input type="hidden" name="list_id" value="new">
			</div><!-- .astoundify_favorites_list_new -->

		</div><!-- .astoundify-favorites-list-field -->

	<?php else : ?>

		<div class="astoundify-favorites-list-field">

			<select name="list_id" class="astoundify_favorites_list" autocomplete="off">

				<?php if ( $args['show_option_none'] ) : ?>
					<option value=""><?php echo esc_html( $args['show_option_none'] ); ?></option>
				<?php endif; ?>

				<?php foreach ( $favorite_list_query->lists as $list ) : ?>
					<option class="level-0" value="<?php echo esc_attr( $list->get_id() ); ?>" <?php selected( $list->get_id(), $args['selected'] ); ?>><?php echo esc_html( $list->get_name() ); ?></option>
				<?php endforeach; ?>

				<?php if ( $args['show_option_new'] ) : ?>
					<option value="new"><?php echo esc_html( $args['show_option_new'] ); ?></option>
				<?php endif; ?>

			</select><!-- .astoundify_favorites_list -->

			<?php if ( $args['show_option_new'] ) : ?>
				<div class="astoundify_favorites_list_new" style="display:none;">
					<input type="text" name="list_new" value="" placeholder="<?php echo esc_attr( $args['placeholder_new'] ); ?>" autocomplete="off"/>
				</div><!-- .astoundify_favorites_list_new -->
			<?php endif; ?>

		</div><!-- .astoundify-favorites-list-field -->

	<?php endif; ?>

	<?php
}

/**
 * Note Field
 *
 * @since 1.0.0
 *
 * @param array $args Modify default arguments.
 */
function astoundify_favorites_note_field( $args = array() ) {
	$defaults = array(
		'placeholder' => __( 'Add note...', 'astoundify-favorites' ),
		'note'        => '',
		'rows'        => '2',
	);

	$args = wp_parse_args( $args, $defaults );
?>

<textarea class="astoundify-favorites-note-field" autocomplete="off" name="note" rows="<?php esc_attr( $args['rows'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"><?php echo esc_textarea( $args['note'] ); ?></textarea>

<?php
}

/**
 * List Name Field.
 * This is for form-list-edit.php template.
 *
 * @since 1.0.0
 *
 * @param array $args Modify default arguments.
 */
function astoundify_favorites_list_name_field( $args = array() ) {
	$defaults = array(
		// Translators: %s is List.
		'placeholder' => sprintf( __( '%s Name', 'astoundify-favorites' ), astoundify_favorites_label( 'List' ) ),
		'list_name'   => '',
	);

	$args = wp_parse_args( $args, $defaults );
?>

<input type="text" name="list_name" value="<?php echo esc_attr( sanitize_text_field( $args['list_name'] ) ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" autocomplete="off" required="required"/>

<?php
}

/**
 * Get text for a button depending on the current count.
 *
 * @since 1.3.0
 *
 * @param int    $count        Current count
 * @param int    $target_id    Target Post ID.
 * @param string $target_type  Target type.
 * @param bool   $is_Favorited Favorited or not.
 * @return string
 */
function astoundify_favorites_get_link_text( $count, $target_id, $target_type, $is_favorited ) {
	if ( 2 > $count ) {
		// Translators: %1$s heart SVG, %2$s Number of Favorite, %3$s Favorite singular label.
		$text = sprintf( __( '%1$s %2$s %3$s', 'astoundify-favorites' ), astoundify_favorites_get_svg( 'heart' ), $count, astoundify_favorites_label( 'Favorite' ) );
	} else {
		// Translators: %1$s heart SVG, %2$s Number of Favorite, %3$s Favorite plural label.
		$text = sprintf( __( '%1$s %2$s %3$s', 'astoundify-favorites' ), astoundify_favorites_get_svg( 'heart' ), $count, astoundify_favorites_label( 'Favorites' ) );
	}

	return apply_filters( 'astoundify_favorites_link_text', $text, $target_id, $is_favorited, $target_type );
}

/**
 * Favorite URL.
 *
 * @since 1.3.0
 *
 * @param bool   $is_favorited Favorited or not.
 * @param int    $target_id    Target Post ID.
 * @param string $target_type  Target type.
 * @return string
 */
function astoundify_favorites_get_link_url( $is_favorited, $target_id, $target_type ) {
	// Get URL for this link.
	if ( ! is_user_logged_in() ) {

		$url = wp_login_url( esc_url( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) );

	} elseif ( $is_favorited ) {

		$favorite = astoundify_favorites_get_favorite( $is_favorited );
		$url      = $favorite->get_edit_url();

	} else { // Create new favorite URL.

		$url = add_query_arg( array(
			'af_favorite_id' => 'new',
			'af_data'        => $target_id,
			'af_type'        => $target_type,
			'_nonce'         => wp_create_nonce( "astoundify_favorites_create_{$target_id}" ),
			'_redirect'      => rawurlencode( esc_url( wp_doing_ajax() ? wp_get_referer() : $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ),
		), astoundify_favorites_dashboard_url() );

	}

	return $url;
}

/**
 * Favorite Link
 *
 * @since 1.0.0
 *
 * @param int    $target_id   Target Post ID.
 * @param string $before      HTML before link.
 * @param string $after       HTML after link.
 * @param string $target_type Target type.
 * @return string
 */
function astoundify_favorites_link( $target_id, $before = '', $after = '', $target_type = 'post' ) {
	// Need to have a target ID and not in dashboard.
	if ( ! $target_id ) {
		return '';
	}

	// Sanitize Type.
	$target_type = astoundify_favorites_sanitize_target_type( $target_type );

	// Check supported post types.
	if ( 'post' === $target_type ) {
		$post_type = get_post_type( $target_id );

		// Exclude dashboard.
		if ( 'page' === $post_type && astoundify_favorites_get_option( 'dashboard-page' ) === $target_id ) {
			return '';
		}

		if ( ! in_array( $post_type, astoundify_favorites_post_types(), true ) ) {
			return '';
		}
	}

	$count        = astoundify_favorites_count( $target_id, $target_type );
	$is_favorited = astoundify_favorites_is_favorited( $target_id, get_current_user_id(), $target_type );

	$text = astoundify_favorites_get_link_text( $count, $target_id, $target_type, $is_favorited );
	$url  = astoundify_favorites_get_link_url( $is_favorited, $target_id, $target_type );

	$atts = apply_filters( 'astoundify_favorites_link_atts', array(
		'href'                => esc_url( $url ),
		'class'               => $is_favorited ? 'astoundify-favorites-link astoundify-favorites-link--active' : 'astoundify-favorites-link astoundify-favorites-link--inactive',
		'data-user_id'        => absint( get_current_user_id() ),
		'data-af_favorite_id' => absint( $is_favorited ),
		'data-af_data'        => absint( $target_id ),
		'data-af_type'        => esc_attr( $target_type ),
		'data-_nonce'         => wp_create_nonce( "astoundify_favorites_create_{$target_id}" ),
	), $target_id, $target_type, $is_favorited, $count );

	$attr_str = astoundify_favorites_attr( $atts );

	return "{$before}<a {$attr_str}>{$text}</a>{$after}";
}

/**
 * Get SVG
 *
 * @since 1.0.0
 *
 * @param string $icon Icon name.
 */
function astoundify_favorites_get_svg( $icon ) {
	$file = ASTOUNDIFY_FAVORITES_PATH . "public/images/{$icon}.svg";
	$file = apply_filters( 'astoundify_favorites_svg', $file, $icon );

	if ( file_exists( $file ) ) {
		ob_start();
?>

<span class="astoundify-favorites-icon"><?php require( $file ); ?></span>

<?php
		return ob_get_clean();
	}
}

/**
 * Dashboard URL
 *
 * @since 1.0.0
 *
 * @param string $view The current dashboard view.
 */
function astoundify_favorites_dashboard_url( $view = '' ) {
	$page_id = astoundify_favorites_get_option( 'dashboard-page' );

	// Bail if no dashboard page.
	if ( ! $page_id ) {
		return;
	}

	// Polylang Plugin Support.
	if ( function_exists( 'pll_get_post' ) ) {
		$page_id = pll_get_post( $page_id );
	}

	// Get dashboard URL.
	$page_url = get_permalink( $page_id );

	// Validate views.
	$valid_views = array( 'favorites', 'lists' );
	if ( in_array( $view, $valid_views, true ) ) {
		$page_url = add_query_arg( 'af_view', $view, $page_url );
	}

	return esc_url( $page_url );
}


/**
 * HTML Attribute Helper
 * Convert Array into HTML Attr
 *
 * @since 1.0.0
 *
 * @param array $attr Attributes to build.
 * @return string $attr_str
 */
function astoundify_favorites_attr( $attr ) {
	if ( ! is_array( $attr ) || empty( $attr ) ) {
		return '';
	}

	$attr_str = '';

	foreach ( $attr as $name => $value ) {
		$attr_str .= false !== $value ? sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) ) : esc_html( " {$name}" );
	}

	return $attr_str;
}

/**
 * Display Notices
 *
 * @since 1.0.0
 */
function astoundify_favorites_notices() {
	if ( ! empty( \Astoundify\Favorites\Notices::get() ) ) {
		echo wp_kses_post( \Astoundify\Favorites\Notices::display() );
	}
}

/**
 * Labels
 *
 * @since 1.0.1
 *
 * @param string $for Valid value "favorite", "favorites", "list", "lists".
 * @return string
 */
function astoundify_favorites_label( $for ) {
	if ( 'favorite' === $for ) {
		return esc_attr( astoundify_favorites_get_option( 'favorite-label-singular', __( 'favorite', 'astoundify-favorites' ) ) );
	} elseif ( 'Favorite' === $for ) {
		return esc_attr( ucfirst( astoundify_favorites_get_option( 'favorite-label-singular', __( 'favorite', 'astoundify-favorites' ) ) ) );
	} elseif ( 'favorites' === $for ) {
		return esc_attr( astoundify_favorites_get_option( 'favorite-label-plural', __( 'favorites', 'astoundify-favorites' ) ) );
	} elseif ( 'Favorites' === $for ) {
		return esc_attr( ucfirst( astoundify_favorites_get_option( 'favorite-label-plural', __( 'favorites', 'astoundify-favorites' ) ) ) );
	} elseif ( 'list' === $for ) {
		return esc_attr( astoundify_favorites_get_option( 'list-label-singular', __( 'list', 'astoundify-favorites' ) ) );
	} elseif ( 'List' === $for ) {
		return esc_attr( ucfirst( astoundify_favorites_get_option( 'list-label-singular', __( 'list', 'astoundify-favorites' ) ) ) );
	} elseif ( 'lists' === $for ) {
		return esc_attr( astoundify_favorites_get_option( 'list-label-plural', __( 'lists', 'astoundify-favorites' ) ) );
	} elseif ( 'Lists' === $for ) {
		return esc_attr( ucfirst( astoundify_favorites_get_option( 'list-label-plural', __( 'lists', 'astoundify-favorites' ) ) ) );
	}
}

/**
 * Sanitize Target Types
 *
 * @since 1.3.0
 *
 * @param string $type Target type.
 * @return string
 */
function astoundify_favorites_sanitize_target_type( $type ) {
	$default = 'post';
	$valid_types = apply_filters( 'astoundify_favorites_valid_target_types', array( 'post' ) );
	return $type && in_array( $type, $valid_types ) ? $type : $default;
}
