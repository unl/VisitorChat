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
\Epoch\Controller::$url = 'http://ucommfairchild.unl.edu/visitorchat/www/';

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

/**********************************************************************************************************************
 * Operator Registry settings
 */
//Uncomment to set the registry service to something other than the default.
#\UNL\VisitorChat\Controller::$mailService = new MockRegistryDriver();;

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