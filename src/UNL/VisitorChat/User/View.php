<?php
namespace UNL\VisitorChat\User;

class View extends \UNL\VisitorChat\User\Record
{
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::$pagetitle = "User";

        \UNL\VisitorChat\Controller::requireOperatorLogin();

        if (isset($options['id']) && $object = \UNL\VisitorChat\User\Record::getByID($options['id'])) {
            $this->synchronizeWithArray($object->toArray());
        } else {
            $this->synchronizeWithArray(\UNL\VisitorChat\User\Service::getCurrentUser()->toArray());
        }

        \UNL\VisitorChat\Controller::$pagetitle = "User - " . $this->name;
    }

}