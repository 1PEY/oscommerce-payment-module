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

if (! class_exists('OnePEYCurrency', false)) {

    /**
     * Class representing a currency, used for converting alpha/numeric ISO codes and float/integer amounts.
     */
    class OnePEYCurrency
    {

        private $alpha3;
        private $num;
        private $decimals;

        public function __construct($alpha3, $num, $decimals = 2)
        {
            $this->alpha3 = $alpha3;
            $this->num = $num;
            $this->decimals = $decimals;
        }

        public function convertAmountToInteger($float)
        {
            $coef = pow(10, $this->decimals);

            $amount = $float * $coef;
            return (int) (string) $amount; // cast amount to string (to avoid rounding) than return it as int
        }

        public function convertAmountToFloat($integer)
        {
            $coef = pow(10, $this->decimals);

            return ((float) $integer) / $coef;
        }

        public function getAlpha3()
        {
            return $this->alpha3;
        }

        public function getNum()
        {
            return $this->num;
        }

        public function getDecimals()
        {
            return $this->decimals;
        }
    }
}
