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
    public $loginHTML          = false;
    public $userType           = false;
    
    function __construct($options = array())
    {
        $this->phpssid = session_id();
        
        $this->serverTime = date('r');
        
        if (isset($options['checkOperators'])) {
            $this->operatorsAvailable = $this->areOperatorsAvaiable($options['checkOperators']);

            if (!Service::getCurrentUser() || Service::getCurrentUser()->type == 'client') {
                //For now we need to include the login html (until rollout is complete).
                $login = new \stdClass();

                $this->loginHTML = \UNL\VisitorChat\Controller::$templater->render($login, 'UNL/VisitorChat/User/ClientLogin.tpl.php');
            }
        }
        
        if (!$user = \UNL\VisitorChat\User\Service::getCurrentUser()) {
            return;
        }

        $this->userType = $user->type;
        
        $this->userID = $user->id;

        if ($conversation = $user->getConversation()){
            $this->conversationID = $conversation->id;
        }
        
        $this->userStatus = $user->status;
        
        if ($user->type == "operator") {
            //Update the last time the user was active.
            $user->last_active = \UNL\VisitorChat\Controller::epochToDateTime();
            $user->save();

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

        if (empty($sites)) {
            return false;
        }

        return \UNL\VisitorChat\User\Service::areUsersAvaiable($sites->current()->getMembers());
    }
}