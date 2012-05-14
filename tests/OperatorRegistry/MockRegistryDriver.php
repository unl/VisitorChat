<?php

use UNL\VisitorChat, UNL\VisitorChat\OperatorRegistry;

class MockRegistryDriver implements OperatorRegistry\DriverInterface
{
    function getSitesByURL($site)
    {
        return new MockSites();
    }

    function getSitesForUser($member)
    {
        return new MockSites();
    }

    function getAllSites()
    {
        return new MockSites();
    }
}

class MockSiteMembers extends ArrayIterator implements OperatorRegistry\SiteMembersIterator
{
    protected $url;
    
    function __construct($url, $members)
    {
        $this->url = $url;
        parent::__construct($members);
    }
    
    function current() {
        $data = parent::current();
        return new MockSiteMember(parent::key(), $data['roles'], $this->url);
    }
}

class MockSiteMember extends OperatorRegistry\SiteMemberInterface
{
    protected $uid;
    protected $url;
    protected $roles;

    function __construct($uid, $roles, $url)
    {
        $this->uid   = $uid;
        $this->site  = $url;
        $this->roles = $roles;
    }

    function getUID()
    {
        return $this->uid;
    }

    function getSite()
    {
        return $this->site;
    }

    function getRole()
    {
        return $this->roles[0];
    }
    
    function getEmail()
    {
        return 'email@unl.com';
    }
}

class MockSite extends OperatorRegistry\SiteInterface
{
    protected $url;
    protected $data;
    
    function __construct($url, $data)
    {
        $this->url = $url;
        $this->data = $data;
    }
    
    function getEmail()
    {
        return $this->data['support_email'];
    }
    
    function getTitle()
    {
        return $this->data['title'];
    }
    
    function getMembers()
    {
        return new MockSiteMembers($this->url, $this->data['members']);
    }
}

class MockSites extends ArrayIterator implements OperatorRegistry\SitesIterator
{
    function __construct()
    {
        parent::__construct(new ArrayIterator(array('http://www.unl.edu/' => array('support_email'=>'support@unl.edu', 
                                                                                   'title'=>'UNL',
                                                                                   'members'=>array('bbieber2'=>array('roles' => array('operator')),
                                                                                                    's-mfairch4'=>array('roles' => array('manager')))))));
    }
    
    function current() {
        return new MockSite(parent::key(),  parent::current());
    }
}