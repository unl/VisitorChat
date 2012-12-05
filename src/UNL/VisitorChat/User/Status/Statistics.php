<?php
namespace UNL\VisitorChat\User\Status;

class Statistics
{
    /**
     * Get the total number of available users at a given time
     * 
     * In order to figure this out, we have to look at all the record BEFORE the given time.
     * 
     * @param $userIDs
     * @param $time
     * 
     * @return int
     */
    public function getTotalAvailableAtTime($userIDs, $time)
    {
        $total = 0;
        
        //Get everything before the start date.
        foreach (RecordList::getAllForUsersBetweenDates($userIDs, false, $time) as $status) {
            if ($status->status == 'AVAILABLE') {
                //Add to the total available.
                $total++;
            } else if ($total > 0) {
                //We may or may not have all the history in the db, so this MAY dip below 0. Don't allow that.
                $total--;
            }
        }
        
        return $total;
    }
    
    public function getStats($userIDs, $start = false, $end = false)
    {
        if (!$start) {
            $start = "2010-01-01 0:0:0";
        }

        if (!$end) {
            $end = \UNL\VisitorChat\Controller::epochToDateTime();
        }
        
        $total = $this->getTotalAvailableAtTime($userIDs, $start);
        
        $changes = array();
        
        //Give total at the start.
        $changes[0]['start'] = strtotime($start) * 1000;
        $changes[0]['total'] = $total;
        
        //Get everything between the dates
        $i = 1;
        
        foreach (RecordList::getAllForUsersBetweenDates($userIDs, $start, $end) as $status) {
            if ($status->status == 'AVAILABLE') {
                //Add to the total available.
                $total++;
            } else if ($total > 0) {
                //We may or may not have all the history in the db, so this MAY dip below 0. Don't allow that.
                $total--;
            }

            $changes[$i-1]['end'] = strtotime($status->date_created) * 1000;
            $changes[$i]['start'] = strtotime($status->date_created) * 1000;
            $changes[$i]['total'] = $total;
            
            $i++;
        }
        
        $changes[$i-1]['end'] = strtotime($end) * 1000;
        
        return $changes;
    }
}