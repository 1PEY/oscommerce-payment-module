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

require_once 'onepey_api.php';

if (! class_exists('OnePEYResponse', false)) {

    /**
     * Class representing the result of a transaction (sent by the IPN URL or by the client return).
     */
	class OnePEYResponse {
	
		protected $_response;
		protected $_responseArray;
	
		const APPROVED = 1;
		const DECLINED = 2;
		const FAILED = 3;
		const REDIRECT = 4;
		const CANCELLED = 5;
		const PENDING_APPROVAL = 6;
		const PENDING_REFUND = 7;
		const PENDING_PROCESSOR = 8;
		const AUTHORIZED = 10;
		const REFUNDED = 40;
		const PENDING= 80;
	
		public function __construct($message){
			$this->_response = json_decode($message);
			$this->_responseArray = json_decode($message, true);
		}
	
		public function isError() {
			if (!is_object($this->getResponse()))
				return true;
	
				if (isset($this->getResponse()->responseCode) && $this->getResponse()->responseCode == self::FAILED)
					return true;
	
					if (isset($this->getResponse()->errorInfo))
						return true;
	
						return false;
		}
	
		public function isValid() {
			return !($this->_response === false || $this->_response == null);
		}
	
		public function getResponse() {
			return $this->_response;
		}
	
		public function getResponseArray() {
			return $this->_responseArray;
		}
		
		public function isSuccess() {
			return in_array($this->getStatus(), array(self::APPROVED, self::AUTHORIZED));
		}
		
		public function isFailed() {
			return in_array($this->getStatus(), array(self::FAILED));
		}
		
		public function isIncomplete() {
			return in_array($this->getStatus(),
					array(self::PENDING, self::PENDING_APPROVAL, self::PENDING_PROCESSOR, self::PENDING_REFUND));
		}
		
		public function isDeclined() {
			return in_array($this->getStatus(), array(self::DECLINED));
		}
		
		public function isPending() {
			return in_array($this->getStatus(),
					array(self::PENDING, self::PENDING_APPROVAL, self::PENDING_PROCESSOR, self::PENDING_REFUND));
		}
		
		public function isTest() {
			return false;
		}
		
		public function getStatus() {
			 
			if (isset($this->getResponse()->responseCode))
				return $this->getResponse()->responseCode;
				else
					return null;
		}
		
		public function getUid() {
			if ($this->hasTransactionSection()) {
				return $this->getResponse()->transaction->transactionID;
			}else{
				return false;
			}
		}
		
		public function getPSign() {
			if (isset($this->getResponse()->pSign)){
				return $this->getResponse()->pSign;
			}else{
				return null;
			}
		}
		
		public function getRedirectUrl() {
			if (isset($this->getResponse()->redirectURL)) {
				return $this->getResponse()->redirectURL;
			}else{
				return false;
			}
		}
		
		public function getTrackingId() {
			if ($this->hasTransactionSection()) {
				return $this->getResponse()->transaction->orderID;
			}else{
				return false;
			}
		}
		
		public function getPaymentMethod() {
			return false;
		}
		
		public function hasTransactionSection() {
			return (is_object($this->getResponse()) && isset($this->getResponse()->transaction));
		}
		
		public function getMessage() {
		
			if (is_object($this->getResponse())) {
		
				if (isset($this->getResponse()->errorInfo))
					return $this->getResponse()->errorInfo;
		
					else if (isset($this->getResponse()->responseCode)){
						 
						switch($this->getResponse()->responseCode){
		
							case self::APPROVED:
								return 'Approved';
							case self::AUTHORIZED:
								return 'Authorized';
							case self::CANCELLED:
								return 'Cancelled';
							case self::DECLINED:
								return 'Declined';
							case self::FAILED:
								return 'Failed';
							case self::PENDING:
								return 'Pending';
							case self::PENDING_APPROVAL:
								return 'Pending Approval';
							case self::PENDING_PROCESSOR:
								return 'Pending Processor';
							case self::PENDING_REFUND:
								return 'Pending Refund';
							case self::REDIRECT:
								return 'Pending Customer Redirect';
							case self::REFUNDED:
								return 'Refunded';
		
							default:
								return '';
						}
					}
			}
		
			return '';
		
		}
	
	}
}
