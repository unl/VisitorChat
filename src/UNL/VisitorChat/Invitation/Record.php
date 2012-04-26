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
    }
}