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
    
    /**
     * Generates a site url based on a path, and will optionally appened the
     * current format and phpsessid to the url.
     * 
     * @param string $path
     * @param mixed $appendFormat
     * @param bool $appendPhpsessid
     */
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
    
    /**
     * Generates a url with data from the parse_url function.
     * 
     * @param array $data
     * @throws \Exception
     * 
     * @return string url
     */
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
    
    
    /**
     * Appends a format to to a url.  If the $format param is set to true
     * it will appened the current format, otherwise it will append the value
     * of $format.
     * 
     * @param string $url
     * @param mixed $format
     * 
     * @return string $url
     */
    function appendFormat($url, $format) {
        if ($format === true) {
            //Do we know what the format is?
            if (!isset($this->options['format'])) {
                return $url;
            }
            
            $format = $this->options['format'];
        }
        
        if (!$url = $this->addParams($url, array('format' => $format))) {
            return false;
        }
        
        return $url;
    }
    
    /**
     * Appends the current phpsessid to a url.  This function is 
     * avaible so that servers that do not have the phpsessid auto appened
     * can call this function.
     * 
     * @param string $url
     * 
     * @return string $url
     */
    function appendPhpsessid($url)
    {
        if (!$url = $this->addParams($url, array('PHPSESSID' => session_id()))) {
            return false;
        }
        
        return $url;
    }
    
    /**
     * Add GET parameters to a url.
     * 
     * @param string $url
     * @param array $params
     * 
     * @return string $url
     */
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
    
    /**
     * converts a query string built with parse_url to
     * an array.
     * 
     * @param string $query
     * 
     * @return array $array
     */
    private function queryToArray($query)
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