<?php
namespace UNL\VisitorChat\Conversation\History;
class User extends \UNL\VisitorChat\Conversation\RecordList
{
    function __construct($options = array())
    {
        //Require a login.
        \UNL\VisitorChat\Controller::requireLogin();

        $options['returnArray'] = true;
        
        $options['limit'] = 10;
        
        $options['array'] = self::getConversationsForUser(\UNL\VisitorChat\User\Service::getCurrentUser()->id, false, $options);
        
        parent::__construct($options);
    }
}