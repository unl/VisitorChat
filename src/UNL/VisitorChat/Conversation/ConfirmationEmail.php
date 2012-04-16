<?php
namespace UNL\VisitorChat\Conversation;

class ConfirmationEmail extends Email
{
    public static function sendConversation(\UNL\VisitorChat\Conversation\Record $conversation, $options = array())
    {
        $class = get_called_class();
        
        $to = array();
        $client = $conversation->getClient();
        
        //Do we have an email address to send this to?
        if (!isset($client->email) || empty($client->email)) {
            return false;
        }
        
        $to[] = $client->email;
        
        $email = new $class($conversation, $to, $options);
        
        $email->subject = "UNL VisitorChat System: Confirmation";
        
        return $email->send();
    }
}