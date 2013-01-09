<?php
namespace UNL\VisitorChat\Asset;

class View 
{
    public $data    = "";
    
    public $options = array();
    
    public $version = "3.1";
    
    function __construct($options = array())
    {
        $this->options = $options;
        
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
        
        $this->type = $this->options['type'];

        $this->for  = $this->options['for'];
        
        $this->data = $this->getData();
    }
    
    function getCacheFileName()
    {
        return \UNL\VisitorChat\CacheableURL::$tmpDir . "unl_visitorchat_asset_" . $this->type . "_" . $this->for . "_" . $this->version;
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
        if (\UNL\VisitorChat\Controller::$cacheJS && file_exists($this->getCacheFileName())) {
            $this->sendCacheHeaders();
            
            return file_get_contents($this->getCacheFileName());
        }

        $path = \UNL\VisitorChat\Controller::$templater->getTemplatePath();

        \UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/asset/"));
        
        $data = \UNL\VisitorChat\Controller::$templater->render($this, 'UNL/VisitorChat/Asset/' . strtoupper($this->type) . '.tpl.php');
        
        \UNL\VisitorChat\Controller::$templater->setTemplatePath($path);

        if (\UNL\VisitorChat\Controller::$cacheJS) {
            require_once(\UNL\VisitorChat\Controller::$applicationDir . '/lib/JSMin.php');

            $js = \JSMin::minify($data);
            
            file_put_contents($this->getCacheFileName(), $data);
        }
        
        return $data;
    }
}