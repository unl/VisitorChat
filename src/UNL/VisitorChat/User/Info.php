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
    public $userStatusReason   = false;
    public $popupNotifications = false;
    
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
            
            $this->popupNotifications = \UNL\VisitorChat\User\Service::getCurrentUser()->popup_notifications;
            if ($this->popupNotifications == null) {
                $this->popupNotifications = 0;
            }
        }

        if (!$user = \UNL\VisitorChat\User\Service::getCurrentUser()) {
            //Hide Operator Info
            unset($this->pendingAssignment);
            unset($this->unreadMessages);
            unset($this->pendingDate);
            unset($this->userStatus);
            unset($this->userStatusReason);
            unset($this->popupNotifications);

            //Hide client Info
            unset($this->conversationID);
            unset($this->userID);
            unset($this->serverTime);
            unset($this->userType);
            
            //Don't continue
            return;
        }

        $this->userType = $user->type;
        
        $this->userID = $user->id;

        if ($conversation = $user->getConversation()){
            $this->conversationID = $conversation->id;
        }
        
        if ($user->type == "operator") {
            //Send the current user status;
            $this->userStatus       = $user->status;
            $this->userStatusReason = $user->status_reason;
            
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
        } else {
            //Hide Operator Info
            unset($this->pendingAssignment);
            unset($this->unreadMessages);
            unset($this->pendingDate);
            unset($this->userStatus);
            unset($this->userStatusReason);
            unset($this->popupNotifications);
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