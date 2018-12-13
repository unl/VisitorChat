<?php 
namespace UNL\VisitorChat\Assignment;

class Edit extends \UNL\VisitorChat\Assignment\Record
{
    function __construct($options = array())
    {
        if (!$user = \UNL\VisitorChat\User\Service::getCurrentUser()) {
            \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$url);
        }
        
        if (isset($options['id']) && $object = \UNL\VisitorChat\Assignment\Record::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        }
    }
    
    function handlePost($post = array())
    {
        if (\UNL\VisitorChat\User\Service::getCurrentUser()->id !== $this->users_id) {
            throw new \Exception("you do not have permission to edit this.", 403);
        }
        
        if (isset($post['is_typing'])) {
            $this->handleIsTyping($post);
        } else if ($post['status']) {
            $this->handleAssignmentStatus($post);
        } else {
            throw new \Exception("A valid request was not given.", 400);
        }
    }
    
    protected function handleIsTyping($post = array())
    {
        if (!in_array($post['is_typing'], array(
            \UNL\VisitorChat\Assignment\Record::IS_NOT_TYPING,
            \UNL\VisitorChat\Assignment\Record::IS_TYPING
        ))) {
            throw new \Exception("A valid status was not supplied.", 400);
        }
        
        $this->is_typing = $post['is_typing'];
        $this->save();

        $format = "";
        if (isset($_GET['format'])) {
            $format = "?format=" . $_GET['format'];
        }
        
        \UNL\VisitorChat\Controller::redirect(\UNL\VisitorChat\Controller::$url . "success" . $format);
    }
    
    protected function handleAssignmentStatus($post = array())
    {
        if ($this->status !== 'PENDING') {
            throw new \Exception("this request has already been processed", 400);
        }
        
        if (empty($post['status'])) {
            throw new \Exception("a new status was not provided.", 400);
        }

        $accepted = array("ACCEPTED", "REJECTED");

        if (!in_array($post['status'], $accepted)) {
            throw new \Exception("invalid status.", 400);
        }

        if ($post['status'] == "ACCEPTED") {
            $this->accept();
        } else {
            $this->reject();
        }

        //Update the conversation status
        $conversation = \UNL\VisitorChat\Conversation\Record::getByID($this->conversations_id);

        //Reset to searching if we are initalizing the chat.
        if ($conversation->status == "OPERATOR_PENDING_APPROVAL") {
            $conversation->status = "SEARCHING";
        }

        //Only change the status to chatting if it was accepted.
        if ($post['status'] == 'ACCEPTED') {
            $conversation->status = "CHATTING";
        }

        $conversation->save();

        $format = "";
        if (isset($_GET['format'])) {
            $format = "?format=" . $_GET['format'];
        }

        $phpsessid = "";
        if (isset($_GET['PHPSESSID'])) {
            $phpsessid = "&";
            if ($format == "") {
                $phpsessid = "?";
            }

            $phpsessid .= "PHPSESSID=" . $_GET['PHPSESSID'];
        }

        \UNL\VisitorChat\Controller::redirect(\UNL\VisitorChat\Controller::$url . "success" . $format . $phpsessid);
    }
}
