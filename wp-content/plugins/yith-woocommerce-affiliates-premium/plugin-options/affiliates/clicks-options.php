<?php
/**
 * Clicks report page
 *
 * @author  YITH
 * @package YITH\Affiliates
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCAF' ) ) {
	exit;
} // Exit if accessed directly

/**
 * APPLY_FILTERS: yith_wcaf_clicks_settings
 *
 * Filters the options available in the Visits subtab.
 *
 * @param array $options Array with options
 *
 * @return array
 */
return apply_filters(
	'yith_wcaf_clicks_settings',
	array(
		'affiliates-clicks' => array(
			'clicks_section_start' => array(
				'type' => 'title',
				'desc' => '',
				'id'   => 'yith_wcaf_clicks_settings',
			),
			'clicks_table'         => array(
				'name'                 => _x( 'Visits', '[ADMIN] Clicks table title', 'yith-woocommerce-affiliates' ),
				'type'                 => 'yith-field',
				'yith-type'            => 'list-table',
				'class'                => '',
				'list_table_class'     => 'YITH_WCAF_Clicks_Admin_Table',
				'list_table_class_dir' => YITH_WCAF_INC . 'admin/admin-tables/class-yith-wcaf-clicks-table.php',
				'id'                   => 'clicks',
			),
			'clicks_section_end'   => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcaf_clicks_settings',
			),
		),
	)
);
