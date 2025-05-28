<?php

class Builder_plans{
	
	public function __construct(){
		add_shortcode('builder_plans', array($this, 'builder_plans'));
	}

	function builder_plans(){
		if(is_user_logged_in()){
		ob_start();
			?>
			<div class="contentWrapper user_actions" id="table-page">
				<!-- //sidebar  --> 
				<?php require_once 'builder_siderbar.php'; ?>

				<div class="wrapContent">
						<!-- //Top Bar  --> 
            		<?php require_once 'builder_topnav.php'; ?> 
            		

            		<?php 
            			//echo get_the_content(237);
            			$get_post = get_post(237); 
						echo do_shortcode($get_post->post_content);
            		?>



				   		<div>
				   	<div>		

				   <?php require_once 'builder_footer.php'; ?>
			</div>
			<?php
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;	
		
		} else {
			restricate_page_content();
		}
	}


} // end class
$builder_plans = new Builder_plans();
