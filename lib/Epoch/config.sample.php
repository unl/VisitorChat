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
    .dirname(__FILE__) . '/src'.PATH_SEPARATOR //path to Epoch's src dir.
);

//Session life in seconds.
ini_set("session.gc_maxlifetime", 7200); 

ini_set('display_errors', true);

error_reporting(E_ALL);

\Epoch\Controller::$cacheRoutes = false;

//Change this to the full base url of this instance.
\Epoch\Controller::$url = 'http://localhost/Epoch/www/';

\Epoch\Controller::$applicationDir = dirname(__FILE__);

//Change this if you want to use a custom base namespace.
\Epoch\Controller::$customNamespace = "App";

\App\Controller::setDbSettings(array(
    'host'     => 'localhost',
    'user'     => 'app',
    'password' => 'app',
    'dbname'   => 'app'
));