<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Site extends \UNL\VisitorChat\OperatorRegistry\SiteInterface
{
    private $url     = null;
    
    private $members = array();
    
    private $email   = null;
    
    private $title   = null;
    
    function __construct($url, $data)
    {
        $this->url = $url;
        
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        
        if (isset($data['members'])) {
            $this->members = $data['members'];
        }
        
        if (isset($data['support_email'])) {
            $this->email = $data['support_email'];
        }
    }
    
    function getURL()
    {
        return $this->url;
    }
    
    function getMembers()
    {
        return new Site\MemberList($this->url, $this->members);
    }
    
    function getEmail()
    {
        if ($this->email !== null) {
            return $this->email;
        }

        $email = "";
        foreach ($this->getMembers() as $person) {
            if ($mail = $person->getEmail()) {
                $email .= $mail . ', ';
            }
        }
        
        return $email;
    }
    
    function getTitle()
    {
        if ($this->title == "") {
            return $this->url;
        }
        return $this->title;
    }
}