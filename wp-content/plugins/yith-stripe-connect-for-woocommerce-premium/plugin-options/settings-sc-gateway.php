<?php
/**
 * Settings for Stripe Connect Gateway.
 */

$payment_flow_description = __( 'Choose the alternative flow to apply, when it is possible for the order', 'yith-stripe-connect-for-woocommerce' );
$payment_flow_description .= '<ul>';
$payment_flow_description .= sprintf(
	'<li><b>%1$s:</b> %2$s</li>',
	__( 'None', 'yith-stripe-connect-for-woocommerce' ),
	__( 'keep using separate Transfers and Charges for any transaction, even one eligible for other flows. Accounts must be located in the same region (US/EU); Stripe fees are due to platform.', 'yith-stripe-connect-for-woocommerce' )
);
$payment_flow_description .= sprintf(
	'<li><b>%1$s:</b> %2$s</li>',
	__( 'Destination charges', 'yith-stripe-connect-for-woocommerce' ),
	__( 'are created on the platform, but as part of the charge operation, funds are transferred to the connected account. Stripe fees are split between platform and connected account.', 'yith-stripe-connect-for-woocommerce' )
);

$payment_flow_description .= sprintf(
	'<li><b>%1$s:</b> %2$s</li>',
	__( 'Direct charges', 'yith-stripe-connect-for-woocommerce' ),
	__( 'charge will be created directly on connected account, while platform will retain its fee. Will disable saved cards. Stripe fees are due to connected account. Not available when order includes subscriptions.', 'yith-stripe-connect-for-woocommerce' )
);

$payment_flow_description .= '</ul>';

/** APPLY_FILTERS: yith_wcsc_general_settings
*
* Filter the default plugin general settings.
*/
return apply_filters(
	'yith_wcsc_general_settings',
	array(
		'enabled'                  => array(
			'title'   => _x( 'Enable/Disable', 'Settings, activate or deactivate Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
			'type'    => 'checkbox',
			'label'   => _x( 'Enable Stripe Connect Gateway', 'Settings, Label for checkbox that enables/disables Stripe Connect', 'yith-stripe-connect-for-woocommerce' ),
			'default' => 'yes',
			'id'      => 'yith_wcsc_on_off',
		),
		'label'                    => array(
			'title'       => __( 'Label Settings', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' => __( 'Change the title and description that Stripe Connect displays on Checkout', 'yith-stripe-connect-for-woocommerce' ),
			'id'          => 'yith_wcsc_label_settings',
		),
		'label-title'              => array(
			'title'       => __( 'Title', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => __( 'This controls the title that users see during checkout.', 'yith-stripe-connect-for-woocommerce' ),
			'default'     => __( 'Credit Card (Stripe Connect)', 'yith-stripe-connect-for-woocommerce' ),
			'desc_tip'    => true,
		),
		'label-description'        => array(
			'title'       => __( 'Description', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'desc_tip'    => true,
			'description' => __( 'This controls the description that users see during checkout.', 'yith-stripe-connect-for-woocommerce' ),
			'default'     => __( "Pay via Stripe Connect; you can pay with your credit card if you don't have a Stripe account.", 'yith-stripe-connect-for-woocommerce' ),
		),
		'credit-cards-logo'        => array(
			'id'       => 'yith_wcsc_logo_card',
			'title'    => __( 'Display card logo', 'yith-stripe-connect-for-woocommerce' ),
			'type'     => 'multiselect',
			'desc'     => __( 'Choose the card logo that you want to show', 'yith-stripe-connect-for-woocommerce' ),
			'desc_tip' => true,
			'options'  => array(
				'american-express' => 'A.Express',
				'discover'         => 'Discover',
				'mastercard'       => 'Mastercard',
				'visa'             => 'Visa',
				'diners'           => 'Diners Club',
				'jcb'              => 'JCB',
			),
		),
		'show-name-on-card'        => array(
			'id'          => 'yith_wcsc_show_name_on_card',
			'title'       => __( 'Display "Name on Card" field during checkout', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'checkbox',
			'description' => __( 'Choose whether to show "Name on Card" field of Credit Card form', 'yith-stripe-connect-for-woocommerce' ),
			'default'     => 'no',
		),
		'save-cards'               => array(
			'id'          => 'yith_wcsc_save_cards',
			'title'       => __( 'Save cards', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'checkbox',
			'description' => __( 'Choose whether to save cards used at checkout (this is required when YITH WooCommerce Subscription is enabled)', 'yith-stripe-connect-for-woocommerce' ),
			'default'     => 'no',
		),
		'api'                      => array(
			'title'       => __( 'API Settings', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' => __( 'Type here your API Keys from Stripe Connect Account. This step is mandatory for the plugin to work', 'yith-stripe-connect-for-woocommerce' ),
			'id'          => 'yith_wcsc_label_settings',
		),
		'api-prod-client-id'       => array(
			'title'       => __( 'Live mode client ID', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => '<a href="https://dashboard.stripe.com/settings/applications" target="_blank">Stripe Dashboard > Settings > Connect > Integration ></a> <b>' . __( 'Live mode client ID', 'yith-stripe-connect-for-woocommerce' ) . '</b> ' . __( 'section (Check before "View test data" is <b>disabled</b> )', 'yith-stripe-connect-for-woocommerce' ),
		),
		'api-public-live-key'      => array(
			'title'       => __( 'Publishable live key', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => '<a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard > Developers > Api keys > </a> <b>' . __( 'Standard keys', 'yith-stripe-connect-for-woocommerce' ) . '</b> ' . __( 'section (Check before "View test data" is <b>disabled</b> )', 'yith-stripe-connect-for-woocommerce' ),
		),
		'api-secret-live-key'      => array(
			'title'       => __( 'Secret live key', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => '<a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard > Developers > Api keys ></a> <b>' . __( 'Standard keys', 'yith-stripe-connect-for-woocommerce' ) . '</b> ' . __( 'section, <b>Reveal live key</b> (Check before "View test data" is <b>disabled</b> )', 'yith-stripe-connect-for-woocommerce' ),
		),
		'test-live'                => array(
			'title' => __( 'Test mode', 'yith-stripe-connect-for-woocommerce' ),
			'type'  => 'checkbox',
			'label' => __( 'Enable test mode', 'yith-stripe-connect-for-woocommerce' ),
			'id'    => 'yith_wcsc_test_live_mode',
		),
		'api-dev-client-id'        => array(
			'title'       => __( 'Test mode client ID', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => '<a href="https://dashboard.stripe.com/settings/applications" target="_blank">Stripe Dashboard > Settings > Connect > Integration ></a> <b>' . __( 'Test mode client ID', 'yith-stripe-connect-for-woocommerce' ) . '</b> ' . __( 'section (Check before "View test data" is <b>enabled</b> )', 'yith-stripe-connect-for-woocommerce' ),
			'class'       => 'yith_wcsc_test_live_item',
		),
		'api-public-test-key'      => array(
			'title'       => __( 'Publishable test key', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => '<a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard > Developers > Api keys ></a> <b>' . __( 'Standard API keys', 'yith-stripe-connect-for-woocommerce' ) . '</b> ' . __( 'section (Check before "View test data" is <b>enabled</b> )', 'yith-stripe-connect-for-woocommerce' ),
			'class'       => 'yith_wcsc_test_live_item',
		),
		'api-secret-test-key'      => array(
			'title'       => __( 'Secret test key', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'text',
			'description' => '<a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard > Developers > Api keys ></a> <b>' . __( 'Standard API keys', 'yith-stripe-connect-for-woocommerce' ) . '</b> ' . __( 'section, <b>Reveal live key</b> (Check before "View test data" is <b>enabled</b> )', 'yith-stripe-connect-for-woocommerce' ),
			'class'       => 'yith_wcsc_test_live_item',
		),
		'payment-flow'             => array(
			'title'       => __( 'Payment Flow settings', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' => __( 'Choose how you want to transfer money to receivers', 'yith-stripe-connect-for-woocommerce' ),
		),
		'enable-alternative-flows' => array(
			'title' => __( 'Enable alternative flows', 'yith-stripe-connect-for-woocommerce' ),
			'type'  => 'checkbox',
			'label' => __( 'By default plugin will create separate charges and transfers; anyway, for order that only requires one transfer, you can opt for one of the other supported methods.', 'yith-stripe-connect-for-woocommerce' ),
			'id'    => 'yith_wcsc_alternative_payment_flows',
		),
		'alternative-flow'         => array(
			'id'          => 'yith_wcsc_alternative_flow',
			'title'       => __( 'Alternative flow', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'select',
			'description' => $payment_flow_description,
			'desc_tip'    => false,
			'options'     => array(
				'none'                => __( 'None', 'yith-stripe-connect-for-woocommerce' ),
				'destination_charges' => __( 'Destination charges', 'yith-stripe-connect-for-woocommerce' ),
				'direct_charges'      => __( 'Direct charges', 'yith-stripe-connect-for-woocommerce' ),
			),
		),
		'payment'                  => array(
			'title'       => __( 'Delay settings', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' => __( 'Indicate after how many days the payment will be sent to the receivers (this option will apply only for Separate Charges and Transfers)', 'yith-stripe-connect-for-woocommerce' ),
		),
		'payment-delay'            => array(
			'title'       => __( 'Delay time', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'number',
			'id'          => 'yith_wcsc_payment_delay',
			'description' => __( 'Instant payment if empty or with "0" value', 'yith-stripe-connect-for-woocommerce' ),
			'desc_tip'    => true,
		),
		'others'                   => array(
			'title' => __( 'Advanced', 'yith-stripe-connect-for-woocommerce' ),
			'type'  => 'title',
		),
		'log'                      => array(
			'title'       => __( 'Log', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable log', 'yith-stripe-connect-for-woocommerce' ),
			'id'          => 'yith_wcsc_log',
			'description' =>
				// translators: 1. Path to log file.
				sprintf( __( 'Log Stripe Connect events inside <code>%s</code>', 'yith-stripe-connect-for-woocommerce' ), wc_get_log_file_path( 'stripe-connect' ) ) . '<br />' . sprintf( __( 'You can also consult the logs in your <a href="%s">Log Dashboard</a>, without checking this option.', 'yith-stripe-connect-for-woocommerce' ), 'https://dashboard.stripe.com/logs' ),

		),
		'commissions-exceeded'     => array(
			'title' => __( 'Exceeding commissions', 'yith-stripe-connect-for-woocommerce' ),
			'type'  => 'checkbox',
			'label' => __( 'Enable this option to allow commission creation when the commissions exceed the order total', 'yith-stripe-connect-for-woocommerce' ),
			'id'    => 'yith_wcsc_commissions_exceeded',
		),
		'webhooks'                 => array(
			'title'       => __( 'Config Webhooks', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' =>
				// translators: 1. Webhook url to configure. 2. Url to Stripe Dashboard settings page.
				sprintf( __( 'You can configure the webhook url <code>%1$s</code> in your <a href="%2$s" target="_blank">Stripe Dashboard > Developers > Webhooks</a> (Endpoints receiving events from Connect applications) section. All the webhooks for all your connected users will be sent to this endpoint.', 'yith-stripe-connect-for-woocommerce' ), esc_url( site_url( '/wc-api/sc_webhook_event' ) ), 'https://dashboard.stripe.com/account/webhooks' ) . '<br /><br />'
				. __( "It's important to note that only test webhooks will be sent to your development webhook url*. Yet, if you are working on a live website, <b>both live and test</b> webhooks will be sent to your production webhook URL. This is due to the fact that you can create both live and test objects under a production application.", 'yith-stripe-connect-for-woocommerce' ) . ' — ' . __( "we'd recommend that you check the livemode when receiving an event webhook.", 'yith-stripe-connect-for-woocommerce' ) . '<br /><br />'
				// translators: 1. Url to stripe doc.
				. sprintf( __( 'For more information about webhooks, see the <a href="%s" target="_blank">webhook documentation</a>', 'yith-stripe-connect-for-woocommerce' ), 'https://stripe.com/docs/webhooks' ),
		),
		'redirect-uris'            => array(
			'title'       => __( 'Config Redirect URIs', 'yith-stripe-connect-for-woocommerce' ),
			'type'        => 'title',
			'description' =>
				// translators: 1. Url sto Stripe Dashboard settings page. 2. Redirect URI.
				sprintf( __( 'A <b>Redirection URI is required</b> when users connect their account to your site. Go to <a href="%1$s" target="_blank">Stripe Dashboard > Settings > Connect > Integration ></a> <b>Redirects</b> section and add the following URl to redirect: <code>%2$s</code>. Redirects URI can be defined on test and live mode, we would recommend to test both scenarios.', 'yith-stripe-connect-for-woocommerce' ), 'https://dashboard.stripe.com/account/applications/settings', esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'stripe-connect' ) ),
		),
	)
);
