<?php 
namespace UNL\VisitorChat\URL;

class Service
{
    protected $options;
    
    function __construct($options = array())
    {
        $this->options = $options;
    }
    
    function generateSiteURL($path, $appendFormat = false, $appendPhpsessid = false)
    {
        $url = \UNL\VisitorChat\Controller::$url . $path;
        
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
        if (!$data = parse_url($url)) {
            return false;
        }
        
        $url = $this->generateURLWithURLData($data);
        
        if ($format === true) {
            $format = $this->options['format'];
        }
        
        $query = array();
        
        if (isset($data['query'])) {
            $data['query'] .= "&format=" . $format;
        } else {
            $data['query'] = "format=" . $format;
        }
        
        $query = http_build_query($this->queryToArray($data['query']));
        
        return $url .= "?" . $query;
    }
    
    function appendPhpsessid($url)
    {
        if (!$data = parse_url($url)) {
            return false;
        }
        
        $url = $this->generateURLWithURLData($data);
        
        if (isset($data['query'])) {
            $data['query'] .= "&PHPSESSID=" . session_id();
        } else {
            $data['query'] = "PHPSESSID=" . session_id();
        }
        
        $query = http_build_query($this->queryToArray($data['query']));
        
        return $url .= "?" . $query;
    }
    
    private function queryToArray($query = array())
    {
        $array = array();
        $pairs = explode("&", $query);
        foreach ($pairs as $pair) {
            
            if ($temp = explode("=", $pair)) {
              $array[$temp[0]] = $temp[1];
            } else {
              $array[] = $pair;
            }
        }
        
        return $array;
    }
}