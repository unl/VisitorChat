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

$for = 'client';
if (isset($_GET['for'])) {
    $for = $_GET['for'];
}

$filename = sys_get_temp_dir() . "/VisitorChatJS_" . md5(\UNL\VisitorChat\Controller::$url . $for) . ".php";
if (\UNL\VisitorChat\Controller::$cacheJS && file_exists($filename)) {
    echo file_get_contents($filename);
    exit();
}

ob_start();
?>
var VisitorChat = false;
<?php

//Include the required things:
require_once(dirname(__FILE__) . "/SimpleJavaScriptInheritance.js");
require_once(dirname(__FILE__) . "/form.js");
require_once(dirname(__FILE__) . "/VisitorChat/ChatBase.js");

switch($for) {
    case 'operator':
        require_once(dirname(__FILE__) . "/VisitorChat/Operator.js");
        ?>
        //start the chat
        WDN.jQuery(function(){
            VisitorChat = new VisitorChat_Chat("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>, <?php echo \UNL\VisitorChat\Controller::$chatRequestTimeout; ?>);
            VisitorChat.start();
        });
        <?php
        break;
    case 'client':
    default:
        require_once(dirname(__FILE__) . "/jquery.cookies.min.js");
        require_once(dirname(__FILE__) . "/jquery.watermark.min.js");
        require_once(dirname(__FILE__) . "/jquery.backgroundPosition.js");
        require_once(dirname(__FILE__) . "/VisitorChat/Remote.js");
        ?>
        //Start the chat
        WDN.jQuery(function(){
            WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
                VisitorChat = new VisitorChat_Chat("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
            });
        });
        <?php
}

$js = ob_get_contents();
ob_clean();

if (\UNL\VisitorChat\Controller::$cacheJS) {
    require_once('jsmin.php');

    $js = JSMin::minify($js);
    file_put_contents($filename, $js);
}

echo $js;
?>