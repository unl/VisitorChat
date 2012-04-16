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
     * Get the member's unique ID.
     * 
     * @return string
     */
    function getUID();
    
    /**
     * Get the email of the member.
     * 
     * @return mixed (string if exisits, false if no email provided).
     */
    function getEmail();
}