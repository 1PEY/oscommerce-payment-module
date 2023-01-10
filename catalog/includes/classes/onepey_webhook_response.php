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
require_once 'onepey_response.php';

if (! class_exists('OnePEYWebhookResponse', false)) {
	
	class OnePEYWebhookResponse extends OnePEYResponse {
	
		protected $requiredParams = array('responseCode', 'reasonCode' , 'transactionID', 'orderID', 'pSign');
		
		private $pSignAlgo;
		private $passCode;
	
		public function __construct($passCode='', $pSignAlgo='sha1') {
			 
			$this->pSignAlgo = $pSignAlgo;
			$this->passCode = $passCode;
			
			$httpParams = count($this->requiredParams) == count(array_intersect($this->requiredParams, array_keys($_POST))) ? $_POST : null;
			if ($httpParams == null)
				$httpParams = count($this->requiredParams) == count(array_intersect($this->requiredParams, array_keys($_GET))) ? $_GET : null;
	
			parent::__construct(json_encode($httpParams));
		}
	
	
		public function assignResponse($response){
			if (is_array($response))
				$this->__setResponseArray($response);
			else if (is_object($response))
				$this->__setResponse($response);
			else
				return;
		}
	
		private function __setResponseArray($arr){
			$this->_responseArray = $arr;
			$this->_response = json_decode(json_encode($arr));
		}
	
	
		private function __setResponse($obj){
			$this->_response = $obj;
			$this->_responseArray = json_decode(json_encode($obj));
		}
	
		public function getHash() {
			return $this->getPSign();
		}
		
		public function unsetParameter($paramName){
		
			if (isset($this->_response->$paramName)){
				unset($this->_response->$paramName);
			}
			if (isset($this->_responseArray[$paramName])){
				unset($this->_responseArray[$paramName]);
			}
		}
	
		public function isAuthorized() {
			 
			if ($this->_responseArray == null)
				return false;
				 
				$paramsPSignCheckArr = array_merge(array(), $this->_responseArray);
				 
				if (isset($paramsPSignCheckArr['pSign']) && trim($paramsPSignCheckArr['pSign']) != ''
						&& strlen($paramsPSignCheckArr['pSign']) >= 40){
	
							$remotePSign = $paramsPSignCheckArr['pSign'];
							unset($paramsPSignCheckArr['pSign']);
							$localPSign = hash($this->pSignAlgo, implode('', array_merge(array($this->passCode), array_values($paramsPSignCheckArr))));
	
							return $remotePSign == $localPSign;
				}
				return false;
		}
	
	
		public function getUid() {
	
			if (isset($this->getResponse()->transactionID)) {
				return $this->getResponse()->transactionID;
			}else{
				return false;
			}
		}
	
		public function getTrackingId() {
	
			if (isset($this->getResponse()->orderID)) {
				return $this->getResponse()->orderID;
			}else{
				return false;
			}
		}
	
	}
}