<?php
/**
 * Peoplefinder class for UNL's online directory.
 *
 * PHP version 5
 *
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Record
{
    public $cn;
    public $ou;
    public $eduPersonNickname;
    public $eduPersonPrimaryAffiliation;
    public $givenName;
    public $displayName;
    public $mail;
    public $postalAddress;
    public $sn;
    public $telephoneNumber;
    public $title;
    public $uid;
    public $unlHRPrimaryDepartment;
    public $unlHRAddress;
    public $unlSISClassLevel;
    public $unlSISCollege;
//    public $unlSISLocalAddr1;
//    public $unlSISLocalAddr2;
//    public $unlSISLocalCity;
//    public $unlSISLocalPhone;
//    public $unlSISLocalState;
//    public $unlSISLocalZip;
//    public $unlSISPermAddr1;
//    public $unlSISPermAddr2;
//    public $unlSISPermCity;
//    public $unlSISPermState;
//    public $unlSISPermZip;
    public $unlSISMajor;
    public $unlEmailAlias;
    
    
    static function fromLDAPEntry(array $entry)
    {
        $r = new self();
        foreach (get_object_vars($r) as $var=>$val) {
            if (isset($entry[strtolower($var)], $entry[strtolower($var)][0])) {
                $r->$var = $entry[strtolower($var)][0];
            }
        }
        return $r;
    }
    
    static function fromUNLLDAPEntry(UNL_LDAP_Entry $entry)
    {
        $r = new self();
        foreach (get_object_vars($r) as $var=>$val) {
            $r->$var = $entry->$var;
        }
        return $r;
    }
    
    /**
     * Takes in a string from the LDAP directory, usually formatted like:
     *     ### ___ UNL 68588-####
     *    Where ### is the room number, ___ = Building Abbreviation, #### zip extension
     *
     * @param string
     * @return array Associative array.
     */
    function formatPostalAddress()
    {
        /* this is a faculty postal address
            Currently of the form:
            ### ___ UNL 68588-####
            Where ### is the room number, ___ = Building Abbreviation, #### zip extension
        */
        /**
         * We assumed that the address format is: ### ___ UNL 68588-####.
         * Some 'fortunate' people have addresses not in this format.
         */
        //RLIM
        // treat UNL as the delimiter for the streetaddress and zip
        if (strpos($this->postalAddress,'UNL')) {
            $addressComponent = explode('UNL', $this->postalAddress);
        } elseif (strpos($this->postalAddress,'UNO')) {
            $addressComponent = explode('UNO', $this->postalAddress);
        } elseif (strpos($this->postalAddress,'Omaha')) {
            $addressComponent = explode('Omaha', $this->postalAddress);
        } else {
            $addressComponent = array($this->postalAddress);
        }
        $address['region']         = 'NE';
        $address['street-address'] = trim($addressComponent[0]);
        if (isset($addressComponent[1])) {
            $address['postal-code'] = trim($addressComponent[1]);
        } else {
            $address['postal-code'] = '';
        }
        switch (substr($address['postal-code'],0,3)) {
            case '681':
                $address['locality'] = 'Omaha';
            break;
            case '685':
            default:
                $address['locality'] = 'Lincoln';
            break;
        }
        
        return $address;
    }
    
    function __toString()
    {
        return $this->uid;
    }
}

?>