<?php
if (file_exists(dirname(__FILE__) . '/config.inc.php')) {
    require_once dirname(__FILE__) . '/config.inc.php';
} else {
    require dirname(__FILE__) . '/config.sample.php';
}

if (isset($_GET['model'])) {
    unset($_GET['model']);
}

$router = new RegExpRouter\Router(array('baseURL' => Example\Controller::$url, 'srcDir' => dirname(__FILE__) . "/src/Example/"));

$example = new Example\Controller($router->route($_SERVER['REQUEST_URI'], $_GET));