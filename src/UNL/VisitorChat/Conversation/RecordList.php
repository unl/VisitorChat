<?php
namespace UNL\VisitorChat\Conversation;
class RecordList extends \Epoch\RecordList
{
    function __construct($options = array())
    {
         if (!isset($options['model']) || $options['model'] != 'UNL\VisitorChat\Conversation\RecordList') {
            parent::__construct($options);
            return;
        }
        
        $options['returnArray'] = true;
        
        $options['array'] = self::getOpenConversations(\UNL\VisitorChat\User\Record::getCurrentUser()->id, $options);
        
        parent::__construct($options);
    }
    
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\Conversation\Record';
        $options['listClass'] = '\UNL\VisitorChat\Conversation\RecordList';
        
        return $options;
    }
    
    public static function getOpenConversations($userID, $options = array())
    {
        return self::getConversationsForUser($userID, 'CHATTING', $options);
    }
    
    public static function getConversationsForUser($userID, $chatStatus = false, $options = array())
    {
        //Build the chat status constraint.
        $constraint = "";
        if ($chatStatus) {
            $constraint = "AND conversations.status = '" . self::escapeString($chatStatus) . "'";
        }
        
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT conversations.id
                           FROM conversations
                           LEFT JOIN assignments ON (conversations.id = assignments.conversations_id)
                           WHERE assignments.users_id = " . (int)$userID . "
                               $constraint
                           ORDER BY conversations.date_created ASC";
        
        return self::getBySql($options);
    }
}