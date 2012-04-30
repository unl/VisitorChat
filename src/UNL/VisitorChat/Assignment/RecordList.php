<?php
namespace UNL\VisitorChat\Assignment;
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
        $options['itemClass'] = '\UNL\VisitorChat\Assignment\Record';
        $options['listClass'] = '\UNL\VisitorChat\Assignment\RecordList';
        
        return $options;
    }
    
    public static function getAllAssignmentsForConversation($conversationID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE conversations_id = " . (int)$conversationID . "
                           ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
    
    public static function getAllAssignmentsForInvitation($invitationID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE invitations_id = " . (int)$invitationID . "
                           ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
    
    public static function getAcceptedAndCompletedAssignmentsForConversation($conversationID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE conversations_id = " . (int)$conversationID . "
                               AND (status = 'ACCEPTED'
                               OR status = 'COMPLETED')
                           ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
    
    public static function getPendingAssignmentsForUser($userID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE status = 'PENDING'
                               AND users_id = " . (int)$userID . "
                           ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
    
    public static function getAcceptedAssignmentsForUser($userID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE status = 'ACCEPTED'
                               AND users_id = " . (int)$userID . "
                           ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
}