<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class SiteList extends \ArrayIterator implements \UNL\VisitorChat\OperatorRegistry\SitesIterator
{
    function __construct(array $sites)
    {
        parent::__construct($sites);
    }
    
    function current() {
        return new Site(parent::key(),  parent::current());
    }
}