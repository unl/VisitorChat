<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN\Site;

class Member implements \UNL\VisitorChat\OperatorRegistry\SiteMemberInterface
{
    private $uid   = null;
    
    private $roles = array();
    
    private $site  = null;
    
    function __construct($uid, $roles, $site)
    {
        $this->uid   = $uid;
        $this->roles = $roles;
        $this->site  = $site;
    }
    
    function getSite()
    {
        return $this->site;
    }
    
    function getRole()
    {
        $rank = false;
        
        //Loop though each of the user's roles for this site and determine their max role.
        foreach ($this->roles as $role) {
            //Get the role's rank.
            $key = array_search($role, \UNL\VisitorChat\Controller::$roles);
            
            //If the rank is higher, use that one.
            if ($rank < $key) {
                $rank = $key;
            }
        }
        
        return \UNL\VisitorChat\Controller::$roles[$rank];
    }
    
    function getUID()
    {
        return $this->uid;
    }
}