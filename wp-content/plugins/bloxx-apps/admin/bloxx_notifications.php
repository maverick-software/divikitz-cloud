<?php

	global $wpdb;
	$table = $wpdb->prefix . 'bloxx_notifications';
	$cloud = new Cloudways();
	$userid = get_current_user_id();
	$notifications = $wpdb->get_results( "SELECT * FROM $table WHERE status = 0 AND user_id= $userid" );
?>

<table id="notification_table" class="widefat fixed striped table-view-list pages">
	<thead>
		<th>App Name</th>
		<th>Customer Name</th>
		<th>Message</th>
		<th>Action</th>
	</thead>
	<tbody>
		<?php	
			if(!empty($notifications)){
				foreach($notifications as $k => $notification){
		?>
		<tr id="row-msg-<?php echo $notification->id;?>">
			<td><?php echo $notification->app_name; ?></td>
			<td><?php echo get_user_meta( $notification->user_id, 'nickname', true ); ?></td>
			<td><?php echo html_entity_decode($notification->msg); ?></td>
			<td><a href="javascript:void(0)" onclick="markread(this)" data-id="<?php echo $notification->id;?>">Mark as Complete</a></td>
		</tr>
		<?php
				}
			}
		?>
</tbody>
</table>