<?php
/**
 * This is a CAS central authentication.
 *
 * PHP version 5
 * 
 * @category  Authentication 
 * @package   UNL_Auth
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://pear.unl.edu/package/UNL_Auth
 */

require_once 'SimpleCAS/Autoload.php';
require_once 'HTTP/Request2.php';

/**
 * UNL_Auth_SimpleCAS
 *
 * This is the CAS UserAccount class.
 * This class takes care of user authentication using CAS and obtains the user
 * account information via LDAP.
 *
 * This class does not handle changes to the user account information. All account
 * information changes are handled by http://login.unl.edu/
 * 
 */
class UNL_Auth_SimpleCAS extends UNL_Auth
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
    protected $options = array('hostname' => 'login.unl.edu',
                               'port'     => 443,
                               'uri'      => 'cas');
    
    protected $client;
    
    /**
     * The class constructor used to initialize the SimpleCAS class settings.
     */
    private function __construct(array $options = array())
    {
        $options = array_merge($this->options, $options);
        $protocol = new SimpleCAS_Protocol_Version2($this->options);
        
        $protocol->getRequest()->setConfig('ssl_verify_peer', false);
        
        $this->client = SimpleCAS::client($protocol);
        if ($this->client->isAuthenticated()) {
            $this->isAuth = true;
            $this->uid    = $this->client->getUsername();
        }
    }
    
    /**
     * get a singleton instance of this class
     *
     * @return UNL_Auth_SimpleCAS
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    function isLoggedIn()
    {
        return $this->isAuth;
    }
    
    function getUser()
    {
        return $this->client->getUsername();
    }
    
    function login()
    {
        return $this->client->forceAuthentication();
    }
    
    function logout()
    {
        return $this->client->logout();
    }
}
?>