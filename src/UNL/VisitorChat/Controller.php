<?php
namespace UNL\VisitorChat;

class Controller extends \Epoch\Controller
{
    //The refresh rate of the chat in miliseconds.
    public static $refreshRate = 1000;
    
    //The timeout of chat requests in miliseconds.
    public static $chatRequestTimeout = 10000;
    
    public static $sessionKey = "key";
    
    /**
     * an array of default operators.  Must be the UIDs
     * of the operators.  When no operators for a site
     * are found, these operators will be used.
     */
    public static $defaultOperators = array();
    
    public static $URLService = false;
    
    public static $registryService = "http://www1.unl.edu/wdn/registry/";
    
    function __construct($options = array())
    {
        //Set the application dir for Epoch.
        self::$applicationDir = dirname(dirname(dirname(dirname(__FILE__))));
        
        self::$customNamespace = "UNL\VisitorChat";
        
        //1. Send CORS.
        $this->sendCORS();
        
        //2. Start the session.
        $this->startSession();
        
        //3. Make sure we get the post data.
        $this->retrievePostData();
        
        //reject all old requests.
        \UNL\VisitorChat\Assignment\Service::rejectAllExpiredRequests();
        
        //Create a URL service.
        self::$URLService = new \UNL\VisitorChat\URL\Service($options);
        
        //4. Move along...
        parent::__construct($options);
    }
    
    public static function requireClientLogin()
    {
        if (!isset($_SESSION['id'])) {
            self::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("clientLogin", true, true));
        }
    }
    
    public static function requireOperatorLogin()
    {
        if (!isset($_SESSION['id'])) {
            self::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("operatorLogin", true, true));
        }
    }
    
    function retrievePostData()
    {
        /* If the post is empty is could be because of the IE8 XDomainRequest object,
         * which sends post data as plain text.  Check to see if it is actually there.
         */
        if (empty($_POST) && ($data = file_get_contents('php://input'))) {
            parse_str($data, $_POST);
        }
    }
    
    function sendCORS()
    {
        //Get the headers.
        $headers = getallheaders();
        
        //set the origin to allow all by default.
        $origin = "*";
        
        //Check if the Origin header was set. If it was, make that the new origin.
        if (isset($headers['Origin'])) {
            $origin = $headers['Origin'];
        }
        
        // Specify domains from which requests are allowed (in this case, the same one that requested).
        header('Access-Control-Allow-Origin: ' . $origin);
        
        //Allow cookies to be passed.
        header('Access-Control-Allow-Credentials: true');
        
        // Specify which request methods are allowed
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        
        // Additional headers which may be sent along with the CORS request
        // The X-Requested-With header allows jQuery requests to go through
        header("Access-Control-Allow-Headers: 'X-Requested-With'");
        
        // Exit early so the page isn't fully loaded for options requests
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
            exit();
        }
    }
    
    /**
     * startSession does just that.  We need special session handling because 
     * the session ID will be visible as a GET param, due to IE8+ not allowing 
     * for cookies to be passed cross domain.  After starting the session, 
     * do a security check.
     */
    function startSession()
    {
        /**
         * IE8+ does not allow for cookies to be passed with its XDomainRequest.
         * So use a work around instead.
         */
        if (isset($_GET['PHPSESSID']) && $_GET['PHPSESSID'] != "false") {
            session_id($_GET['PHPSESSID']);
        }
        
        session_start();
        
        //Do we have a key in the session? (session hijacking prevention)
        if (!isset($_SESSION['key'])) {
            $_SESSION['key'] = md5($_SERVER['HTTP_USER_AGENT']);
        }
        
        //Check the key (session hijacking prevention)
        if ($_SESSION['key'] != md5($_SERVER['HTTP_USER_AGENT'])) {
            session_write_close();
            session_start();
        }
        
        //Prevent session fixation attacks
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id();
            $_SESSION['initiated'] = true;
        }
    }
    
    function run()
    {
         if (!isset($this->options['model'])) {
             throw new \Exception('Un-registered view', 404);
         }
         /**
          * webkit cant follow cors redirects, so... if the user isn't logged in don't
          * redirect, instead change the current model.
          */
         if ($this->options['model'] =='VisitorChat\Conversation\View' && !$user = \UNL\VisitorChat\User\Record::getCurrentUser()) {
            //redirect to client login
            $this->options['model'] = '\UNL\VisitorChat\User\ClientLogin';
         }
         
         return parent::run();
    }
    
    /**
     * Handle data that is POST'ed to the controller.
     *
     * @return void
     */
    function handlePost()
    {
        if (!isset($_POST['_class']) && isset($this->options['model'])) {
            $_POST['_class'] = $this->options['model'];
        }
        
        if (!isset($_POST['_class'])) {
            return false;
        }
        
        $object = new $_POST['_class']($this->options);
        
        //the function handle post is /not/ required, so we shouldn't bet on it being there.
        if (method_exists($object, 'handlePost')) {
            $object->handlePost($_POST);
        }
    }
    
    public static function epochToDateTime($time = false)
    {
        if (!$time) {
            $time = time();
        }
        
        return date("Y-m-d H:i:s", $time);
    }
}