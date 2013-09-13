<?php
namespace UNL\VisitorChat\Conversation\Email;
class RecordList extends \Epoch\RecordList
{
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\Conversation\Email\Record';
        $options['listClass'] = '\UNL\VisitorChat\Conversation\Email\RecordList';

        return $options;
    }
    
    public static function getAllEmailsForConversation($conversationID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id FROM emails WHERE conversations_id = " . (int)$conversationID  . " ORDER BY date_created ASC";
        return self::getBySql($options);
    }
}