<?php
namespace UNL\VisitorChat\OperatorRegistry;

interface SiteInterface
{
    /**
     * Get the support email for this site.
     * 
     * @return string
     */
    function getEmail();

    /**
     * Get the site members
     * 
     * @return ArrayIterator
     */
    function getMembers();
    
    /**
     * Get the title of the site.
     * 
     * @return string
     */
    function getTitle();
}