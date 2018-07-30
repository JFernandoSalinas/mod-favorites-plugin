<?php
/**
 * Favorite Item
 * 
 * @since 1.0.0
 * @version 1.0.0
 * 
 * @vars object $favorite \Astoundify\Favorites\Favorite
 */
?>

<tr id="astoundify-favorite-<?php echo $favorite->get_id(); ?>" class="astoundify-favorite">
	<td>
		<strong><?php echo $favorite->get_target_link(); ?></strong>
		<div class="astoundify-favorite-actions">
			<?php echo $favorite->get_edit_link(); ?>
			<?php echo $favorite->get_remove_link(); ?>
		</div>
	</td>
	<td>
		<?php echo $favorite->get_gas_field(); ?>
	</td>
	<td>
		<?php echo $favorite->get_diesel_field(); ?>
	</td>
	
	<td>
		<?php echo $favorite->get_note_html(); ?>
	</td>
</tr><!-- .astoundify-favorite -->
