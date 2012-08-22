<?php
namespace UNL\VisitorChat\Assignment;

/**
 * Status Definitions
 * 'PENDING' -> pending operator response
 * 'REJECTED' -> rejected by operator
 * 'ACCEPTED' -> accepted by operator
 * 'EXPIRED' -> timed out (no response by operator)
 * 'COMPLETED' -> accepted and completed.  This status is only reached when the conversation is closed.
 * 'LEFT' -> left the conversation before it was completed
 * 'FAILED' -> assignment failed, probably due to the invitation failing prematurely.
 */

class Record extends \Epoch\Record
{
    public $id;
    public $conversations_id;
    public $users_id;
    public $date_created;
    public $status;
    public $date_updated;
    public $answering_site;
    public $invitations_id;
    public $date_finished;
    public $date_accepted;
    
    function __construct($options = array()) {
        parent::__construct($options);
    }
    
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Assignment\Record', 'id', (int)$id);
    }
    
    public static function getTable()
    {
        return 'assignments';
    }
    
    function keys()
    {
        return array('id');
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
     * Mark this assignment as completed.
     * 
     * @return bool
     */
    public function markAsCompleted()
    {
        //set all accepted or pending assignments as completed.
        if ($this->status == 'ACCEPTED') {
            return $this->updateStatus('COMPLETED');
        }

        if ($this->status == 'PENDING') {
            return $this->updateStatus('FAILED');
        }

        return true;
    }

    /**
     * Mark this assignment as completed.
     *
     * @return bool
     */
    public function markAsFailed()
    {
        return $this->updateStatus('FAILED');
    }
    
    /**
     * Mark this assignment as left (left before the conversation was completed).
     * 
     * @return bool
     */
    public function markAsLeft()
    {
        return $this->updateStatus('LEFT');
    }
    
    public function updateStatus($status)
    {
        $this->status = $status;
        
        if (in_array($status, array('LEFT', 'COMPLETED', 'REJECTED', 'EXPIRED', 'FAILED'))) {
            $this->date_finished = \UNL\VisitorChat\Controller::epochToDateTime();
        }
        
        if ($status == 'ACCEPTED') {
            $this->getInvitation()->complete();
            $this->date_accepted = \UNL\VisitorChat\Controller::epochToDateTime();
        }
        
        return $this->save();
    }
    
    /**
     * Retrieves the olest pending assignment for auser.
     * 
     * @param int $userID
     * 
     * @return bool | \UNL\VisitorChat\Assignment\Record
     */
    public static function getOldestPendingRequestForUser($userID)
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        
        $sql = "SELECT * FROM assignments 
                WHERE status = 'PENDING' 
                    AND users_id = " . (int)$userID . "
                    ORDER BY date_created ASC
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
    
    public static function getLatestForInvitation($invitionID)
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        
        $sql = "SELECT * FROM assignments 
                WHERE invitations_id = " . (int)$invitionID . "
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
    
    public static function getLatestByStatusForUserAndConversation($status, $userID, $conversationID)
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        
        $sql = "SELECT * FROM assignments 
                WHERE status = '" . \Epoch\RecordList::escapeString($status) . "'
                    AND users_id = " . (int)$userID . "
                    AND conversations_id = " . (int)$conversationID . "
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
    
    function getInvitation()
    {
        return \UNL\VisitorChat\Invitation\Record::getByID($this->invitations_id);
    }
    
    /**
     * Creates a new assigment record.
     * 
     * @param int $userID
     * @param int $conversationID
     * 
     * @return bool
     */
    public static function createNewAssignment($userID, $answeringSite, $conversationID, $invitationID)
    {
        $assignment = new self();
        $assignment->users_id         = $userID;
        $assignment->status           = 'PENDING';
        $assignment->conversations_id = $conversationID;
        $assignment->answering_site   = $answeringSite;
        $assignment->invitations_id   = $invitationID;
        
        return $assignment->save();
    }
    
    public function getConversation()
    {
        return \UNL\VisitorChat\Conversation\Record::getByID($this->conversations_id);
    }
    
    public function accept()
    {
        $conversation = $this->getConversation();
        
        if ($conversation->getAcceptedAssignments()->count() == 0) {
            $messageText = "Hello, my name is " . $this->getUser()->getFirstName() . ".  Please wait while I review your message so that I can assist you.";
            
            //Create a new message.
            $message = new \UNL\VisitorChat\Message\Record();
            $message->users_id         = $this->users_id;
            $message->date_created     = \UNL\VisitorChat\Controller::epochToDateTime();
            $message->conversations_id = $this->conversations_id;
            $message->message          = $messageText;
            $message->save();
        }
        
        return $this->updateStatus('ACCEPTED');
    }
    
    public function reject()
    {
        return $this->updateStatus('REJECTED');
    }
    
    public function getUser()
    {
        return \UNL\VisitorChat\User\Record::getByID($this->users_id);
    }
}
