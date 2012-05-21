<?php 
namespace UNL\VisitorChat\Message;

class Edit extends \UNL\VisitorChat\Message\Record
{
    function __construct($options = array())
    {
        if (!$user = \UNL\VisitorChat\User\Service::getCurrentUser()) {
            \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$url);
        }
        
        if (isset($options['conversations_id'])) {
            $this->conversations_id = $options['conversations_id'];
        } else {
            $this->conversations_id = \UNL\VisitorChat\Conversation\Record::getLatestForClient($user->id)->id;
        }
    }
    
    function handlePost($post = array())
    {
        if (!isset($post['message']) || empty($post['message'])) {
            throw new \Exception("message was not provided.", 400);
        }
        
        if (!isset($post['conversations_id']) || empty($post['conversations_id'])) {
            throw new \Exception("conversations_id was not provided.", 400);
        }
        
        if (!$conversation = \UNL\VisitorChat\Conversation\Record::getByID($post['conversations_id'])) {
            throw new \Exception("Could not find that conversation", 400);
        }
        
        $user = \UNL\VisitorChat\User\Service::getCurrentUser();
        
        //check if we have permission to post here.
        if ($user->id !== $conversation->users_id && $user->type == 'client') {
            throw new \Exception("You do not have permission to reply to this conversation", 400);
        }
        
        $this->users_id = $user->id;
        $this->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
        
        $this->synchronizeWithArray($post);
        
        $this->save();
        
        //Update the user.
        $user->ping();
        
        //Update the conversation
        $this->getConversation()->ping();
        
        $conversation_id = "";
        if (isset($_GET['conversation_id'])) {
            $conversation_id = "?conversation_id=" . $_GET['conversation_id'];
        }
        
        $last = "?last=";
        if (!empty($conversation_id)) {
            $last = "&last=";
        }
        
        if (isset($_GET['last'])) {
            $last = $last.$_GET['last'];
        } else {
            $last = $last."0";
        }
        
        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("conversation" . $conversation_id . $last, true, true));
    }
}