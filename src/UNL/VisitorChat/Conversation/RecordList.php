<?php
namespace UNL\VisitorChat\Conversation;
class RecordList extends \Epoch\RecordList
{
    function __construct($options = array())
    {
         if (!isset($options['model']) || $options['model'] != 'UNL\VisitorChat\Conversation\RecordList') {
            parent::__construct($options);
            return;
        }
        
        $options['returnArray'] = true;
        
        $options['array'] = self::getOpenConversations(\UNL\VisitorChat\User\Service::getCurrentUser()->id, $options);
        
        parent::__construct($options);
    }
    
    function getDefaultOptions()
    {
        $options = array();
        $options['itemClass'] = '\UNL\VisitorChat\Conversation\Record';
        $options['listClass'] = '\UNL\VisitorChat\Conversation\RecordList';
        
        return $options;
    }
    
    public static function getOpenConversations($userID, $options = array())
    {
        return self::getConversationsForUser($userID, 'CHATTING', $options);
    }
    
    public static function getConversationsForSite($url, $options = array())
    {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT conversations.id
                           FROM conversations
                           LEFT JOIN assignments ON (conversations.id = assignments.conversations_id)
                           WHERE assignments.answering_site LIKE '%" . self::escapeString($url) . "%'
                           GROUP BY conversations.id
                           ORDER BY conversations.date_created ASC";
        
        return self::getBySql($options);
    }

    /**
     * Gets a list of all idle conversations.  A conversation is considered idle based on the time of the last message posted
     * and the conversationTTL setting in the controller.
     *
     * @static
     * @param array $options
     * @return mixed
     */
    public static function getAllIdleConversations($options = array()) {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT conversations.id, message
                           FROM conversations
                           LEFT JOIN messages ON (conversations.id = messages.conversations_id)
                           WHERE (SELECT id FROM messages WHERE conversations_id = conversations.id ORDER BY date_created DESC LIMIT 1) = messages.id
                               AND conversations.method = 'CHAT'
                               AND messages.date_created < now() + INTERVAL -" . (int)\UNL\VisitorChat\Controller::$conversationTTL . " MINUTE
                               AND (conversations.status = 'CHATTING' OR conversations.status = 'SEARCHING')";

        return self::getBySql($options);
    }

    /**
     * Gets a list of all idle conversations.  A conversation is considered idle based on the time of the last message posted
     * and the conversationTTL setting in the controller.
     *
     * @static
     * @param array $options
     * @return mixed
     */
    public static function getAllSearchingEmailConversations($options = array()) {
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT conversations.id, message
                           FROM conversations
                           LEFT JOIN messages ON (conversations.id = messages.conversations_id)
                           WHERE (SELECT id FROM messages WHERE conversations_id = conversations.id ORDER BY date_created DESC LIMIT 1) = messages.id
                               AND conversations.method = 'EMAIL'
                               AND messages.date_created < now() + INTERVAL -" . (int)\UNL\VisitorChat\Controller::$conversationTTL . " MINUTE
                               AND (conversations.status = 'CHATTING' OR conversations.status = 'SEARCHING')";

        return self::getBySql($options);
    }
    
    public static function getConversationsForUser($userID, $chatStatus = false, $options = array())
    {
        //Build the chat status constraint.
        $constraint = "";
        if ($chatStatus) {
            $constraint = "AND conversations.status = '" . self::escapeString($chatStatus) . "'";
        }
        
        //Build the list
        $options = $options + self::getDefaultOptions();
        $options['sql'] = "SELECT conversations.id
                           FROM conversations
                           LEFT JOIN assignments ON (conversations.id = assignments.conversations_id)
                           WHERE assignments.users_id = " . (int)$userID . "
                               AND (assignments.status = 'ACCEPTED' OR assignments.status = 'COMPLETED')
                               $constraint
                           ORDER BY conversations.date_created ASC";
        
        return self::getBySql($options);
    }
}