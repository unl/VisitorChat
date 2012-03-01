<?php
namespace UNL\VisitorChat\Assignment;

class Service
{
    public static function rejectAllExpiredRequests()
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        $sql = "UPDATE assignments
                LEFT JOIN conversations ON (assignments.conversations_id = conversations.id)
                SET assignments.status = 'EXPIRED', conversations.status = 'SEARCHING'
                WHERE NOW() >= (assignments.date_created + INTERVAL " . (int)(\UNL\VisitorChat\Controller::$chatRequestTimeout / 1000)  . " SECOND)
                    AND assignments.status = 'PENDING'";
        
        if ($db->query($sql)) {
            return true;
        }
        
        return false;
    }
}