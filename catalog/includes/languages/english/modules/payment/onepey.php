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

// administration interface - informations
define('MODULE_PAYMENT_ONEPEY_MODULE_INFORMATION', "MODULE DETAILS");
define('MODULE_PAYMENT_ONEPEY_DEVELOPED_BY', "Developed by : ");
define('MODULE_PAYMENT_ONEPEY_CONTACT_EMAIL', "Contact us : ");
define('MODULE_PAYMENT_ONEPEY_CONTRIB_VERSION', "Module version : ");
define('MODULE_PAYMENT_ONEPEY_GATEWAY_VERSION', "Platform version : ");
define('MODULE_PAYMENT_ONEPEY_CMS_VERSION', "Tested with : ");
define('MODULE_PAYMENT_ONEPEY_CHECK_URL', "Instant Payment Notification URL to copy into your 1PEY Back Office: <br />");

// administration interface - module settings
define('MODULE_PAYMENT_ONEPEY_STATUS_TITLE', "Activation");
define('MODULE_PAYMENT_ONEPEY_STATUS_DESC', "Enables / disables the 1PEY payment module.");
define('MODULE_PAYMENT_ONEPEY_SORT_ORDER_TITLE', "Display order");
define('MODULE_PAYMENT_ONEPEY_SORT_ORDER_DESC', "The smallest index is displayed first.");
define('MODULE_PAYMENT_ONEPEY_ZONE_TITLE', "Payment area");
define('MODULE_PAYMENT_ONEPEY_ZONE_DESC', "If an area is selected, this payment mode will only be available for it.");

// administration interface - platform settings
define('MODULE_PAYMENT_ONEPEY_MERCHANT_ID_TITLE', "Merchant ID");
define('MODULE_PAYMENT_ONEPEY_MERCHANT_ID_DESC', "The identifier provided by the gateway.");
define('MODULE_PAYMENT_ONEPEY_PASS_CODE_TITLE', "Pass Code (secret key)");
define('MODULE_PAYMENT_ONEPEY_PASS_CODE_DESC', "Secret key provided by the gateway for securing the payment protocol.");
define('MODULE_PAYMENT_ONEPEY_PSIGN_ALGO_TITLE', "PSign Algorithm");
define('MODULE_PAYMENT_ONEPEY_PSIGN_ALGO_DESC', "Algorithm used in the protocol signing, used for authentication of the payment operations. Leave default unless instructed otherwise.");
define('MODULE_PAYMENT_ONEPEY_CTX_MODE_TITLE', "Mode");
define('MODULE_PAYMENT_ONEPEY_CTX_MODE_DESC', "The context mode of this module.");
define('MODULE_PAYMENT_ONEPEY_PLATFORM_URL_TITLE', "Gateway URL");
define('MODULE_PAYMENT_ONEPEY_PLATFORM_URL_DESC', "Link to the gateway base URL.");

// administration interface - amount restrictions settings
define('MODULE_PAYMENT_ONEPEY_AMOUNT_MIN_TITLE', "Minimum amount");
define('MODULE_PAYMENT_ONEPEY_AMOUNT_MIN_DESC', "Minimum amount to activate this payment method.");
define('MODULE_PAYMENT_ONEPEY_AMOUNT_MAX_TITLE', "Maximum amount");
define('MODULE_PAYMENT_ONEPEY_AMOUNT_MAX_DESC', "Maximum amount to activate this payment method.");

// administration interface - back to store settings
define('MODULE_PAYMENT_ONEPEY_ORDER_STATUS_TITLE', "Order Status");
define('MODULE_PAYMENT_ONEPEY_ORDER_STATUS_DESC', "Defines the status of the orders paid with OnePEY.");

// administration interface - misc constants
define('MODULE_PAYMENT_ONEPEY_VALUE_0', "Disabled");
define('MODULE_PAYMENT_ONEPEY_VALUE_1', "Enabled");

define('MODULE_PAYMENT_ONEPEY_VALIDATION_DEFAULT', "Back Office configuration");
define('MODULE_PAYMENT_ONEPEY_VALIDATION_0', "Automatic");
define('MODULE_PAYMENT_ONEPEY_VALIDATION_1', "Manual");

define('MODULE_PAYMENT_ONEPEY_LANGUAGE_ENGLISH', "English");

// catalog messages
define('MODULE_PAYMENT_ONEPEY_TECHNICAL_ERROR', "An error occured in the payment process.");
define('MODULE_PAYMENT_ONEPEY_PAYMENT_ERROR', "Your order has not been confirmed. The payment has not been accepted.");
define('MODULE_PAYMENT_ONEPEY_CHECK_URL_WARN', "The automatic notification has not worked. Please contact 1PEY team in order to fix this problem.");
define('MODULE_PAYMENT_ONEPEY_CHECK_URL_WARN_DETAIL', "The automatic notifications (also called IPNs) are messages from 1PEY system sent directly to your server. These are keeping your system up-to-date with all transactions states and changes regardless of your customers connections (browsers may fail to return due to external factors out of our control).");
define('MODULE_PAYMENT_ONEPEY_GOING_INTO_PROD_INFO', "<b>GOING INTO PRODUCTION :</b> At this time your account is in Test Mode. Please contact 1PEY representatives for LIVE account enrollment and credentials.");

// single payment catalog messages
define('MODULE_PAYMENT_ONEPEY_STD_TITLE', "OnePEY - Payment by credit card");
