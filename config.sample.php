<?php
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

//Session life in seconds.
ini_set("session.gc_maxlifetime", 7200); 

ini_set('display_errors', true);

error_reporting(E_ALL);

\Epoch\Controller::$cacheRoutes = false;

//Change this to the full base url of this instance.
\Epoch\Controller::$url = 'http://ucommfairchild.unl.edu/visitorchat/www/';

\Epoch\Controller::$applicationDir = dirname(__FILE__);

//Change this if you want to use a custom base namespace.
\Epoch\Controller::$customNamespace = "UNL\VisitorChat";

//Refresh rate of the chat in miliseconds.
\UNL\VisitorChat\Controller::$refreshRate = 1000;

//Set the default operators.  (by UIDs)
\UNL\VisitorChat\Controller::$defaultOperators = array('s-mfairch4');

//Set session key to prevent man in the middle attacks.
\UNL\VisitorChat\Controller::$sessionKey = "totallyCoolKey";

\Epoch\Controller::setDbSettings(array(
    'host'     => 'localhost',
    'user'     => 'visitorchatapp',
    'password' => 'visitorchatapp',
    'dbname'   => 'visitorchatapp'
));