<?php 
/**
 * First thigns first.  This script needs to be configurable (url to server, etc.)
 * To do this, we will use PHP to render the javascript.
 */
if (file_exists(dirname(dirname(dirname(__FILE__))) . '/config.inc.php')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/config.inc.php';
} else {
    require dirname(dirname(dirname(__FILE__))) . '/config.sample.php';
}

//Make sure the browser knows it is javascript.
header('Content-Type: application/javascript');

?>
var $ = WDN.jQuery;
var VisitorChat = false;
<?php

$for = 'client';
if (isset($_GET['for'])) {
    $for = $_GET['for'];
}

//Include the required things:
require_once(dirname(__FILE__) . "/SimpleJavaScriptInheritance.js");
require_once(dirname(__FILE__) . "/form.js");
require_once(dirname(__FILE__) . "/VisitorChat/ChatBase.js");

switch($for) {
    case 'operator':
        require_once(dirname(__FILE__) . "/VisitorChat/Operator.js");
        break;
    case 'client':
    default:
        require_once(dirname(__FILE__) . "/jquery.cookies.min.js");
        require_once(dirname(__FILE__) . "/jquery.watermark.min.js");
        require_once(dirname(__FILE__) . "/VisitorChat/Remote.js");
}
?>