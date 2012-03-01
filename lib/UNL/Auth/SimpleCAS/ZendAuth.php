<?php
/**
 * This is a Zend_Auth adapter library for CAS.
 * It uses SimpleCAS.
 *
 * <code>
 * public function casAction()
 * {
 *     $auth = Zend_Auth::getInstance();
 *     $authAdapter = UNL_Auth::factory('SimpleCAS', Zend_Registry::get('config')->auth->cas);
 * 
 *     # User has not been identified, and there's a ticket in the URL
 *     if (!$auth->hasIdentity() && isset($_GET['ticket'])) {
 *         $authAdapter->setTicket($_GET['ticket']);
 *         $result = $auth->authenticate($authAdapter);
 * 
 *         if ($result->isValid()) {
 *             Zend_Session::regenerateId();
 *         }
 *     }
 * 
 *     # No ticket or ticket was invalid. Redirect to CAS.
 *     if (!$auth->hasIdentity()) {
 *         $this->_redirect($authAdapter->getLoginURL());
 *     }
 * }
 * </code>
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

require_once 'UNL/Auth/SimpleCAS.php';

class UNL_Auth_SimpleCAS_ZendAuth implements Zend_Auth_Adapter_Interface
{
    /**
     * CAS client
     * 
     * @var UNL_Auth_SimpleCAS
     */
    protected $_simplecas;

    /**
     * Constructor
     *
     * @return void
     */ 
    public function __construct()
    {
        $this->_simplecas = UNL_Auth::factory('SimpleCAS');
    }

    /**
     * Authenticates the user
     *
     * @return Zend_Auth_Result
     */ 
    public function authenticate()
    {
        $this->_simplecas->login();
        if ($this->_simplecas->isLoggedIn()) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $this->_simplecas->getUser(),
                array("Authentication successful"));
        } else {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE,
                null,
                array("Authentication failed"));
        }
    }
 
}
