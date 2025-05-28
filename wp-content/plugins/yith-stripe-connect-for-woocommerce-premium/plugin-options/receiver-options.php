<?php
/*
* This file belongs to the YITH framework.
*
* This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://www.gnu.org/licenses/gpl-3.0.txt
*/

return array(

	/** APPLY_FILTERS: yith_wcsc_receiver_options
	*
	* Filter the default plugin receiver options tab.
	*/
	'receiver' => apply_filters( 'yith_wcsc_receiver_options', array(
			'receiver_panel' => array(
				'type'         => 'custom_tab',
				'action'       => 'yith_wcsc_receiver_panel',
				'hide_sidebar' => true
			)
		)
	)
);