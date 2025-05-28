<?php
$logo = ( $user_logo = et_get_option('divi_logo') ) && !empty($user_logo) ? $user_logo : $template_directory_uri . '/images/logo.svg';
?>


<div class="wrapPanel" id="sidebar-wrapper">
    <!-- User Own Create Categories -->
    <div class="brand-image">
        <a href="<?php echo site_url(); ?>">
            <img src="<?php echo builder_url; ?>images/Divi-Webkitz-Logo-white.png?v=<?php echo time(); ?>" alt="Bloxx" width="140px"/>
        </a>
    </div>
    <?php
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;
    $builder_project = get_terms(
            array(
                'taxonomy' => 'project_categories',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'builder_cat_user',
                        'value' => $current_user_id,
                        'compare' => 'LIKE'
                    )
                )
            )
    );
    ?>
    <!-- End User Own Create Categories -->
    <div class="wrapMenu">
        <div class="sidebar_top_menus">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'my-custom-menu'
                )
            );
            ?>
        </div>
        <div class="bottomDiv">
            <p>If you notice something wrong with the platform or plugin, please submit the bug to our support team here.</p>
            <a href="javascript:void(0)" class="modalForm"><i class="far fa-handshake"></i> <span>Report a Bug</span></a>
        </div>
    </div>
</div>

<div class="hire-model-main hireUs">
    <div class="custom-model-inner">        
        <div class="modalClose">×</div>
        <div class="custom-model-wrap">
            <div class="pop-up-content-wrap">
                <h2><strong>Report a Bug</strong></h2>
                <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true" tabindex=""]'); ?>
            </div>
        </div>  
    </div>  
    <div class="bg-overlay"></div>
</div>



<script>
jQuery(function($){
    $("body").on("click touch", ".togglebar", function () {
        var activeTab = sessionStorage.getItem('activeTab');
        if (activeTab == '1') {
            $(".wrapPanel").addClass("wrapPanel-sm");
            $(".togglebar").find("img").attr("src", "<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png");

            $(".wrapPanel .brand-image").attr("style", "padding: 10px;");
            $(".wrapPanel .brand-image img").attr("src", "<?php echo plugins_url(); ?>/divi-builder/images/Divi-Webkitz-Icon-white.png?v=<?php echo time(); ?>");
            $(".wrapContent").addClass("wrapContent-sm");
            $(".togglebar").addClass("active");
        }

        if ($(this).hasClass("active")) {
            $(".togglebar").find("img").attr("src", "<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png");

            //$(this).find("i").addClass("fa-angle-left").removeClass("fa-angle-right");
            $(".wrapPanel").removeClass("wrapPanel-sm");
            $(".wrapPanel .brand-image").removeAttr("style");
            $(".wrapPanel .brand-image img").attr("src", "<?php echo builder_url; ?>images/Divi-Webkitz-Logo-white.png?v=<?php echo time(); ?>");
            $(".wrapContent").removeClass("wrapContent-sm");
            $(this).removeClass("active");
            sessionStorage.setItem('activeTab', "0");
        } else {
            $(this).find("i").addClass("fa-angle-right").removeClass("fa-angle-left");
            $(this).find("img").attr("src", "<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png");
            $(".wrapPanel .brand-image").attr("style", "padding: 10px;");
            $(".wrapPanel .brand-image img").attr("src", "<?php echo plugins_url(); ?>/divi-builder/images/Divi-Webkitz-Icon-white.png?v=<?php echo time(); ?>");
            $(".wrapPanel").addClass("wrapPanel-sm");
            $(".wrapContent").addClass("wrapContent-sm");
            $(this).addClass("active");
            sessionStorage.setItem('activeTab', "1");
        }

    });
});


</script>