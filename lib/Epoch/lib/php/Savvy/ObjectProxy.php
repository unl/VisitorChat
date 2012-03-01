<?php
/**
 * Savvy_ObjectProxy
 *
 * PHP version 5
 *
 * @category  Templates
 * @package   Savvy
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 Brett Bieber
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/repository/pear2/Savvy
 */

/**
 * ObjectProxy class for Savvy
 * 
 * The ObjectProxy acts as an intermediary between an object and a template.
 * The $context variable will be an ObjectProxy which proxies member variable
 * access so escaping can be applied.
 *
 * @category  Templates
 * @package   Savvy
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 Brett Bieber
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/repository/pear2/Savvy
 */
class Savvy_ObjectProxy implements Countable
{
    /**
     * The internal object
     * 
     * @var mixed
     */
    protected $object;
    
    /**
     * The savvy templating system
     * 
     * @var Savvy
     */
    protected $savvy;
    
    /**
     * Construct a new object proxy
     * 
     * @param mixed $object The object
     * @param Main  $savvy The savvy templating system
     */
    function __construct($object, $savvy)
    {
        $this->object = $object;
        $this->savvy  = $savvy;
    }
    
    /**
     * Magic method for retrieving data.
     * 
     * String data will be escaped with $savvy->escape() before it is returned
     * 
     * @return mixed
     */
    function __get($var)
    {
        return $this->filterVar($this->object->$var);
    }
    
    /**
     * Returns a variable, after it has been filtered.
     * 
     * @param mixed $var
     * 
     * @return string|Savvy_ObjectProxy
     */
    protected function filterVar($var)
    {
        switch(gettype($var)) {
        case 'object':
            return self::factory($var, $this->savvy);
        case 'string':
        case 'int':
        case 'double':
            return $this->savvy->escape($var);
        case 'array':
            return new Savvy_ObjectProxy_ArrayAccess(
                        new ArrayIterator($var), $this->savvy);
        }
        return $var;
    }
    
    /**
     * Allows direct access to the entire object for situations where the proxy
     * interferes.
     * 
     * @return mixed The raw object
     */
    function getRawObject()
    {
        return $this->object;
    }
    
    /**
     * Allows access to the raw member variables of the internal object.
     * 
     * @return mixed
     */
    function getRaw($var)
    {
        return $this->object->$var;
    }
    
    function __set($var, $value)
    {
        $this->object->$var = $value;
    }
    
    /**
     * Magic method for checking if a property is set.
     * 
     * @param string $var The var
     * 
     * @return bool
     */
    function __isset($var)
    {
        return isset($this->object->$var);
    }
    
    /**
     * Unset a property.
     * 
     * @param string $var The var
     * 
     * @return void
     */
    function __unset($var)
    {
        unset($this->object->$var);
    }
    
    /**
     * Magic method which will call methods on the object.
     * 
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return $this->filterVar(call_user_func_array(array($this->object, $name), $arguments));
    }
    
    /**
     * Gets the class of the internal object
     * 
     * When using the ClassToTemplateMapper this method will be called to
     * determine the class of the object.
     * 
     * @return string
     */
    function __getClass()
    {
        return get_class($this->object);
    }
    
    /**
     * Constructs an ObjectProxy for the given object.
     * 
     * @param mixed $object The object to proxy
     * @param Main  $savvy The main savvy instance
     * 
     * @return Savvy_ObjectProxy
     */
    public static function factory($object, $savvy)
    {
        if ($object instanceof Traversable) {
            return new Savvy_ObjectProxy_Traversable($object, $savvy);
        }
        if ($object instanceof ArrayAccess) {
            return new Savvy_ObjectProxy_ArrayAccess($object, $savvy);
        }
        return new self($object, $savvy);
    }
    
    function __toString()
    {
        if (method_exists($this->object, '__toString')) {
            return $this->savvy->escape($this->object->__toString());
        }
        throw new Savvy_BadMethodCallException('Object of class '.$this->__getClass().' could not be converted to string');
    }
    
    /**
     * Returns the number of elements if the object has implemented Countable,
     * otherwise 1 is returned.
     * 
     * @return int
     */
    function count()
    {
        return count($this->object);
    }
}