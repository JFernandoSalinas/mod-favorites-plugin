<?php
/**
 * User Favorite Lists Query
 * 
 * @since 1.0.0
 * @version 1.0.0
 * 
 * @vars object $favorite_list_query \Astoundify\Favorites\Favorite_List_Query
 */
?>

<?php astoundify_favorites_notices(); ?>

<?php astoundify_favorites_get_template( 'dashboard-view-tabs', array(
	'active_tab' => 'lists',
) ); ?>

<div id="astoundify-favorites-dashboard-lists">

	<table>
		<thead>
			<tr>
				<th style="width: 80%;"><?php echo astoundify_favorites_label( 'List' ); ?></th>
				<th><?php _e( 'Count', 'astoundify-favorites' ); ?></th>
			</tr>
		</thead>
		<tbody>

			<?php foreach( $favorite_list_query->lists as $list ) : ?>
				<?php astoundify_favorites_get_template( 'dashboard-list-item', array(
					'list' => $list,
				) ); ?>
			<?php endforeach; ?>

			<tr id="astoundify-favorite-list-new">
				<td colspan="2">
					<?php echo $favorite_list_query->get_create_list_link(); ?>
				</td>
			</tr>

		</tbody>
	</table>

</div><!-- #astoundify-favorites-dashboard-lists -->
