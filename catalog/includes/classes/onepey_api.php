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

require_once 'onepey_currency.php';
require_once 'onepey_field.php';

if (! class_exists('OnePEYApi', false)) {

    /**
     * Utility class for managing parameters checking, inetrnationalization, signature building and more.
     */
    class OnePEYApi
    {

        /**
         * The list of encodings supported by the API.
         *
         * @var array[string]
         */
        public static $SUPPORTED_ENCODINGS = array(
            'UTF-8',
            'ASCII',
            'Windows-1252',
            'ISO-8859-15',
            'ISO-8859-1',
            'ISO-8859-6',
            'CP1256'
        );

        /**
         * Generate a trans_id.
         * To be independent from shared/persistent counters, we use the number of 1/10 seconds since midnight
         * which has the appropriatee format (000000-899999) and has great chances to be unique.
         *
         * @param int $timestamp
         * @return string the generated trans_id
         */
        public static function generateTransId($timestamp = null)
        {
            if (! $timestamp) {
                $timestamp = time();
            }

            $parts = explode(' ', microtime());
            $id = ($timestamp + $parts[0] - strtotime('today 00:00')) * 10;
            $id = sprintf('%06d', $id);

            return $id;
        }

        /**
         * Returns an array of languages accepted by the 1PEY payment platform.
         *
         * @return array[string][string]
         */
        public static function getSupportedLanguages()
        {
            return array(
            	'en' => 'English',
                /* 'de' => 'German','zh' => 'Chinese', 'es' => 'Spanish', 'fr' => 'French',
                'it' => 'Italian', 'ja' => 'Japanese', 'nl' => 'Dutch', 'pl' => 'Polish', 'pt' => 'Portuguese',
                'ru' => 'Russian', 'sv' => 'Swedish', 'tr' => 'Turkish' */
            );
        }

        /**
         * Returns true if the entered language (ISO code) is supported.
         *
         * @param string $lang
         * @return boolean
         */
        public static function isSupportedLanguage($lang)
        {
            foreach (array_keys(self::getSupportedLanguages()) as $code) {
                if ($code == strtolower($lang)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Return the list of currencies recognized by the 1PEY platform.
         *
         * @return array[int][OnePEYCurrency]
         */
        public static function getSupportedCurrencies()
        {
            $currencies = array(
                array('ARS', '032', 2), array('AUD', '036', 2), array('KHR', '116', 0), array('CAD', '124', 2),
                array('CNY', '156', 1), array('HRK', '191', 2), array('CZK', '203', 2), array('DKK', '208', 2),
                array('EKK', '233', 2), array('HKD', '344', 2), array('HUF', '348', 2), array('ISK', '352', 0),
                array('IDR', '360', 0), array('JPY', '392', 0), array('KRW', '410', 0), array('LVL', '428', 2),
                array('LTL', '440', 2), array('MYR', '458', 2), array('MXN', '484', 2), array('NZD', '554', 2),
                array('NOK', '578', 2), array('PHP', '608', 2), array('RUB', '643', 2), array('SGD', '702', 2),
                array('ZAR', '710', 2), array('SEK', '752', 2), array('CHF', '756', 2), array('THB', '764', 2),
                array('GBP', '826', 2), array('USD', '840', 2), array('TWD', '901', 1), array('RON', '946', 2),
                array('TRY', '949', 2), array('XOF', '952', 0), array('BGN', '975', 2), array('EUR', '978', 2),
                array('PLN', '985', 2), array('BRL', '986', 2)
            );

            $onepey_currencies = array();

            foreach ($currencies as $currency) {
                $onepey_currencies[] = new OnePEYCurrency($currency[0], $currency[1], $currency[2]);
            }

            return $onepey_currencies;
        }

        /**
         * Return a currency from its 3-letters ISO code.
         *
         * @param string $alpha3
         * @return OnePEYCurrency
         */
        public static function findCurrencyByAlphaCode($alpha3)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var OnePEYCurrency $currency
                 */
                if ($currency->getAlpha3() == $alpha3) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Returns a currency form its numeric ISO code.
         *
         * @param int $numeric
         * @return OnePEYCurrency
         */
        public static function findCurrencyByNumCode($numeric)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var OnePEYCurrency $currency
                 */
                if ($currency->getNum() == $numeric) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Return a currency from its 3-letters or numeric ISO code.
         *
         * @param string $code
         * @return OnePEYCurrency
         */
        public static function findCurrency($code)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var OnePEYCurrency $currency
                 */
                if ($currency->getNum() == $code || $currency->getAlpha3() == $code) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Returns currency numeric ISO code from its 3-letters code.
         *
         * @param string $alpha3
         * @return int
         */
        public static function getCurrencyNumCode($alpha3)
        {
            $currency = self::findCurrencyByAlphaCode($alpha3);
            return ($currency instanceof OnePEYCurrency) ? $currency->getNum() : null;
        }

        /**
         * Returns an array of card types accepted by the 1PEY payment platform.
         *
         * @return array[string][string]
         */
        public static function getSupportedCardTypes()
        {
            return array(
                'CB' => 'CB', 'E-CARTEBLEUE' => 'E-Carte bleue', 'MAESTRO' => 'Maestro', 'MASTERCARD' => 'MasterCard',
                'VISA' => 'Visa', 'VISA_ELECTRON' => 'Visa Electron', 'VPAY' => 'V PAY', 'AMEX' => 'American Express',
                'ACCORD_STORE' => 'Carte de paiement Oney', 'ACCORD_STORE_SB' => 'Carte de paiement Oney - Sandbox',
                'ALINEA' => 'Carte Privative Alinea', 'ALINEA_CDX' => 'Carte cadeau Alinea',
                'ALINEA_CDX_SB' => 'Carte cadeau Alinea - SandBox', 'ALINEA_SB' => 'Carte Privative Alinea - SandBox',
                'AURORE-MULTI' => 'Carte Aurore', 'BANCONTACT' => 'Carte Maestro Bancontact Mistercash',
                'BITCOIN' => 'Bitcoin', 'BIZZBEE_CDX' => 'Carte cadeau Bizzbee',
                'BIZZBEE_CDX_SB' => 'Carte cadeau Bizzbee - Sandbox', 'BRICE_CDX' => 'Carte cadeau Brice',
                'BRICE_CDX_SB' => 'Carte cadeau Brice - Sandbox', 'CDGP' => 'Carte Privil??ge', 'COF3XCB' => '3 fois CB Cofinoga',
                'COF3XCB_SB' => '3 fois CB Cofinoga - Sandbox', 'COFINOGA' => 'Carte Be Smart', 'CORA_BLANCHE' => 'Carte Cora Blanche',
                'CORA_PREM' => 'Carte Cora Premium', 'CORA_VISA' => 'Carte Cora Visa', 'DINERS' => 'Carte Diners Club',
                'E_CV' => 'E-ch??que vacance', 'EDENRED_TR' => 'Ticket Restaurant', 'EDENRED_EC' => 'Ticket EcoCheque',
                'EPS' => 'eps-??berweisung', 'FULLCB3X' => 'Paiement en 3X avec BNPP PF', 'FULLCB4X' => 'Paiement en 4X avec BNPP PF',
                'GIROPAY' => 'Giropay', 'KLARNA' => 'Klarna', 'IDEAL' => 'iDEAL', 'ILLICADO' => 'Carte cadeau Illicado',
                'ILLICADO_SB' => 'Carte cadeau Illicado - Sandbox', 'JCB' => 'Carte JCB', 'JOUECLUB_CDX' => 'Carte cadeau Jou??club',
                'JOUECLUB_CDX_SB' => 'Carte cadeau Jou??club - Sandbox', 'JULES_CDX' => 'Carte cadeau Jules',
                'JULES_CDX_SB' => 'Carte cadeau Jules - Sandbox', 'MASTERPASS' => 'Portefeuille num??rique MasterCard',
                'ONEY' => 'Paiement en 3/4 fois Oney FacilyPay', 'ONEY_SANDBOX' => 'Paiement en 3/4 fois Oney FacilyPay - Sandbox',
                'PAYLIB' => 'Paylib', 'PAYPAL' => 'PayPal', 'PAYPAL_SB' => 'PayPal - Sandbox',
                'PAYSAFECARD' => 'Carte pr??pay??e Paysafecard', 'POSTFINANCE' => 'PostFinance',
                'POSTFINANCE_EFIN' => 'PostFinance mode E-finance', 'RUPAY' => 'RuPay',
                'SCT' => 'Virement SEPA', 'SDD' => 'Pr??l??vement SEPA', 'SOFORT_BANKING' => 'Sofort',
                'TRUFFAUT_CDX' => 'Carte cadeau Truffaut', 'VILLAVERDE' => 'Carte cadeau Villaverde',
                'VILLAVERDE_SB' => 'Carte cadeau Villaverde - SandBox'
            );
        }
        
        
        /**
         * Returns an array of Hashing Algorithms used for payment protocol signing (pSign)
         *
         * @return array[string][string]
         */
        public static function getAvailablePsignAlgorithms()
        {
        	return array(
        			'sha1' => 'SHA-1 (160 bits)',
        			'sha224' => 'SHA-2 (224 bits)',
        			'sha256' => 'SHA-2 (256 bits)',
        			'sha384' => 'SHA-2 (384 bits)',
        			'sha512' => 'SHA-2 (521 bits)'
        	);
        }
        

        /**
         * Compute a 1PEY signature. Parameters must be in UTF-8.
         *
         * @param array[string][string] $parameters payment platform request/response parameters
         * @param string $key shop certificate
         * @param boolean $hashed set to false to get the unhashed signature
         * @return string
         */
        public static function sign($parameters, $key, $hashed = true)
        {
            ksort($parameters);

            $sign = '';
            foreach ($parameters as $name => $value) {
                if (substr($name, 0, 5) == 'vads_') {
                    $sign .= $value . '+';
                }
            }

            $sign .= $key;
            return $hashed ? sha1($sign) : $sign;
        }

        /**
         * PHP is not yet a sufficiently advanced technology to be indistinguishable from magic...
         * so don't use magic_quotes, they mess up with the platform response analysis.
         *
         * @param array $potentially_quoted_data
         * @return mixed
         */
        public static function uncharm($potentially_quoted_data)
        {
            if (get_magic_quotes_gpc()) {
                $sane = array();
                foreach ($potentially_quoted_data as $k => $v) {
                    $sane_key = stripslashes($k);
                    $sane_value = is_array($v) ? self::uncharm($v) : stripslashes($v);
                    $sane[$sane_key] = $sane_value;
                }
            } else {
                $sane = $potentially_quoted_data;
            }

            return $sane;
        }
    }
}
