<?php
/**
 * List Edit
 * This will display create/edit form
 * 
 * @since 1.0.0
 * @version 1.0.0
 *
 * @var object $list \Astoundify\Favorites\Favorite_List
 * @var string $nonce Nonce Action "astoundify_favorites_create_list" / "astoundify_favorites_edit_{$list_id}"
 * @var string $redirect Redirect URL after submit.
 */
?>

<?php astoundify_favorites_notices(); ?>

<form class="astoundify-favorites-form-list-edit" method="post">

	<h3><?php echo '' === $list->get_name() ? __( 'New List', 'astoundify-favorites' ) : $list->get_name(); ?></h3>

	<?php astoundify_favorites_list_name_field( array( 'list_name' => $list->get_name() ) ); ?>

	<div class="astoundify-favorites-submit-field">
		<button type="submit"><?php _e( 'Save', 'astoundify-favorites' ); ?></button>
	</div><!-- . astoundify-favorites-submit-field -->

	<input type="hidden" name="_list" value="<?php echo esc_attr( $list->get_id() ); ?>"/>
	<input type="hidden" name="_redirect" value="<?php echo esc_url( $redirect ); ?>"/>
	<?php wp_nonce_field( $nonce, '_nonce' ); ?>

</form><!-- .astoundify-favorites-form-list-edit -->
