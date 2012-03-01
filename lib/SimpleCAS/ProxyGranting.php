<?php
/**
 * Interface for servers that implement proxy granting tickets.
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
interface SimpleCAS_ProxyGranting
{
    
    /**
     * get a proxy ticket
     *
     * @return string
     */
    function getProxyTicket();
    
    /**
     * try and validate a proxy ticket
     *
     * @param unknown_type $ticket
     */
    function validateProxyTicket($ticket);
}
?>