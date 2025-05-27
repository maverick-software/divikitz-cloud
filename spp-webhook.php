<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );

//add_action("init","master_payment_user_set");

//function master_payment_user_set(){

	global $wpdb;
	$message = json_decode(file_get_contents('php://input'));

	update_option('spp_webhook_data', $message->data);
	update_option('spp_subscription_data', $message->data);

	// when order created

	if ($message->event == 'order.created') {
		$data = $message->data;

		$email = $data->client->email;
		$exists = email_exists( $email );
		if ( $exists ) {

			$user = get_user_by( 'email', $email );
			$order_created_data = json_encode($data);

			global $wpdb;
			$wpdb->update('wp_usermeta', array('user_id'=>$user->ID, 'meta_key'=>'spp_order_created', 'meta_value'=>$order_created_data), array('user_id' => $user->ID));

			$role = '';
			if($data->service == 'Teams') {
				$role = 'um_team';
			} else if($data->service == 'Agency') {
				$role = 'um_agency';
			} else if($data->service == 'Freelancer') {
				$role = 'um_freelancer';
			} else {
				$role = 'um_free';
			}
			setcookie("diviuser_login2", $user->ID, time()+3600, COOKIEPATH, COOKIE_DOMAIN);

			if(!in_array( "administrator" , $user->roles)){
				wp_update_user( array ('ID' => $user->ID, 'role' => $role));
			}
			

			// $u = new WP_User( $user->ID );
			// $u->set_role( $role );

			update_option('sd', 1);
			clean_user_cache($user->ID);
			// wp_clear_auth_cookie();
			// wp_set_current_user($user->ID);
			// wp_set_auth_cookie($user->ID);

		//do_action('wp_login', $user->user_login, $user);
		} else {
			update_option('sd', 2);
			$WP_array = array (
				'user_login'    =>  $data->client->email,
				'user_email'    =>  $data->client->email,
				'user_pass'     =>  '',
				'first_name'    =>  $data->client->name_f,
				'last_name'     =>  $data->client->name_l,
			);

			$id = wp_insert_user( $WP_array );


			$role = '';
			if($data->service == 'Teams') {
				$role = 'um_team';
			} else if($data->service == 'Agency') {
				$role = 'um_agency';
			} else if($data->service == 'Freelancer') {
				$role = 'um_freelancer';
			} else {
				$role = 'subscriber';
			}

			wp_update_user( array ('ID' => $id, 'role' => $role));

	    // update user meta
			$order_created_data = json_encode($data);
			global $wpdb;
			$wpdb->insert('wp_usermeta', array(
				'user_id' => $id,
				'meta_key' => 'spp_order_'.$role,
				'meta_value' => $order_created_data
			));
			clean_user_cache($id);
			wp_clear_auth_cookie();
			wp_set_current_user($id);
			wp_set_auth_cookie($id);

		}
	}

	// when subscription updated

	if ($message->event == 'subscription.canceled') {
		$data = $message->data;
		update_option('sd', 3);
		update_option('subscription_cancel_data', $data);
		update_option('subscription_cancel', $data->subscription);

		$email = $data->client->email;
		$exists = email_exists( $email );
		if ( $exists ) {
			$user = get_user_by( 'email', $email );

		}
	}
//}
?>