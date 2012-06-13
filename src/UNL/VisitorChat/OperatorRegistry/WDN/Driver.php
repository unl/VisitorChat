<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Driver implements \UNL\VisitorChat\OperatorRegistry\DriverInterface
{
    public static $baseURI      = "http://www1.unl.edu/wdn/registry/";
    
    public static $cacheTimeout = 18000;  //seconds (5 hours)
    
    function query($query)
    {
        $url       = self::$baseURI . "?u=" . urlencode($query) . "&output=json";
        $cachePath = $this->getCachePath($url);
        
        //See if the query is cached, if it is, return it.
        if ($sites = $this->getCache($cachePath)) {
            return new SiteList($sites);
        }
        
        //data was not cached, get data and then cache it.
        $data = @file_get_contents($url);
        
        if (!$data) {
            return false;
        }
        
        if (!$sites = json_decode($data, true)) {
            return false;
        }
        
        //Set the cache.
        $this->setCache($cachePath, $sites);
        
        return new SiteList($sites);
    }
    
    function getCachePath($url)
    {
        $path = sys_get_temp_dir();

        //Some paths may not have a trailing separator.  Other may?  weird.
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path = $path . DIRECTORY_SEPARATOR;
        }

        return $path . "unl_visitorchat_wdn_" . md5($url);
    }
    
    function getCache($path)
    {
        if (file_exists($path) && (filemtime($path) + self::$cacheTimeout > time())) {
            return unserialize(file_get_contents($path));
        }
        
        return false;
    }
    
    function setCache($path, $data)
    {
        file_put_contents($path, serialize($data));
    }
    
    function getSitesByURL($site)
    {
        return $this->query($site);
    }
    
    function getSitesForUser($user)
    {
        return $this->query($user);
    }

    function getAllSites()
    {
        return $this->query('*');
    }
}