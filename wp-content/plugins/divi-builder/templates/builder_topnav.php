<!-- Top Nav Bar -->
<div class="topWrapmenu">
    <div>
        <a href="javascript:void(0);" class="togglebar">
            <img src="<?php echo plugins_url(); ?>/divi-builder/images/right-angle.png"/>
        </a>
    </div>

    <div class="rowWrap">
        <div class="flex-4">
            <?php
            global $wp_roles;
            global $ultimatemember;
            $user = wp_get_current_user();

            $current_user_id = $user->ID;
            $current_user_email = $user->user_email;
            $current_registration = $user->user_registered;

            $display_nm = get_user_meta($current_user_id, "display_name", true);
            $nickname = get_user_meta($current_user_id, "nickname", true);
            $timestemp = strtotime(date("Y-m-d H:i:s"));
            $nonce = wp_create_nonce('um_upload_nonce-' . $timestemp);

            um_fetch_user($current_user_id);

            $user_profile = get_user_meta($current_user_id, "profile_photo", true);

            $avatar_uri = um_get_avatar_uri(um_profile('profile_photo'), 32);
            if ($user_profile == "") {
                $avatar_uri = builder_url . "images/profile-icon.png";
            }
            
            global $wp_query;
            $post_id = $wp_query->post->ID;
            ?>

            <h5><?php echo get_the_title($post_id); ?></h5>
        </div>

        <div class="flex-8 text-right">
            <ul class="topMenuUser">
                <a href="javascript:void(0)"></a>
                <a href="<?php echo site_url().'/repository/kitz-pro-builder.zip'; ?>" download><i class="far fa-file-archive"></i> Download Plugin</a>

                <?php //if(is_page(264)){ ?>
                   <!--  <a href="javascript:void(0)" class="videoButton" data-id="<?= get_field('neo_tutorial_youtube_url', 'options'); ?>">
                    <i class="fas fa-video"></i> Tutorials</a> -->
                     <a href="<?php echo site_url('/tutorials'); ?>">
                    <i class="fas fa-video"></i> Tutorials</a>
                <?php //} ?>

                <!-- <li><a href="#"><i class="far fa-bell"></i></a></li>                                     -->
                <li><?php echo do_shortcode('[profile_details]'); ?></li>
            </ul>
        </div>
    </div>
</div>
<!-- End Top Nav Bar -->