<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

if (!UNL\VisitorChat\Controller::$registryService) {
    UNL\VisitorChat\Controller::$registryService = new \UNL\VisitorChat\OperatorRegistry\WDN\Driver();
}

$site = false;
if (!isset($_SERVER['argv'], $_SERVER['argv'][1])) {
    echo "This tool will delete a cache file for a given registry query" . PHP_EOL;
    echo "Usage: php clearWDNCache.php query" . PHP_EOL;
    exit(1);
}

$toDelete = $_SERVER['argv'][1];

$url = UNL\VisitorChat\Controller::$registryService->getQueryURL($_SERVER['argv'][1]);
$file = UNL\VisitorChat\Controller::$registryService->getCachePath($url);


echo $url . PHP_EOL;
echo md5($file) . PHP_EOL;

if (file_exists($file) && unlink($file)) {
    echo "Deleted" . PHP_EOL;
} else {
    echo "Unable to delete" . PHP_EOL;
}