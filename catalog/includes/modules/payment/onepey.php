<?php
/**
 * 1PEY Payment Module version 1.1.0 for osCommerce 2.3.x. Support contact : support@1pey.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 *
 * @author    1PEY (https://www.1pey.com/)
 * @copyright 2014-2018 1PEY
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html  GNU General Public License (GPL v2)
 * @category  payment
 * @package   onepey
 */

/* include 1PEY API class */
require_once (DIR_FS_CATALOG . 'includes/classes/onepey_api.php');

if (defined('DIR_FS_ADMIN')) {
    /* include the admin configuration functions */
    require_once (DIR_FS_ADMIN . 'includes/functions/onepey_output.php');
}

/* load module language file */
require_once (DIR_FS_CATALOG . "includes/languages/$language/modules/payment/OnePEY.php");

/**
 * Main class implementing 1PEY payment module for osCommerce.
 */
class onepey
{
    var $prefix = 'MODULE_PAYMENT_ONEPEY_';

    /**
     * @var string
     */
    var $code;
    /**
     * @var string
     */
    var $title;
    /**
     * @var string
     */
    var $description;
    /**
     * @var boolean
     */
    var $enabled;
    /**
     * @var int
     */
    var $sort_order;
    /**
     * @var string
     */
    var $form_action_url;
    /**
     * @var int
     */
    var $order_status;

    /**
     * Class constructor.
     */
    function onepey()
    {
        global $order;

        // initialize code
        $this->code = 'onepey';

        // initialize title
        $this->title = MODULE_PAYMENT_ONEPEY_STD_TITLE;

        // initialize description
        $this->description  = '';
        $this->description .= '<b>' . MODULE_PAYMENT_ONEPEY_MODULE_INFORMATION . '</b>';
        $this->description .= '<br/><br/>';

        $this->description .= '<table class="infoBoxContent">';
        $this->description .= '<tr><td style="text-align: right;">' . MODULE_PAYMENT_ONEPEY_DEVELOPED_BY . '</td><td><a href="https://www.1pey.com/" target="_blank"><b>OnePEY</b></a></td></tr>';
        $this->description .= '<tr><td style="text-align: right;">' . MODULE_PAYMENT_ONEPEY_CONTACT_EMAIL . '</td><td><a href="mailto:support@1pey.com"><b>support@1pey.com</b></a></td></tr>';
        $this->description .= '<tr><td style="text-align: right;">' . MODULE_PAYMENT_ONEPEY_CONTRIB_VERSION . '</td><td><b>1.1.0</b></td></tr>';
        $this->description .= '<tr><td style="text-align: right;">' . MODULE_PAYMENT_ONEPEY_GATEWAY_VERSION . '</td><td><b>V5.9</b></td></tr>';
        $this->description .= '</table>';

        $this->description .= '<br/>';
        $this->description .= MODULE_PAYMENT_ONEPEY_CHECK_URL . '<b>' . HTTP_SERVER . DIR_WS_CATALOG . 'checkout_process_OnePEY.php</b>';
        $this->description .= '<hr />';

        // initialize enabled
        $this->enabled = defined($this->prefix . 'STATUS') && (constant($this->prefix . 'STATUS') == '1');

        // initialize sort_order
        $this->sort_order = defined($this->prefix . 'SORT_ORDER') ? constant($this->prefix . 'SORT_ORDER') : 0;

        $this->form_action_url = defined($this->prefix . 'PLATFORM_URL') ? constant($this->prefix . 'PLATFORM_URL').'transaction/customerDirect' : '';

        if (defined($this->prefix . 'ORDER_STATUS') && (constant($this->prefix . 'ORDER_STATUS') > 0)) {
            $this->order_status = constant($this->prefix . 'ORDER_STATUS');
        }

        // if there's an order to treat, start preliminary payment zone check
        if (is_object($order)) {
            $this->update_status();
        }
    }

    /**
     * Payment zone and amount restriction checks.
     */
    function update_status()
    {
        global $order;

        if (! $this->enabled) {
            return;
        }

        // check customer zone
        if ((int)constant($this->prefix . 'ZONE') > 0) {
            $flag = false;
            $check_query = tep_db_query('SELECT `zone_id` FROM `' . TABLE_ZONES_TO_GEO_ZONES . '`' .
                                        " WHERE `geo_zone_id` = '" . constant($this->prefix . 'ZONE') . "'" .
                                        " AND `zone_country_id` = '" . $order->billing['country']['id'] . "'" .
                                        ' ORDER BY `zone_id` ASC');
            while ($check = tep_db_fetch_array($check_query)) {
                if (($check['zone_id'] < 1) || ($check['zone_id'] == $order->billing['zone_id'])) {
                    $flag = true;
                    break;
                }
            }

            if (! $flag) {
                $this->enabled = false;
                return;
            }
        }

        // check amount restrictions
        if ((constant($this->prefix . 'AMOUNT_MIN') != '' && $order->info['total'] < constant($this->prefix . 'AMOUNT_MIN'))
                || (constant($this->prefix . 'AMOUNT_MAX') != '' && $order->info['total'] > constant($this->prefix . 'AMOUNT_MAX'))) {
            $this->enabled = false;
            return;
        }

        // check currency
        $defaultCurrency = (defined('USE_DEFAULT_LANGUAGE_CURRENCY') && USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
        if (! OnePEYApi::findCurrencyByAlphaCode($order->info['currency']) && ! OnePEYApi::findCurrencyByAlphaCode($defaultCurrency)) {
            // currency is not supported, module is not available
            $this->enabled = false;
        }
    }

    /**
     * JS checks : we let the platform do all the validation itself.
     * @return false
     */
    function javascript_validation()
    {
        return false;
    }

    /**
     * Parameters for what the payment option will look like in the list.
     * @return array
     */
    function selection()
    {
        return array(
            'id' => $this->code,
            'module' => $this->title
        );
    }

    /**
     * Server-side checks after payment selection : We let the platform do all the validation itself.
     * @return false
     */
    function pre_confirmation_check()
    {
        return false;
    }

    /**
     * Server-size checks before payment confirmation :  We let the platform do all the validation itself.
     * @return false
     */
    function confirmation()
    {
        return false;
    }

    /**
     * Prepare the form that will be sent to the payment gateway.
     * @return string
     */
    function process_button()
    {
        require_once (DIR_FS_CATALOG . 'includes/classes/onepey_request.php');
        $request = new OnePEYRequest(CHARSET);

        $request->setFromArray($this->_build_request());

        return $request->getRequestHtmlFields();
    }

    function _build_request()
    {
        global $order, $languages_id, $currencies, $customer_id;

        $data = array();

        // get the currency to use
        $currencyValue = $order->info['currency_value'];
        $onepeyCurrency = OnePEYApi::findCurrencyByAlphaCode($order->info['currency']);
        if (! $onepeyCurrency) {
            // currency is not supported, use the default shop currency
            $defaultCurrency = (defined('USE_DEFAULT_LANGUAGE_CURRENCY') && USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ?
                                LANGUAGE_CURRENCY : DEFAULT_CURRENCY;

            $onepeyCurrency = OnePEYApi::findCurrencyByAlphaCode($defaultCurrency);
            $currencyValue = 1;
        }

        // calculate amount ...
        $total = tep_round($order->info['total'] * $currencyValue, $currencies->get_decimal_places($onepeyCurrency->getAlpha3()));
		$amount = $onepeyCurrency->convertAmountToInteger($total);
		if ($amount > 0)
			$amount = $amount / 100;
		
		$osCsid = isset($_COOKIE['osCsid']) ? $_COOKIE['osCsid']: null;
		$osCsid = $osCsid == null ? (isset($_POST['osCsid']) ? $_POST['osCsid']: null) : $osCsid;
		$osCsid = $osCsid == null ? (isset($_GET['osCsid']) ? $_GET['osCsid']: null) : $osCsid;
			
		$osCsidStr = '-'.$osCsid;
		
		$orderId = $this->_guess_order_id();
		$orderId = 'T'.str_pad(''.$orderId, 8, '0', STR_PAD_LEFT).'-'.time().$osCsidStr;
		
        // get the Merchant pass_code (secret key) and hash algorithm
        $secretKey = constant($this->prefix . 'PASS_CODE');
        $pSignAlgo = constant($this->prefix . 'PSIGN_ALGO');
        
        // request parameters
        $data = array(
            'merchantID' =>  constant($this->prefix . 'MERCHANT_ID'),
            'amount' => $total,
            'currency' => $onepeyCurrency->getAlpha3(),
            'orderID' => $orderId,
            'returnURL' => HTTP_SERVER . DIR_WS_CATALOG . 'checkout_process_OnePEY.php',
            'notifyURL' => HTTP_SERVER . DIR_WS_CATALOG . 'ipn_process_OnePEY.php',
            'customerEmail' => $order->customer['email_address'],
            'customerPhone' => $order->customer['telephone'], // no cell phone defined, just use customer phone
            'customerFirstName' => $order->billing['firstname'],
            'customerLastName' => $order->billing['lastname'],
            'customerAddress1' => $order->billing['street_address'] . ' ' . $order->billing['suburb'],
            'customerCity' => $order->billing['city'],
            'customerZipCode' => $order->billing['postcode'],
            'customerStateProvince' => $order->billing['state'],
            'customerCountry' => $order->billing['country']['iso_code_2'],
            //'description' => 'osCommerce2.3.x_1.1.0/' . tep_get_version() . '/'. PHP_VERSION,
        );

        $psign = hash($pSignAlgo, $secretKey.implode('',array_values($data))); 
        $data['pSign'] = $psign; 
		
        return $data;
    }


    /**
     * Verify client data after he returned from payment gateway.
     */
    function before_process()
    {
        global $order, $onepey_response, $messageStack;

        require_once (DIR_FS_CATALOG . 'includes/classes/onepey_webhook_response.php');
        $onepey_response = new OnePEYWebhookResponse(
            constant($this->prefix . 'PASS_CODE'),
            constant($this->prefix . 'PSIGN_ALGO')
        );
        
        //remove/clear session added param `osCsid` in order to validate the remote call params with pSign
        if (isset($onepey_response->getResponse()->osCsid)) 
        	$response = $onepey_response->unsetParameter('osCsid');
        
        $fromServer = isset($_COOKIE['__call_from_server__']) && $_COOKIE['__call_from_server__'] == 'yes' ? true :  false ;

         // check authenticity and valid data
		if (!$onepey_response->isAuthorized() || !$onepey_response->isValid() || empty($onepey_response->getUid())) {
            if ($fromServer) {
                die($this->getOutputForPlatform());
            } else {
                $messageStack->add_session('header', MODULE_PAYMENT_ONEPEY_TECHNICAL_ERROR, 'error');

                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true));
                die();
            }
        }

        // messages to display on payment result page
        if (!$fromServer && constant($this->prefix . 'CTX_MODE') == 'TEST') {
            $messageStack->add_session('header', MODULE_PAYMENT_ONEPEY_GOING_INTO_PROD_INFO . ' <a href="https://1pey.com/backoffice/docs/api/testing.html" target="_blank">https://1pey.com/backoffice/docs/api/testing.html</a>', 'success');
        }

        // act according to case
        if ($onepey_response->isSuccess()) {
            // successful payment
	        
            if ($this->_is_order_paid()) {
                if ($fromServer) {
                    die ($this->getOutputForPlatform());
                } else {
                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL', true));
                    die();
                }
            } else {
                // let's borrow the cc_owner field to store transaction id
                $order->info['cc_owner'] = '-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Transaction: ' . $onepey_response->getUid();

                // let checkout_process.php finish the job
                return false;
            }

        } else {
            // payment process failed
            if ($fromServer) {
                die($this->getOutputForPlatform());
            } else {
                $messageStack->add_session('header', MODULE_PAYMENT_ONEPEY_PAYMENT_ERROR, 'error');
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
                die();
            }
        }
    }

    /**
     * Post-processing after the order has been finalised.
     */
    function after_process()
    {
        global $cart, $onepey_response, $messageStack;

        // this function is called only when payment was successful and the order is not registered yet

        $fromServer = isset($_COOKIE['__call_from_server__']) && $_COOKIE['__call_from_server__'] == 'yes' ? true :  false ;

        if ($fromServer) {
            $this->_clear_session_vars();

            die ($this->getOutputForPlatform());
        } else {
            // payment confirmed by client retun, show a warning if TEST mode
            if (constant($this->prefix . 'CTX_MODE') == 'TEST') {
                $messageStack->add_session('header', MODULE_PAYMENT_ONEPEY_CHECK_URL_WARN . '<br />' . MODULE_PAYMENT_ONEPEY_CHECK_URL_WARN_DETAIL, 'warning');
            }

            return false;
        }
    }
    
    
    
    
    
    /**
     * 1PEY Gateway Requires an HTTP 200 Status to be returned, regardless of content.
     */
    public function getOutputForPlatform()
    {
    	echo 'OK';
    	exit();
    }
    
    
    
    /**
     * Unregister session variables used during checkout and clear cart.
     */
    function _clear_session_vars()
    {
        global $cart;
        tep_session_unregister('sendto');
        tep_session_unregister('billto');
        tep_session_unregister('shipping');
        tep_session_unregister('payment');
        tep_session_unregister('comments');

        // reset cart to allow new checkout process
        $cart->reset(true);
    }

    /**
     * Return true if the module is installed.
     * @return bool
     */
    function check()
    {
        if (! isset($this->_check)) {
            $check_query = tep_db_query('SELECT `configuration_value` FROM `' . TABLE_CONFIGURATION . '`' .
                                        " WHERE `configuration_key` = '" . $this->prefix . "STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }


    /**
     * Build and execute a query for the install() function.
     * Parameters have to be escaped before.
     *
     * @param string $title
     * @param string $key
     * @param string $value
     * @param string $description
     * @param string $group_id
     * @param string $sort_order
     * @param string $date_added
     * @param string $set_function
     * @param string $use_function
     * @return
     */
    function _install_query($key, $value, $sort_order, $set_function=null, $use_function=null)
    {
        $sql_data = array(
            'configuration_title' => constant('MODULE_PAYMENT_ONEPEY_' . $key . '_TITLE'),
            'configuration_key' => $this->prefix . $key,
            'configuration_value' => $value,
            'configuration_description' => constant('MODULE_PAYMENT_ONEPEY_' . $key . '_DESC'),
            'configuration_group_id' => '6',
            'sort_order' => $sort_order,
            'date_added' => 'now()'
        );

        if ($set_function) {
            $sql_data['set_function'] = $set_function;
        }

        if ($use_function) {
            $sql_data['use_function'] = $use_function;
        }

        tep_db_perform(TABLE_CONFIGURATION, $sql_data);
    }

    /**
     * Module install (register admin-managed parameters in database).
     */
    function install()
    {
        // Ex: _install_query($key, $value, $group_id, $sort_order, $set_function=null, $use_function=null)
        // osCommerce specific parameters
        $this->_install_query('STATUS', '1', 1, 'onepey_cfg_draw_pull_down_bools(', 'onepey_get_bool_title');
        $this->_install_query('SORT_ORDER', '0', 2);
        $this->_install_query('ZONE', '0', 3, 'tep_cfg_pull_down_zone_classes(', 'tep_get_zone_class_title');

        // gateway access parameters
        $this->_install_query('MERCHANT_ID', 'YOUR_MERCHANT_ID_HERE', 4);
        $this->_install_query('PASS_CODE', 'YOUR_MERCHANT_PASS_CODE_HERE', 5);
        $this->_install_query('PSIGN_ALGO', 'sha1', 6, 'onepey_cfg_draw_pull_down_psign_algos(', 'onepey_get_psign_algo_title');
        $this->_install_query('CTX_MODE', 'TEST', 7, "tep_cfg_select_option(array(\'TEST\', \'PRODUCTION\'),");
        $this->_install_query('PLATFORM_URL', 'https://1pey.com/', 8);

        $this->_install_query('AMOUNT_MIN', '1', 9);
        $this->_install_query('AMOUNT_MAX', '9999', 10);

        // gateway return parameters
        $this->_install_query('ORDER_STATUS', '2', 11, 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name');
    }

    /**
     * Module deletion.
     */
    function remove()
    {
        $keys = $this->keys();

        foreach ($keys as $key) {
            tep_db_query('DELETE FROM `' . TABLE_CONFIGURATION . "` WHERE `configuration_key` = '$key'");
        }
    }

    /**
     * Returns the names of module's parameters.
     * @return array[int]string
     */
    function keys()
    {
        return array(
            'MODULE_PAYMENT_ONEPEY_STATUS',
            'MODULE_PAYMENT_ONEPEY_SORT_ORDER',
            'MODULE_PAYMENT_ONEPEY_ZONE',

            'MODULE_PAYMENT_ONEPEY_MERCHANT_ID',
            'MODULE_PAYMENT_ONEPEY_PASS_CODE',
            'MODULE_PAYMENT_ONEPEY_PSIGN_ALGO',
            'MODULE_PAYMENT_ONEPEY_CTX_MODE',
            'MODULE_PAYMENT_ONEPEY_PLATFORM_URL',
			
            'MODULE_PAYMENT_ONEPEY_AMOUNT_MIN',
            'MODULE_PAYMENT_ONEPEY_AMOUNT_MAX',

            'MODULE_PAYMENT_ONEPEY_ORDER_STATUS'
        );
    }

    /**
     * Try to guess what will be the order's id when osCommerce will register it at the end of the payment process.
     * This is only used to set order_id in the request to the payment gateway. It might be inconsistent with the
     * final osCommerce order ID (in cases like two clients going to the payment gateway at the same time...)
     *
     * @return int
     */
    function _guess_order_id()
    {
        $query = tep_db_query('SELECT MAX(`orders_id`) AS `order_id` FROM `' . TABLE_ORDERS . '`');

        if (tep_db_num_rows($query) == 0) {
            return 0;
        } else {
            $result = tep_db_fetch_array($query);
            return $result['order_id'] + 1;
        }
    }

    /**
     * Check if order corresponding to entered trans_id is already saved.
     *
     * @return boolean true if order already saved
     */
    function _is_order_paid()
    {
        global $onepey_response;

        $orderIdFromRemote = $onepey_response->getTrackingId();
        $orderId = (int)substr($orderIdFromRemote,1,8);
        $transId = $onepey_response->getUid();

        $query = tep_db_query('SELECT * FROM `' . TABLE_ORDERS . '`' .
                " WHERE orders_id >= $orderId" .
                " AND cc_owner LIKE '%Transaction: $transId'");

        return tep_db_num_rows($query) > 0;
    }
}
