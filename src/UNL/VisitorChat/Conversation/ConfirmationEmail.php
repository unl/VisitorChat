<?php
namespace UNL\VisitorChat\Conversation;

class ConfirmationEmail extends Email
{
    public static function sendConversation(\UNL\VisitorChat\Conversation\Record $conversation, $options = array())
    {
        $class = get_called_class();
        
        $to = array();
        $client = $conversation->getClient();
        if (isset($client->email) && !empty($client->email)) {
            $to[] = $client->email;
        }
        
        $email = new $class($conversation, $to, $options);
        
        $email->subject = "UNL VisitorChat System: Confirmation";
        
        if (!$email->send()) {
            echo "here"; exit();
        }
        
        
    }
}