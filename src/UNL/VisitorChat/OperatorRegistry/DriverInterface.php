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
     * 
     * @return SitesIterator
     */
    function getSitesByURL($site);

    /**
     * Get the list of sites the user is a member of
     * 
     * @param string $user
     * 
     * @return SitesIterator
     */
    function getSitesForUser($user);

    /**
     * Get all of the sites in the registry
     * 
     * @return SitesIterator
     */
    function getAllSites();
}