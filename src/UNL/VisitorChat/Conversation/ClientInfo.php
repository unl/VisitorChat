<?php
namespace UNL\VisitorChat\Conversation;

class ClientInfo extends Record
{
    public static function getFromConversationRecord(\UNL\VisitorChat\Conversation\Record $record)
    {
        $object = new self();
        $object->synchronizeWithArray($record->toArray());
        
        return $object;
    }
}