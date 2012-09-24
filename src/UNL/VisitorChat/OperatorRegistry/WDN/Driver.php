<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Driver extends \UNL\VisitorChat\CacheableURL implements \UNL\VisitorChat\OperatorRegistry\DriverInterface
{
    public static $baseURI      = "http://www1.unl.edu/wdn/registry/";
    
    public static $cacheTimeout = 18000;  //seconds (5 hours)
    
    function getQueryURL($query)
    {
        return self::$baseURI . "?u=" . urlencode($query) . "&output=json";
    }
    
    function query($query, $doNotCache = false)
    {
        $url       = $this->getQueryURL($query);
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
    
    function getSitesByURL($site, $doNotCache = false)
    {
        return $this->query($site, $doNotCache);
    }
    
    function getSitesForUser($user, $doNotCache = false)
    {
        $cachePath = $this->getCachePath($this->getQueryURL($user));
        
        if (!$doNotCache && $sites = $this->getCache($cachePath)) {
            return new SiteList($sites);
        }
        
        //All sites for a user.
        $sites = $this->query($user, $doNotCache);
        
        //Create an array of all sites that need to be removed.
        $unsets = array();
        foreach ($sites as $index=>$site) {
            echo $index . " : " . $site->getTitle() . PHP_EOL;
            foreach ($site->getMembers() as $member) {
                if ($member->getUID() == \UNL\VisitorChat\User\Service::getCurrentUser()->uid) {
                    //Remove this site from the site list IF the user is not a chat user.
                    echo $site->getTitle() . "  " . $member->getRole() . PHP_EOL;
                    if ($member->getRole() == 'other') {
                        $unsets[] = $index;
                        continue 2;
                    }
                }
            }
        }
        
        //remove the sites.
        foreach ($unsets as $index) {
            $sites->offsetUnset($index);
        }
        
        //re-cache the results
        if (!$doNotCache) {
            //Set the cache.
            $this->setCache($cachePath, $sites);
        }
        
        return new SiteList($sites);
    }

    function getAllSites($doNotCache = false)
    {
        $cachePath = $this->getCachePath($this->getQueryURL('*'));

        //Return a cached result if we have it.
        if (!$doNotCache && $sites = $this->getCache($cachePath)) {
            return new SiteList($sites);
        }

        //get all sites
        $sites = $this->query('*', $doNotCache);

        //create an array of all sites that need to be unset
        $unsets = array();
        foreach ($sites as $index=>$site) {
            foreach ($site->getMembers() as $member) {
                //Remove this site from the site list IF the user is not a chat user.
                if ($member->getRole() != 'other') {
                    continue 2;
                }
                
            }
            
            $unsets[] = $index;
        }

        //remove the sites.
        foreach ($unsets as $index) {
            $sites->offsetUnset($index);
        }

        //re-cache the results
        if (!$doNotCache) {
            //Set the cache.
            $this->setCache($cachePath, $sites);
        }

        return new SiteList($sites);
    }
}