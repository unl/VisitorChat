<?php
/**
 * This is a generic authentication framework for UNL which will return customized
 * containers for use at UNL.
 * 
 * <code>
 * <?php
 * require_once 'UNL/Auth.php';
 * $a = UNL_Auth::factory('CAS');
 * if ($a->isLoggedIn()) {
 *     echo 'Hello ' . $a->getUser();
 * } else {
 *     echo 'Sorry, you must log in.';
 * }
 * </code>
 *
 * PHP version 5
 * 
 * @category  Authentication 
 * @package   UNL_Auth
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2009 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_Auth
 */
class UNL_Auth
{
    protected static $_instance = null;
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    private function __construct()
    {}
    
    private function __clone()
    {}
    
    /**
     * Abstract factory, used to get drivers for any of the authentication methods
     * on campus.
     *
     * @param string $auth_type CAS, LDAP, LotusNotes, etc
     * @param mixed  $options   Options for the specific container
     * 
     * @return mixed
     */
    public static function factory($auth_type, $options = null)
    {
        $auth_class = 'UNL_Auth_'.$auth_type;
        $class_file = dirname(__FILE__).'/Auth/'.$auth_type.'.php';
        return self::discoverAndReturn($auth_class, $class_file, $options);
    }
    
    /**
     * Returns an auth container for use with systems compatible with PEAR Auth
     *
     * @param string $auth_type CAS, LDAP, LotusNotes, etc
     * @param mixed  $options   Options for the container
     * 
     * @return mixed
     */
    public static function PEARFactory($auth_type, $options = null, $loginFunction = null, $showLogin = true)
    {
        require_once 'Auth/Auth.php';
        /// Get the class... return the pear auth container.
        $auth_class = 'UNL_Auth_'.$auth_type.'_PEARAuth';
        $class_file = dirname(__FILE__).'/Auth/'.$auth_type.'/PEARAuth.php';
        $container = self::discoverAndReturn($auth_class, $class_file, $options);
        return $container->getPEARAuth($options, $loginFunction, $showLogin);
    }
    
    public static function ZendFactory($auth_type, $options = null)
    {
        throw new Exception('not implemented yet!');
        /// Get the class name, return the Zend Auth extended class
        $auth_class = 'UNL_Auth_'.$auth_type.'_ZendAuth';
        $class_file = dirname(__FILE__).'/Auth/'.$auth_type.'/ZendAuth.php';
        $container = self::discoverAndReturn($auth_class, $class_file, $options);
        return $container;
    }
    
    /**
     * This is a class used to discover and return a new class based given a class
     * name and file.
     *
     * @param string $class      name of the class to load UNL_Auth_CAS
     * @param string $class_file ./Auth/CAS.php
     * 
     * @return object
     */
    protected static function discoverAndReturn($class, $class_file, $options = null)
    {
        if (!class_exists($class)) {
            if (file_exists($class_file)) {
                require_once $class_file;
            } else {
                throw new Exception('Cannot find authentication class that matches '.
                                    $auth_type.' I tried '.$class_file);
            }
        }
        if (method_exists($class, 'getInstance')) {
            return call_user_func(array($class, 'getInstance'), $options);
        } else {
            return new $class($options);
        }
        
    }
}


?>