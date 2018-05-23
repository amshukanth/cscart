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
use Tygh\Settings;
use Tygh\Registry;

/**
 * @return string Notification text displayed at the add-on settings.
 */
function fn_agilecrm_settings_account_info()
{
    return __('agilecrm.account_ask');
}

function fn_agilecrm_get_agile_domain($company_id = null)
{
    if (!fn_allowed_for('ULTIMATE')) {
        $company_id = null;
    }

    return Settings::instance()->getValue('agile_domain', 'agilecrm', $company_id);
}

function fn_agilecrm_get_agile_email($company_id = null)
{
    if (!fn_allowed_for('ULTIMATE')) {
        $company_id = null;
    }

    return Settings::instance()->getValue('agile_email', 'agilecrm', $company_id);
}

function fn_agilecrm_get_agile_rest_api_key($company_id = null)
{
    if (!fn_allowed_for('ULTIMATE')) {
        $company_id = null;
    }

    return Settings::instance()->getValue('agile_rest_api_key', 'agilecrm', $company_id);
}

function fn_agilecrm_get_agile_import_customers($company_id = null)
{
    if (!fn_allowed_for('ULTIMATE')) {
        $company_id = null;
    }

    return Settings::instance()->getValue('import_customers', 'agilecrm', $company_id);
}

function fn_agilecrm_get_view_object()
{
    if (class_exists('Tygh')) {
        $view = Tygh::$app['view'];
    } else {
        $view = Registry::get('view');
    }
    return $view;
}

function fn_agilecrm_update_user_profile_post($user_id, $user_data, $action){


    $sync_customer = Settings::instance()->getValue('sync_contacts', 'agilecrm');
    if($sync_customer == "Y"){
        $customer_email = fn_agilecrm_get_agile_email();
        
        $result = curl_wrap("contacts/search/email/".$user_data['email'], null, "GET", "application/json");
        $result = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
        
        if(count($result)>0)
            $contact_id = $result->id;
        else
            $contact_id = "";

        if($contact_id == "")
        {
            $contact_json = array(
                  "tags"=>array("CS Cart"),
                  "properties"=>array(
                    array(
                      "name"=>"first_name",
                      "value"=> "FN",
                      "type"=>"SYSTEM"
                    ),
                     array(
                      "name"=>"last_name",
                      "value"=>"LN",
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"email",
                      "value"=>$user_data['email'],
                      "type"=>"SYSTEM"
                    ),  
                  )
                );
            $contact_json = json_encode($contact_json);
            $curln = curl_wrap("contacts", $contact_json, "POST", "application/json");

        }
        else
        {
            $contact_json_update = array(
                  "id"=>$contact_id, //It is mandatory field. Id of contact
                  "tags"=>array("CS cart"),
                  "properties"=>array(
                    array(
                      "name"=>"first_name",
                      "value"=> $user_data['b_firstname'],
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"last_name",
                      "value"=>$user_data['b_lastname'],
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"email",
                      "value"=>$user_data['email'],
                      "type"=>"SYSTEM"
                    ),  
                    array(
                        "name"=>"address",
                        "value"=>$user_data['b_address'],
                        "type"=>"SYSTEM"
                    ),
                    array(
                        "name"=>"phone",
                        "value"=>$user_data['b_phone'],
                        "type"=>"SYSTEM"
                    )
                  )
                );
            $contact_json_update = json_encode($contact_json_update);
            $curlupdate = curl_wrap("contacts/edit-properties", $contact_json_update, "PUT", "application/json");
        }
    }

    return "";
}

function import_customers_to_agile($users){

    foreach($users as $user){
      $user_id = $user['user_id'];

      $user_data = fn_get_user_info($user_id);

      $user_email = $user_data['email'];
      $user_firstname = $user_data['b_firstname'];
      $user_lastname = $user_data['b_lastname'];
      $user_companyname = $user_data['company'];
      $user_phone = $user_data['b_phone'];
      $user_street = $user_data['b_address_2'] == '' ? $user_data['b_address'] : $user_data['b_address'].','.$user_data['b_address_2'];
      $user_city = $user_data['b_city'];
      $user_state = $user_data['b_state'];
      $country = getCountryName($user_data['b_country']);
      $user_address = array(
                          "address"=>$user_street,
                          "city"=>$user_city,
                          "state"=>$user_state,
                          "country"=>$country
                           );

      $result = curl_wrap("contacts/search/email/".$user_email, null, "GET", "application/json");
      $result = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);

      if(count($result)>0)
          $contact_id = $result->id;
      else
          $contact_id = "";

      if($contact_id == ""){
          $contact_json = array(
            "tags"=>array("CS Cart"),
            "properties"=>array(
              array(
                "name"=>"first_name",
                "value"=>$user_firstname,
                "type"=>"SYSTEM"
              ),
              array(
                "name"=>"last_name",
                "value"=>$user_lastname,
                "type"=>"SYSTEM"
              ),
              array(
                "name"=>"email",
                "value"=>$user_email,
                "type"=>"SYSTEM"
              ),  
              array(
                  "name"=>"address",
                  "value"=>json_encode($user_address),
                  "type"=>"SYSTEM"
              ),
              array(
                  "name"=>"phone",
                  "value"=>$user_phone,
                  "type"=>"SYSTEM"
              )
            )
          );
          $contact_json = json_encode($contact_json);
          $curln = curl_wrap("contacts", $contact_json, "POST", "application/json");
      }
      else{
          $contact_json_update = array(
            "id"=>$contact_id, //It is mandatory field. Id of contact
            "tags"=>array("CS Cart"),
            "properties"=>array(
              array(
                "name"=>"first_name",
                "value"=>$user_firstname,
                "type"=>"SYSTEM"
              ),
              array(
                "name"=>"last_name",
                "value"=>$user_lastname,
                "type"=>"SYSTEM"
              ),
              array(
                "name"=>"email",
                "value"=>$user_email,
                "type"=>"SYSTEM"
              ),  
              array(
                  "name"=>"address",
                  "value"=>json_encode($user_address),
                  "type"=>"SYSTEM"
              ),
              array(
                  "name"=>"phone",
                  "value"=>$user_phone,
                  "type"=>"SYSTEM"
              )
            )
          );
          $contact_json_update = json_encode($contact_json_update);
          $curlupdate = curl_wrap("contacts/edit-properties", $contact_json_update, "PUT", "application/json");
      }
    }

}

function fn_agilecrm_change_order_status(&$status_to, &$status_from, &$order, $force_notification, $order_statuses){

    $products = $order['products'];
    $sync_customers = Settings::instance()->getValue('sync_contacts', 'agilecrm');
    $sync_orders = Settings::instance()->getValue('sync_orders', 'agilecrm');   
    $order_status = $status_to;

    if(count($products)>0){
        if($sync_customers == "Y"){

            $firstname = $order['firstname'];
            $lastname = $order['lastname'];
            $email = $order['email'];
            $phone = $order['phone'];
            $company = $order['company'];
            $country = getCountryName($order['b_country']);
            $street = $order['b_address_2']=='' ? $order['b_address'] : $order['b_address'].','.$order['b_address_2'];
            $city = $order['b_city'];
            $state = $order['b_state'];
            $address = array(
              "address"=>$street,
              "city"=>$city,
              "state"=>$state,
              "country"=>$country
            );

            $result = curl_wrap("contacts/search/email/".$email, null, "GET", "application/json");
            $result = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);

            if(count($result)>0)
                $contact_id = $result->id;
            else
                $contact_id = "";
 
            if($contact_id == ""){
                $contact_json = array(
                  "tags"=>array("CS Cart"),
                  "properties"=>array(
                    array(
                      "name"=>"first_name",
                      "value"=>$firstname,
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"last_name",
                      "value"=>$lastname,
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"email",
                      "value"=>$email,
                      "type"=>"SYSTEM"
                    ),  
                    array(
                        "name"=>"address",
                        "value"=>json_encode($address),
                        "type"=>"SYSTEM"
                    ),
                    array(
                        "name"=>"phone",
                        "value"=>$phone,
                        "type"=>"SYSTEM"
                    )
                  )
                );
                $contact_json = json_encode($contact_json);
                $curln = curl_wrap("contacts", $contact_json, "POST", "application/json");
            }
            else{
                $contact_json_update = array(
                  "id"=>$contact_id, //It is mandatory field. Id of contact
                  "tags"=>array("CS Cart"),
                  "properties"=>array(
                    array(
                      "name"=>"first_name",
                      "value"=>$firstname,
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"last_name",
                      "value"=>$lastname,
                      "type"=>"SYSTEM"
                    ),
                    array(
                      "name"=>"email",
                      "value"=>$email,
                      "type"=>"SYSTEM"
                    ),  
                    array(
                        "name"=>"address",
                        "value"=>json_encode($address),
                        "type"=>"SYSTEM"
                    ),
                    array(
                        "name"=>"phone",
                        "value"=>$phone,
                        "type"=>"SYSTEM"
                    )
                  )
                );
                $contact_json_update = json_encode($contact_json_update);
                $curlupdate = curl_wrap("contacts/edit-properties", $contact_json_update, "PUT", "application/json");
            }

            if($sync_orders == "Y"){
                $result = curl_wrap("contacts/search/email/".$email, null, "GET", "application/json");
                $result = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
                $contact_id = $result->id;
                $productname = array();
                foreach ($order['products'] as $product_item) {
                    $productname[] = fn_js_escape($product_item['product']);
                }
                $productname = implode('","',$productname);
                $Str = $productname;
                $Str = preg_replace('/[^a-zA-Z0-9_.]/', '_', $Str);
                $contact_json = array(
                    "id" => $contact_id, 
                   "tags" => array($Str)
                );

               $contact_json = stripslashes(json_encode($contact_json));
               $curltags = curl_wrap("contacts/edit/tags", $contact_json, "PUT", "application/json");
               $billingaddress = $street.",".$city.",".$state.",".$country;
               $grandtotal = $order['total'];
               $orderid = $order['order_id'];

               $description = fn_agilecrm_get_order_status_description($order_status, $grandtotal, $productname,$billingaddress);
                $note_json = array(
                  "subject"=> "Order# ". $orderid,
                  "description"=>$description,
                  "contact_ids"=>array($contact_id)
                );

                $note_json = json_encode($note_json);
                $curls = curl_wrap("notes", $note_json, "POST", "application/json");
            }
        }
        else if($sync_orders == "Y")
        {   
            $firstname = $order['firstname'];
            $lastname = $order['lastname'];
            $email = $order['email'];
            $phone = $order['phone'];
            $company = $order['company'];
            $country = getCountryName($order['b_country']);
            $street = $order['b_address_2']=='' ? $order['b_address'] : $order['b_address'].','.$order['b_address_2'];
            $city = $order['b_city'];
            $state = $order['b_state'];
            $address = array(
              "address"=>$street,
              "city"=>$city,
              "state"=>$state,
              "country"=>$country
            );

            $result = curl_wrap("contacts/search/email/".$email, null, "GET", "application/json");
            $result = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
           
            if(count($result)>0)
                $contact_id = $result->id;
            else
                $contact_id = "";

            if($contact_id!="")
            {
                $productname = array();
                foreach ($order['products'] as $product_item) {
                    $productname[] = fn_js_escape($product_item['product']);
                }
                $productname = implode('","',$productname);
                $Str = $productname;
                $Str = preg_replace('/[^a-zA-Z0-9_.]/', '_', $Str);
                $contact_json = array(
                    "id" => $contact_id, 
                   "tags" => array($Str)
                );

               $contact_json = stripslashes(json_encode($contact_json));
               $curltags = curl_wrap("contacts/edit/tags", $contact_json, "PUT", "application/json");
               $billingaddress = $street.",".$city.",".$state.",".$country;
               $grandtotal = $order['total'];
               $orderid = $order['order_id'];

               $description = fn_agilecrm_get_order_status_description($order_status, $grandtotal, $productname,$billingaddress);
                $note_json = array(
                  "subject"=> "Order# ". $orderid,
                  "description"=>$description,
                  "contact_ids"=>array($contact_id)
                );

                $note_json = json_encode($note_json);
                $curls = curl_wrap("notes", $note_json, "POST", "application/json");
            }
        }
    }

    return "";
}

function fn_agilecrm_get_order_status_description($order_status, $grand_total, $productname, $billingaddress){

    switch($order_status){
        case 'P' : return "Order status: Processed\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'O' : return "Order status: Open\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'C' : return "Order status: Complete\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'F' : return "Order status: Failed\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'D' : return "Order status: Declined\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'B' : return "Order status: Backordered\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'I' : return "Order status: Canceled\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'Y' : return "Order status: Awaiting Call\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'N' : return "Order status: Incomplete\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;
        case 'T' : return "Order status: Parent Order\nTotal amount:".$grand_total."\nItems(id-qty):".$productname."\nBilling:".$billingaddress;
                    break;

    }

}

function fn_agilecrm_get_users(){
  
}

function curl_wrap($entity, $data, $method, $content_type) {

    $agile_domain = fn_agilecrm_get_agile_domain();
    $agile_email = fn_agilecrm_get_agile_email();
    $agile_rest_api_key = fn_agilecrm_get_agile_rest_api_key();

    if ($content_type == NULL) {
        $content_type = "application/json";
    }
    
    $agile_url = "https://" . $agile_domain . ".agilecrm.com/dev/api/" . $entity;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
    switch ($method) {
        case "POST":
            $url = $agile_url;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            break;
        case "GET":
            $url = $agile_url;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "PUT":
            $url = $agile_url;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            break;
        case "DELETE":
            $url = $agile_url;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            break;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-type : $content_type;", 'Accept : application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $agile_email . ':' . $agile_rest_api_key);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function getCountryName($ccode)
{
    $countries = array
        (
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua And Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia And Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'Congo, Democratic Republic',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote D\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island & Mcdonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran, Islamic Republic Of',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle Of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KR' => 'Korea',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People\'s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan Arab Jamahiriya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States Of',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'AN' => 'Netherlands Antilles',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territory, Occupied',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts And Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre And Miquelon',
        'VC' => 'Saint Vincent And Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome And Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia And Sandwich Isl.',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard And Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad And Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks And Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UM' => 'United States Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands, British',
        'VI' => 'Virgin Islands, U.S.',
        'WF' => 'Wallis And Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    );
    return isset($countries[$ccode]) ? $countries[$ccode] : $ccode;
}