<?php
//TODO: remove this class.
namespace UNL\VisitorChat\Site;
class Member
{
    public $uid;
    
    public $details = array();
    
    function __construct($uid)
    {
        $this->uid = $uid;
        
        $this->details = $this->syncDetails();
    }
    
    function syncDetails()
    {
        $data_url = 'http://directory.unl.edu/?uid='.urlencode($this->uid).'&format=json';
        
        if (($person = file_get_contents($data_url)) && $details = json_decode($person, true)) {
            return $details;
        }
        
        return array();
    }
    
    function getDefaultEmail()
    {
        if (isset($this->details['mail'][0]) && !empty($this->details['mail'][0])) {
            return $this->details['mail'][0];
        }
        
        return false;
    }
}