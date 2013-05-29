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
        \UNL\VisitorChat\Controller::$pagetitle = "Blocked IP addresses";
        
        \UNL\VisitorChat\Controller::requireOperatorLogin();

        $options['returnArray'] = true;

        $options['array'] = self::getByOptions($options);

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
    
    public static function getByOptions($options = array())
    {
        //Build the list
        $options = $options + array(
                'state' => 'active'
            );
        
        $options = $options + self::getDefaultOptions();
        
        $options['sql'] = "SELECT blocked_ips.id
                           FROM blocked_ips";
        
        switch ($options['state']) {
            case 'active':
                $options['sql'] .= " WHERE NOW() BETWEEN blocked_ips.block_start and blocked_ips.block_end
                                        AND blocked_ips.status = 'ENABLED'";
                break;
            case 'inactive':
                $options['sql'] .= " WHERE NOW() NOT BETWEEN blocked_ips.block_start and blocked_ips.block_end
                                        OR  blocked_ips.status = 'DISABLED'";
                break;
            case 'all':
                break;
        }

        $options['sql'] .= " ORDER BY blocked_ips.date_created ASC";

        return self::getBySql($options);
    }
}