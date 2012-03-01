<?php
/**
 * A class for obtaining info about People from the LDAP directory.
 * 
 * <code>
 * include_once 'UNL/LDAP.php'
 * include_once 'UNL/LDAP/People.php';
 * 
 * echo UNL_LDAP_People::getCommonName('bbieber2');
 * </code>
 *
 * PHP version 5
 * 
 * $Id$
 * 
 * @category  Default 
 * @package   UNL_LDAP
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2009 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_LDAP
 */

/**
 * Class for managing records for people.
 * 
 * @category  Default 
 * @package   UNL_LDAP
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2009 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_LDAP
 */
class UNL_LDAP_People
{
    /**
     * Returns the 'sn' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's lastname.
     */
    function getLastName($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'sn');
    }

    /**
     * Returns the 'cn' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's common name (typically givenname + cn).
     */
    function getCommonName($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'cn');
    }

    /**
     * Returns the 'givenname' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's first name.
     */
    function getFirstName($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'givenname');
    }

    /**
     * Returns the 'telephonenumber' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's telephone number.
     */
    function getTelephone($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'telephonenumber');
    }

    /**
     * Returns the 'facsimiletelephonenumber' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's fax number.
     */
    function getFax($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'fascimiletelephonenumber');
    }

    /**
     * Returns the 'street' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's street address.
     */
    function getStreet($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'street');
    }

    /**
     * Returns the 'l' (locality) LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's city.
     */
    function getCity($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'l');
    }

    /**
     * Returns the 'st' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's state.
     */
    function getState($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'st');
    }

    /**
     * Returns the 'postalcode' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's zipcode.
     */
    function getZip($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'postalcode');
    }

    /**
     * Returns the 'country' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's zipcode.
     */
    function getCountry($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'c');
    }

    /**
     * Returns the 'mail' LDAP attribute of the user.
     * 
     * @param string $uid Unique ID for the user
     * 
     * @return string The user's email address.
     */
    function getEmail($uid)
    {
        return UNL_LDAP::getConnection()->getFirstAttribute($uid, 'mail');
    }
}

?>