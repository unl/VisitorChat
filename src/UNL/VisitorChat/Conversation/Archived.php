<?php
namespace UNL\VisitorChat\Conversation;

class Archived
{
    public $conversation = false;
    
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::requireOperatorLogin();
        
        //Get and set the conversation for viewing.
        if (!isset($options['conversation_id'])) {
            throw new \Exception("No conversation id was given!", 500);
        }
        
        //get the conversation.
        if (!$this->conversation = \UNL\VisitorChat\Conversation\Record::getByID($options['conversation_id'])) {
            throw new \Exception("No conversation was found!", 500);
        }
        
        if (!$this->canView()) {
            throw new \Exception("Sorry, but you do not have permission to view this.", 403);
        }
        
        $this->messages = $this->conversation->getMessages(array('itemClass' => '\UNL\VisitorChat\Message\View'));
    }
    
    /** Determins if the current user can view this conversation.
     * 
     * The current user can view this conversation IF
     * 1) They were assigned to the conversation (and accepted the assignment)
     * 2) A manager is viewing the conversation and anyone in their team was assigned to it (even if they did not accept).
     * 3) The client is viewing.
     * 
     * @return bool
     */
    function canView()
    {
        $user = \UNL\VisitorChat\User\Service::getCurrentUser();
        
        //Is the client viewing it?
        if ($this->conversation->users_id == $user->id) {
            return true;
        }
        
        foreach ($this->conversation->getAssignments() as $assignment) {
            //is the user assigned?
            if ($assignment->users_id == $user->id && ($assignment->status == 'ACCEPTED' || $assignment->status == 'COMPLETED')) {
                return true;
            }
            
            //Is the user a manager of the answering site?
            foreach ($user->getManagedSites() as $site) {
                if ($site->getURL() == $assignment->answering_site) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
