<div class="wrap" id="writesonic-page">
	<h2 class="wp-heading-inline">Writesonic</h2>
	<table class="form-table" role="presentation">
		<tbody>
			<tr class="select_type-wrap">
				<th><label for="select_type">Select type of AI</label></th>
				<td>
					<select name="select_type" id="select_type" onchange="show_form();">
						<?php 
							$integration = $this->integration;
							foreach ($integration as $key => $api) {
								foreach ($api as $key1 => $value) {
						?>
								<option value="<?php echo $key1; ?>"><?php echo $value; ?></option>
						<?php
								}
							}
						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>		
	
	<?php 
							foreach ($integration as $key => $api) {
								foreach ($api as $key1 => $value) {
									require 'forms/'.$key1.'.php';
								}
							}
						?>
	<p class="submit"><input type="button" name="submit" id="submit" class="button button-primary" value="Search" onclick="searchData();"></p>
	<div class="result_div" style="display:none;">
		<h2 class="wp-heading-inline">Result</h2>
		<ol id="result_list">
			
		</ol>
	</div>
</div>
