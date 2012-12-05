<?php
namespace UNL\VisitorChat\User\Status;

/**
 * user_statuses record
 * 
 * The user_statuses table holds a history of users statuses for historical reasons.
 */
class Record extends \Epoch\Record
{
    public $id;
    public $users_id;
    public $date_created;
    public $status;
    public $reason;

    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\User\Status\Record', 'id', (int)$id);
    }

    function insert()
    {
        $this->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
        
        return parent::insert();
    }
    
    function save()
    {
        //do not save if the current status is valid
        if (!$this->validateStatus($this->status)) {
            throw new \Exception("Invalid Status: " + $this->status + ".  Valid statuses include: " + implode(",", self::getValidStatuses()));
        }

        //do not save if the current reason is valid
        if (!$this->validateStatusReason($this->reason)) {
            throw new \Exception("Invalid Status Reason: " + $this->reason + ".  Valid reasons include: " + implode(",", self::getValidStatusReasons()));
        }
        
        return parent::save();
    }
    
    function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'user_statuses';
    }
    
    public static function addStatus($userID, $status, $reason = "USER")
    {
        $newStatus = new self;
        $newStatus->users_id = $userID;
        $newStatus->setStatus($status, $reason);
        
        if (!$newStatus->save()) {
            return false;
        }
        
        return $newStatus;
    }
    
    public function setStatus($status, $reason = null)
    {
        //Set the default reason to USER.
        if ($reason == null) {
            $reason = "USER";
        }
        
        if (!$this->validateStatus($status)) {
            throw new \Exception("Invalid Status: " + $status + ".  Valid statuses include: " + implode(",", self::getValidStatuses()));
        }

        if (!$this->validateStatusReason($reason)) {
            throw new \Exception("Invalid Status Reason: " + $reason + ".  Valid reasons include: " + implode(",", self::getValidStatusReasons()));
        }
        
        $this->status = $status;
        $this->reason = $reason;
    }
    
    protected function validateStatus($status)
    {
        if (!in_array($status, self::getValidStatuses())) {
            return false;
        }
        
        return true;
    }
    
    protected function validateStatusReason($reason)
    {
        if (!in_array($reason, self::getValidStatusReasons())) {
            return false;
        }

        return true;
    }
    
    public static function getValidStatuses()
    {
        return array('AVAILABLE','BUSY');
    }
    
    public static function getValidStatusReasons()
    {
        return array('USER', 'SERVER_IDLE', 'CLIENT_IDLE', 'EXPIRED_REQUEST');
    }
    
    public static function getLatestForUser($userID)
    {
        $db = \Epoch\Controller::getDB();
        
        if (!$result = $db->query("SELECT * FROM user_statuses WHERE id = (select max(id) from user_statuses WHERE users_id = " . (int)$userID . ") LIMIT 1")) {
            return false;
        }

        //Check if we need to set the default status... (No status has been set yet).
        if ($result->num_rows == 0) {
            $record = self::addStatus($userID, "BUSY");
        }
    
        $record = new self();

        $record->synchronizeWithArray($result->fetch_assoc());

        return $record;
    }

    function getUser()
    {
        return \UNL\VisitorChat\User\Record::getByID($this->users_id);
    }
}