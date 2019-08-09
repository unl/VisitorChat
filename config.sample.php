<?php
/**********************************************************************************************************************
 * autoload and include path
 */
function autoload($class)
{
    $file = str_replace(array('_', '\\'), '/', $class).'.php';
    if ($fullpath = stream_resolve_include_path($file)) {
        include $fullpath;
        return true;
    }
    return false;
}

spl_autoload_register("autoload");

set_include_path(
    implode(PATH_SEPARATOR, array(get_include_path())).PATH_SEPARATOR
    .dirname(__FILE__) . '/lib/Epoch/src'.PATH_SEPARATOR //path to Epoch's src dir.
    .dirname(__FILE__) . '/lib'.PATH_SEPARATOR
    .dirname(__FILE__) . '/src'.PATH_SEPARATOR
);

require_once __DIR__ . '/vendor/autoload.php';

/**********************************************************************************************************************
 * php related settings
 */
//Session life in seconds.
ini_set("session.gc_maxlifetime", 7200); 

ini_set('display_errors', false);

error_reporting(E_ALL);

/**********************************************************************************************************************
 * General settings
 */

/* Change this to the full base url of this instance.
 * 
 * @param string - the full url to this instance.
 */
\UNL\VisitorChat\Controller::setURL('http://www.mysite.edu/'); //Training slash is required.

/* Change this to a directory where you want temporary files to be stored.
 * 
 * @var string - absolute path to the temporary directory
 */
\UNL\VisitorChat\CacheableURL::$tmpDir = dirname(__FILE__) . "/tmp/";  //Must be writable

/* Configure the allowed domains
 * 
 * @var array - an array of domains where chats are allowed to be started.
 */
\UNL\VisitorChat\Controller::$allowedDomains = array('mysite.edu', 'mysite2.org');

/* Configure the allowed chatbot domains
 *
 * @var array - an array of domains where chatbots are allowed to be started.
 */
\UNL\VisitorChat\Controller::$allowedChatbotDomains = array('mysite.edu');

/* Current environment of the chat service
 * PRODUCTION - Live production.
 * PHPT       - Unit Testing environment
 * DEV        - Develop environment
 * @var string - the current environment (PRODUCTION|PHPT|DEV)
 */
\UNL\VisitorChat\Controller::$environment = "PRODUCTION";

/* The refresh rate (operator and client)
 * This describes the rate at with the js clients refresh in milliseconds.
 * 
 * @var int - the number of milliseconds to refresh
 */
\UNL\VisitorChat\Controller::$refreshRate = 2000;  //(every 2 seconds)

//TTL for pending chat requests
\UNL\VisitorChat\Controller::$chatRequestTimeout = 10000;;  //(10 seconds)

/* Set fallback URLs
 * Conversations that fail to be answered will fall back to these sites.
 * 
 * @var \ArrayIterator - an \ArrayIterator of sites.
 */
\UNL\VisitorChat\Controller::$fallbackURLs = new \ArrayIterator(array('http://ucommfairchild.unl.edu'));

//Set session key to prevent man in the middle attacks.
/* Session key
 * Helps to prevent man in the middle attacks.  It is important that you replace this value.
 * 
 * @var string
 */
\UNL\VisitorChat\Controller::$sessionKey = "replace me"; //!REPLACE ME!

/* Caches routes (does not auto refresh cache)
 * 
 * @var bool
 */
\UNL\VisitorChat\Controller::$cacheRoutes = false;

/* To cache and minimize or not to cache and minimize javascript and css output.  That is the question.
 * 
 * @var bool
 */
\UNL\VisitorChat\Asset\View::$cache = false;

/* A list of admins (via their UIDs).  Admins can view all past conversations for all sites.
 * 
 * @var array - an array of admins where the value is the uid of a user.
 */
\UNL\VisitorChat\Controller::$admins = array('s-mfairch4');

/**********************************************************************************************************************
 * Email settings
 */
//List of email address to fall back to if no emails can be found for a url.
\UNL\VisitorChat\Conversation\Email::$fallbackEmails   = array();
//Default from address
\UNL\VisitorChat\Conversation\Email::$default_from     = 'unlwdn@gmail.com';
//Default reply_to address
\UNL\VisitorChat\Conversation\Email::$default_reply_to = 'unlwdn@gmail.com';
//Default subject
\UNL\VisitorChat\Conversation\Email::$default_subject  = 'UNL Visitor Chat System';

/**********************************************************************************************************************
 * Operator Registry settings (Required)
 * 
 * If routing of chats is going to work, the system needs to know who is assigned to what site... thus we need 
 * a registry service.
 * 
 * We have our own registry service here at UNL.  You are welcome to use your own or make one.
 * If you want to make your own, use the MockRegistryDriver class as a reference.
 */
//Uncomment to set the registry service to something other than the default.
#\UNL\VisitorChat\Controller::$registryService = new MockRegistryDriver();

//WDN Registry service url.
\UNL\VisitorChat\OperatorRegistry\WDN\Driver::$baseURI = "http://www1.unl.edu/wdn/registry/";

//WDN Registry driver cache timeout.
\UNL\VisitorChat\OperatorRegistry\WDN\Driver::$cacheTimeout = 18000;  //seconds (5 hours)

/**********************************************************************************************************************
 * Mail Service
 */
//Uncomment to set the mail service to something other than the default.
#\UNL\VisitorChat\Controller::$mailService = new MockMailDriver();

/**********************************************************************************************************************
 * DB related settings
 */
\Epoch\Controller::setDbSettings(array(
    'host'     => 'localhost',
    'user'     => 'visitorchatapp',
    'password' => 'visitorchatapp',
    'dbname'   => 'visitorchatapp'
));