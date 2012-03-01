<?php
/**
 * LDAP utilities for building search filters
 *
 * PHP version 5
 * 
 * @category  Default 
 * @package   UNL_Peoplefinder
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Driver_LDAP_Util
{
    /**
    * Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
    *
    * Any control characters with an ACII code < 32 as well as the characters with special meaning in
    * LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
    * backslash followed by two hex digits representing the hexadecimal value of the character.
    *
    * @see Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
    * @link http://pear.php.net/package/Net_LDAP2
    * @author Benedikt Hallinger <beni@php.net>
    *
    * @param array $values Array of values to escape
    *
    * @static
    * @return array Array $values, but escaped
    */
    public static function escape_filter_value($values = array())
    {
        // Parameter validation
        if (!is_array($values)) {
            $values = array($values);
        }

        foreach ($values as $key => $val) {
            // Escaping of filter meta characters
            $val = str_replace(array('\\',  '*',   '(',   ')'),
                               array('\5c', '\2a', '\28', '\29'),
                               $val);

            // ASCII < 32 escaping
            $val = self::asc2hex32($val);

            if (null === $val) $val = '\0';  // apply escaped "null" if string is empty

            $values[$key] = $val;
        }

        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
    * Converts all ASCII chars < 32 to "\HEX"
    *
    * @see Net_LDAP2_Util::asc2hex32() from Benedikt Hallinger <beni@php.net>
    * @link http://pear.php.net/package/Net_LDAP2
    * @author Benedikt Hallinger <beni@php.net>
    *
    * @param string $string String to convert
    *
    * @static
    * @return string
    */
    public static function asc2hex32($string)
    {
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            if (ord($char) < 32) {
                $hex = dechex(ord($char));
                if (strlen($hex) == 1) $hex = '0'.$hex;
                $string = str_replace($char, '\\'.$hex, $string);
            }
        }
        return $string;
    }
    
    /**
     * sort a multidimensional array
     *
     * @return array
     */
    public static function array_csort()
    {
        $args   = func_get_args();
        $marray = array_shift($args);
        
        $msortline = "return(array_multisort(";
        foreach ($args as $arg) {
            @$i++;
            if (is_string($arg)) {
                foreach ($marray as $row) {
                    $sortarr[$i][] = $row[$arg];
                }
            } else {
                $sortarr[$i] = $arg;
            }
            $msortline .= "\$sortarr[".$i."],";
        }
        $msortline .= "\$marray));";
        
        eval($msortline);
        return $marray;
    }
}
