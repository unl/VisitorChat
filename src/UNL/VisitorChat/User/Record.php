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
    public $ip;
    public $type; //Client or Operator
    public $max_chats;
    public $status;
    public $status_reason;
    public $last_active;
    
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
        return parent::insert();
    }
    
    function keys()
    {
        return array('id');
    }
    
    public static function getTable()
    {
        return 'users';
    }
    
    public function update()
    {
        parent::update();
        
        if (\UNL\VisitorChat\User\Service::getCurrentUser() && \UNL\VisitorChat\User\Service::getCurrentUser()->id == $this->id) {
            \UNL\VisitorChat\User\Service::setCurrentUser($this);
        }
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
    
    function getManagedSites()
    {
        if (empty($this->uid)) {
            return false;
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
}