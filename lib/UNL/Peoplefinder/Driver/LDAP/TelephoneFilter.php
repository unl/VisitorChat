<?php
/**
 * Builds a simple telephone filter for searching for records.
 *
 * PHP version 5
 * 
 * @category  Default
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Driver_LDAP_TelephoneFilter
{
    private $_filter;
    
    function __construct($q)
    {
        if (!empty($q)) {
            $this->_filter = '(telephoneNumber=*'.str_replace('-','*',$q).')';
        }
    }
    
    function __toString()
    {
        $this->_filter = '(&'.$this->_filter.'(!(eduPersonPrimaryAffiliation=guest)))';
        return $this->_filter;
    }
}
