<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN\Site;

class MemberList extends \ArrayIterator implements \UNL\VisitorChat\OperatorRegistry\SiteMembersIterator
{
    function __construct($members)
    {
        parent::__construct(new \ArrayIterator($members));
    }
}