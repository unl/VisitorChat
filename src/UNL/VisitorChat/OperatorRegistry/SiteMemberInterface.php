<?php
namespace UNL\VisitorChat\OperatorRegistry;

abstract class SiteMemberInterface
{
    /**
     * Get the user's role for this site
     * 
     * @return string
     */
    abstract function getRole();

    /**
     * Get the site
     * 
     * @return string
     */
    abstract function getSite();

    /**
     * Get the member's unique ID.
     * 
     * @return string
     */
    abstract function getUID();
    
    /**
     * Get the email of the member.
     * 
     * @return mixed (string if exisits, false if no email provided).
     */
    abstract function getEmail();
    
    /**
     * Retrieves the visitorchat account for this member.
     * 
     * @return \UNL\VisitorChat\User\Record the account associated with the member's uid.
     */
    function getAccount()
    {
        return \UNL\VisitorChat\User\Record::getByUID($this->getUID());
    }
}