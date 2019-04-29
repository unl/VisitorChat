<?php
namespace UNL\VisitorChat\Invitation;
class RecordList extends \Epoch\RecordList
{
    function __construct($options = array())
    {
        $options['returnArray'] = true;
        
        parent::__construct($options);
    }

    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\Invitation\Record';
        $options['listClass'] = '\UNL\VisitorChat\Invitation\RecordList';
        
        return $options;
    }
    
    public static function getAllSearchingForConversation($conversationID, $options = array())
    {
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM invitations
                           WHERE conversations_id = " . (int)$conversationID . "
                           AND status = 'SEARCHING'
                           ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
    
    public static function getAllForConversation($conversationID, $options = array())
    {
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM invitations
                           WHERE conversations_id = " . (int)$conversationID . "
                           ORDER BY date_created DESC";
        
        return self::getBySql($options);
    }
}