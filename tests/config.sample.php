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
    .dirname(dirname(__FILE__)) . '/lib/Epoch/src'.PATH_SEPARATOR //path to Epoch's src dir.
    .dirname(dirname(__FILE__)) . '/lib'.PATH_SEPARATOR
    .dirname(dirname(__FILE__)) . '/src'.PATH_SEPARATOR
);

require_once dirname(__FILE__) . "/OperatorRegistry/MockRegistryDriver.php";
require_once dirname(__FILE__) . "/Mail/MockMailDriver.php";
require_once dirname(__FILE__) . "/Application/DBHelper.php";

//Set the Registry
\UNL\VisitorChat\Controller::$registryService = new MockRegistryDriver();

//Set the mailer
\UNL\VisitorChat\Controller::$mailService = new MockMailDriver();

$DBHelper = new DBHelper();

\UNL\VisitorChat\Controller::$environment = "PHPT";

ini_set('display_errors', true);

error_reporting(E_ALL);

\Epoch\Controller::$cacheRoutes = false;

//Change this to the full base url of this instance.
\Epoch\Controller::$url = 'http://visitorchattest.com/';

//Refresh rate of the chat in miliseconds.
\UNL\VisitorChat\Controller::$refreshRate = 1000;

//Set the fallback URLs (conversations will be routed to these operatoes if no one is available).
\UNL\VisitorChat\Controller::$fallbackURLs = new \ArrayIterator(array('http://visitorchattest.com/'));

//Set session key to prevent man in the middle attacks.
\UNL\VisitorChat\Controller::$sessionKey = "lol";

\UNL\VisitorChat\Controller::$cacheJS = false;

\Epoch\Controller::setDbSettings(array(
    'host'     => '127.0.0.1',
    'user'     => 'root',
    'password' => '',
    'dbname'   => 'visitorchattest'
));