<?php 
namespace UNL\VisitorChat\Conversation;

class Share extends \UNL\VisitorChat\Conversation\Record
{
    function __construct($options = array())
    {
        //Needs to be atleast a user to login.
        \UNL\VisitorChat\Controller::requireClientLogin();
        
        if (isset($options['id']) && $object = \UNL\VisitorChat\Conversation\Record::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        } else {
            if (!$conversation = \UNL\VisitorChat\Conversation\Record::getCurrentUser()->getConversation()) {
                throw new \Exception("No conversation was found", 400);
            }
            $this->synchronizeWithArray($conversation->toArray());
        }
    }
    
    function canShare($userID)
    {
        //Anyone currently involved in a chat can edit it.
        if (in_array($userID, $this->getInvolvedUsers())) {
            return true;
        }
        
        return false;
    }
    
    function handlePost($post = array())
    {
        if (!$this->canShare($_SESSION['id'])) {
            throw new \Exception("you do not have permission to share this conversation.", 401);
        }
        
        if (!isset($post['method']) || !in_array($post['method'], array('invite'))) {
            throw new \Exception('A valid method was not given.', 400);
        }
        
        if (!isset($post['to'])) {
            throw new \Exception('No one was specified to share this conversation with.', 400);
        }
        
        switch ($post['to']) {
            case 'invite': 
                //Start a new invitation here.
                break;
        }
        
        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("success", true));
    }
}