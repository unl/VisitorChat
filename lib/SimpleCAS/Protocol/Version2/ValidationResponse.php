<?php
class SimpleCAS_Protocol_Version2_ValidationResponse
{
    protected $authenticationSuccess = false;
    protected $user    = false;
    protected $pgtiou  = false;
    protected $proxies = array();

    /**
     * Construct a validation repsonse object from the CAS server's response.
     * 
     * @param string $response
     */
    function __construct($response)
    {
        $xml = new DOMDocument();
        if ($xml->loadXML($response)) {
            if ($success = $xml->getElementsByTagName('authenticationSuccess')) {
                if ($success->length > 0
                    && $uid = $success->item(0)->getElementsByTagName('user')) {
                    // We have the user name, check for PGTIOU
                    if ($iou = $success->item(0)->getElementsByTagName('proxyGrantingTicket')) {
                        if ($iou->length) {
                            $this->pgtiou = $iou->item(0)->nodeValue;
                        }
                    }
                    $this->authenticationSuccess = true;
                    $this->user = $uid->item(0)->nodeValue;
                }
            }
        }
    }
    
    function authenticationSuccess()
    {
        return $this->authenticationSuccess;
    }
    
    function getPGTIOU()
    {
        return $this->pgtiou;
    }
    
    function getUser()
    {
        return $this->userid;
    }

    function __toString()
    {
        if ($this->authenticationSuccess()) {
            return $this->user;
        }
        throw new Exception('Validation was not successful');
    }
    
    function logout()
    {
        
    }
}
?>