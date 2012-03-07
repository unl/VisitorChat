<?php 
namespace UNL\VisitorChat\URL;

class Service
{
    protected $options;
    
    protected $url;
    
    function __construct($options = array())
    {
        $this->options = $options;
        $this->url = \UNL\VisitorChat\Controller::$url;
    }
    
    function generateSiteURL($path, $appendFormat = false, $appendPhpsessid = false)
    {
        $url = $this->url . $path;
        
        if ($appendFormat) {
            $url = $this->appendFormat($url, $appendFormat);
        }
        
        if ($appendPhpsessid) {
            $url = $this->appendPhpsessid($url);
        }
        
        return $url;
    }
    
    private function generateURLWithURLData($data = array())
    {
        $url = "";
        
        if (!isset($data['host'])) {
            throw new \Exception("No host data found.", 500);
        }
        
        if (!isset($data['path'])) {
            $data['path'] = "";
        }
        
        if (!isset($data['scheme'])) {
            $data['scheme'] = "http";
        }
        
        return $data['scheme'] . "://" . $data['host'] . $data['path'];
    }
    
    function appendFormat($url, $format) {
        if ($format === true) {
            $format = $this->options['format'];
        }
        
        if (!$url = $this->addParams($url, array('format' => $format))) {
            return false;
        }
        
        return $url;
    }
    
    function appendPhpsessid($url)
    {
        if (!$url = $this->addParams($url, array('PHPSESSID' => session_id()))) {
            return false;
        }
        
        return $url;
    }
    
    function addParams($url, $params) {
        if (!$data = parse_url($url)) {
            return false;
        }
        
        $url = $this->generateURLWithURLData($data);
        
        $existingQuery = array();
        if (isset($data['query'])) {
            $existingQuery = $this->queryToArray($data['query']);
        }
        
        $query = http_build_query($existingQuery + $params);
        
        if (!empty($query)) {
            $query = "?" . $query;
        }
        return $url .= $query;
    }
    
    private function queryToArray($query = array())
    {
        $array = array();
        $pairs = explode("&", $query);
        foreach ($pairs as $pair) {
            
            if ($temp = explode("=", $pair)) {
              if (isset($temp[1])) {
                  $array[$temp[0]] = $temp[1];
              } else {
                  $array[$temp[0]] = "";
              }
            } else {
              $array[] = $pair;
            }
        }
        
        return $array;
    }
}