<?php
namespace UNL\VisitorChat\Conversation\History;
class SiteList extends \ArrayIterator
{
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::requireOperatorLogin();
        
        $user = \UNL\VisitorChat\User\Record::getCurrentUser();
        
        parent:: __construct($user->getManagedSites());
    }
}