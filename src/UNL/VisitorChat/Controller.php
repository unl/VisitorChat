<?php
namespace UNL\VisitorChat;

class Controller extends \Epoch\Controller
{
    //The refresh rate of the chat in miliseconds.
    public static $refreshRate = 1000;
    
    //The timeout of chat requests in miliseconds.
    public static $chatRequestTimeout = 10000;
    
    public static $sessionKey = "key";

    public static $conversationTTL = 30;  //minutes
    
    public static $headerHTML = ""; //Header html to inject.
    
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

    public static $allowedChatbotDomains = array();
    
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
    
    public static $pagetitle = "Chat";
    
    public static $badWords = array();
    
    public static $title9Words = array();
    
    public static $title9Emails = array();

    /**
     * Array of baseurls on which operator chat transcripts should be sent to
     * 
     * @var array
     */
    public static $sendOperatorTranscriptEmails = array();

    /**
     * Block a conversation if it had equal to or more than this number of 'bad' words.
     * 
     * @var int
     */
    public static $badWordsBlockCount = 3;
    
    function __construct($options = array())
    {
        //Set the application dir for Epoch.
        self::$applicationDir = dirname(dirname(dirname(dirname(__FILE__))));
        
        self::$customNamespace = "UNL\\VisitorChat";

        parent::__construct($options);
        
        //1. Send CORS.
        $this->sendCORS();
        
        //2. Start the session.
        $this->startSession();
        
        //3. Make sure we get the post data.
        $this->retrievePostData();
        
        //reject all old requests.
        try {
            $assignmentService = new \UNL\VisitorChat\Assignment\Service();
            $assignmentService->expirePendingRequests();
        } catch (Exception $e) {
            // Do nothing just ignore any exceptions
            //TODO: Add application logging and log at appropriate level (ERROR most likely)
        }
        
        //Create a URL service.
        self::$URLService = new \UNL\VisitorChat\URL\Service($options);
        
        if (!self::$registryService) {
            self::$registryService = new \UNL\VisitorChat\OperatorRegistry\WDN\Driver();
        }
        
        if (!self::$mailService) {
            self::$mailService = new \UNL\VisitorChat\Mail\Driver();
        }
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
        if (self::$environment == "CLI") {
            //Assume a client permission is always granted in CLI
            return true;
        }
        
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
            
            self::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("operatorLogin?redirect=" . $url, true, false));
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
        } else if (isset($_SERVER['HTTP_ORIGIN'])) {
            $origin = $_SERVER['HTTP_ORIGIN'];
        }
        
        // Specify domains from which requests are allowed (in this case, the same one that requested).
        header('Access-Control-Allow-Origin: ' . $origin);
        
        //Allow cookies to be passed.
        header('Access-Control-Allow-Credentials: true');
        
        // Specify which request methods are allowed
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        
        // Additional headers which may be sent along with the CORS request
        // The X-Requested-With header allows jQuery requests to go through
        if (isset($headers['Access-Control-Request-Headers'])) {
            //Safari was sending some new headers which was causing things to break
            header("Access-Control-Allow-Headers: " . $headers['Access-Control-Request-Headers']);
        } else {
            //Fall back to the old way we were doing things
            header("Access-Control-Allow-Headers: 'X-Requested-With'");
        }
        
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
        //Set the session cookie name.
        session_name("UNL_Visitorchat_Session"); 
        
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

        if (!isset($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = "unknown";
        }
        
        //Do we have a key in the session? (session hijacking prevention)
        if (!isset($_SESSION['key'])) {
            $_SESSION['key'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        }
        
        //Check the key (session hijacking prevention)
        if ($_SESSION['key'] != md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'])) {
            session_write_close();
            session_start();
        }
        
        $sessionModels = array(
            'UNL\VisitorChat\User\ClientLogin',
            'UNL\VisitorChat\User\OperatorLogin',
            'UNL\VisitorChat\User\Logout',
            'UNL\VisitorChat\Conversation\View'
        );
        
        //Close the session early so that it isn't locked... UNLESS we will be saving data to it.
        if (isset($this->options['model']) && !in_array($this->options['model'], $sessionModels)) {
            session_write_close();
        }
    }
    
    function run()
    {
        //Don't try to run if we are running in cli.
        if (self::$environment == "CLI") {
            return false;
        }

        try {
            if (!isset($this->options['model'])) {
                throw new \Exception('Page Not Found', 404);
            }
            
            //Handle Post
            if (!empty($_POST)) {
                $this->handlePost();
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

            //Handle GET
            if (!isset($this->options['model'])) {
                throw new \Exception('Un-registered view', 404);
            }

            $this->actionable = new $this->options['model']($this->options);
        } catch(\Exception $e) {
            if (isset($this->options['ajaxupload'])) {
                echo $e->getMessage();
                exit();
            }

            if (false == headers_sent()
                && $code = $e->getCode()) {
                header('HTTP/1.1 '.$code.' '.$e->getMessage());
                header('Status: '.$code.' '.$e->getMessage());
            }

            $this->actionable = $e;
        }
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
        if (self::$environment == "CLI") {
            //Don't echo in CLI, this could expose the url in things like emails
            return true;
        }
        
        if (self::$environment == "PHPT") {
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
            //Don't html5 encode (too many issues and it isn't necessary)
            self::$templater->setHTMLEscapeSettings(array('quotes'=>ENT_COMPAT));
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