<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Driver implements \UNL\VisitorChat\OperatorRegistry\DriverInterface
{
    public static $baseURI = "http://www1.unl.edu/wdn/registry/";
    
    function getMembers($site, $type = 'all')
    {
        $members = array();
        
        //Get Site Details
        $data = @file_get_contents(self::$baseURI . "?u=" . urlencode($site) . "&output=php&memberType=" . $type);
        
        if ($data) {
            $data = unserialize($data);
        }
        
        if (is_array($data)) {
            $members = $data;
        }
        
        return new Site\MemberList($members);
    }

    function getSites($member, $type = null)
    {
        //NOT IMPLEMENTED
        return false;
    }

    function getEmail($site)
    {
        //NOT IMPLEMENTED
        return false;
    }
}