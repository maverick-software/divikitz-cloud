<?php

$cloudways = new Cloudways();
$allservers = $cloudways->getallapps();
$allservers = json_decode($allservers);

?>
<div class="container wrap">
	<h1 class="wp-heading-inline">App List</h1>
	<a href="<?php strtok($_SERVER["REQUEST_URI"], '?');?>?page=cloudways&tab=servers" class="page-title-action">Back to Servers List</a><hr class="wp-header-end">

	<hr class="wp-header-end">
	<table id="servers_table" class="wp-list-table widefat fixed striped table-view-list servers table-responsive">
		<thead>
			<th>Name</th>
			<th>Server IP</th>
		</thead>
		<tbody>
	<?php
	if(!empty($allservers) && isset($allservers->servers)): 
		$key = getparentkeyindex($_GET['server_id'],$allservers->servers);
		if(!empty($key)):
			foreach($allservers->servers[$key]->apps as $k => $app): 
	?>
	<tr>
		<td><?php echo $app->label; ?></td>
		<td><?php echo $allservers->servers[$key]->public_ip?></td>
	</tr>

	<?php

		endforeach;
		endif;
	endif;
	?>
	</tbody>
	</table>
</div>