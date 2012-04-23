<?php
namespace UNL\VisitorChat\OperatorRegistry;

abstract class SiteInterface
{
    /**
     * Get the support email for this site.
     * 
     * @return string
     */
    abstract function getEmail();

    /**
     * Get the site members
     * 
     * @return ArrayIterator
     */
    abstract function getMembers();
    
    /**
     * Get the title of the site.
     * 
     * @return string
     */
    abstract function getTitle();
    
    /**
     * Determins the number of operators for this site that are current available.
     * 
     * @return int the number of operators currently available
     */
    function getAvailableCount()
    {
        $count = 0;
        
        foreach ($this->getMembers() as $member) {
            if ($member->getRole() == 'other') {
                continue;
            }
            
            if (!$user = $member->getAccount()) {
                continue;
            }
            
            if ($user->status != 'AVAILABLE') {
                continue;
            }
            
            $count++;
        }
        
        return $count;
    }
}