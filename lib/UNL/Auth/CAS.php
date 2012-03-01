<?php
/**
 * This is a CAS central authentication.
 *
 * DO NOT MODIFY THIS FILE.
 * This file remains part of the UNL Login public API and is subject to change.
 * If you require features built into this class, please contact us by email at
 * <accounts@answers4families.org>.
 *
 * based on the Answers4Families [http://www.answers4families.org/] Account Services 
 * LDAP-CAS API.
 *
 * 
 * PHP version 5
 * 
 * @category  Authentication 
 * @package   UNL_Auth
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @author    Ryan Lim <rlim@ccfl.unl.edu>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_Auth
 */

require_once 'CAS.php';


/**
 * UNL_Auth_CAS
 *
 * This is the CAS UserAccount class.
 * This class takes care of user authentication using CAS and obtains the user
 * account information via LDAP.
 *
 * This class does not handle changes to the user account information. All account
 * information changes are handled by http://login.unl.edu/
 * 
 */
class UNL_Auth_CAS extends UNL_Auth
{
    
    /**
     * Boolean flag to if the user is authenticated or not.
     * 
     * @var bool
     */
    protected $isAuth = false;

    /**
     * $uid is the LDAP uid value of the authenticated user.
     * 
     * @var string
     */
    protected $uid;
    
    /**
     * Options for the CAS server
     *
     * @var array
     */
    protected $cas_options = array('host' => 'login.unl.edu',
                                   'port' => 443,
                                   'path' => 'cas');

    /**
     * The class constructor used to initialize the phpCAS class settings.
     */
    private function __construct(array $options = null)
    {
        if (session_id() != '') {
            $start_session = false;
        } else {
            $start_session = true;
        }
        phpCAS::setDebug(false);
        phpCAS::client(CAS_VERSION_2_0,
            $this->cas_options['host'], $this->cas_options['port'], $this->cas_options['path'],
            $start_session);
        phpCAS::setNoCasServerValidation();
        phpCAS::setCacheTimesForAuthRecheck(-1);

        $this->isAuth = phpCAS::checkAuthentication();
    }
    
    /**
     * get a singleton instance of this class
     *
     * @return UNL_Auth_CAS
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Log in the user.
     */
    function login()
    {
        phpCAS::forceAuthentication();
        $this->isAuth = true;
        $this->uid    = phpCAS::getUser();
    }

    /**
     * Log out the user.
     */
    function logout()
    {
        $this->isAuth = false;
        phpCAS::forceAuthentication();
        if (!empty($_SERVER['HTTP_REFERER'])) {
            phpCAS::logoutWithUrl($_SERVER['HTTP_REFERER']);
        } else {
            phpCAS::logout();
        }
    }

    /**
     * Checks to see if the user is logged in.
     * 
     * @return bool true if logged in, false otherwise.
     */
    function isLoggedIn()
    {
        return $this->isAuth;
    }

    /**
     * Get the LDAP-uid.
     *
     * @return string | bool The LDAP uid of the logged in user.
     */
    function getUser()
    {
        if ($this->isAuth) {
            return phpCAS::getUser();
        } else {
            return false;
        }
    }

    /**
     * Stores the LDAP-uid internally in this instance of the class.
     *
     * @return string The LDAP uid of the logged in user. If the user is not logged in, return false.
     */
    function getUid()
    {
        $this->uid = $this->getUser();
        return $this->uid;
    }
}
