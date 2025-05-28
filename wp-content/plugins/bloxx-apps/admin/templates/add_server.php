<?php

$msg = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['server_name']) && !empty($_POST['server_name'])){
	$cloudways = new Cloudways();
	$response = $cloudways->postRequest('/server',array('cloud' => 'do','region'=>'sfo1','instance_type'=>$_POST['server_size'],'application'=>'wordpress','app_version'=>'5.8','server_label'=>$_POST['server_name'],'app_label'=>$_POST['server_name']));
	$response = json_decode($response);
	if(isset($response->server) && !empty($response->server)){
		$msg = @$response->server->operations[0]->message;
	}

}

?>

<div class="container wrap">
	<h1 class="wp-heading-inline">Create A Server</h1>
	<?php 
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		?>
		<a href="<?php echo $actual_link;?>&type=list" class="page-title-action">Server List</a><hr class="wp-header-end">
	<?php
		if(isset($msg) && !empty($msg)){
			?>
			<div class="notice notice-warning is-dismissible">
		        <p><?php echo $msg;?></p>
		    	<span class="screen-reader-text">Dismiss this notice.</span>
			</div>
	<?php
		}
	?>
	<div class="setting-content wrap bloxx-setting-box">
		<form method="post">
			<div class="form-group" style="width: 300px">
				<label for="server_name">Enter Server Name
				<input type="text" id="server_name" name="server_name" value="" class="regular-text"  required>
			</div>
			<div class="form-group" style="margin-top: 10px;">
				<select name="server_size" style="width: 200px">
					<option disabled value="512MB" selected>Server Size</option>
					<option value="512MB">512MB</option>
					<option value="1GB">1GB</option>
					<option value="2GB">2GB</option>
					<option value="4GB">4GB</option>
					<option value="8GB">8GB</option>
					<option value="16GB">16GB</option>
					<option value="32GB">32GB</option>
					<option value="48GB">48GB</option>
					<option value="64GB">64GB</option>
				</select>
			</div>
			<input type="submit" name="save" value="Save">
		</form>
		
	</div>
</div>