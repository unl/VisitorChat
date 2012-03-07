<?php
namespace UNL\VisitorChat\Conversation;
class History extends RecordList
{
    function __construct($options = array())
    {
        $options['returnArray'] = true;
        
        $options['limit'] = 10;
        
        $options['array'] = self::getConversationsForUser(\UNL\VisitorChat\User\Record::getCurrentUser()->id, false, $options);
        
        parent::__construct($options);
    }
}