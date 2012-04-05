<?php
/**
 * Standard driver for an operator registry
 * 
 * The operator registry is responsible for returning information about who
 * serves a specific web page.
 * s
 * @author bbieber
 *
 */
namespace UNL\VisitorChat\OperatorRegistry;

interface DriverInterface
{

    /**
     * Get the list of members for a site
     * 
     * @param string $site
     * @param string $type One of: operator, manager
     * 
     * @return SiteMembersIterator
     */
    function getMembers($site, $type = null);

    /**
     * Get the list of sites the user is a member of
     * @param string $user
     * @param string $type One of: operator, manager
     * 
     * @return SitesIterator
     */
    function getSites($user, $type = null);

    /**
     * Get the email address(es) for a specific site
     * 
     * @param string $site
     * 
     * @return string Email address
     */
    function getEmail($site);
}