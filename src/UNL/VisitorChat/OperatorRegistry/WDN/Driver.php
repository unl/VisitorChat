<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Driver extends \UNL\VisitorChat\CacheableURL implements \UNL\VisitorChat\OperatorRegistry\DriverInterface
{
    public static $baseURI      = "http://www1.unl.edu/wdn/registry/";
    
    public static $cacheTimeout = 18000;  //seconds (5 hours)
    
    function query($query, $doNotCache = false)
    {
        $url       = self::$baseURI . "?u=" . urlencode($query) . "&output=json";
        $cachePath = $this->getCachePath($url);
        
        //See if the query is cached, if it is, return it.
        if (!$doNotCache && $sites = $this->getCache($cachePath)) {
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
        
        if (!$doNotCache) {
            //Set the cache.
            $this->setCache($cachePath, $sites);
        }
        
        return new SiteList($sites);
    }
    
    function getCacheTitle()
    {
        return "unl_visitorchat_wdn_";
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