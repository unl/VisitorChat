<?php
namespace UNL\VisitorChat\User;

class RecordList extends \Epoch\RecordList
{
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\User\Record';
        $options['listClass'] = '\UNL\VisitorChat\User\RecordList';

        return $options;
    }

    public static function getAllOperators($options = array())
    {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM users
                           WHERE type = 'operator'";

        return self::getBySql($options);
    }

    public static function getIdleOperators($options = array())
    {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT id
                           FROM users
                           WHERE type = 'operator'
                               AND last_active < ADDTIME(now(), '-00:10:00')
                               AND status = 'AVAILABLE'";
echo $options['sql']; exit();
        return self::getBySql($options);
    }
}