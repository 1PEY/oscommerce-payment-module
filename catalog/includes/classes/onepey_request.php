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

if (! class_exists('OnePEYRequest', false)) {

    /**
     * Class managing and preparing request parameters and HTML rendering of request.
     */
    class OnePEYRequest
    {

        /**
         * The fields to send to the 1PEY platform.
         *
         * @var array[string][OnePEYField]
         * @access private
         */
        private $requestParameters;

        /**
         * URL of the payment page.
         *
         * @var string
         * @access private
         */
        private $platformUrl;


        /**
         * The original data encoding.
         *
         * @var string
         * @access private
         */
        private $encoding;


        public function __construct($encoding = 'UTF-8')
        {
            // initialize encoding
            $this->encoding = in_array(strtoupper($encoding), OnePEYApi::$SUPPORTED_ENCODINGS) ?
                strtoupper($encoding) : 'UTF-8';
            
            $this->requestParameters = array();
        }

        /**
         * Shortcut function used in constructor to build requestParameters.
         *
         * @param string $name
         * @param string $label
         * @param string $regex
         * @param boolean $required
         * @param mixed $value
         * @return boolean
         */
        private function addField($name, $value = null)
        {
            $this->requestParameters[$name] = new OnePEYField($name);

            if ($value !== null) {
                return $this->set($name, $value);
            }

            return true;
        }

        /**
         * Shortcut for setting multiple values with one array.
         *
         * @param array[string][mixed] $parameters
         * @return boolean
         */
        public function setFromArray($parameters)
        {
            $ok = true;
            foreach ($parameters as $name => $value) {
                $ok &= $this->set($name, $value);
            }

            return $ok;
        }

        /**
         * General getter that retrieves a request parameter with its name.
         * Adds "vads_" to the name if necessary.
         * Example : <code>$site_id = $request->get('site_id');</code>
         *
         * @param string $name
         * @return mixed
         */
        public function get($name)
        {
            if (! $name || ! is_string($name)) {
                return null;
            }

           	if (key_exists($name, $this->requestParameters)) {
                return $this->requestParameters[$name]->getValue();
            } else {
                return null;
            }
        }

        /**
         * Set a request parameter with its name and the provided value.
         *
         * @param string $name
         * @param mixed $value
         * @return boolean
         */
        public function set($name, $value)
        {
            if (! $name || ! is_string($name)) {
                return false;
            }

           	if (key_exists($name, $this->requestParameters)) {
                return $this->requestParameters[$name]->setValue($value);
            } else {
            	$this->addField($name,$value);
                return false;
            }
        }


        /**
         * Set target URL of the payment form.
         *
         * @param string $url
         * @return boolean
         */
        public function setPlatformUrl($url)
        {
            if (preg_match('#^https?://([^/]+/)+$#u', $url)) {
                $this->platformUrl = $url;
                return true;
            } else {
                return false;
            }
        }

        /**
         * Enable/disable vads_redirect_* parameters.
         *
         * @param mixed $enabled
         *            false, 0, null, negative integer or 'false' to disable
         * @return boolean
         */
        public function setRedirectEnabled($enabled)
        {
            $this->redirectEnabled = ($enabled && $enabled != '0' && strtolower($enabled) != 'false');
            return true;
        }



        /**
         * Unset the value of optionnal fields if they are invalid.
         */
        public function clearInvalidOptionnalFields()
        {
            $fields = $this->getRequestFields();
            foreach ($fields as $field) {
                if (! $field->isValid() && ! $field->isRequired()) {
                    $field->setValue(null);
                }
            }
        }

        /**
         * Check all payment fields.
         *
         * @param array[string] $errors will be filled with the names of invalid fields
         * @return boolean
         */
        public function isRequestReady(&$errors = null)
        {
            $errors = is_array($errors) ? $errors : array();

            foreach ($this->getRequestFields() as $field) {
                if (! $field->isValid()) {
                    $errors[] = $field->getName();
                }
            }

            return count($errors) == 0;
        }
        
        
        /**
         * Return the list of fields to send to the payment platform.
         *
         * @return array[string][OnePEYField] a list of OnePEYField
         */
        public function getRequestFields()
        {
        	return $this->requestParameters;
        }
        
        
        
        /**
         * Return the URL of the payment page with urlencoded parameters (GET-like URL).
         *
         * @return string
         */
        public function getRequestUrl()
        {
            $fields = $this->getRequestFields();

            $url = $this->platformUrl . '?';
            foreach ($fields as $field) {
                if (! $field->isFilled()) {
                    continue;
                }

                $url .= $field->getName() . '=' . rawurlencode($field->getValue()) . '&';
            }
            $url = substr($url, 0, - 1); // remove last &
            return $url;
        }

        /**
         * Return the HTML form to send to the payment platform.
         *
         * @param string $form_add
         * @param string $input_type
         * @param string $input_add
         * @param string $btn_type
         * @param string $btn_value
         * @param string $btn_add
         * @return string
         */
        public function getRequestHtmlForm(
            $form_add = '',
            $input_type = 'hidden',
            $input_add = '',
            $btn_type = 'submit',
            $btn_value = 'Pay',
            $btn_add = '',
            $escape = true
        ) {
            $html = '';
            $html .= '<form action="' . $this->platformUrl . '" method="POST" ' . $form_add . '>';
            $html .= "\n";
            $html .= $this->getRequestHtmlFields($input_type, $input_add, $escape);
            $html .= '<input type="' . $btn_type . '" value="' . $btn_value . '" ' . $btn_add . '/>';
            $html .= "\n";
            $html .= '</form>';

            return $html;
        }

        /**
         * Return the HTML inputs of fields to send to the payment page.
         *
         * @param string $input_type
         * @param string $input_add
         * @return string
         */
        public function getRequestHtmlFields($input_type = 'hidden', $input_add = '', $escape = true)
        {
            $fields = $this->getRequestFields();

            $html = '';
            $format = '<input name="%s" value="%s" type="' . $input_type . '" ' . $input_add . "/>\n";
            foreach ($fields as $field) {
                if (! $field->isFilled()) {
                    continue;
                }

                // convert special chars to HTML entities to avoid data truncation
                if ($escape) {
                    $value = htmlspecialchars($field->getValue(), ENT_QUOTES, 'UTF-8');
                }

                $html .= sprintf($format, $field->getName(), $value);
            }
            return $html;
        }

        /**
         * Return the html fields to send to the payment page as a key/value array.
         *
         * @param bool $for_log
         * @return array[string][string]
         */
        public function getRequestFieldsArray($for_log = false, $escape = true)
        {
            $fields = $this->getRequestFields();

            $sensitive_data = array('vads_card_number', 'vads_cvv', 'vads_expiry_month', 'vads_expiry_year');

            $result = array();
            foreach ($fields as $field) {
                if (! $field->isFilled()) {
                    continue;
                }

                $value = $field->getValue();
                if ($for_log && in_array($field->getName(), $sensitive_data)) {
                    $value = str_repeat('*', strlen($value));
                }

                // convert special chars to HTML entities to avoid data truncation
                if ($escape) {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }

                $result[$field->getName()] = $value;
            }

            return $result;
        }
    }
}
