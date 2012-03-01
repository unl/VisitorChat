--TEST--
route() test, runs tests for route().  Tests static routs being set with setRoutes();
--FILE--
<?php
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/Example/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/Example/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/Example/config.sample.php';
}

$router = new RegExpRouter\Router(array('baseURL' => Example\Controller::$url));

$routes = array('/^home\/((?<id>[\d]+)\/)?edit$/i' => 'Example\Home\Edit');

$router->setRoutes($routes);

$uri = parse_url(Example\Controller::$url . "home/2/edit", PHP_URL_PATH);

$example = new Example\Controller($router->route($uri, array()));
?>
--EXPECT--
I am in Example\Home\Edit and my ID is: 2