<h1 class="wp-heading-inline">Setting</h1>

<form name="post" class="ws_settings_form" method="post" style="display: block !important;">

	<table class="form-table" role="presentation">
		<tbody>		
			<tr class="">
				<th><label>API Key for writesonic</label></th>
				<td><input type="text" name="api_key_writesonic_bussiness" id="api_key_writesonic_bussiness" value="<?php echo get_option( 'api_key_writesonic_bussiness' )?>" class="regular-text"></td>
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
					<select class="regular-text" name="writesonic_bussiness_credit_product">
						<option value="">Select Product</option>
				<?php 
					foreach ($products as $product) {
				?>
						<option <?php if(get_option( 'writesonic_bussiness_credit_product' )==$product->ID){ echo "selected"; }?> value="<?php echo $product->ID; ?>"><?php echo $product->post_title; ?></option>
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
				<td style="width: 150px;"><b>#</b></td>
				<td style="width: 100px;"><b>Credit</b></td>
				<td style="width: 200px;"><b>Description</b></td>
				<td style="width: 50px;"><b>Action</b></td>
			</tr>
			<?php 
				$writesonic_credit = json_decode(get_option( 'writesonic_bussiness_credit' ),true);
				$writesonic_integration_desc = json_decode(get_option( 'writesonic_integration_desc' ),true);
				$writesonic_integration_switch = json_decode(get_option( 'writesonic_integration_switch' ),true);
				//echo '<pre>';
				//print_r($writesonic_integration_desc);
				// exit;
				$integration = $this->integration;
				foreach ($integration as $key => $api) {
					foreach ($api as $key1 => $value) {
			?>
					 <tr class="">
						<th style="width: 200px;"><label><?php echo $value['name']; ?></label></th>
						<td style="width: 100px;"><input type="number" name="writesonic_bussiness_credit[<?php echo $value['slug']; ?>]" id="<?php echo $value['slug']; ?>" min="1" value="<?php if(!empty($writesonic_credit[$value['slug']])){ echo $writesonic_credit[$value['slug']]; }else{ echo "1"; } ?>" class="regular-text"></td>
						<td style="width: 300px;"><textarea name="writesonic_integration_desc[<?php echo $value['slug']; ?>]" style="width: 90%;"><?php echo @$writesonic_integration_desc[$value['slug']]; ?></textarea>
						</td>
						<td>
						
							<label class="switch">
							  <input type="checkbox" value="1" name="writesonic_integration_switch[<?php echo $value['slug']; ?>]" <?php echo (@$writesonic_integration_switch[$value['slug']])?' checked':'';?>>
							  <span class="slider round"></span>
							</label>
						</td>
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

<style>
.switch {
  position: relative;
  display: inline-block;
  width: 53px;
  height: 27px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
.ws_settings_form{display: block !important;}
</style>