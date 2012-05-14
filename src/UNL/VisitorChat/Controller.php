<?php
namespace UNL\VisitorChat;

class Controller extends \Epoch\Controller
{
    //The refresh rate of the chat in miliseconds.
    public static $refreshRate = 1000;
    
    //The timeout of chat requests in miliseconds.
    public static $chatRequestTimeout = 10000;
    
    public static $sessionKey = "key";
    
    //Should the sysem compress and cache JS output?
    public static $cacheJS = true;
    
    /**
     * An array of possible roles.
     * Note tha the lower the index, the less rights will be given to that user.
     * 'other' will have no rights.
     * 
     * @var array
     */
    public static $roles = array('other', 'operator', 'operator1', 'operator2', 'manager');
    
    /**
     * an array of default operators.  Must be the UIDs
     * of the operators.  When no operators for a site
     * are found, these operators will be used.
     */
    public static $fallbackURLs = array();
    
    public static $URLService = false;
    
    /**
     * The driver for the registry service.  If false, the system will load
     * the default WDN registry driver.
     */
    public static $registryService = false;
    
    public static $mailService = false;
    
    public static $environment = "PRODUCTION";
    
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
        $assignmentService = new \UNL\VisitorChat\Assignment\Service();
        $assignmentService->rejectAllExpiredRequests();
        
        //Create a URL service.
        self::$URLService = new \UNL\VisitorChat\URL\Service($options);
        
        if (!self::$registryService) {
            self::$registryService = new \UNL\VisitorChat\OperatorRegistry\WDN\Driver();
        }
        
        if (!self::$mailService) {
            self::$mailService = new \UNL\VisitorChat\Mail\Driver();
        }
        
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
        
        if (empty(\UNL\VisitorChat\User\Service::getCurrentUser()->uid)) {
            throw new \Exception("You do not have permission to view this", 403);
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
        $headers = array();
        //Get the headers.
        if (function_exists("getallheaders")) {
            $headers = getallheaders();
        }
        
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
        if (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
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
        //has it already been started?
        if (session_id() !== "") {
            return true;
        }
        
        
        /**
         * IE8+ does not allow for cookies to be passed with its XDomainRequest.
         * So use a work around instead.
         */
        if (isset($_GET['PHPSESSID']) && $_GET['PHPSESSID'] != "false") {
            session_id($_GET['PHPSESSID']);
        }
        
        session_start();
        
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = "unknown";
        }
        
        //Do we have a key in the session? (session hijacking prevention)
        if (!isset($_SESSION['key'])) {
            $_SESSION['key'] = md5($_SERVER['HTTP_USER_AGENT']);
        }
        
        //Check the key (session hijacking prevention)
        if ($_SESSION['key'] != md5($_SERVER['HTTP_USER_AGENT'])) {
            session_write_close();
            session_start();
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
         //are they already logged in?
         if ($this->options['model'] =='UNL\VisitorChat\Conversation\View' && !isset($_SESSION['id'])) {
            //redirect to client login
            $this->options['model'] = '\UNL\VisitorChat\User\ClientLogin';
         }
         
         if ($this->options['model'] =='UNL\VisitorChat\User\ClientLogin' && isset($_SESSION['id'])) {
            //redirect to conversation view
            $this->options['model'] = '\UNL\VisitorChat\Conversation\View';
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
    
    public static function redirect($url, $exit = true)
    {
        if (self::$environment == "PHPT") {
            echo "Location: " . $url  . PHP_EOL;
            return true;
        }
        
        parent::redirect($url, $exit);
    }
}