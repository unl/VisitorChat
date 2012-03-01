<?php
class UNL_Peoplefinder_Driver_WebService implements UNL_Peoplefinder_DriverInterface
{
    public $service_url = 'http://peoplefinder.unl.edu/service.php';
    
    function getExactMatches($query)
    {
        $results = file_get_contents($this->service_url.'?q='.urlencode($query).'&format=php&method=getExactMatches');
        if ($results) {
            $results = unserialize($results);
        }
        return $results;
    }
    function getAdvancedSearchMatches($sn, $cn, $eppa)
    {
        throw new Exception('Not implemented yet');
    }
    function getLikeMatches($query)
    {
        $results = file_get_contents($this->service_url.'?q='.urlencode($query).'&format=php&method=getLikeMatches');
        if ($results) {
            $results = unserialize($results);
        }
        return $results;
    }
    function getPhoneMatches($query)
    {
        throw new Exception('Not implemented yet');
    }
    
    function getUID($uid)
    {
        $record = file_get_contents($this->service_url.'?uid='.urlencode($uid).'&format=php');
        if ($record) {
            $record = unserialize($record);
        }
        return $record;
    }
}
?>