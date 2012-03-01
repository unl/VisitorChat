<?php
/**
 * This file contains a class for operating with the UNL LDAP directory.
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

require_once 'UNL/LDAP/Exception.php';

/**
 * This class is a singleton class for operating with the UNL LDAP directory.
 * 
 * <code>
 * $options['bind_dn']       = 'uid=youruseridhere,ou=service,dc=unl,dc=edu';
 * $options['bind_password'] = 'passwordhere';
 * echo UNL_LDAP::getConnection($options)->getFirstAttribute('bbieber2', 'sn');
 * </code>
 * 
 * @category  Default 
 * @package   UNL_LDAP
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2009 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_LDAP
 */
class UNL_LDAP
{
    
    /**
     * The actual ldap connection link id.
     *
     * @var link
     */
    private $_ldap = false;
    
    /**
     * @var array
     */
    public $options = array('uri'           => 'ldap://ldap.unl.edu/ ldap://ldap-backup.unl.edu/',
                            'base'          => 'dc=unl,dc=edu',
                            'suffix'        => 'ou=People,dc=unl,dc=edu',
                            'bind_dn'       => 'get this from the identity management team',
                            'bind_password' => 'get this from the identity management team');
    
    /**
     * <code>
     * UNL_LDAP::getConnection($options)->getAttribute('bbieber','cn');
     * </code>
     * 
     * @param array $options Associative array of options.
     */
    public function __construct(array $options = null)
    {
        $this->setOptions($options);
    }
    
    /**
     * Set options for the ldap connection.
     * 
     * @param array $options Associative array of options to set.
     *
     * @return void
     */
    public function setOptions(array $options = null)
    {
        if (count($options)) {
            foreach ($options as $attr=>$value) {
                $this->options[$attr] = $value;
            }
        }
    }
    
    /**
     * Connect & bind to the directory.
     *
     * @return UNL_LDAP
     */
    public function connect()
    {
        if ($this->_ldap !== false) {
            return $this;
        }
        if ($this->_ldap = ldap_connect($this->options['uri'])) {
            if (ldap_bind($this->_ldap, $this->options['bind_dn'], $this->options['bind_password'])) {
                $this->options['bind_password'] = '****';
                return $this;
            }
            throw new UNL_LDAP_Exception('Connection failure: ldap_bind() returned false for the server.');
        }
        throw new UNL_LDAP_Exception('Could not connect to the LDAP server.');
    }
    
    /**
     * Get the LDAP connection
     * 
     * <code>
     * $conn = UNL_LDAP::getConnection($options);
     * </code>
     * 
     * @param array $options Associative array of options to set.
     *
     * @return UNL_LDAP
     */
    public static function getConnection(array $options = null)
    {
        $ldap = new self($options);
        return $ldap->connect();
    }
    
    /**
     * Get an attribute from LDAP given the LDAP-uid and attribute name.
     *
     * @param string $uid       The LDAP-uid of the user we are looking for.
     * @param string $attribute The attribute name we are interested in.
     * 
     * @return array The array of attribute values.
     */
    public function getAttribute($uid, $attribute)
    {
        $uid    = addslashes($uid);
        $result = ldap_search($this->_ldap, $this->options['suffix'], "uid=$uid");
        $info   = ldap_get_entries($this->_ldap, $result);
        
        if (count($info) == 0) {
            return false;
        } else {
            if (isset($info[0][$attribute])) {
                return $info[0][$attribute];
            } else {
                return false;
            }
        }
    }
    
    /**
     * Return the first attribute of an entry
     *
     * @param string $uid       The LDAP uid of the user we are looking for.
     * @param string $attribute The attribute name we are interested in.
     * 
     * @return string | false
     */
    public function getFirstAttribute($uid, $attribute)
    {
        if ($ret = $this->getAttribute($uid, $attribute)) {
            return $ret[0];
        } else {
            return false;
        }
    }
    
    /**
     * Search the directory for matching entries.
     *
     * @param string $base   Search base
     * @param string $filter LDAP filter to use
     * @param array  $params Optional parameters to add to the LDAP query
     * 
     * @return UNL_LDAP_Result
     */
    public function search($base = null, $filter = null, array $params = array())
    {
        include_once 'UNL/LDAP/Result.php';
        /* setting searchparameters  */
        (isset($params['sizelimit']))  ? $sizelimit  = $params['sizelimit']  : $sizelimit = 0;
        (isset($params['timelimit']))  ? $timelimit  = $params['timelimit']  : $timelimit = 0;
        (isset($params['attrsonly']))  ? $attrsonly  = $params['attrsonly']  : $attrsonly = 0;
        (isset($params['attributes'])) ? $attributes = $params['attributes'] : $attributes = array();

        $sr = ldap_search($this->_ldap, $base, $filter, $attributes, $attrsonly, $sizelimit, $timelimit);
        if ($sr === false) {
            throw new UNL_LDAP_Exception('Search failed');
        }
        return new UNL_LDAP_Result($this, $sr);
    }
    
    /**
     * returns the ldap connection resource link
     *
     * @return resource
     */
    public function &getLink()
    {
        return $this->_ldap;
    }

    /**
     * set the ldap connection resource link
     *
     * @return resource
     */
    public function setLink(&$link)
    {
        $this->_ldap = $link;
    }

    /**
     * unbinds from the ldap directory.
     * 
     * @return void
     */
    function disconnect()
    {
        if ($this->_ldap) {
            return ldap_unbind($this->_ldap);
        }
    }
    
    /**
     * destroy the object
     *
     * @return void
     */
    function __destruct()
    {
        $this->disconnect();
    }
}
