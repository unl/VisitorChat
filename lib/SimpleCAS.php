<?php
/**
 * This is a CAS client authentication library for PHP 5.
 * 
 * <code>
 * <?php
 * $protocol = new SimpleCAS_Protocol_Version2('login.unl.edu', 443, 'cas');
 * $client = SimpleCAS::client($protocol);
 * $client->forceAuthentication();
 * 
 * if (isset($_GET['logout'])) {
 *     $client->logout();
 * }
 * 
 * if ($client->isAuthenticated()) {
 *     echo '<h1>Authentication Successful!</h1>';
 *     echo '<p>The user\'s login is '.$client->getUsername().'</p>';
 *     echo '<a href="?logout">Logout</a>';
 * }
 * </code>
 * 
 * PHP version 5
 * 
 * @category  Authentication 
 * @package   SimpleCAS
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://code.google.com/p/simplecas/
 */
class SimpleCAS
{
    /**
     * Version of the CAS library.
     */
    const VERSION = '0.0.1';
    
    /**
     * Singleton CAS object
     *
     * @var CAS
     */
    static private $_instance;
    
    /**
     * Is user authenticated?
     *
     * @var bool
     */
    private $_authenticated = false;
    
    /**
     * Protocol for the server running the CAS service.
     *
     * @var SimpleCAS_Protocol
     */
    protected $protocol;
    
    /**
     * User's login name if authenticated.
     *
     * @var string
     */
    protected $username;

    /**
     * (Optional) alternative service URL to return to after CAS authentication.
     *
     * @var string
     */
    static protected $url;
    
    /**
     * Construct a CAS client object.
     *
     * @param SimpleCAS_Protocol $protocol Protocol to use for authentication.
     */
    private function __construct(SimpleCAS_Protocol $protocol)
    {
        $this->protocol = $protocol;
        
        if ($this->protocol instanceof SimpleCAS_SingleSignOut
            && isset($_POST)) {
            if ($ticket = $this->protocol->validateLogoutRequest($_POST)) {
                $this->logout($ticket);
            }
        }
        
        if (session_id() == '') {
            session_start();
        }
        
        if (isset($_SESSION['__SIMPLECAS_TICKET'])) {
            $this->_authenticated = true;
        }
        
        
        if ($this->_authenticated == false
            && isset($_GET['ticket'])) {
            $this->validateTicket($_GET['ticket']);
        }
    }
    
    /**
     * Checks a ticket to see if it is valid.
     * 
     * If the CAS server verifies the ticket, a session is created and the user
     * is marked as authenticated.
     *
     * @param string $ticket Ticket from the CAS Server
     * 
     * @return bool
     */
    protected function validateTicket($ticket)
    {
        if ($uid = $this->protocol->validateTicket($ticket, self::getURL())) {
            $this->setAuthenticated($uid);
            $this->redirect(self::getURL());
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Marks the current session as authenticated.
     *
     * @param string $uid User name returned by the CAS server.
     * 
     * @return void
     */
    protected function setAuthenticated($uid)
    {
        $_SESSION['__SIMPLECAS_TICKET'] = true;
        $_SESSION['__SIMPLECAS_UID']    = $uid;
        $this->_authenticated           = true;
    }
    
    /**
     * Return the authenticated user's login name.
     *
     * @return string
     */
    public function getUsername()
    {
        return $_SESSION['__SIMPLECAS_UID'];
    }
    
    /**
     * Singleton interface, returns CAS object.
     * 
     * @param CAS_Server $server CAS Server object
     * 
     * @return CAS
     */
    static public function client(SimpleCAS_Protocol $protocol)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($protocol);
        }
        
        return self::$_instance;
    }
    
    /**
     * If client is not authenticated, this will redirecting to login and exit.
     * 
     * Otherwise, return the CAS object.
     *
     * @return CAS
     */
    function forceAuthentication()
    {
        if (!$this->isAuthenticated()) {
            self::redirect($this->protocol->getLoginURL(self::getURL()));
        }
        return $this;
    }
    
    /**
     * Check if this user has been authenticated or not.
     * 
     * @return bool
     */
    function isAuthenticated()
    {
        return $this->_authenticated;
    }
    
    /**
     * Destroys session data for this client, redirects to the server logout
     * url.
     * 
     * @param string $url URL to provide the client on logout.
     * 
     * @return void
     */
    public function logout($url = '')
    {
        session_destroy();
        if (empty($url)) {
            $url = self::getURL();
        }
        $this->redirect($this->protocol->getLogoutURL($url));
    }
    
    /**
     * Returns the current URL without CAS affecting parameters.
     * 
     * @return string url
     */
    static public function getURL()
    {
        if (!empty(self::$url)) {
            return self::$url;
        }
        if (isset($_SERVER['HTTPS'])
            && !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] == 'on') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
    
        $url = $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        
        $replacements = array('/\?logout/'        => '',
                              '/&ticket=[^&]*/'   => '',
                              '/\?ticket=[^&;]*/' => '?',
                              '/\?%26/'           => '?',
                              '/\?&/'             => '?',
                              '/\?$/'             => '');
        
        $url = preg_replace(array_keys($replacements),
                            array_values($replacements), $url);
        
        return $url;
    }
    
   /**
    * Set an alternative return URL
    * 
    * @param string $url alternative return URL
    * 
    * @return void
    */ 
    public static function setURL($url)
    {
        self::$url = $url; 
    }
    
    /**
     * Send a header to redirect the client to another URL.
     *
     * @param string $url URL to redirect the client to.
     * 
     * @return void
     */
    public static function redirect($url)
    {
        header("Location: $url");
        exit();
    }
    
    /**
     * Get the version of the CAS library
     *
     * @return string
     */
    static public function getVersion()
    {
        return self::VERSION;
    }
}
