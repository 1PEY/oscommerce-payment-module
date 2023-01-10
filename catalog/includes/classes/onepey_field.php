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

if (! class_exists('OnePEYField', false)) {

    /**
     * Class representing a form field to send to the payment platform.
     */
    class OnePEYField
    {

        /**
         * field name.
         * Matches the HTML input attribute.
         *
         * @var string
         */
        private $name;


        /**
         * field value.
         * Null or string.
         *
         * @var string
         */
        private $value = null;

        /**
         * Constructor.
         *
         * @param string $name
         * @param string $label
         * @param string $regex
         * @param boolean $required
         * @param int length
         */
        public function __construct($name)
        {
            $this->name = $name;
        }


        /**
         * Setter for value.
         *
         * @param mixed $value
         * @return boolean
         */
        public function setValue($value)
        {
            $value = ($value === null) ? null : (string) $value;
            // we save value even if invalid but we return "false" as warning
            $this->value = $value;
            return true;
        }

        /**
         * Return the current value of the field.
         *
         * @return string
         */
        public function getValue()
        {
            return $this->value;
        }


        /**
         * Return the name (HTML attribute) of the field.
         *
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }


        /**
         * Has a value been set ?
         *
         * @return boolean
         */
        public function isFilled()
        {
            return ! is_null($this->value);
        }
    }
}
