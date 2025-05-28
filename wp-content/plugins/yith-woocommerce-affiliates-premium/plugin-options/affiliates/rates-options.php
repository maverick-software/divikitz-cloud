<?php
/**
 * Rates options
 *
 * @author  YITH
 * @package YITH\Affiliates\
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

$panel     = YITH_WCAF_Admin()->get_tab_instance();
$rules_doc = $panel && isset( $panel->rules_doc ) ? $panel->rules_doc : '';

/**
 * APPLY_FILTERS: yith_wcaf_rates_settings
 *
 * Filters the options available in the Rates subtab.
 *
 * @param array $options Array with options
 *
 * @return array
 */
return apply_filters(
	'yith_wcaf_rates_settings',
	array(
		'affiliates-rates' => array(
			'rates_section_start' => array(
				'type' => 'title',
				'desc' => '',
				'id'   => 'yith_wcaf_rates_settings',
			),
			'rates_table'         => array(
				'name'                 => _x( 'Rates', '[ADMIN] Rates table title', 'yith-woocommerce-affiliates' ),
				'desc'                 => sprintf(
					// translators: 1. Url to documentation page about rules.
					_x( 'Rate rules allow to override the global rate (defined in General options) for specific users, user roles or products.<br/>Please note: rules applied to products are higher in priority by default. <a target="_blank" href="%s">Read the documentation to better understand how rules work ></a>', '[ADMIN] Rates table description', 'yith-woocommerce-affiliates' ),
					$rules_doc
				),
				'type'                 => 'yith-field',
				'yith-type'            => 'list-table',
				'class'                => '',
				'list_table_class'     => 'YITH_WCAF_Rate_Rules_Admin_Table',
				'list_table_class_dir' => YITH_WCAF_INC . 'admin/admin-tables/class-yith-wcaf-rate-rules-table.php',
				'id'                   => 'yith_wcaf_rate_rules',
				'add_new_button'       => _x( 'Add rule', '[ADMIN] Add new rate rule button, in Rates tab', 'yith-woocommerce-affiliates' ),
				'add_new_url'          => '#',
			),
			'rates_section_end'   => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcaf_rates_settings',
			),
		),
	)
);
