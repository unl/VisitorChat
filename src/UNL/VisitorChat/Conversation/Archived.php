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
        
        $this->messages = $this->conversation->getMessages(array('itemClass' => '\UNL\VisitorChat\Message\View'));
    }
}
