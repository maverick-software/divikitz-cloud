<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WFACP_Compatibility_With_Thrive_Theme_builder {


	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_remove_is_checkout' ), - 1 );
		add_filter( 'thrive_theme_shortcode_prefixes', function ( $prefixes ) {
			array_push( $prefixes, 'wfacp_' );

			return $prefixes;
		} );
		add_action( 'tve_editor_print_footer_scripts', function () {

			?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', (event) => {
                    if (typeof TVE !== "undefined") {
                        TVE.add_filter('tve.allowed.empty.posts.type', function (list) {
                            list.push('wfacp_checkout');
                            return list;
                        });
                    }
                });


            </script>
			<?php
		} );
	}

	public function maybe_remove_is_checkout() {
		if ( is_editor_page() ) {
			add_filter( 'woocommerce_is_checkout', '__return_false' );
		}
	}


}

if ( ! function_exists( 'thrive_theme' ) ) {
	return;
}
WFACP_Plugin_Compatibilities::register( new WFACP_Compatibility_With_Thrive_Theme_builder(), 'thrive_theme' );

