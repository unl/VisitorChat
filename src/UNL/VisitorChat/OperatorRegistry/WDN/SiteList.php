<?php
namespace UNL\VisitorChat\OperatorRegistry\WDN;

class SiteList extends \ArrayIterator implements \UNL\VisitorChat\OperatorRegistry\SitesIterator
{
    function __construct($sites)
    {
        parent::__construct(new \ArrayIterator($sites));
    }
    
    function current() {
        return new Site(parent::key(),  parent::current());
    }
}