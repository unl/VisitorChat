<?php
namespace UNL\VisitorChat\Conversation;

class View
{
    public $conversation_id = false;
    
    public $conversation = false;
    
    public $messages = array();
    
    public $request_id = 0;
    
    public $latest_message_id = 0;
    
    public $sendHTML = false;
    
    function __construct($options = array())
    {
        //Always require that someone is logged in
        \UNL\VisitorChat\Controller::requireClientLogin();
        
        //Get the current user.
        $user = \UNL\VisitorChat\User\Record::getCurrentUser();
        
        //Get and set the conversation for viewing.
        if (isset($options['conversation_id']) && $user->type == 'operator') {
            $this->conversation_id = $options['conversation_id'];
            
            //get the latest conversation.
            if (!$this->conversation = \UNL\VisitorChat\Conversation\Record::getByID($this->conversation_id)) {
                throw new \Exception("No conversation was found!", 500);
            }
        } else {
            //Just get the latest conversation.
            if (!$this->conversation = $user->getConversation()) {
                throw new \Exception("Could not find a conversation", 500);
            }
            $this->conversation_id = $this->conversation->id;
        }
        
        //Handle assignments for the conversation.
        $assignmentSerivce = new \UNL\VisitorChat\Assignment\Service();
        $assignmentSerivce->handleAssignments($this->conversation);
        
        //The rest of the logic only applies if we are currently chatting.
        if ($this->conversation->status !== 'CHATTING') {
            return;
        }
        
        if (isset($options['last'])) {
            $this->request_id = $options['last'];
        }
        
        if ($message = $this->conversation->getLastMessage()) {
            $this->latest_message_id = $message->id;
        }
        
        if ($this->latest_message_id > $this->request_id) {
            $this->messages = $this->conversation->getMessages(array('itemClass' => '\UNL\VisitorChat\Message\View'));
        }
        
        //Only send html output if we have to (to reduce size of response).
        if (($this->latest_message_id > $this->request_id) || ($this->latest_message_id == 0)) {
            $this->sendHTML = true;
        }
        
        //save the last viewed time to the session (for operators).
        $_SESSION['last_viewed'][$this->conversation->id] = \UNL\VisitorChat\Controller::epochToDateTime();
    }
}
