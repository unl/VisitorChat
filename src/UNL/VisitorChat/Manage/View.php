<?php
namespace UNL\VisitorChat\Manage;

class View
{
    public $conversations;
    
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::requireOperatorLogin();
        
        $this->conversations = \UNL\VisitorChat\Conversation\RecordList::getOpenConversations(\UNL\VisitorChat\User\Service::getCurrentUser()->id);
    }
}