<?php
namespace UNL\VisitorChat\Message;

class View extends \UNL\VisitorChat\Message\Record
{
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::requireClientLogin();
        
        parent::__construct($options);
    }
    
    //Override the parent getByID, so that when called on this object, it returns a view object instead of a record.
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Message\View', 'id', (int)$id);
    }
}