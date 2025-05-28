<div class="container wrap">
	<?php
		if(isset($msg) && !empty($msg)){
			?>
			<div class="notice notice-warning is-dismissible">
		        <p><?php echo $msg;?></p>
		    	<span class="screen-reader-text">Dismiss this notice.</span>
			</div>
	<?php
		}
		if(isset($_GET['type']) && $_GET['type'] == 'new'):

			if(isset($_GET['update_id']) && $_GET['update_id'] != ''){
				global $wpdb;
				$table = $wpdb->prefix.'bloxx_clusters';
				$group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$table." WHERE id = %s",$_GET['update_id'] ) );
				if(!empty($group)){
					$group_name = $group->name;
					// $group_size = $group->app_size;
					$memory = $group->server_size;
					$plans = @unserialize($group->plans);
					$type = $group->type;
				}
			}

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

            $argss = array(
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
                        'terms' => 369
                    )
                )
            );
            $queryy = new WP_Query($argss);
            $yearly_plan = get_posts($argss);

	?>
		<h1 class="wp-heading-inline">Create Load Balancer</h1>
		<?php 
			$actual_link = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?page=cloudways&tab=clusters&type=list';
		?>
		<a href="<?php echo $actual_link;?>&type=list" class="page-title-action">List Load Balancers</a><hr class="wp-header-end">
			<div class="setting-content wrap bloxx-setting-box" style="width:50%">
				<form method="post">
					<div class="form-group">
						<label for="cluster_name">Enter Load Balancer</label>
						<input type="text" id="cluster_name" name="name" value="<?php echo (isset($group_name))?$group_name:'' ?>" class="regular-text"  required>
					</div>
					<?php /*
					<div class="form-group">
						<select name="type" style="margin-top: 5px;width: 300px;">
							<option value="paid" <?php echo (isset($type) && $type == '')?'selected':'' ?>>Select Type</option>
							<option value="paid" <?php echo (isset($type) && $type == 'paid')?'selected':'' ?>>Paid</option>
							<option value="free" <?php echo (isset($type) && $type == 'free')?'selected':'' ?>>Free</option>
						</select>
					</div>
					*/ ?>
					<div class="form-group">
						<select name="memory_size" style="margin-top: 5px;width: 300px;">
							<option value="1GB" <?php echo (isset($memory) && $memory == '1GB')?'selected':'' ?>>Select Server Size</option>
							<option value="1GB" <?php echo (isset($memory) && $memory == '1GB')?'selected':'' ?>>1GB</option>
							<option value="4GB" <?php echo (isset($memory) && $memory == '4GB')?'selected':'' ?>>4GB</option>
							<option value="8GB" <?php echo (isset($memory) && $memory == '8GB')?'selected':'' ?>>8GB</option>
						</select>
					</div>
					<div class="form-group">
						<select name="size" style="margin-top: 5px;width: 300px;">
							<option value="10" <?php echo (isset($group_size) && $group_size == '10')?'selected':'' ?>>Maximum Apps Per Server</option>
							<option value="10" <?php echo (isset($group_size) && $group_size == '10')?'selected':'' ?>>10 Apps</option>
							<option value="20" <?php echo (isset($group_size) && $group_size == '20')?'selected':'' ?>>20 Apps</option>
							<option value="30" <?php echo (isset($group_size) && $group_size == '30')?'selected':'' ?>>30 Apps</option>
						</select>
					</div>
					<div class="form-group" style="margin-top:8px">
						<label>Assign Plans</label>
						<select name="plan_id[]"  multiple="multiple">
			   			 	<?php foreach ($monthly_plan as $monthly_view) { 
                                $mv_post_id = $monthly_view->ID;
                            ?>
			   			 		<option value="<?= $mv_post_id; ?>" <?= (isset($plans) && is_array($plans) && in_array($mv_post_id, $plans))?'selected':''?> ><?= $monthly_view->post_title; ?></option>
			   			 	<?php }?>
			   				<?php foreach ($yearly_plan as $yearly_view) { ?>
			   					<option value="<?= $yearly_view->ID; ?>" <?= (isset($plans) && is_array($plans) && in_array($yearly_view->ID, $plans))?'selected':''?>><?= $yearly_view->post_title; ?></option>
			   				<?php }?>
			   			 </select>
					</div>
					<?php if(isset($_GET['update_id']) && $_GET['update_id'] != ''): ?>
						<input type="hidden" name="update_id" value="<?php echo $_GET['update_id']; ?>">
					<?php endif; ?>
					<input type="submit" name="save" value="Save">
				</form>
				
				
			</div>
		
	<?php 
		else:
			global $wpdb;
			$clusterlists = $wpdb->get_results ( "
							    SELECT * 
							    FROM  ".$wpdb->prefix."bloxx_clusters"
							);	

			
            
		?>
		<h1 class="wp-heading-inline">Load Balancers List</h1>
		<?php 
			$actual_link = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?page=cloudways&tab=clusters&type=new';
		?>
		<a href="<?php echo $actual_link;?>" class="page-title-action">Add New</a><hr class="wp-header-end">
		<div class="table-responsive">
			<table id="clusters_table" class="wp-list-table widefat fixed striped table-view-list clusters">
				<thead>
					<th>Name</th>
					<!-- <th>Type</th> -->
					<!-- <th>Total Size Of Apps In Single Server</th> -->
					<th>Total Servers</th>
					<th>Edit</th>
				</thead>
				<tbody>
					<?php 
						if(!empty($clusterlists)){
							foreach ( $clusterlists as $cluster )
							{
							   ?>
							   <tr>
							   		<td>
							   			<a href="<?=strtok($_SERVER["REQUEST_URI"], '?');?>?page=cloudways&tab=servers&type=list&cluster=<?= $cluster->id;?>"><?= $cluster->name;?></a>
							   		</td>
							   		<?php /*
							   		<td>
							   			<?=ucfirst($cluster->type);?>
							   		</td>
							   		<!-- <td>
							   			< ?=$cluster->app_size . ' Apps';?>
							   		</td> */ ?>
							   		<td>
							   			<?= (!empty($cluster->servers)) ? count(unserialize($cluster->servers)):0;?>
							   		</td>
							   		<td>
							   			<a href="<?= strtok($_SERVER["REQUEST_URI"], '?');?>?page=cloudways&tab=clusters&type=new&update_id=<?= $cluster->id;?>">Edit</a>
							   		</td>
							   		
							   </tr>
							   <?php
							}
						}

					?>
				</tbody>
			</table>
		</div>

	<?php 
		endif;
?>
</div>