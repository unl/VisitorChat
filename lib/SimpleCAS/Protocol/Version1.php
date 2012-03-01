<?php
/**
 * Class representing a CAS server which supports the CAS1 protocol.
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
class SimpleCAS_Protocol_Version1 extends SimpleCAS_Protocol
{
    const VERSION = '1.0';
    
    protected $request;
    
    /**
     * Construct a new SimpleCAS server object.
     *
     *  <code>
     *  $options = array('hostname' => 'login.unl.edu',
     *                   'port'     => 443,
     *                   'uri'      => 'cas');
     *  $protocol = new SimpleCAS_Protocol_Version1($options);
     *  </code>
     *
     * @param array()
     */
    function __construct($options)
    {
        foreach ($options as $option=>$val) {
            $this->$option = $val;
        }
    }
    
    /**
     * Returns the URL used to validate a ticket.
     *
     * @param string $ticket  Ticket to validate
     * @param string $service URL to the service requesting authentication
     * 
     * @return string
     */
    function getValidationURL($ticket, $service)
    {
        return 'https://' . $this->hostname . '/'
                          . $this->uri . '/validate?'
                          . 'service=' . urlencode($service)
                          . '&ticket=' . $ticket;
    }
    
    /**
     * Returns the URL to login form for the CAS server.
     *
     * @param string $service Service url requesting authentication.
     * 
     * @return string
     */
    function getLoginURL($service)
    {
        return 'https://' . $this->hostname
                          . '/'.$this->uri
                          . '/login?service='
                          . urlencode($service);
    }
    
    /**
     * Returns the URL to logout of the CAS server.
     *
     * @param string $service Service url provided to the user.
     * 
     * @return string
     */
    function getLogoutURL($service = '')
    {
        if (isset($service)) {
            $service = '?url='.urlencode($service);
        }
        
        return 'https://' . $this->hostname
                          . '/'.$this->uri
                          . '/logout'
                          . $service;
    }
    
    /**
     * Function to validate a ticket and service combination.
     *
     * @param string $ticket  Ticket given by the CAS Server
     * @param string $service Service requesting authentication
     * 
     * @return false|string False on failure, user name on success.
     */
    function validateTicket($ticket, $service)
    {
        $validation_url = $this->getValidationURL($ticket, $service);
        
        $http_request = clone $this->getRequest();
        
        $defaultClass = SimpleCAS_Protocol::DEFAULT_REQUEST_CLASS;
        if ($http_request instanceof $defaultClass) {
            $http_request->setURL($validation_url);
            
            $response = $http_request->send();
        } else {
            $http_request->setUri($validation_url);
            
            $response = $http_request->request();
        }
        
        
        if ($response->getStatus() == 200
            && substr($response->getBody(), 0, 3) == 'yes') {
            list($message, $uid) = explode("\n", $response->getBody());
            return $uid;
        }
        return false;
    }
    
    /**
     * Returns the CAS server protocol this object implements.
     *
     * @return string
     */
    function getVersion()
    {
        return self::VERSION;
    }
}
?>