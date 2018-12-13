<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Driver extends \UNL\VisitorChat\CacheableURL implements \UNL\VisitorChat\OperatorRegistry\DriverInterface
{
    public static $baseURI      = "https://webaudit.unl.edu/registry/";
    
    public static $cacheTimeout = 18000;  //seconds (5 hours)
    
    function getQueryURL($query)
    {
        return self::$baseURI . "?query=" . urlencode($query) . "&format=json";
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
        $data = $this->queryRegistry($url);
        
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
    
    protected function queryRegistry($url) {
        // create a new cURL resource
        $ch = curl_init();
        
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // grab URL and pass it to the browser
        $result = curl_exec($ch);
        
        // close cURL resource, and free up system resources
        curl_close($ch);
        
        return $result;
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
        $query = $user .'@unl.edu';
        $cachePath = $this->getCachePath($this->getQueryURL($query));
        
        if (!$doNotCache && $sites = $this->getCache($cachePath)) {
            return $sites;
        }
        
        //All sites for a user.
        $sites = $this->query($query, $doNotCache);
        
        if (!$sites) {
            $sites = array();
        }
        
        //Create an array of all sites that need to be removed.
        $unsets = array();
        foreach ($sites as $index=>$site) {
            foreach ($site->getMembers() as $member) {
                if ($member->getUID() == \UNL\VisitorChat\User\Service::getCurrentUser()->uid) {
                    //Remove this site from the site list IF the user is not a chat user.
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
        
        return $sites;
    }

    function getAllSites($doNotCache = false)
    {
        $cachePath = $this->getCachePath($this->getQueryURL('*'));

        //Return a cached result if we have it.
        if (!$doNotCache && $sites = $this->getCache($cachePath)) {
            return $sites;
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

        return $sites;
    }
}
