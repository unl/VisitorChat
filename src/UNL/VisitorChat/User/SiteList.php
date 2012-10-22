<?php 
namespace UNL\VisitorChat\User;

class SiteList
{
    public $sites = array();
    
    function __construct($options = array())
    {
        if (!\UNL\VisitorChat\User\Service::getCurrentUser()) {
            throw new \Exception("You must be logged in to do this.", 401);
        }
        
        \UNL\VisitorChat\Controller::requireOperatorLogin();
        
        $user = \UNL\VisitorChat\User\Service::getCurrentUser();
        
        foreach (\UNL\VisitorChat\Controller::$registryService->getSitesForUser($user->uid) as $site) {
            $this->sites[$site->getURL()]['title']           = $site->getTitle();
            $this->sites[$site->getURL()]['total_available'] = $site->getAvailableCount();
        }
    }
}