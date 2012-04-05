<?php
namespace UNL\VisitorChat\OperatorRegistry;

interface SiteMemberInterface
{
    /**
     * Get the user's role for this site
     * 
     * @return string
     */
    function getRole();

    /**
     * Get the site
     * 
     * @return string
     */
    function getSite();

    /**
     * Get the member
     * 
     * @return string
     */
    function getMember();
}