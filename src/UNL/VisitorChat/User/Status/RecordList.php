<?php
namespace UNL\VisitorChat\User\Status;

class RecordList extends \Epoch\RecordList
{
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\User\Status\Record';
        $options['listClass'] = '\UNL\VisitorChat\User\Status\RecordList';

        return $options;
    }

    public static function getAllForUsersBetweenDates($userIDs, $start = false, $end = false, $options = array())
    {
        //Find everything by default
        $where = "1";
        
        //Refine search by date
        if ($start && $end) {
            $where = "date_created BETWEEN '" . self::escapeString($start) . "' AND '" . self::escapeString($end) . "'";
        } else if ($end) {
            $where = "date_created < '" . self::escapeString($end) . "'";
        } else if ($start) {
            $where = "date_created > '" . self::escapeString($start) . "'";
        }
        
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM user_statuses
                           WHERE " . $where . "
                           AND (false ";
                           
                           foreach($userIDs as $id) {
                               $options['sql'] .= "OR users_id = " . (int)$id . " ";
                           }
                           
        $options['sql'] .= ") ORDER BY date_created ASC";

        return self::getBySql($options);
    }
}