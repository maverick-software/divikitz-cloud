<?php
global $wpdb;


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assigned_to_group']) && isset($_POST['server_id'])){
	$group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."bloxx_clusters WHERE id = %s",$_POST['assigned_to_group'] ) );
	$table = $wpdb->prefix.'bloxx_clusters';
	if(!empty($group) && !empty($group->servers)){
		$existing_servers = unserialize($group->servers);
		(!empty($existing_servers)) ? array_push($existing_servers, $_POST['server_id']):$existing_servers[] = $_POST['server_id'];
		if($_POST['assigned_to_group'] !='0'){$wpdb->update($table, array('servers'=>serialize($existing_servers)),['id'=>$_POST['assigned_to_group']]);}
		if(!empty($_POST['old_group_id'])){
			$oldgroup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."bloxx_clusters WHERE id = %s",$_POST['old_group_id'] ) );
			if(!empty($oldgroup) && !empty($oldgroup->servers)){
				if (($key = array_search($_POST['server_id'], unserialize($oldgroup->servers))) !== false) {
				    $newarr = unserialize($oldgroup->servers);
				    unset($newarr[$key]);
				    $wpdb->update($table, array('servers'=>serialize($newarr)),['id'=>$_POST['old_group_id']]);
				}
			}
		}

	}else{
		if($_POST['assigned_to_group'] !='0'){$wpdb->update($table, array('servers'=>serialize([$_POST['server_id']])),['id'=>$_POST['assigned_to_group']]);}
		if(!empty($_POST['old_group_id'])){
			$oldgroup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."bloxx_clusters WHERE id = %s",$_POST['old_group_id'] ) );
			if(!empty($oldgroup) && !empty($oldgroup->servers)){
				if (($key = array_search($_POST['server_id'], unserialize($oldgroup->servers))) !== false) {
				    $newarr = unserialize($oldgroup->servers);
				    unset($newarr[$key]);
				    $wpdb->update($table, array('servers'=>serialize($newarr)),['id'=>$_POST['old_group_id']]);
				}
			}
		}
	}
}
$cloudways = new Cloudways();
$allservers = $cloudways->getallapps();
$allservers = json_decode($allservers);
if(isset($_GET['cluster']) && $_GET['cluster'] != 0){
	$clusterlists = $wpdb->get_results ( "
				    SELECT * 
				    FROM  ".$wpdb->prefix."bloxx_clusters WHERE id=".$_GET['cluster']
				);
}else{
	$clusterlists = $wpdb->get_results ( "
				    SELECT * 
				    FROM  ".$wpdb->prefix."bloxx_clusters"
				);
}
	

?>
<div class="container wrap">
	<h1 class="wp-heading-inline">Servers List</h1>
	<?php 
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		?>
		<a href="<?php echo $actual_link;?>&type=new" class="page-title-action">Add New</a><hr class="wp-header-end">
		<div class="table-responsive">
	<table id="servers_table" class="wp-list-table widefat fixed striped table-view-list servers">
		<thead>
			<th>Name</th>
			<th>Total Apps</th>
			<th>Assign To</th>
			<!-- <th>Stats</th> -->
		</thead>
		<tbody>
	<?php
	if(!empty($allservers) && isset($allservers->servers)): 
		foreach($allservers->servers as $key => $server): 

			if(isset($_GET['cluster']) && $_GET['cluster'] != 0){
				if(!empty($clusterlists) && !empty($clusterlists[0]->servers) && !in_array($server->id,unserialize($clusterlists[0]->servers))){
					continue;
				}
			}

	?>
	<tr>
		<td><a href="<?php strtok($_SERVER["REQUEST_URI"], '?');?>?page=cloudways&tab=servers&type=list&show_apps=true&server_id=<?php echo $server->id;?>"><?php echo $server->label; ?></a></td>
		<td><?php echo (isset($server->apps))?count($server->apps):0; ?></td>
		<td><form method="post">
			<input type="hidden" name="server_id" value="<?php echo $server->id;?>">

		<?php
			$clusters = '<select class="select select-group" name="assigned_to_group">';
			$clusters .= '<option value="0">Select Cluster</option>';
			$old_grp_id = '';
			if(!empty($clusterlists)){
				foreach ( $clusterlists as $cluster )
				{
					$isselet = (!empty($cluster->servers) && in_array($server->id, unserialize($cluster->servers)))?'selected':'';
					if(!empty($cluster->servers) && in_array($server->id, unserialize($cluster->servers)) && $old_grp_id == ''){
						$old_grp_id = $cluster->id;
					}
					$clusters .= '<option value="'.$cluster->id.'" '.$isselet.' >'.$cluster->name.'</option>';
				}
			}
			$clusters .= '</select>';
			echo $clusters;
		?>
		<input type="hidden" name="old_group_id" value="<?php echo $old_grp_id;?>">
		</form>
		</td>
		<!-- <td><a href="<?php strtok($_SERVER["REQUEST_URI"], '?');?>?page=cloudways&tab=servers&type=stats&server_id=<?php echo $server->id;?>">See Stats</a></td> -->
	</tr>

	<?php

	endforeach;
	endif;

	?>
	</tbody>
	</table>
</div>
</div>