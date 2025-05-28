<h1 class="wp-heading-inline">Setting</h1>

<form name="post" method="post">

	<table class="form-table" role="presentation">
		<tbody>		
			<tr class="">
				<th><label >API Key for writesonic</label></th>
				<td><input type="text" name="api_key_writesonic" id="api_key_writesonic" value="<?php echo get_option( 'api_key_writesonic' )?>" class="regular-text"></td>
			</tr>
		</tbody>
	</table>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th colspan="2"><h2>Product Setting</h2></th>
			</tr>
			<tr>
				<?php
				    $args = array(
				        'numberposts'      => "-1",
				        'post_type'        => 'product'
				    );
					$products = get_posts($args);
				?>
				<th>Credit Product</th>
				<td>
					<select class="regular-text" name="writesonic_credit_product">
						<option value="">Select Product</option>
				<?php 
					foreach ($products as $product) {
				?>
						<option <?php if(get_option( 'writesonic_credit_product' )==$product->ID){ echo "selected"; }?> value="<?php echo $product->ID; ?>"><?php echo $product->post_title; ?></option>
				<?php
					}
				?>
					</select>
				</td>
			</tr>			
		</tbody>
	</table>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th><h2>Credit Setting</h2></th>
			</tr>
			<tr>
				<td><b>#</b></td>
				<td><b>Credit</b></td>
			</tr>
			<?php 
				$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
				$integration = $this->integration;
				foreach ($integration as $key => $api) {
					foreach ($api as $key1 => $value) {
			?>
					 <tr class="">
						<th><label><?php echo $value; ?></label></th>
						<td><input type="number" name="writesonic_credit[<?php echo $key1; ?>]" id="<?php echo $key1; ?>" min="1" value="<?php if(!empty($writesonic_credit[$key1])){ echo $writesonic_credit[$key1]; }else{ echo "1"; } ?>" class="regular-text"></td>
					</tr>
			<?php
					}
				}
			?>
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