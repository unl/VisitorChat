--TEST--
route() test, runs tests for route().  Note, the example app must me correctly set up.
--FILE--
<?php
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/Example/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/Example/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/Example/config.sample.php';
}

$router = new RegExpRouter\Router(array('baseURL' => Example\Controller::$url, 'srcDir' => dirname(dirname(dirname(__FILE__))) . "/Example/src/Example/"));

$uri = parse_url(Example\Controller::$url . "home/1/edit", PHP_URL_PATH);
$example = new Example\Controller($router->route($uri, array()));
?>
--EXPECT--
I am in Example\Home\Edit and my ID is: 1