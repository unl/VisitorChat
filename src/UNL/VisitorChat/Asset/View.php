<?php
namespace UNL\VisitorChat\Asset;

class View 
{
    public $data     = ""; //Data (asset) to be sent.
    
    public $options  = array();
    
    public $version  = "3.1";
    
    public $type     = false;  //'js' OR 'css'
    
    public $for      = 'client';  //'operator' OR 'client'
    
    public $protocol = 'http';
    
    public $allowed_versions = array('3.1', '4.0');
    
    //Set to true to start caching.
    public static $cache = false;
    
    function __construct($options = array())
    {
        $this->options = $options;
        
        if (isset($this->options['v'])) {
            $this->version = $this->options['v'];
        }
        
        //Handle legacy urls.
        if (strpos($_SERVER['REQUEST_URI'], 'js/chat.php') !== false) {
            $this->options['type'] = "js";
        }

        if (strpos($_SERVER['REQUEST_URI'], 'css/remote.php') !== false) {
            $this->options['type'] = "css";
        }
        
        if (!isset($this->options['type'])) {
            throw new \Exception("Unknown Type");
        }

        if (!isset($this->options['for'])) {
            $this->options['for'] = "client";
        }
        
        $parts = parse_url(\UNL\VisitorChat\Controller::$url);
        
        if (isset($parts['scheme'])) {
            $this->protocol = $parts['scheme'];
        }
        
        $this->type = $this->options['type'];

        $this->for  = $this->options['for'];
        
        $this->data = $this->getData();
        
        $this->sendContentTypeHeaders();
    }
    
    function getCacheFileName()
    {
        return \UNL\VisitorChat\CacheableURL::$tmpDir . "unl_visitorchat_asset_" . $this->type . "_" . $this->for . "_" . $this->protocol . "_" . $this->version;
    }

    function sendContentTypeHeaders()
    {
        switch ($this->type) {
            case 'js':
                header('Content-Type: application/javascript');
                break;
            case 'css':
                header('Content-Type: text/css');
                break;
        }
    }

    function sendCacheHeaders()
    {
        $filename = $this->getCacheFileName();
        
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

        header('Expires:' . gmdate("D, d M Y H:i:s", strtotime('+2 week')) . " GMT");
        
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
    
    function getData()
    {
        if (self::$cache && file_exists($this->getCacheFileName())) {
            $this->sendCacheHeaders();
            
            return file_get_contents($this->getCacheFileName());
        }

        ob_start();

        switch ($this->type) {
            case 'js':
                ?>
                if (VisitorChat == undefined) {
                    var VisitorChat = false;
                }
                <?php
                
                //Include the required things for all versions and types:
                require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/SimpleJavaScriptInheritance.js");
                require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/form.js");
                require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/VisitorChat/" . $this->version . "/ChatBase.js");
                
                switch($this->for) {
                    case 'operator':
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/chosen.min.js");
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/VisitorChat/" . $this->version . "/Operator.js");
                        ?>
                        //start the chat
                        WDN.jQuery(function(){
                        VisitorChat = new VisitorChat_Operator("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>, <?php echo \UNL\VisitorChat\Controller::$chatRequestTimeout; ?>);
                        VisitorChat.start();
                        });
                        <?php
                        break;
                    case 'client':
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js/jquery.cookies.min.js");
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js/VisitorChat/" . $this->version . "/Client.js");
                        ?>
                        WDN.jQuery(function(){
                        WDN.loadJS('/wdn/templates_3.1/scripts/plugins/validator/jquery.validator.js', function() {
                        if (VisitorChat == false) {
                        VisitorChat = new VisitorChat_Client("<?php echo \UNL\VisitorChat\Controller::$url;?>", <?php echo \UNL\VisitorChat\Controller::$refreshRate;?>);
                        }
                        });
                        });
                        <?php
                        break;
                    }
                break;
            case 'css':
                switch ($this->for) {
                    case 'client':
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/VisitorChat/" . $this->version . "/client.css");
                        break;
                    case 'operator':
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/share.css");
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/chosen.css");
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/css/VisitorChat/" . $this->version . "/operator.css");
                        break;
                }
                break;
        }
        
        $data = ob_get_contents();
        ob_end_clean();

        //Cache if we have to.
        if (self::$cache) {
            switch ($this->type) {
                case 'js':
                    $data = \JSMin::minify($data);
                    break;
                case 'css':
                    $data = \Minify_CSS::minify($data);
                    break;
            }
            
            file_put_contents($this->getCacheFileName(), $data);
        }
        
        return $data;
    }
}