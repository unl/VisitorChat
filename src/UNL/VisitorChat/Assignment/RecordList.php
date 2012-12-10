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

    public static function getPendingAssignmentsForInvitation($invitationId, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE status = 'PENDING'
                               AND invitations_id = " . (int)$invitationId . "
                           ORDER BY date_created ASC";

        return self::getBySql($options);
    }
    
    public static function getAcceptedForConversation($conversationID, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE status = 'ACCEPTED'
                               AND conversations_id = " . (int)$conversationID . "
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
    
    public static function getAssignmentsForSite($url = false, $start = false, $end = false, $status = false, $options = array())
    {
        $options = $options + self::getDefaultOptions();
        
        //Build sql
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE true ";
        if ($url) {
            $options['sql'] .= "AND answering_site = '" . self::escapeString($url) . "' ";
        }

        if ($start && $end) {
            $options['sql'] .= "AND date_created BETWEEN '" . self::escapeString($start) . "' AND '" . self::escapeString($end) . "' ";
        } else if ($start) {
            $options['sql'] .= "AND date_created > '" . self::escapeString($start) . "' ";
        } else if ($end) {
            $options['sql'] .= "AND date_created < '" . self::escapeString($end) . "' ";
        }

        if ($status) {
            $options['sql'] .= "AND status = '" . self::escapeString($status) . "' ";
        }
        
        $options['sql'] .= "ORDER BY date_created ASC";
        
        return self::getBySql($options);
    }
    
    public static function getAllPendingAndExpired($options = array())
    {
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM assignments
                           WHERE NOW() >= (assignments.date_created + INTERVAL " . (int)(\UNL\VisitorChat\Controller::$chatRequestTimeout / 1000)  . " SECOND)
                                 AND assignments.status = 'PENDING'";

        return self::getBySql($options);
    }
}