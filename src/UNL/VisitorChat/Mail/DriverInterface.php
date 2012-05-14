<?php
/**
 * Standard driver for sending an email
 * 
 * @author mfairchild365
 *
 */
namespace UNL\VisitorChat\Mail;

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
    function send($to, $hdrs, $body);
}