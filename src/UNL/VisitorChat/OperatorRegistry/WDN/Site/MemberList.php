<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN\Site;

class MemberList extends \ArrayIterator implements \UNL\VisitorChat\OperatorRegistry\SiteMembersIterator
{
    public $site;
    
    function __construct($site, $members)
    {
        $this->site = $site;
        
        parent::__construct(new \ArrayIterator($members));
    }
    
    function current() {
        $data = parent::current();
        return new Member(parent::key(), $data['roles'], $this->site);
    }
}