<?php 
namespace UNL\VisitorChat\User;

class Info
{
    public $userID             = false;
    public $conversationID     = false;
    public $phpssid            = false;
    public $pendingAssignment  = false;
    public $pendingDate        = false;
    public $userStatus         = false;
    public $unreadMessages     = array(); //The total number of messages for all open conversations
    public $serverTime         = 0;
    public $operatorsAvailable = null;
    
    function __construct($options = array())
    {
        $this->phpssid = session_id();
        
        $this->serverTime = date('r');
        
        if (isset($options['checkOperators'])) {
            $this->operatorsAvailable = $this->areOperatorsAvaiable($options['checkOperators']);
        }
        
        if (!$user = \UNL\VisitorChat\User\Record::getCurrentUser()) {
            return;
        }
        
        $this->userID = $user->id;
        if ($conversation = $user->getConversation()){
            $this->conversationID = $conversation->id;
        }
        
        $this->userStatus = $user->status;
        
        if ($user->type == "operator") {
            //If the user is avaiable, proccess pending assignments.
            if ($user->status == "AVAILABLE" && $assignment = \UNL\VisitorChat\Assignment\Record::getOldestPendingRequestForUser($user->id)) {
                $this->pendingAssignment = $assignment->id;
                $this->pendingDate       = date('r', strtotime($assignment->date_created));
            }
            
            //send the total message count.
            $this->unreadMessages = $user->getCurrentUnreadMessageCounts();
        }
    }
    
    function areOperatorsAvaiable($url)
    {
        $sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($url);
        
        return \UNL\VisitorChat\User\Service::areUsersAvaiable($sites->current()->getMembers());
    }
}