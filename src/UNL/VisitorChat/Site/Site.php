<?php
//TODO: remove this class.
namespace UNL\VisitorChat\Site;
class Site
{
    protected $url;
    protected $memberType;
    protected $members;
    
    function __construct($url, $memberType = 'operators')
    {
        $this->url = $url;
        $this->memberType = $memberType;
        $this->members = Members::getMembersByTypeAndSite($this->memberType, $this->url);
    }
    
    function operatorsOnline()
    {
        foreach ($this->members as $member) {
            if (!$user = \UNL\VisitorChat\User\Record::getByUID($member)) {
                continue;
            }
            
            if ($user->status == 'AVAILABLE') {
                return true;
            }
        }
        
        return false;
    }
}