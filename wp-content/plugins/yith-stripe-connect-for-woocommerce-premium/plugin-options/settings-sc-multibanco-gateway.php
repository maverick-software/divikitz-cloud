<?php

/** APPLY_FILTERS: yith_wcsc_multibanco_settings
 *
 * Filter the default plugin multibanco settings.
 */
return apply_filters(
	'yith_wcsc_multibanco_settings',
	array(
		'enabled' => array(
			'title'   => _x( 'Enable/Disable', 'Settings, activate or deactivate Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
			'type'    => 'checkbox',
			'label'   => _x( 'Enable Stripe Connect MultiBanco Gateway', 'Settings, Label for checkbox that enables/disables Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
			'default' => 'no',
		),
		'label' => array(
			'title'       => __( 'Label Settings', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' => __( 'Change the title and description that Stripe Connect displays on Checkout', 'yith-stripe-connect-for-woocommerce' ),
		),
		'title' => array(
			'title'       => __( 'Title', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => __( 'This controls the title that users see during checkout.', 'yith-stripe-connect-for-woocommerce' ),
			'default'     => __( 'MultiBanco (Stripe Connect)', 'yith-stripe-connect-for-woocommerce' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'desc_tip'    => true,
			'description' => __( 'This controls the description that users see during checkout.', 'yith-stripe-connect-for-woocommerce' ),
			'default'     => __( 'Pay via MultiBanco.', 'yith-stripe-connect-for-woocommerce' ),
		),
		'api' => array(
			'title'       => __( 'API Settings', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' => sprintf(
				// translators: 1. Href to main settings tab.
				__( 'This gateway will use API Credentials that you configured in main settings tab. You can check your configuration <a href="%s">here</a>', 'yith-stripe-connect-for-woocommerce' ),
				add_query_arg( 'page', 'yith_wcsc_panel', admin_url( 'admin.php' ) )
			),
		),
		'others' => array(
			'title' => __( 'Advanced', 'yith-stripe-connect-for-woocommerce' ),
			'type'  => 'title',
		),
		'log' => array(
			'title'       => __( 'Log', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable log', 'yith-stripe-connect-for-woocommerce' ),
			'description' => sprintf(
				// translators: 1. Multibanco log file path. 2. Stripe dashboard url.
				__( 'Log Stripe Connect events inside <code>%1$s</code><br/>You can also consult the logs in your <a href="%2$s">Log Dashboard</a>, without checking this option.', 'yith-stripe-connect-for-woocommerce' ),
				wc_get_log_file_path( 'stripe-connect-multibanco' ),
				'https://dashboard.stripe.com/logs'
			),
		),
	)
);
