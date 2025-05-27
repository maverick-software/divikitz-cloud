<?php
//Template Name: Paginate demo
get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$get_projects = new WP_Query(array( 
    'post_type'     => 'project', 
    'status'        => 'published', 
    'posts_per_page'=> 4,
    'orderby'   => 'post_date',
    'order'         => 'DESC',
    'paged'         => $paged
));

if($get_projects->have_posts()){ ?>
<ul class="project-list"><?php while($get_projects->have_posts()){ $get_projects->the_post(); ?>
    <li class="post"><strong><?= the_title();?></strong></li>
</ul>
<?php
} 
} 
 ?>
    <div id="project-loader" class="loading-banner"><a class="btn" href="javascript:void(0)" id="loadmore">Load more posts</a></div>







<?php wp_footer(); ?>

