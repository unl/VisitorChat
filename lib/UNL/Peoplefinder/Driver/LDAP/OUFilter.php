<?php
/**
 * Builds a simple ou filter for records.
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
class UNL_Peoplefinder_Driver_LDAP_OUFilter
{
    private $_filter;
    
    /**
     * Create a filter for OU filtering.
     *
     * @param string $ou Organizational Unit eg:org|College of Engineering
     */
    function __construct($ou)
    {
        if (!empty($ou)) {
            $this->_filter = '(ou='.str_replace('-', '*', $ou).')';
        }
    }
    
    function __toString()
    {
        $this->_filter = '(&'.$this->_filter.'(!(eduPersonPrimaryAffiliation=guest)))';
        return $this->_filter;
    }
}
