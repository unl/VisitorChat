<?php
namespace UNL\VisitorChat\Chatbot;

class RecordList extends \Epoch\RecordList
{
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\Chatbot\Record';
        $options['listClass'] = '\UNL\VisitorChat\Chatbot\RecordList';

        return $options;
    }

    public static function getSiteChatbots($site_url, $options = array())
    {
        $urlParts = parse_url($site_url);
        $url = $urlParts['scheme'] . '://'. $urlParts['host'] . '/';
        //Build the list
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT c.id
                            FROM chatbots c INNER JOIN site_chatbot sc ON c.id = sc.chatbot_id
                            WHERE sc.site_url = '" . self::escapeString($url) ."'
                            AND sc.active = 1 AND c.active = 1";
        return self::getBySql($options);
    }
}