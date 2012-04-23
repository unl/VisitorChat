<?php 
namespace UNL\VisitorChat\User;

class SiteList
{
    public $sites = array();
    
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::requireOperatorLogin();
        
        $user = \UNL\VisitorChat\User\Record::getCurrentUser();
        
        foreach (\UNL\VisitorChat\Controller::$registryService->getSitesForUser($user->uid) as $site) {
            $this->sites[$site->getURL()]['title']           = $site->getTitle();
            $this->sites[$site->getURL()]['total_available'] = $site->getAvailableCount();
        }
    }
}