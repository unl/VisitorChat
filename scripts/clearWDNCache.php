<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

$site = false;
if (!isset($_SERVER['argv'], $_SERVER['argv'][1])) {
    echo "This tool will delete all cache files for the closest site to the given url." . PHP_EOL;
    echo "Usage: php clearWDNCache.php http://baseurl.unl.edu/index.php?stuff" . PHP_EOL;
    exit(1);
}

$toDelete = $_SERVER['argv'][1];

$driver = new UNL\VisitorChat\OperatorRegistry\WDN\Driver();

if (!$result = $driver->query($toDelete)) {
    echo 'Unable to find the site in the registry.' . PHP_EOL;
    exit(1);
}

//Delete all cache files for the closest site.
$toDelete = $result->key();

echo "Deleting all cache files for: " . $toDelete . PHP_EOL;

$time_start = microtime(true);

$dir = new DirectoryIterator(\UNL\VisitorChat\CacheableURL::$tmpDir);

$deleted = 0;
foreach ($dir as $fileinfo) {
    if ($fileinfo->isDot()) {
        continue;
    }
    
    $name = $fileinfo->getFilename();
    
    if (strpos($name, 'unl_visitorchat_wdn_') === false) {
        continue;
    }
    
    $file = file_get_contents(\UNL\VisitorChat\CacheableURL::$tmpDir . $name);

    $file = unserialize($file);
    
    $urls = array_keys($file);
    
    //Delete the cache file if this site is listed as the primary responding site.
    if (isset($urls[0]) && $urls[0] == $toDelete) {
        if (unlink(\UNL\VisitorChat\CacheableURL::$tmpDir . $name)) {
            $deleted++;
        } else {
            echo "Unable to delete: " . UNL\VisitorChat\CacheableURL::$tmpDir . $name . PHP_EOL;
        }
        
        continue;
    }
}

echo "Deleted " . $deleted . " cache files." . PHP_EOL;

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "operation took $time seconds" . PHP_EOL;
