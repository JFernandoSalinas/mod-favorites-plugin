<?php
/**
 * Edit Favorite Item
 * User can change the list and edit note of a favorite here.
 * 
 * @since 1.0.0
 * @version 1.3.0
 *
 * @var object $favorite \Astoundify\Favorites\Favorite Class.
 * @var string $nonce Nonce Action "astoundify_favorites_edit_{$favorite_id}".
 * @var string $redirect Redirect URL after submit.
 */
?>

<?php astoundify_favorites_notices(); ?>

<form class="astoundify-favorites-form-favorite-edit" method="post">

	<h3><?php printf( __( '%1$s %2$s', 'astoundify-favorites' ), astoundify_favorites_count_html( $favorite->get_target_id(), $favorite->get_target_type() ) , astoundify_favorites_label( 'Favorites' ) ); ?></h3>

	<?php astoundify_favorites_note_field( array( 'note' => $favorite->get_note() ) ); ?><!-- . astoundify-favorites-note-field -->

	<?php astoundify_favorites_list_field( array( 'selected' => $favorite->get_list_id() ) ); ?><!-- . astoundify-favorites-list-field -->

	<div class="astoundify-favorites-submit-field">
		<button type="submit"><?php _e( 'Update', 'astoundify-favorites' ); ?></button> <?php echo $favorite->get_remove_link(); ?><!-- .astoundify-favorites-remove-favorite -->
	</div><!-- . astoundify-favorites-submit-field -->

	<input type="hidden" name="_favorite" value="<?php echo esc_attr( $favorite->get_id() ); ?>"/>
	<input type="hidden" name="_target" value="<?php echo esc_attr( $favorite->get_target_id() ); ?>"/>
	<input type="hidden" name="_redirect" value="<?php echo esc_url( $redirect ); ?>"/>
	<?php wp_nonce_field( $nonce, '_nonce' ); ?>

</form><!-- .astoundify-favorites-form-favorite-edit -->
