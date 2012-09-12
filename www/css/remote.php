<?php 
/**
 * First things first.  This script needs to be configurable (url to server, etc.)
 * To do this, we will use PHP to render the javascript.
 */
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/config.sample.php';
}

//Make sure the browser knows it is javascript.
header('Content-Type: text/css');

require_once(dirname(__FILE__) . "/remote.css");
?>