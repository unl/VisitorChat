<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \UNL\VisitorChat\Controller($_GET);
$app->run();
echo $app->render();
