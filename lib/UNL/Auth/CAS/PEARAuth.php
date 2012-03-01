<?php
/**
 * PEAR Auth compatible container for CAS
 *
 * PHP version 5
 * 
 * @category  Default 
 * @package   UNL_Auth
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_Auth
 */

include_once 'Auth/Container.php';
require_once 'UNL/Auth/CAS.php';

class UNL_Auth_CAS_PEARAuth extends Auth_Container
{
    protected $cas;
    
    public function __construct($options)
    {
        $this->cas = UNL_Auth_CAS::getInstance();
    }
    
    public function getPEARAuth($options = null, $loginFunction = null, $showLogin = true)
    {
        if (!isset($loginFunction)) {
            $loginFunction = array('UNL_Auth_CAS_PEARAuth', 'login');
        }
        $auth = new Auth($this, $options, $loginFunction, $showLogin);
        if ($this->checkAuth()) {
            $auth->setAuth($this->getUsername());
        }
        $auth->setLogoutCallback(array('UNL_Auth_CAS_PEARAuth','logout'));
        return $auth;
    }
    
    public function login()
    {
        UNL_Auth_CAS::getInstance()->login();
    }
    
    public function logout()
    {
        return UNL_Auth_CAS::getInstance()->logout();
    }
    
    public function getAuth()
    {
        return UNL_Auth_CAS::getInstance()->isLoggedIn();
    }
    
    public function checkAuth()
    {
        return UNL_Auth_CAS::getInstance()->isLoggedIn();
    }
    
    public function getUsername()
    {
        return UNL_Auth_CAS::getInstance()->getUser();
    }
    
}
