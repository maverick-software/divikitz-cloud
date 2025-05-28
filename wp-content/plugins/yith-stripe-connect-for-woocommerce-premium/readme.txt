=== YITH Stripe Connect for WooCommerce ===

Contributors: yithemes
Tags: Stripe, Stripe Connect, commissions, e-commerce, WooCommerce, payments, yit, yith, yithemes
Requires at least: 6.0
Tested up to: 6.2
Stable tag: 2.23.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html/

Automatized payments with Stripe Connect.

== Description ==

Automatized payments with Stripe Connect.


= Features: =

== Installation ==

**Important**: First of all, you have to download and activate [WooCommerce](https://wordpress.org/plugins/woocommerce) plugin, because without it YITH Event Ticket for WooCommerce cannot work.

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site.
3. Activate `YITH Stripe Connect for WooCommerce` from Plugins page.

= 2.23.0 – Released on 06 June 2023 =

* New: support for WooCommerce 7.8
* Update: YITH plugin framework
* Dev: added new filter 'yith_wcsc_force_transaction_id'

= 2.22.0 – Released on 08 May 2023 =

* New: support for WooCommerce 7.7
* Update: YITH plugin framework

= 2.21.0 – Released on 11 April 2023 =

* New: support for WooCommerce 7.6
* Update: YITH plugin framework

= 2.20.0 – Released on 06 March 2023 =

* New: Support for WordPress 6.2
* New: support for WooCommerce 7.5
* Update: YITH plugin framework
* Tweak: always skip customer creation when using direct charges (customer cannot be created nor retrieved from connected account)
* Fix: round commission total before saving it
* Fix: register customer only when not executing direct charges

= 2.19.0 – Released on 08 February 2023 =

* New: support for WooCommerce 7.4
* Update: YITH plugin framework

= 2.18.0 – Released on 04 January 2023 =

* New: support for WooCommerce 7.3
* Update: YITH plugin framework
* Update: Stripe PHP library to version 10.3
* Update: Stripe API to version 2022-11-15
* Update: mpdf to version 8.1.4

= 2.17.0 – Released on 13 December 2022 =

* New: support for WooCommerce 7.2
* Update: YITH plugin framework
* Tweak: refactored code to allow third party dev to filter tokenization supports value for Stripe Connect gateway
* Tweak: allow to add query parameters in the return url

= 2.16.1 – Released on 14 November 2022 =

* Fix: patched security vulnerability

= 2.16.0 – Released on 26 October 2022 =

* New: support for WordPress 6.1
* New: support for WooCommerce 7.1
* Update: YITH plugin framework

= 2.15.0 – Released on 04 October 2022 =

* New: support for WooCommerce 7.0
* Update: YITH plugin framework
* Update: Stripe PHP library to version 9.6
* Update: Stripe API to version 2022-08-01

= 2.14.0 – Released on 13 September 2022 =

* New: support for WooCommerce 6.9
* Update: YITH plugin framework
* Dev: added new filter 'yith_wcsc_gateway_supports'
* Dev: added new parameter to 'yith_stripe_connect_create_payment_intent' filter

= 2.13.0 – Released on 02 August 2022 =

* New: support for WooCommerce 6.8
* Update: YITH plugin framework

= 2.12.0 – Released on 04 July 2022 =

* New: support for WooCommerce 6.7
* Update: YITH plugin framework
* Dev: new filter yith_wcsc_gateway_background_color
* Dev: new filter yith_wcsc_gateway_font_size
* Dev: new filter yith_wcsc_gateway_color
* Dev: new filter yith_wcsc_gateway_font_family
* Dev: new filter yith_wcsc_gateway_placeholder_color

= 2.11.0 – Released on 09 June 2022 =

* New: support for WooCommerce 6.6
* Update: YITH plugin framework

= 2.10.0 – Released on 10 May 2022 =

* New: support for WordPress 6.0
* New: support for WooCommerce 6.5
* Update: YITH plugin framework
* Tweak: slight changes to receivers query, to make use of wpdb->prepare method
* Tweak: improved product label on Receivers table
* Tweak: included variable products as a valid product to created commissions on

= 2.9.0 – Released on 05 April 2022 =

* New: support for WooCommerce 6.4
* Update: YITH plugin framework
* Tweak: added version compare on affiliate integration, to avoid fatal errors with older versions
* Fix: prevent deprecated message on new integration with YITH Affiliates

= 2.8.0 – Released on 03 March 2022 =

* New: support for WooCommerce 6.3
* Update: YITH plugin framework

= 2.7.0 – Released on 07 February 2022 =

* New: support for WooCommerce 6.2
* Update: YITH plugin framework

= 2.6.0 – Released on 10 January 2022 =

* New: support for WooCommerce 6.1
* New: support for WordPress 5.9
* Update: YITH plugin framework

= 2.5.0 – Released on 21 December 2021 =

* New: support for WooCommerce 6.0
* Update: YITH plugin framework
* Dev: new filter yith_wcsc_commission_fee_base

= 2.4.0 – Released on 05 November 2021 =

* New: support for WooCommerce 5.9
* Update: YITH plugin framework
* Fix: pay process cannot correctly save Stripe meta inside subscription objects
* Fix: prevent error if is not a valid object

= 2.3.0 – Released on 07 October 2021 =

* New: support for WooCommerce 5.8
* Update: YITH plugin framework
* Fix: subscription renew with expired cards and authentication required cards

= 2.2.1 – Released on 27 September 2021 =

* Update: YITH plugin framework
* Fix: debug info feature removed for all logged in users

= 2.2.0 – Released on 10 September 2021 =

* New: support for WooCommerce 5.7
* Update: YITH Plugin Framework
* Fix: only send source_transaction parameter with transfer request when origin order was processed with our gateway
* Fix: setting a decimal commission value in product page
* Dev: added some checks to prevent errors over null objects

= 2.1.17 – Released on 12 August 2021 =

* New: support for WooCommerce 5.6
* Update: YITH Plugin Framework
* Update: mPDF Library
* Fix: added check getting session intent to avoid unexpected error

= 2.1.16 – Released on 08 July 2021 =

* New: support for WordPress 5.8
* New: support for WooCommerce 5.5
* Update: YITH Plugin Framework
* Fix: added new check before updating CC form fields, in order to avoid interfering with other plugins

= 2.1.15 – Released on 11 June 2021 =

* New: support for WooCommerce 5.4
* Update: YITH Plugin Framework

= 2.1.14 – Released on 18 May 2021 =

* New: support for WooCommerce 5.3
* Update: YITH Plugin Framework
* Update: minified js files
* Tweak: added check over gateway existance fo avoid fatal errors
* Fix: Added missing npm dependencies
* Fix: 3D Secure validation on order-pay page
* Fix: wrong idempotency key when request doesnt contain order_id

= 2.1.13 – Released on 21 April 2021 =

* New: support for WooCommerce 5.2
* Update: YITH Plugin Framework
* Update: Stripe PHP library to 7.77.0
* Fix: possible error on older versions of PHP
* Dev: added filter yith_wcstripe_update_charge_params for update_charge params

= 2.1.12 – Released on 14 March 2021 =

* New: support for WordPress 5.7
* New: support for WooCommerce 5.1
* Update: YITH Plugin Framework
* Update: Stripe PHP library to 7.75.0
* Tweak: send metadata with destination payment on receiver account
* Tweak: added on_behalf_of parameter to destination charges
* Fix: fixed the stripe fee amount by calculating it on the products price
* Dev: added new yith_wcsc_check_exist_commission_args filter

= 2.1.11 – Released on 21 February 2021 =

* New: support for WooCommerce 5.0
* Update: YITH Plugin Framework
* Update: Spanish translation
* Fix: issue when trying to pay order with 0 total
* Dev: added new yith_wcstripe_allow_save_different_cards filter

= 2.1.10 – Released on 13 January 2021 =

* New: support for WooCommerce 4.9
* Update: plugin framework
* Fix: possible fatal error happening in rare cases when processing payment

= 2.1.9 – Released on 18 December 2020 =

* Update: plugin framework
* Tweak: updated various backend descriptions, to match current Stripe Dashboard layout
* Tweak: security improvements for checkout process
* Fix: check intent type before proceeding with payment
* Fix: show currency symbol based on order currency

= 2.1.8 – Released on 01 December 2020 =

* Update: plugin framework
* Update: Stripe PHP library to version 7.67.0
* Fix: usage of test_live boolean property

= 2.1.7 – Released on 01 December 2020 =

* New: support for WooCommerce 4.8
* New: Greek translation
* Update: plugin framework
* Update: Stripe PHP library to version 7.66.0
* Tweak: fixed some typos and added missing text domains
* Tweak: minor code refactoring on multiple files
* Fix: prevent possible fatal error when initializing API Handler
* Fix: prevent possible deadlock when creating gateway instance

= 2.1.6 – Released on 13 November 2020 =

* New: support for WordPress 5.6
* New: support for WooCommerce 4.7
* New: possibility to update plugin via WP-CLI
* New: Direct Charges and Destination Charges can now be used even if order contains more than one commission for the same receiver
* Update: plugin framework
* Update: Stripe PHP library to version 7.62.0
* Tweak: Direct Charges option is now available even if you have YITH WooCommerce Subscription active; option will be ignored if subscription products are added to cart
* Tweak: added note when commission is processed through alternative payment flows
* Tweak: added link to payment on order page
* Fix: save_cards parameter is used as string (yes/no) over the entire plugin
* Fix: direct charges failing because of wrong customer id sent within create_intent API call
* Fix: issue with verify intent, when processing Direct Charge
* Dev: removed deprecated .ready method from scripts
* Dev: added new yith_wcsc_card_code filter
* Dev: added new yith_wcsc_placeholder_card_number filter
* Dev: fixed typo in yith_wcsc_process_cart_item_commissions filter name

= 2.1.5 – Released on 16 October 2020 =

* New: support for WooCommerce 4.6
* Update: plugin framework
* Fix: make sure that my-account url ends with trailing slash, before appending stripe connect endpoint
* Dev: added new filter yith_wcsc_oauth_link_args
* Dev: added new filter yith_wcstripe_connect_transfer_args
* Dev: added new filter yith_wcsc_get_receiver_panel_args

= 2.1.4 – Released on 18 September 2020 =

* New: support for WooCommerce 4.5
* Update: plugin framework
* Update: Stripe PHP library to version 7.52
* Update: API version to 2020-08-27
* Fix: possible issue happening when trying to generate more than one commission from the same order

= 2.1.3 – Released on 18 August 2020 =

* New: support for WordPress 5.5
* New: support for WooCommerce 4.4
* Update: plugin framework
* Tweak: updated ajax handling functions to improve compatibility
* Tweak: perform correct do_action after paying YITH WooCommerce Affiliates commissions, to trigger affiliate email
* Tweak: Improved Integration with YITH WooCommerce Subscription 2.0
* Tweak: reviewed customer creation process
* Tweak: improved ajax update when saving receivers on admin panel
* Tweak: use of uniform transfer groups; added transfer group where missing on transfer creation
* Fix: notice when loading commission panel template on frontend
* Fix: commission not being created for variations
* Fix: wrong usage of $wp global
* Fix: The receivers table is hidden when creating a new product
* Dev: added filter yith_wcsc_receivers_result
* Dev: added postal code field to the Stripe payment
* Dev: Show the WC Stripe notice only for admins

= 2.1.2 – Released on 22 June 2020 =

* Update: plugin framework
* Tweak: added back \YITH_Stripe_Connect_Frontend::stripe_connect_account_page  method as deprecated, to improve compatibility

= 2.1.1 – Released on 19 June 2020 =

* New: support for WooCommerce 4.3-beta
* Update: plugin framework
* Fix: plugin unable to retrieve default gateway since last update


= 2.1.0 – Released on 17 June 2020 =

* New: support for WooCommerce 4.2
* New: support Direct Charges and Destination Charges (only under specific conditions)
* New: added MultiBanco gateway
* Update: Stripe PHP library to version 7.37.1
* Update: plugin framework
* Tweak: unified db update process
* Tweak: avoid executing dbDelta when not required
* Fix: changed currency to uppercase to do correct check of currencies
* Fix: filter by user when exporting from admin side
* Dev: added yith_wcstripe_connect_transfer_metadata filter, specific to transfers metadata
* Dev: added yith_wcsc_meta_box_available_roles filter

= 2.0.4 – Released on 14 March 2020 =

* New: support for WordPress 5.4
* New: support for WooCommerce 4.0
* New: support for API 2020-03-02
* Update: Stripe PHP library to version 7.27.2
* Update: plugin framework
* Update: Dutch language
* Tweak: improved check over WooCommerce existance
* Tweak: idempotency key now is generated basing on order id
* Tweak: code refactor
* Dev: added new function to get charge object
* Dev: added new function to get balance transaction object

= 2.0.3 – Released on 24 December 2019 =

* New: support for WooCommerce 3.9
* Update: plugin framework
* Update: Italian language
* Update: Spanish language
* Update: API version to 2019-12-03
* Updated: Stripe library to 7.14.2
* Tweak: change export action name, to avoid problems when event ticket is installed
* Fix: subscription renew for guest users

= 2.0.2 – Released on 08 November 2019 =

* New: support for WordPress 5.3
* New: support for WooCommerce 3.8
* New: support for 2019-11-05 API
* Update: Plugin framework
* Update: StripePHP library
* Fix: notice when registering failed payment attempt
* Fix: deprecated function called on verify_intent error handling
* Dev: added new parameter to yith_wcsc_create_commission filter

= 2.0.1 – Released on 20 September 2019 =

* Tweak: minified checkout js
* Tweak: reviewed renew_needs_action subject to remove HTML string
* Tweak: moved methods to set card as default and delete card from gateway to main class
* Tweak: changed conditions that triggers renew_needs_action email sending, to be more specific
* Tweak: after registering failed renew attempt, get order again to account for any status change
* Update: Italian language
* Update: Dutch language
* Fix: fixed language files name
* Fix: payment intent missing customer for guest users

= 2.0.0 – Released on 13 September 2019 =

* New: support for SCA-ready payment methods
* New: extended card management, even without YITH WooCommerce Subscription enabled
* New: now you can set up the email sent to customers when the renewal requires authentication (only when used with YITH WooCommerce Subscription)
* New: support for 2019-09-09 API version
* Update: internal plugin framework
* Update: Stripe PHP to version 7.0.2
* Update: Italian language
* Fix: avoiding duplicated connection attempts
* Dev: added new action 'yith_wcsc_after_disconnect_with_stripe' after user disconnect from Stripe
* Dev: filter yith_wcsc_disconnect_from_stripe_button_text and filter yith_wcsc_connect_with_stripe_button_text

= 1.1.8 - Released on 12 August 2019 =

* New: WooCommerce 3.7 support
* Tweak: add maxlenght to expiry date
* Tweak: now permit decimals to commission rate
* Update: internal plugin framework
* Update: Italian language
* Update: minify js files
* Dev: added new param to yith_wcsc_create_commission filter
* Dev: added new hook after connect with stripe action
* Dev: new filter yith_wcsc_args_create_charge
* Dev: added new filter yith_wcsc_commission_value
* Dev: add parameters to the filter yith_wcsc_commission_value

= 1.1.7 - Released on 30 May 2019 =

* Tweak: add no cache headers
* Tweak: improve how to get the CSV file
* Tweak: added ignore_user_abort
* Update: internal plugin framework
* Update: Updated .pot
* Fix: preventing notice when user_id not found in the receiver array
* Fix: warning when creating export CSV
* Fix: Fixed users could get all commissions in csv and pdf files
* Fix: Prevent error of insufficient funds of a card
* Fix: Removed undefined method add_block that generated fatal error with subscription item
* Fix: Fixed subscription renew orders payment issue
* Dev: Added wc-credit-card-form among yith-stripe-connect-js script dependencies, to be sure that it is always loaded at checkout
* Dev: Added new filters 'yith_wcsc_prepare_columns_list' and 'yith_wcsc_prepare_rows_list'

= 1.1.6 - Released on 17 April 2019 =

* Fix: js error preventing card submission

= 1.1.5 - Released on 17 April 2019 =

* New: WooCommerce 3.6 support
* Tweak: removed unused fonts from MPDF library
* Update: internal plugin framework
* Fix: retrieve subscriptions from session when needed
* Fix: js error at checkout possibly causing payment failure
* Dev: added filters yith_wcsc_add_tax_to_commission and yith_wcsc_order_total_with_tax
* Dev: added filter yith_wcsc_account_menu_item

= 1.1.4 - Released on 19 February 2019 =

* Update: Updated plugin FW
* Update: Updated Stripe-PHP lib to revision 6.27
* Update: Updated Dutch translation
* Dev: new filter yith_wcstripe_connect_metadata

= 1.1.3 - Released on 31 December 2018 =

* New: Support WordPress 5.0.2
* Tweak: Allow payments with source when customer already registered one previously
* Update: Updated plugin FW
* Update: Updated Dutch language
* Update: Updated .pot
* Fix: fixed issue with subscriptions
* Fix: Fixed subscription processing with new card
* Fix: Fixed issue with new sources, when purchasing non subscribed products

= 1.1.2 - Released on 25 October 2018 =

* New: WooCommerce 3.5 support
* Tweak: updated plugin framework

= 1.1.1 - Released on 15 October 2018 =

* New: WooCommerce 3.5-rc support
* New: WordPress 4.9.8 support
* Tweak: updated plugin framework
* Update: Italian language
* Update: Dutch language
* Fix: some warning and notice if $order doesn't exist
* Fix: name of american express logo file
* Fix: gateway now works on page "pay order"
* Fix: minified js files
* Dev: added filter yith_wc_stripe_connect_credit_cards_logos_width

= 1.1.0 Released on 30 July 2018 =
* New: Integration with YITH WooCommerce Subscription Premium from v 1.4.6
* Update: Language files
* Update: plugin framework to latest revision

= 1.0.6 Released on 12 June 2018 =

* Dev: yith_wcsc_process_product_commissions to check if process the current product or not
* Dev: yith_wcsc_process_order_commissions to check if process the current order or not

= 1.0.5 Released on 11 June 2018 =

* New French translation (thanks to Josselyn Jayant)
* Fix: Commissions with notes above 320 characters are not saved
* Fix: Prevent fatal error on unserialize function

= 1.0.4 Released on 04 June 2018 =

* Update: Spanish language

* New: YITH WooCommerce Multi Vendor (3.0.0 +) support (admin can now pay vendors' commissions using Stripe Connect)
* Dev: added yith_wcsc_payment_complete action to add charge_id in stripe transfers

= 1.0.3 Released on 28 May 2018 =

New: WooCommerce 3.4 compatibility
New: WordPress 4.9.6 compatibility
New: GDPR compliance
New: Spanish language
New: Italian language
New: Dutch language
New: added option to show Name on Card field at checkout
Tweak: now gateway works on pay page too
Tweak: added transfer group to charges
Update: plugin framework to latest revision
Dev: added filter 'yith_wcsc_schedule_timestamp_change_format'
Dev: added filter 'yith_wc_stripe_connect_credit_cards_logos'
Dev: added filter 'yith_wcsc_connect_account_template_args' to let third party code filter the connect template args
Dev: added filter 'yith_wcsc_account_page_script_data' to let third party code filter data in localize scripts for disconnection
Dev: added filter 'yith_wcsc_order_button_text'

= 1.0.2 Released on 31 January 2018 =

New: Support to YITH Plugin Framework 3.0.11
Fix: Redirect URI messages.
Fix: Backbone modal window, now can display for all templates.

= 1.0.1 Released on 30 January 2018 =

Fix: Issue with Endpoint.
New: Support to WooCommerce 3.3.0 RC2

= 1.0.0 Released on 29 January 2018 =

* Initial release

