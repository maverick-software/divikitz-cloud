<?php
add_shortcode("custom_register","custom_register");
if(!function_exists('custom_register')){
	function custom_register(){
		ob_start();
		global $wpdb, $user_ID;  
		//Check whether the user is already logged in  
		if (is_user_logged_in()){
			//return 'User Already Login
			return '<ul class="um-misc-ul">
			<li><a href="'.get_site_url().'/dashboard/">Your account</a></li>
			<li><a href="'.wp_logout_url( get_site_url().'/login' ).'">Logout</a>
			</li>
			</ul>';
		}else{
			$errors = array();

			if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {  
				// Check email address is present and valid  
				$email = $wpdb->escape($_REQUEST['email']);  
				if( !is_email( $email ) ) {   
					$errors['email'] = "Please enter a valid email";  
				} elseif( email_exists( $email ) ){  
					$errors['email'] = "This email address is already in use";  
				}  

				// Check terms of service is agreed to  
				if(!isset($_POST['terms'])){  
					$errors['terms'] = "You must agree to Terms of Service";  
				}  

				if(0 === count($errors)) {
					$new_user_id = wp_create_user( $email, $password, $email ); 
					$WP_array = array (
						'user_login'    =>  $email,
						'user_email'    =>  $email,
						'role' => get_option('default_role'),
						'first_name'    =>  $_POST['first_name'],
						'last_name'     =>  $_POST['first_name'],
					);

					$id = wp_insert_user( $WP_array );
					update_user_meta( $id, 'billing_first_name', $_POST['first_name'] );
					update_user_meta( $id, 'first_name', $_POST['first_name'] );
					update_user_meta( $id, 'last_name', $_POST['last_name'] );
					update_user_meta( $id, 'billing_last_name', $_POST['last_name'] );
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
					$u = new WP_User( $id );
					$u->remove_role( 'subscriber' );
					$u->add_role( 'um_free' );
					
					setcookie("diviuser_r3eg", 1, time()+3600, COOKIEPATH, COOKIE_DOMAIN);
					header( 'Location:' . get_site_url() . '/login/?success=1');  
					exit;
				}  

			}  


			if (isset($errors)) {
				foreach ($errors as $key => $value) {
					echo '<div class="um-field-error" role="alert">'.$value.'</div>';
				}
			}
			?>  
			<div class="um">
				<form id="wp_signup_form" class="um-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" autocomplete="off">  
					<div class="um-row _um_row_1 " style="margin: 0 0 30px 0;">
						<div class="um-col-1">
							<div class="um-field">
								<div class="um-field-label">
									<label for="username">First Name</label>
									<div class="um-clear"></div>
								</div>
								<div class="um-field-area">
									<input autocomplete="off" class="um-form-field valid" type="text" name="first_name" id="first_name" value="" placeholder="">
								</div>
							</div>
							<div class="um-field">
								<div class="um-field-label">
									<label for="username">Last Name</label>
									<div class="um-clear"></div>
								</div>
								<div class="um-field-area">
									<input autocomplete="off" class="um-form-field valid" type="text" name="last_name" id="last_name" value="" placeholder="">
								</div>
							</div>
							<div class="um-field">
								<div class="um-field-label">
									<label for="username">Email</label>
									<div class="um-clear"></div>
								</div>
								<div class="um-field-area">
									<input autocomplete="off" class="um-form-field valid" type="text" name="email" id="email" value="" placeholder="">
								</div>
							</div>

							<div class="um-field">
								<input name="terms" id="terms" type="checkbox" value="Yes">  
								<label for="terms">I agree to the Terms of Service</label>  
							</div>

							<div class="um-col-alt">
								<div class="um-left um-full">
									<input type="submit" value="Register" class="um-button" id="um-submit-btn">
								</div>
								<div class="um-clear"></div>
							</div>
						</div>
					</form>
				</div>
				<?php
				$html = ob_get_contents();
				ob_clean();
				return $html;
			}  
		}
	}