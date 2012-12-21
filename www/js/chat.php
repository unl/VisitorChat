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

function sendCacheHeaders($filename)
{
    //get the last modified date of the cache file
    $lastModified = filemtime($filename);
    
    //get a unique hash of this file (etag)
    $etagFile = md5_file($filename);
    
    //check if HTTP_IF_MODIFIED_SINCE is set
    $ifModifiedSince = false;
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
    }
    
    //get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
    $etag = false;
    if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        $etag = trim($_SERVER['HTTP_IF_NONE_MATCH']);
    }
    
    //set last-modified header
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified)." GMT");
    
    //set etag-header
    header("Etag: $etagFile");
    
    //make sure caching is turned on
    header('Cache-Control: public');
    
    //check if page has changed. If not, send 304 and exit
    if (@strtotime($ifModifiedSince)==$lastModified || $etag == $etagFile) {
        header("HTTP/1.1 304 Not Modified");
        exit();
    }
}

//Make sure the browser knows it is javascript.
header('Content-Type: application/javascript');

$for = 'client';
if (isset($_GET['for'])) {
    $for = $_GET['for'];
}

$filename = \UNL\VisitorChat\CacheableURL::$tmpDir . "unl_visitorchat_js_" . md5(\UNL\VisitorChat\Controller::$url . $for);

if (\UNL\VisitorChat\Controller::$cacheJS && file_exists($filename)) {
    sendCacheHeaders($filename);
    echo file_get_contents($filename);
    exit();
}

ob_start();
?>
if (VisitorChat == undefined) {
    var VisitorChat = false;
}
<?php

//Include the required things:
require_once(dirname(__FILE__) . "/SimpleJavaScriptInheritance.js");
require_once(dirname(__FILE__) . "/form.js");
require_once(dirname(__FILE__) . "/VisitorChat/ChatBase.js");

switch($for) {
    case 'operator':
        require_once(dirname(__FILE__) . "/chosen.min.js");
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
        require_once(dirname(__FILE__) . "/VisitorChat/Remote.js");

        ?>
        WDN.jQuery(function(){
            WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
                if (VisitorChat == false) {
                    VisitorChat = new VisitorChat_Chat("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
                }
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