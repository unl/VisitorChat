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
    
    public $allowed_versions = array('3.0', '3.1', '4.0', '4.1', '5.0', '5.1', '5.2');
    
    //Set to true to start caching.
    public static $cache = false;
    
    function __construct($options = array())
    {
        $this->options = $options;
        
        if (isset($this->options['v'])) {
            $this->version = $this->options['v'];
        }

        if (isset($this->options['version'])) {
            $this->version = $this->options['version'];
        }

        //parse the version. (handle the case that x.0 is sent as x
        if (!substr_count($this->version, '.')) {
            $this->version = $this->version . '.0';
        }
        
        if (!in_array($this->version, $this->allowed_versions)) {
            throw new \Exception('The specified version is not allowed.', 400);
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
        // Cache JS if set and cache exists
        if (self::$cache && $this->type == 'js' && file_exists($this->getCacheFileName())) {
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
                switch($this->for) {
                    case 'operator':
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js" . "/VisitorChat/" . $this->version . "/Operator.js.php");
                        break;
                    case 'client':
                        
                        require_once(\UNL\VisitorChat\Controller::$applicationDir . "/www/js/VisitorChat/" . $this->version . "/Client.js.php");
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

        //Cache JS if we have to.
        if (self::$cache && $this->type == 'js') {
            $data = \JSMin::minify($data);
            file_put_contents($this->getCacheFileName(), $data);
        }
        
        return $data;
    }
}
