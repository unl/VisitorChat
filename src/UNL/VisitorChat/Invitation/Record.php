<?php
namespace UNL\VisitorChat\Invitation;

class Record extends \Epoch\Record
{
    public $id;
    public $conversations_id;
    public $users_id;
    public $invitee;
    public $status;
    public $date_created;
    public $date_updated;
    
    function __construct($options = array()) {
        parent::__construct($options);
    }
    
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Invitation\Record', 'id', (int)$id);
    }
    
    public static function getTable()
    {
        return 'invitations';
    }
    
    function keys()
    {
        return array('id');
    }
    
    public static function getLatestForConversation($conversationID)
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        
        $sql = "SELECT * FROM invitations 
                WHERE conversations_id = " . (int)$conversationID . "
                ORDER BY date_created DESC
                LIMIT 1";
        
        if (!$result = $db->query($sql)) {
            return false;
        }
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $record = new self();
        
        $record->synchronizeWithArray($result->fetch_assoc());
        
        return $record;
    }
    
    public function insert()
    {
        $this->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
        $this->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
        return parent::insert();
    }
    
    public function save()
    {
        $this->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
        return parent::save();
    }
    
    /**
     * Determins if the invitee is a site.
     * 
     * @return bool
     */
    function isForSite()
    {
        if (filter_var($this->invitee, FILTER_VALIDATE_URL)) {
            return true;
        }
        
        return false;
    }
    
    function getSiteURL()
    {
        $data = explode('::', $this->invitee);
        
        return $data[0];
    }
    
    function getAccountUID()
    {
        if ($this->isForSite()) {
            return false;
        }
        
        $data = explode('::', $this->invitee);
        
        if (!isset($data[1])) {
            return false;
        }
        
        return $data[1];
    }
    
    public static function createNewInvitation($conversationID, $invitee, $inviter = 1)
    {
        $invitation = new self();
        $invitation->conversations_id = $conversationID;
        $invitation->invitee          = $invitee;
        $invitation->users_id         = $inviter;
        $invitation->status           = "SEARCHING";
        
        return $invitation->save();
    }
    
    public function complete()
    {
        $this->status = "COMPLETED";
        $this->save();
    }
    
    public function fail()
    {
        $this->status = "FAILED";
        $this->save();
        
        //Update the conversation status if needed.
        $conversation = \UNL\VisitorChat\Conversation\Record::getByID($this->conversations_id);
        
        //Was this invitation sent by the system?  if so, that means we need to fall though to email.
        if ($this->users_id == 1) {
            $conversation->status = "OPERATOR_LOOKUP_FAILED";
            
            //Try to send an email to the team.
            if (\UNL\VisitorChat\Conversation\FallbackEmail::sendConversation($conversation)) {
                $conversation->status  = "EMAILED";
                $conversation->emailed = 1;
            }
            
            $conversation->save();
        }
    }
    
    public function getAssignments()
    {
        return \UNL\VisitorChat\Assignment\RecordList::getAllAssignmentsForInvitation($this->id);
    }
}