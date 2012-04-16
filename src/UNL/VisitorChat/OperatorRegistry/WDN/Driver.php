<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Driver implements \UNL\VisitorChat\OperatorRegistry\DriverInterface
{
    public static $baseURI = "http://www1.unl.edu/wdn/registry/";
    
    function query($query)
    {
        $data = @file_get_contents(self::$baseURI . "?u=" . urlencode($query) . "&output=json");
        
        if (!$data) {
            return false;
        }
        
        if (!$sites = json_decode($data, true)) {
            return false;
        }
        
        return new SiteList($sites);
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