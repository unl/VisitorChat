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
     * Note: The system expects to recieve a list of sites that relate to the given url.
     * The first result should be the closest registered site, and each result thereafter
     * should continue to get further away from the requested url.  This is so that
     * when finding an operator to assign, the assignment will fallback on the closest 
     * related site first if no one closer can be found.
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