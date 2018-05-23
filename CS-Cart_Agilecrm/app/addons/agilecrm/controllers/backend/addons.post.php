<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

if ( !defined('AREA') ) { die('Access denied');    }

use Tygh\Settings;

if($mode == "update")
{
	if ($_REQUEST['addon'] == 'agilecrm') {

		$agile_domain = fn_agilecrm_get_agile_domain();
		$agile_email = fn_agilecrm_get_agile_email();
		$agile_rest_api_key = fn_agilecrm_get_agile_rest_api_key();	

		$view = fn_agilecrm_get_view_object();
		$view->assign('agile_domain', $agile_domain);
		$view->assign('agile_email', $agile_email);
		$view->assign('agile_rest_api_key', $agile_rest_api_key);

	}

	if($_REQUEST['addon'] == 'agilecrm' && $_REQUEST['selected_section'] == 'agilecrm_general'){
		
		$import_customers =  fn_agilecrm_get_agile_import_customers();

		if($import_customers == "Y"){
			
			list($users, $search) = fn_get_users($_REQUEST, $auth);
			import_customers_to_agile($users);
		}	
	}
}