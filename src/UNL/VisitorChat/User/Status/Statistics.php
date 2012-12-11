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
        $changes[0]['start']                   = strtotime($start) * 1000;
        $changes[0]['total']                   = $total;
        $changes['total_time_online']          = 0;
        $changes['total_time_online_business'] = 0;
        $changes['total_time']                 = 0;
        $changes['percent_online']             = 0;
        $changes['percent_online_business']    = 0;
        
        //Get everything between the dates
        $i = 1;
        
        foreach (RecordList::getAllForUsersBetweenDates($userIDs, $start, $end) as $status) {
            //Don't display status changes for new users (otherwise we would be subtracting 1 from the total and skewing the results).
            if ($status->reason == "NEW_USER") {
                continue;
            }
            
            if ($status->status == 'AVAILABLE') {
                //Add to the total available.
                $total++;
            } else if ($total > 0) {
                //We may or may not have all the history in the db, so this MAY dip below 0. Don't allow that.
                $total--;
            }
            
            $changes[$i-1]['end']  = strtotime($status->date_created) * 1000;
            $changes[$i]['start']  = strtotime($status->date_created) * 1000;
            $changes[$i]['total']  = $total;
            $changes[$i]['user']   = $status->getUser()->name;
            $changes[$i]['reason'] = $status->reason;
            $changes[$i]['status'] = $status->status;

            $i++;
        }

        $changes[$i-1]['end'] = strtotime($end);
        
        if (strtotime($end) > time()) {
            $changes[$i-1]['end'] = time();
        } 
        
        $changes[$i-1]['end'] = $changes[$i-1]['end'] * 1000;
        
        //Calculate percents and total times.
        
        $changes['total_time'] = strtotime($end) - strtotime($start);
        
        //Add total time online.
        foreach ($changes as $change) {
            if ($change['total'] > 0) {
                $changes['total_time_online'] += ($change['end']/1000 - ($change['start']/1000));
                
                $tmpStart = new \DateTime(date("r", $change['start']/1000));
                $tmpEnd   = new \DateTime(date("r", $change['end']/1000));
                
                if ($tmpStart->format("N") > 0 && $tmpStart->format("N") < 6
                    || $tmpEnd->format("N") > 0 && $tmpEnd->format("N") < 6) {
                    
                    if ($tmpStart->format("N") > 5) {
                        $diff = $tmpStart->format("N") - 5;
                        $tmpStart->modify("-$diff day");
                    }

                    if ($tmpStart->format("G") < 8) {
                        $tmpStart->setTime(8, 0);
                    }

                    if ($tmpEnd->format("N") > 5) {
                        $diff = $tmpStart->format("N") - 5;
                        $tmpEnd->modify("-$diff day");
                    }
                    
                    if ($tmpEnd->format("G") > 17) {
                        $tmpEnd->setTime(17, 0);
                    }

                    $changes['total_time_online_business'] += ($tmpEnd->getTimestamp() - $tmpStart->getTimestamp());
                }
            }
        }
        
        if ($changes['total_time'] > 0) {
            $changes['percent_online'] = round(($changes['total_time_online'] / $changes['total_time']), 2);
        }
        
        $totalDays = $changes['total_time'] / 86400;
        $totalBusinessSeconds = $totalDays * 28800;
        
        $changes['percent_online_business'] = round($changes['total_time_online_business'] / $totalBusinessSeconds, 2);
        
        return $changes;
    }
}