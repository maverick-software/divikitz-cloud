<?php

class HostingSetting{
	
	public $response_msg = '';
	public function __construct(){
		add_action( 'admin_menu', array($this,'bloxx_admin_hostingmenu') );
	}

	
	public function bloxx_admin_hostingmenu() {
		add_submenu_page('bloxx-app','Hosting Settings', 'Hosting Settings','administrator', 'hosting-settings', array( $this, 'hostingsettings' ) ); 
	}

	public function savehostingSettings()
	{
		$data = $_POST['hosting_features'];
		foreach($data as $k =>$v){
			if($k != 'free_plan_options'){
				$planid_yr = $data[$k]['yearly_plan_id']; //echo $planid_yr;
				unset($data[$k]['yearly_plan_id']);
				$data[$planid_yr] = $data[$k];
			}
		}
		
		if(update_option( 'hosting_features', $data)){
			return true;
		}else{
			return false;
		} 	
	}

	public function getplans(){
		$args = array(
                'post_type' => 'wpi_item',
                'showposts' => 3,
                'order' => 'asc',
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'subjects',
                        'field' => 'term_id',
                        'terms' => 370
                    ),
                    array(
                        'taxonomy' => 'subjects',
                        'field' => 'term_id',
                        'terms' => 368
                    )
                )
            );
            $query = new WP_Query($args);
            $monthly_plan = get_posts($args);

            return $monthly_plan;
	}
	public function hostingsettings(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$res = $this->savehostingSettings();
			if($res === true){
				$msg = 'Settings Saved Successfully.';
			}else{
				$msg = 'Something Went Wrong.';
			}
		}
		$hosting_features = get_option('hosting_features',true);
		$monthly_plan = $this->getplans();
		$yearly_plans = ['64230','64228','64229'];
		?>
			<div class="container wrap">
			<h1 class="wp-heading-inline">Hosting Plans Settings</h1>
			
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
			<div class="setting-content wrap bloxx-setting-box hosting-box ">
				<form method="post">
					<div class="col-4">
							<h2>Free Apps Options</h2>
							<div class="form-group" style="margin-top: 10px;">
								<label>Custom Domains</label>
								<select name="hosting_features[free_plan_options][custom_domain]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['custom_domain']) && $hosting_features['free_plan_options']['custom_domain'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['custom_domain']) && $hosting_features['free_plan_options']['custom_domain'] == 'no')?'selected':''; ?>>No</option>
								</select>

							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Team Chat</label>
								<select name="hosting_features[free_plan_options][team_chat]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['team_chat']) && $hosting_features['free_plan_options']['team_chat'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['team_chat']) && $hosting_features['free_plan_options']['team_chat'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Tasks</label>
								<select name="hosting_features[free_plan_options][tasks]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['tasks']) && $hosting_features['free_plan_options']['tasks'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['tasks']) && $hosting_features['free_plan_options']['tasks'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Collaborators</label>
								<select name="hosting_features[free_plan_options][collaborators]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['collaborators']) && $hosting_features['free_plan_options']['collaborators'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['collaborators']) && $hosting_features['free_plan_options']['collaborators'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Integrations</label>
								<select name="hosting_features[free_plan_options][integrations]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['integrations']) && $hosting_features['free_plan_options']['integrations'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features['free_plan_options']) && isset($hosting_features['free_plan_options']['integrations']) && $hosting_features['free_plan_options']['integrations'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
						</div>
					<?php $i=0; foreach ($monthly_plan as $monthly_view) { ?>
						<div class="col-4">
							<h2><?= $monthly_view->post_title ?> Options</h2>
							<div class="form-group" style="margin-top: 10px;">
								<label>Custom Domains</label>
								<select name="hosting_features[<?= $monthly_view->ID;?>][custom_domain]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['custom_domain']) && $hosting_features[$monthly_view->ID]['custom_domain'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['custom_domain']) && $hosting_features[$monthly_view->ID]['custom_domain'] == 'no')?'selected':''; ?>>No</option>
								</select>

							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Team Chat</label>
								<select name="hosting_features[<?= $monthly_view->ID;?>][team_chat]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['team_chat']) && $hosting_features[$monthly_view->ID]['team_chat'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['team_chat']) && $hosting_features[$monthly_view->ID]['team_chat'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Tasks</label>
								<select name="hosting_features[<?= $monthly_view->ID;?>][tasks]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['tasks']) && $hosting_features[$monthly_view->ID]['tasks'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['tasks']) && $hosting_features[$monthly_view->ID]['tasks'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Collaborators</label>
								<select name="hosting_features[<?= $monthly_view->ID;?>][collaborators]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['collaborators']) && $hosting_features[$monthly_view->ID]['collaborators'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['collaborators']) && $hosting_features[$monthly_view->ID]['collaborators'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<div class="form-group" style="margin-top: 10px;">
								<label>Integrations</label>
								<select name="hosting_features[<?=$monthly_view->ID;?>][integrations]">
									<option value="yes" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['integrations']) && $hosting_features[$monthly_view->ID]['integrations'] == 'yes')?'selected':''; ?>>Yes</option>
									<option value="no" <?= (is_array($hosting_features) && isset($hosting_features[$monthly_view->ID]) && isset($hosting_features[$monthly_view->ID]['integrations']) && $hosting_features[$monthly_view->ID]['integrations'] == 'no')?'selected':''; ?>>No</option>
								</select>
							</div>
							<input type="hidden" name="hosting_features[<?= $monthly_view->ID;?>][yearly_plan_id]" value="<?= $yearly_plans[$i];?>">
						</div>
					<?php $i++; } ?>
					<!--  -->
					<div class="col-12">
						<input type="submit" name="save" value="Save">
					</div>
				</form>
				
			</div>
		</div>

		<?php 

	}

}


$hostingsetting = new HostingSetting();