<?php 
namespace UNL\VisitorChat\User;

class Edit extends \UNL\VisitorChat\User\Record
{
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::$pagetitle = "User Settings";

        if (!\UNL\VisitorChat\User\Service::getCurrentUser()) {
            throw new \Exception("You must be logged in to do this.", 401);
        }

        if (isset($options['id']) && $object = \UNL\VisitorChat\User\Record::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        } else {
            $this->synchronizeWithArray(\UNL\VisitorChat\User\Service::getCurrentUser()->toArray());
        }
    }
    
    function handlePost($post = array())
    {
        if (\UNL\VisitorChat\User\Service::getCurrentUser()->id !== $this->id) {
            throw new \Exception("you do not have permission to edit this.", 403);
        }
        
        //Handle status changes.
        if (isset($post['status']) && !empty($post['status'])) {
            $reason = "USER";

            if (isset($post['reason']) && !empty($post['reason'])) {
                $reason = strtoupper($post['reason']);
            }

            if (!in_array($post['reason'], array('CLIENT_IDLE', 'USER'))) {
                throw new \Exception("invalid status.", 400);
            }

            $accepted = array("BUSY", "AVAILABLE");
            
            if (!in_array($post['status'], $accepted)) {
                throw new \Exception("invalid status.", 400);
            }
            
            $this->setStatus($post['status'], $reason);
        }
        
        if (isset($post['max_chats'])) {
            if ($post['max_chats'] < 1) {
                throw new \Exception("You must have atleast 1 max chat", 400);
            }
            
            $this->max_chats = $post['max_chats'];
        }

        if (isset($post['alias'])) {
            $this->alias = $post['alias'];
        }

        if (isset($post['popup_notifications'])) {
            if (!in_array($post['popup_notifications'], array('1', '0'))) {
                throw new \Exception("Values for popup_notifications can be either 1 or 0", 400);
            }

            $this->popup_notifications = $post['popup_notifications'];
        }
        
        $this->save();
        
        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("success", true));
    }
}