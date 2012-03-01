<?php
/**
 * LDAP attribute object
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
 * Class representing an LDAP entry's attribute
 * 
 * @category  Default 
 * @package   UNL_LDAP
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2009 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_LDAP
 */
class UNL_LDAP_Entry_Attribute extends ArrayIterator
{
    public $count;
    
    /**
     * construct an ldap attribute object
     *
     * @param array $attribute Array returned from ldap_next_attribute
     */
    public function __construct(array $attribute)
    {
        $this->count = $attribute['count'];
        unset($attribute['count']);
        parent::__construct($attribute);
    }
    
    /**
     * Return the total number of attributes
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    public function unserialize($data)
    {
        parent::unserialize($data);
        $this->rewind();
    }

    /**
     * Returns the first attribute entry
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->current();
    }
}