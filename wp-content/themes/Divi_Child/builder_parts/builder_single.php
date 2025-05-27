<?php
$body_id= get_the_id(); 
$post_cats  = get_the_terms( $body_id, 'project_categories');
$term_4id=$post_cats[0]->term_id;
?>


<?php if(!isset($_REQUEST['button_hide']) && !isset($_REQUEST['update']) && !isset($_REQUEST['et_fb']) && !isset($_REQUEST['screenshot'])){ ?>
<div class="template-post-navigation">
	 <ul>
	   <li class="prev">
	   	<?php
	   	 previous_post_link(
	        '%link',
	        'Prev' . _x( '', 'Previous post link', $term_4id=$post_cats[0]->slug ),
	         true,
	         array(),
	         'project_categories'
	     );
	 	?>
	   </li>
	   
	   <li class="next">
	   	<?php
	   	 next_post_link(
	        '%link',
	        'Next' . _x( '', 'Next post link', $term_4id=$post_cats[0]->slug ),
	         true,
	         array(),
	         'project_categories'
	     );
	 	?>
	   </li>
	 </ul>
</div>

<?php } ?>

<?php


if(isset($post_cats) && !empty($post_cats)){
	$term_4id=$post_cats[0]->term_id;
	$sync_header = get_term_meta($term_4id, 'project_header', true);

	if(!empty($sync_header)){
		$post_header   = get_post( $sync_header );

		if(!empty($post_header)){
			echo do_shortcode("$post_header->post_content");
		}
	}
}
wp_reset_postdata();	
//$post_body   = get_post( $body_id );
//echo $body_content =  apply_filters( 'the_content', $post_body->post_content );

the_content();

wp_reset_postdata();

if(isset($post_cats) && !empty($post_cats)){
	$term_4id=$post_cats[0]->term_id;						
	$sync_footer = get_term_meta($term_4id, 'project_footer', true);
	
	if(!empty($sync_footer)){
		$post_footer   = get_post($sync_footer);
		if(!empty($post_footer)){
			//echo $footer_content =  apply_filters( 'the_content', $post_footer->post_content );
			echo do_shortcode("$post_footer->post_content");
		}

	}
}
?>