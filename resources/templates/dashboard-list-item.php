<?php
/**
 * List Item
 * 
 * @since 1.0.0
 * @version 1.0.0
 * 
 * @vars object $list \Astoundify\Favorites\Favorite_List
 */
?>

<tr id="astoundify-favorite-list-<?php echo $list->get_id(); ?>" class="astoundify-favorite-list">
	<td>
		<strong><?php echo $list->get_name(); ?></strong>
		<div class="astoundify-favorite-list-actions">
			<?php echo $list->get_edit_link(); ?>
			<?php echo $list->get_remove_link(); ?>
		</div>
	</td>
	<td>
		<?php if ( $list->get_count() ) : ?>
			<a href="<?php echo esc_url( $list->get_url() ); ?>"><?php echo $list->get_count(); ?></a>
		<?php else : ?>
			<?php echo $list->get_count(); ?>
		<?php endif; ?>
	</td>
</tr><!-- .astoundify-favorite-list -->
