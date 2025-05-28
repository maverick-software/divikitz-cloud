<div class="wrap" id="writesonic-page">
	<h2 class="wp-heading-inline">Tools</h2>
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
								<option value="<?php echo $value['slug']; ?>"><?php echo $value['name']; ?></option>
						<?php
								}
							}
						?>
					</select>
				</td>
				<th><label for="engine">Engine</label></th>
				<td>
					<select id="engine" >
						
							<option value="economy">Economy</option>
							<option value="business">Business</option>
						
					</select>
				</td>
				<th><label for="language">Language</label></th>
				<td>
					<select id="language" >
						<option value="en">en</option>
						<option value="nl">nl</option>
						<option value="fr">fr</option>
						<option value="de">de</option>
						<option value="it">it</option>
						<option value="pl">pl</option>
						<option value="es">es</option>
						<option value="pt-pt">pt-pt</option>
						<option value="pt-br">pt-br</option>
						<option value="ru">ru</option>
						<option value="ja">ja</option>
						<option value="zh">zh</option>
						<option value="bg">bg</option>
						<option value="cs">cs</option>
						<option value="da">da</option>
						<option value="el">el</option>
						<option value="hu">hu</option>
						<option value="lt">lt</option>
						<option value="lv">lv</option>
						<option value="ro">ro</option>
						<option value="sk">sk</option>
						<option value="sl">sl</option>
						<option value="sv">sv</option>
						<option value="fi">fi</option>
						<option value="et">et</option>
						
					</select>
				</td>
			</tr>
		</tbody>
	</table>		
	
	<?php 
							foreach ($integration as $key => $api) {
								foreach ($api as $key1 => $value) {
									require 'forms/'.$value['slug'].'.php';
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
