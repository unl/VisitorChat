<?php 
namespace UNL\VisitorChat\User;

class Edit extends \UNL\VisitorChat\User\Record
{
    function __construct($options = array())
    {
        if (isset($options['id']) && $object = \UNL\VisitorChat\User\Record::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        } else {
            $this->synchronizeWithArray(\UNL\VisitorChat\User\Record::getCurrentUser()->toArray());
        }
    }
    
    function handlePost($post = array())
    {
        if (\UNL\VisitorChat\User\Record::getCurrentUser()->id !== $this->id) {
            throw new \Exception("you do not have permission to edit this.", 401);
        }
        
        //Handle status changes.
        if (isset($post['status']) && !empty($post['status'])) {
            $accepted = array("BUSY", "AVAILABLE");
            
            if (!in_array($post['status'], $accepted)) {
                throw new \Exception("invalid status.", 400);
            }
            
            $this->status = $post['status'];
        }
        
        if (isset($post['max_chats'])) {
            if ($post['max_chats'] < 1) {
                throw new \Exception("You must have atleast 1 max chat", 400);
            }
            
            $this->max_chats = $post['max_chats'];
        }
        
        $this->save();
        
        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("success", true));
    }
}