<?php
namespace UNL\VisitorChat\User;

class Record extends \Epoch\Record
{
    public $id;
    public $uid;
    public $name;
    public $email;
    public $date_created;
    public $date_updated;
    public $type; //Client or Operator
    public $max_chats;
    public $status;        //Store current status here (as kind of a cache)
    public $status_reason; //Store current status reason here (as kind of a cache)
    public $last_active;
    public $popup_notifications; //1=show, 2=no show
    public $alias; //custom name to be shown to clients
    
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\User\Record', 'id', (int)$id);
    }
    
    public static function getByUID($uid)
    {
        return self::getByAnyField('\UNL\VisitorChat\User\Record', 'uid', $uid);
    }
    
    public static function getByEmail($email)
    {
        return self::getByAnyField('\UNL\VisitorChat\User\Record', 'email', $email);
    }

    function insert()
    {
        if ($result = parent::insert()) {
            //Store the status in the history table.
            $this->setStatus($this->status);
        }
        
        return $result;
    }
    
    function keys()
    {
        return array('id');
    }
    
    public static function getTable()
    {
        return 'users';
    }
    
    public function isAdmin()
    {
        if (in_array($this->uid, \UNL\VisitorChat\Controller::$admins)) {
            return true;
        }
        
        return false;
    }
    
    public function update()
    {
        $result = parent::update();
        
        //Update the current user object in memory
        if (\UNL\VisitorChat\User\Service::getCurrentUser() && \UNL\VisitorChat\User\Service::getCurrentUser()->id == $this->id) {
            \UNL\VisitorChat\User\Service::setCurrentUser($this);
        }
        
        return $result;
    }
    
    public static function getCurrentUser()
    {
        if (isset($_SESSION['id'])) {
            return self::getByID($_SESSION['id']);
        }
        
        return false;
    }
    
    function ping()
    {
        $this->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
        $this->save();
    }
    
    function getConversation()
    {
        return \UNL\VisitorChat\Conversation\Record::getLatestForClient($this->id);
    }
    
    /**
     * returns the total number of messages in all open
     * conversations that this user is assigned to and the 
     * total number of read messages for each conversation.
     * 
     * @return int messageCount
     */
    function getCurrentUnreadMessageCounts()
    {
        $totals = array();
        
        $conversations = \UNL\VisitorChat\Conversation\RecordList::getOpenConversations($this->id);
        
        foreach($conversations as $conversation) {
            $totals[$conversation->id] = $conversation->getUnreadMessageCount();
        }
        
        return $totals;
    }
    
    function getSites()
    {
        if (empty($this->uid)) {
            return array();
        }
        
        return \UNL\VisitorChat\Controller::$registryService->getSitesForUser($this->uid);
    }
    
    function getManagedSites()
    {
        if (empty($this->uid)) {
            return array();
        }
        
        $sites = array();
        
        foreach (\UNL\VisitorChat\Controller::$registryService->getSitesForUser($this->uid) as $site) {
            foreach ($site->getMembers() as $member) {
                if ($member->getUID() !== $this->uid) {
                    continue;
                }
                
                if ($member->getRole() !== 'manager') {
                    continue;
                }
                
                $sites[] = $site;
            }
        }
        
        return $sites;
    }
    
    function getFirstName()
    {
        $names = explode(" ", $this->name);
        
        return $names[0];
    }
    
    function getAlias()
    {
        if (!empty($this->alias)) {
            return $this->alias;
        }
        
        return $this->getFirstName();
    }
    
    /*
     * Checks to see if this user is a manager for a given site.
     */
    function managesSite($url)
    {
        //Check if the current user has permission to view the site.
        foreach ($this->getManagedSites() as $site) {
            if ($site->getURL() == $url) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Sets the user's status.  If the user is an operator, the status is recorded in the
     * history table.
     * 
     * @param        $status
     * @param string $reason
     *
     * @return bool
     */
    function setStatus($status, $reason = "USER") 
    {
        //Store the current status in this record (for caching and easy access).
        $this->status        = $status;
        $this->status_reason = $reason;
        
        //If we failed to save, exist early.
        if (!$this->save()) {
            return false;
        }
        
        //We are not tracking client statuses so exit early...
        if ($this->type == 'client') {
            return true;
        }
        
        if (!Status\Record::addStatus($this->id, $status, $reason)) {
            return false;
        }
        
        return true;
    }
    
    function getStatus()
    {
        if ($this->status == null) {
            $this->status = "BUSY";
        }
        
        //Format the current status as a user_status record for consistency
        $status = new Status\Record();
        $status->setStatus($this->status, $this->status_reason);
        $status->users_id = $this->id;
        
        return $status;
    }
}