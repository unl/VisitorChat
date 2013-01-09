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

    public static $conversationTTL = 30;  //minutes
    
    /**
     * An array of possible roles.
     * Note tha the lower the index, the less rights will be given to that user.
     * 'other' will have no rights.
     * 
     * @var array
     */
    public static $roles = array('other', 'operator', 'operator1', 'operator2', 'manager');
    
    /**
     * uids of admins for the chat system.
     * @var array
     */
    public static $admins = array();
    
    public static $allowedDomains = array();
    
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
    
    public static $pagetitle = "UNLchat";
    
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
        $assignmentService->expirePendingRequests();
        
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

    /**
     * @static
     * @param $url A full URL including protocol and trailing /
     * 
     * This function will set the url for the server.  If the request protocol is https,
     * this function will attempt to convert the url to https.
     */
    public static function setURL($url)
    {
        //If we are requesting via https, change to https.
        if ((isset($_SERVER["SERVER_PROTOCOL"]) && strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5)) == 'https')
            //OR check to see if we are using HTTPS by checking Apache Env Variables
            || (isset($_SERVER['USING_HTTPS']) && $_SERVER['USING_HTTPS'] == 1)) {
            $url = str_replace('http://', 'https://', $url);
        }
        
        self::$url = $url;
    }

    /**
     * Requires a client login.
     *
     * @static
     */
    public static function requireClientLogin()
    {
        if (!isset($_SESSION['id'])) {
            self::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("clientLogin", true, true));
        }
    }

    /**
     * Requires a basic login (either client or operator).
     *
     * Used for views that are accessible by both clients and operators.
     *
     * For now it will have an operator log in as clients can not log back in after they log out.
     *
     * @static
     */
    public static function requireLogin()
    {
        if (!isset($_SESSION['id'])) {
            $url = "";
            if (isset($_SERVER['REQUEST_URI'])) {
                $url = $_SERVER['REQUEST_URI'];
            }
            
            self::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("operatorLogin?redirect=" . $url, true, true));
        }
    }

    /**
     * requires an operator login.
     *
     * @static
     * @throws \Exception
     */
    public static function requireOperatorLogin()
    {
        if (!isset($_SESSION['id'])) {
            $url = "";
            if (isset($_SERVER['REQUEST_URI'])) {
                $url = $_SERVER['REQUEST_URI'];
            }
            
            self::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("operatorLogin?redirect=" . $url, true, true));
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
         //Don't try to run if we are running in cli.
         if (self::$environment == "CLI") {
             return false;
         }
         
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
        if (self::$environment == "PHPT" || self::$environment == "CLI") {
            echo "Location: " . $url  . PHP_EOL;
            return true;
        }
        
        parent::redirect($url, $exit);
    }

    /**
     * Render the actionable items for this controller via savvy.
     *
     * @return string the rendered output.
     */
    function render()
    {
        //Assets will always have the format of asset (to remove the wrapper)
        if ($this->actionable instanceof \UNL\VisitorChat\Asset\View) {
            $this->options['format'] = 'asset';
        } else {
            // Always escape output, use $context->getRaw('var'); to get the raw data.
            self::$templater->setEscape('htmlentities');
        }

        if ($this->options['format'] != 'html') {
            self::$templater->addTemplatePath(self::$applicationDir . '/www/templates/' . $this->options['format']);
            self::$templater->addTemplatePath(dirname(dirname(dirname(__FILE__))).'/www/templates/Epoch/formats/' . $this->options['format']);
            switch($this->options['format']) {
                case 'json':
                    header('Content-type:application/json;charset=UTF-8');
                    break;
                case 'asset':
                    //Do not send a content-type.  That will be done by the asset.
                    break;
                default:
                    header('Content-type:text/html;charset=UTF-8');
            }
        }

        return self::$templater->render($this);
    }
}