<?php
/**
 * Interface for servers that implement single sign out.
 * 
 * PHP version 5
 * 
 * @category  Authentication 
 * @package   SimpleCAS
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://code.google.com/p/simplecas/
 */
interface SimpleCAS_SingleSignOut
{
    /**
     * Determines if the posted request is a valid single sign out request.
     *
     * @param mixed $post $_POST data sent to the service.
     * 
     * @return bool
     */
    function validateLogoutRequest($post);
}