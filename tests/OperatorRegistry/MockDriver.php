<?php

use UNL\VisitorChat, UNL\VisitorChat\OperatorRegistry;

class MockDriver implements OperatorRegistry\DriverInterface
{
    function getMembers($site, $type = null)
    {
        return new MockSiteMembers();
    }

    function getSites($member, $type = null)
    {
        return new MockSites();
    }

    function getEmail($site)
    {
        return 'me@example.com';
    }
}

class MockSiteMembers extends ArrayIterator implements OperatorRegistry\SiteMembersIterator
{
    function __construct()
    {
        parent::__construct(new ArrayIterator(array('bbieber2', 'manager2')));
    }
    
    function current()
    {
        return new MockSiteMember(parent::current());
    }
}

class MockSiteMember implements OperatorRegistry\SiteMemberInterface
{
    protected $uid;

    function __construct($uid)
    {
        $this->uid = $uid;
    }

    function getUID()
    {
        return $this->uid;
    }

    function getSite()
    {
        return 'http://www.unl.edu/';
    }

    function getRole()
    {
        return 'manager';
    }
}

class MockSites extends ArrayIterator implements OperatorRegistry\SitesIterator
{
    function __construct()
    {
        parent::__construct(new ArrayIterator(array('http://www.unl.edu/')));
    }
}