<?php
namespace UNL\VisitorChat\BlockedIP;
class RecordList extends \Epoch\RecordList
{
    function __construct($options = array())
    {
        if (!isset($options['model']) || $options['model'] != 'UNL\VisitorChat\BlockedIP\RecordList') {
            parent::__construct($options);
            return;
        }

        \UNL\VisitorChat\Controller::requireOperatorLogin();

        $options['returnArray'] = true;
        
        if (isset($options['ip_address'])) {
            $options['array'] = self::getALLActive($options);
        } else {
            //Return a list of all currently active blocks
        }

        parent::__construct($options);
    }
    
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\BlockedIP\Record';
        $options['listClass'] = '\UNL\VisitorChat\BlockedIP\RecordList';

        return $options;
    }

    public static function getAllForIP($ip, $options = array())
    {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT blocked_ips.id
                           FROM blocked_ips
                           WHERE blocked_ips.ip_address = '" . self::escapeString($ip) . "'
                           ORDER BY blocked_ips.date_created ASC";

        return self::getBySql($options);
    }

    public static function getAllEnabledForIP($ip, $options = array())
    {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT blocked_ips.id
                           FROM blocked_ips
                           WHERE blocked_ips.ip_address = '" . self::escapeString($ip) . "'
                              AND blocked_ips.status = 'ENABLED'
                           ORDER BY blocked_ips.date_created ASC";

        return self::getBySql($options);
    }

    public static function getAllActive($options = array())
    {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT blocked_ips.id
                           FROM blocked_ips
                           WHERE NOW() BETWEEN blocked_ips.start_date and blocked_ips.end_date
                               AND blocked_ips.status = 'ENABLED'
                           ORDER BY blocked_ips.date_created ASC";

        return self::getBySql($options);
    }
}