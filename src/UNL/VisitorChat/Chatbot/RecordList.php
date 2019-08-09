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
        $scheme = isset($urlParts['scheme']) ? $urlParts['scheme'] : '';
        $host = isset($urlParts['host']) ? $urlParts['host'] : '';
        $url = $scheme . '://'. $host . '/';

        // Skip lookup if domain not whitelisted for chatbot
        if (!in_array($host, \UNL\VisitorChat\Controller::$allowedChatbotDomains)) {
            return array();
        }

        //Build the list
        $options = $options + (new RecordList)->getDefaultOptions();
        $options['sql'] = "SELECT c.id
                            FROM chatbots c INNER JOIN site_chatbot sc ON c.id = sc.chatbot_id
                            WHERE sc.site_url = '" . self::escapeString($url) ."'
                            AND sc.active = 1 AND c.active = 1";
        return self::getBySql($options);
    }
}