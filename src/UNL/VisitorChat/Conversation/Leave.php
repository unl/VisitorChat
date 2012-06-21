<?php 
namespace UNL\VisitorChat\Conversation;

class Leave extends \UNL\VisitorChat\Conversation\Edit
{
    function __construct($options = array())
    {
        //Needs to be atleast a user to login.
        \UNL\VisitorChat\Controller::requireClientLogin();
        
        if (isset($options['id']) && $object = \UNL\VisitorChat\Conversation\Record::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        } else {
            if (!$conversation = \UNL\VisitorChat\User\Service::getCurrentUser()->getConversation()) {
                throw new \Exception("No conversation was found", 400);
            }
            $this->synchronizeWithArray($conversation->toArray());
        }
    }
    
    function handlePost($post = array())
    {
        if (!$this->canEdit($_SESSION['id'])) {
            throw new \Exception("you do not have permission to edit this conversation.", 403);
        }
        
        if (!isset($post['confirm'])) {
            throw new \Exception("You did not confirm your leave action", 400);
        }
        
        if ($this->getAcceptedAssignments()->count() < 2) {
            throw new \Exception("You can not leave the conversation if you are the only current operator", 400);
        }
        
        //Get the assignment for the current user
        if (!$assignment = \UNL\VisitorChat\Assignment\Record::getLatestByStatusForUserAndConversation('ACCEPTED', \UNL\VisitorChat\User\Service::getCurrentUser()->id, $this->id)) {
            throw new \Exception("Could not locate your assignment for this conversation", 500);
        }
        
        $assignment->markAsLeft();
        
        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("success", true));
    }
}