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
 * This file is an access point for the 1PEY IPN (gateway notifications)
 */
require_once('includes/configure.php');

require_once (DIR_FS_CATALOG . 'includes/classes/onepey_webhook_response.php');
$onepey_response = new OnePEYWebhookResponse();

// check authenticity and valid data
if (!$onepey_response->isValid() || empty($onepey_response->getUid())
		|| empty($onepey_response->getTrackingId())) {
	echo 'NOK';
	exit();
}


$_COOKIE['__call_from_server__'] = 'yes';
$cartRef = $onepey_response->getTrackingId();

$osCsid = substr($cartRef, strrpos($cartRef,'-')+1);

$_POST['osCsid'] = $osCsid;
$_GET['osCsid'] = $osCsid;

// for cookie based sessions ...
$_COOKIE['osCsid'] = $osCsid;
$_COOKIE['cookie_test'] = 'please_accept_for_session';

require_once 'checkout_process.php';
