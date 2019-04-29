<?php
namespace UNL\VisitorChat\Message;
class RecordList extends \Epoch\RecordList
{
    function __construct($options = array())
    {
        parent::__construct($options);
    }
    
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\Message\Record';
        $options['listClass'] = '\UNL\VisitorChat\Message\RecordList';
        
        return $options;
    }
    
    public static function getAllMessagesForConversation($conversationID, $options = array())
    {
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT id FROM messages WHERE conversations_id = " . (int)$conversationID  . " ORDER BY date_created ASC";
        return self::getBySql($options);
    }
    
    public static function getMessagesAfterTime($conversationID, $time, $options = array()) {
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT id FROM messages WHERE conversations_id = " . (int)$conversationID . " AND date_created > " . (int)$time . " ORDER BY date_created ASC";
        return self::getBySql($options);
    }
    
    public static function getMessagesAfterIDForConversation($conversationID, $messageID, $options = array()) {
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT id FROM messages WHERE conversations_id = " . (int)$conversationID . " AND id > " . (int)$messageID . " ORDER BY date_created ASC";
        return self::getBySql($options);
    }
}