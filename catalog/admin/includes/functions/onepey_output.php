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
 * General functions to draw 1PEY configuration parameters.
 */

global $onepey_supported_languages, $onepey_supported_psign_algos;

// load 1PEY payment API
$onepey_supported_languages = OnePEYApi::getSupportedLanguages();
$onepey_supported_psign_algos = OnePEYApi::getAvailablePsignAlgorithms();


function onepey_get_bool_title($value)
{
    $key = 'MODULE_PAYMENT_ONEPEY_VALUE_' . $value;

    if (defined($key)) {
        return constant($key);
    } else {
        return $value;
    }
}

function onepey_get_lang_title($value)
{
    global $onepey_supported_languages;

    $key = 'MODULE_PAYMENT_ONEPEY_LANGUAGE_' . strtoupper($onepey_supported_languages[$value]);

    if (defined($key)) {
        return constant($key);
    } else {
        return $value;
    }
}


function onepey_get_validation_mode_title($value)
{
    $key = 'MODULE_PAYMENT_ONEPEY_VALIDATION_' . $value;

    if (defined($key)) {
        return constant($key);
    } else {
        return MODULE_PAYMENT_ONEPEY_VALIDATION_DEFAULT;
    }
}

function onepey_get_psign_algo_title($value)
{
    global $onepey_supported_psign_algos;

    if (! empty($value)) {
        $algos = explode(';', $value);

        $result = array();
        foreach ($algos as $calgo) {
            $result[] = $onepey_supported_psign_algos[$calgo];
        }

        return implode(', ', $result);
    } else {
        return '';
    }
}



function onepey_cfg_draw_pull_down_bools($value='', $name)
{
    $name = 'configuration[' . tep_output_string($name) . ']';
    if (empty($value) && isset($GLOBALS[$name])) $value = stripslashes($GLOBALS[$name]);

    $bools = array('1', '0');

    $field = '';
    foreach ($bools as $bool) {
        $field .= '<input type="radio" name="' . $name . '" value="' . $bool . '"';
        if ($value == $bool) {
            $field .= ' checked="checked"';
        }

        $field .= '> ' . tep_output_string(onepey_get_bool_title($bool)) . '<br />';
    }

    return $field;
}


function onepey_cfg_draw_pull_down_langs($value='', $name)
{
    global $onepey_supported_languages;

    $name = 'configuration[' . tep_output_string($name) . ']';
    if (empty($value) && isset($GLOBALS[$name])) $value = stripslashes($GLOBALS[$name]);

    $field = '<select name="' . $name . '">';
    foreach ($onepey_supported_languages as $key => $label) {
        $field .= '<option value="' . $key . '"';
        if ($value == $key) {
            $field .= ' selected="selected"';
        }

        $field .= '>' . tep_output_string(onepey_get_lang_title($key)) . '</option>';
    }

    $field .= '</select>';

    return $field;
}


function onepey_cfg_draw_pull_down_psign_algos($value='', $name)
{
    global $onepey_supported_psign_algos;

    $fieldName = 'configuration[' . tep_output_string($name) . ']';
    if (empty($value) && isset($GLOBALS[$fieldName])) $value = stripslashes($GLOBALS[$fieldName]);

    $cards = empty($value) ? array() : explode(';', $value);

    $field = '<select name="' . tep_output_string($name) . '" multiple="multiple" onChange="JavaScript:onepeyProcessPsignAlgos()">';
    foreach ($onepey_supported_psign_algos as $key => $label) {
        $field .= '<option value="' . $key . '"';
        if (in_array($key, $cards)) {
            $field .= ' selected="selected"';
        }

        $field .= '>' . tep_output_string($label) . '</option>';
    }
    $field .= '</select> <br />';

    $field .= <<<JSCODE
    <script type="text/javascript">
        function onepeyProcessPsignAlgos() {
            var elt = document.forms['modules'].elements['$name'];

            var result = '';
            for (var i=0; i < elt.length; i++) {
                if (elt[i].selected) {
                    if (result != '') result += ';';
                    result += elt[i].value;
                }
            }

            document.forms['modules'].elements['$fieldName'].value = result;
        }
    </script>
JSCODE;

    $field .= '<input type="hidden" name="' . tep_output_string($fieldName) . '" value="' . $value . '">';

    return $field;
}



function onepey_js_serialize($name)
{
    $fieldName = 'configuration[' . $name . ']';

    $js_code = <<<JSCODE
    var JSON = JSON || {};

    // implement JSON.stringify serialization
    JSON.stringify || function(obj) {
        var t = typeof (obj);
        if (t != "object" || obj === null) {
            // simple data type
            if (t == "string") obj = '"' + obj + '"';
            return String(obj);
        } else {
            // recurse array or object
            var n, v, json = [], arr = (obj && obj.constructor == Array);

            for (n in obj) {
                v = obj[n]; t = typeof(v);

                if (t == "string") v = '"'+v+'"';
                else if (t == "object" && v !== null) v = JSON.stringify(v);

                json.push((arr ? "" : '"' + n + '":') + String(v));
            }

            return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
        }
    };

    jQuery(document.forms['modules']).submit(function(event) {
        var options = {};

        jQuery('#$name' + '_table tbody tr td input[type=text]').each(function() {
            var name = jQuery(this).attr('name');
            name = name.replace(/\]/g, '');

            var keys = name.split('[');
            keys.shift();

            options = onepeyFillArray(options, keys, jQuery(this).val());
        });

            document.forms['modules'].elements['$fieldName'].value = JSON.stringify(options);
            return true;
    });

    function onepeyFillArray(arr, keys, val) {
        if (keys.length > 0) {
            var key = keys[0];

            if (keys.length == 1) {
                // it's a leaf, let's set the value
                arr[key] = val;
            } else {
                keys.shift();

                if (! arr[key]) {
                    arr[key] = {};
                }
                arr[key] = onepeyFillArray(arr[key], keys, val);
            }
        }

        return arr;
    }
JSCODE;

    return $js_code;
}
