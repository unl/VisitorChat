<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class Site extends \UNL\VisitorChat\OperatorRegistry\SiteInterface
{
    private $url     = null;
    
    private $members = array();
    
    private $email   = null;
    
    private $title   = null;
    
    private $groups  = null;
    
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

        if (isset($data['support_groups'])) {
            $this->groups = $data['support_groups'];
        }
    }
    
    function getURL()
    {
        return $this->url;
    }

    function getSupportGroups()
    {
        return $this->groups;
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
		
        $email = trim($email, ", ");
        return $email;
    }
    
    function getTitle()
    {
        if ($this->title == "") {
            return $this->url;
        }
        return $this->title;
    }
    
    /**
     * Determines the number of operators for this site that are current available.
     *
     * @return int the number of operators currently available
     */
    function getAvailableCount()
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        
        $sql = "SELECT count(id) as total 
                        FROM users 
                        WHERE status = 'AVAILABLE' AND (false ";
        
        foreach ($this->getMembers() as $member) {
            if (!$member->canOperate()) {
                continue;
            }
        
            $sql .= "OR uid = '" . $db->escape_string($member->getUID()) . "' ";
        }
        
        $sql .= ");";
        
        if ($result = $db->query($sql)) {
            $row = $result->fetch_assoc();
        
            if (isset($row['total'])) {
                return $row['total'];
            }
        }
        
        return 0;
    }
}