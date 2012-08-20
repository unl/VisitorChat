<?php
namespace UNL\VisitorChat\Conversation;

class ConfirmationEmail extends Email
{
    public static function sendConversation(\UNL\VisitorChat\Conversation\Record $conversation, $fromId = 1, $email = false, $options = array())
    {
        $class = get_called_class();
        
        $to = array();
        $client = $conversation->getClient();

        if ($email) {
            $to[] = $email;
        } else {
            //Do we have an email address to send this to?
            if (!isset($client->email) || empty($client->email)) {
                return false;
            }

            $to[] = $client->email;
        }
        
        $email = new $class($conversation, $to, $fromId, $options);
        
        $email->subject = \UNL\VisitorChat\Conversation\Email::$default_subject . ": Transcript (" . $conversation->id . ")";

        return $email->send();
    }
}