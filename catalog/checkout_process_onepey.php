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

/**
 * This file is an access point for the 1PEY payment gateway to validate an order.
 */

    
    require_once('includes/application_top.php');

    global $onepey_response, $language, $messageStack;

    require_once (DIR_FS_CATALOG . 'includes/modules/payment/OnePEY.php');
    $paymentObject = new onepey();

    require_once (DIR_FS_CATALOG . 'includes/classes/onepey_webhook_response.php');
    $onepey_response = new OnePEYWebhookResponse(
            constant($paymentObject->prefix . 'PASS_CODE'),
            constant($paymentObject->prefix . 'PSIGN_ALGO')
    );

    // check authenticity and valid data
	if (!$onepey_response->isAuthorized() || !$onepey_response->isValid() || empty($onepey_response->getUid())) {
        $messageStack->add_session('header', MODULE_PAYMENT_ONEPEY_TECHNICAL_ERROR, 'error');

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true));
    }
    
    // keep track of originating source (app needs to know further if this is a gateway notification or not) 
    $_COOKIE['__call_from_server__'] = 'no';

    if ($paymentObject->_is_order_paid()) {
        // messages to display on payment result page
        if (constant($paymentObject->prefix . 'CTX_MODE') == 'TEST') {
        	$messageStack->add_session('header', MODULE_PAYMENT_ONEPEY_GOING_INTO_PROD_INFO . ' <a href="https://1pey.com/backoffice/docs/api/testing.html" target="_blank">https://1pey.com/backoffice/docs/api/testing.html</a>', 'success');
        }
        
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL', true));
    } else {

    	tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, http_build_query($onepey_response->getResponseArray()), 'SSL', true));
    }

