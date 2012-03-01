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
          . dirname(dirname(__FILE__)) . '/src' . PATH_SEPARATOR
          . dirname(__FILE__) . '/src' . PATH_SEPARATOR
);

ini_set('display_errors', true);

error_reporting(E_ALL);

RegExpRouter\Router::$cacheRoutes = false;

Example\Controller::$url = 'http://localhost/application/vendor/RegExpRouter/Example/';