<?php
namespace UNL\VisitorChat\Assignment;

class Record extends \Epoch\Record
{
    public $id;
    public $conversations_id;
    public $users_id;
    public $date_created;
    public $status;
    public $date_updated;
    
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
        return $this->updateStatus('COMPLETED');
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
        return $this->save();
    }
    
    /**
     * Retrieves the olest pending assignment for auser.
     * 
     * @param int $userID
     * 
     * @return VisitorChat\Assignment\Record
     */
    public static function getOldestPendingRequestForUser($userID)
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        
        $sql = "SELECT * FROM assignments 
                WHERE status = 'PENDING' 
                    AND users_id = " . (int)$userID . "
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
    
    /**
     * Creates a new assigment record.
     * 
     * @param int $userID
     * @param int $conversationID
     * 
     * @return bool
     */
    public static function createNewAssignment($userID, $answeringSite, $conversationID)
    {
        $assignment = new self();
        $assignment->users_id         = $userID;
        $assignment->status           = 'PENDING';
        $assignment->conversations_id = $conversationID;
        $assignment->answering_site   = $answeringSite;
        return $assignment->save();
    }
}