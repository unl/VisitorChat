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
//Change this to the full base url of this instance.
\UNL\VisitorChat\Controller::setURL('http://ucommfairchild.unl.edu/visitorchat/www/');

//Configure the allowed domains
\UNL\VisitorChat\Controller::$allowedDomains = array('unl.edu', 'throughtheeyes.org', 'quiltstudy.org', 'digital-community.com', 'huskeralum.org');

//Current environment of the chat service (PRODUCTION, PHPT, DEV).
\UNL\VisitorChat\Controller::$environment = "PRODUCTION";

//How often javascript clients should refresh (both operator and client)
\UNL\VisitorChat\Controller::$refreshRate = 2000;  //(every 2 seconds)

//TTL for pending chat requests
\UNL\VisitorChat\Controller::$chatRequestTimeout = 10000;;  //(10 seconds)

//Set the fallback URLs (conversations will be routed to these operatoes if no one is available).
\UNL\VisitorChat\Controller::$fallbackURLs = new \ArrayIterator(array('http://ucommfairchild.unl.edu'));

//Set session key to prevent man in the middle attacks.
\UNL\VisitorChat\Controller::$sessionKey = "totallyCoolKey";

//Cache routes (does not auto refresh cache).
\Epoch\Controller::$cacheRoutes = false;

//To cache and minimize or not to cache and minimize javascrip.  That is the question.
\UNL\VisitorChat\Controller::$cacheJS = false;

//A list of admins (UIDs). Admins are able to view all past conversations.
\UNL\VisitorChat\Controller::$admins = array('s-mfairch4');

\UNL\VisitorChat\CacheableURL::$tmpDir = dirname(__FILE__) . "/tmp/";
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
 * Operator Registry settings
 */
//Uncomment to set the registry service to something other than the default.
#\UNL\VisitorChat\Controller::$registryService = new MockRegistryDriver();;

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