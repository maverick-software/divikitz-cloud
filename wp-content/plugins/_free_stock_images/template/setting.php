<h1 class="wp-heading-inline">Pixabay Setting</h1>

<form name="post" action="post.php" method="post" id="post">
	
	<input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=free_stock_image">
	<table class="form-table" role="presentation">
		<tbody>

		
			<tr class="">
				<th><label for="api_key_pixabay">API Key for Pixabay</label></th>
				<td><input type="text" name="api_key_pixabay" id="api_key_pixabay" value="<?php echo get_option( 'api_key_pixabay' )?>" class="regular-text"></td>
			</tr>

		</tbody>
	</table>
	<table class="form-table" role="presentation">
		<tbody>

		
			<tr class="">
				<td><input type="submit" value="Save" class="button button-primary"></td>
			</tr>

		</tbody>
	</table>
</form>