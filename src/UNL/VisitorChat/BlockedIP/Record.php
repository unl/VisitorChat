<?php
namespace UNL\VisitorChat\BlockedIP;

class Record extends \Epoch\Record
{
    public $id;
    public $ip_address;
    public $users_id;
    public $block_start;
    public $block_end;
    public $status;
    public $date_created;

    /**
     * Returns a conversation record by ID.
     *
     * @param int $id
     * @return bool
     */
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\BlockedIP\Record', 'id', (int)$id);
    }

    /**
     * (non-PHPdoc)
     * @see Epoch.Record::keys()
     */
    function keys()
    {
        return array('id');
    }
    
    public function insert()
    {
        $this->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
        
        return parent::insert();
    }

    /**
     * The table name.
     * @return string $tablename
     */
    public static function getTable()
    {
        return 'blocked_ips';
    }
    
    public function getEditURL()
    {
        if (!$this->id) {
            return \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/edit");
        }
        
        return \UNL\VisitorChat\Controller::$URLService->generateSiteURL("blocks/" . $this->id . "/edit");
    }
    
    public function getTimeLength()
    {
        if (!isset($this->block_end, $this->block_start)) {
            return false;
        }

        $diff = strtotime($this->block_end) - strtotime($this->block_start);

        if ($this->getTimeUnit() == 'hours') {
            return (int)($diff/3600);
        }
        
        //Default to days
        return (int)($diff/86400);
    }
    
    public function getTimeUnit()
    {
        if (!isset($this->block_end, $this->block_start)) {
            return false;
        }
        
        $diff = strtotime($this->block_end) - strtotime($this->block_start);
        
        if ($diff >= 86400) {
            return 'days';
        }
        
        //Default to hours
        return 'hours';
    }
    
    public function initializeDefaultDates()
    {
        $count = 0;
        
        if ($this->ip_address) {
            $blocks = RecordList::getAllForIP($this->ip_address);
            $count = $blocks->count();
        }

        $this->block_start = \UNL\VisitorChat\Controller::epochToDateTime();
        
        switch ($count) {
            case 0:
                $this->block_end = \UNL\VisitorChat\Controller::epochToDateTime(strtotime('+1 hour'));
                break;
            case 1:
                $this->block_end = \UNL\VisitorChat\Controller::epochToDateTime(strtotime('+1 day'));
                break;
            case 2:
                $this->block_end = \UNL\VisitorChat\Controller::epochToDateTime(strtotime('+1 week'));
                break;
            case 3:
                $this->block_end = \UNL\VisitorChat\Controller::epochToDateTime(strtotime('+1 month'));
                break;
            default:
                $this->block_end = \UNL\VisitorChat\Controller::epochToDateTime(strtotime('+6 months'));
                break;
        }
    }
}