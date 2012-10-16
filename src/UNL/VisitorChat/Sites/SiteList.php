<?php
namespace UNL\VisitorChat\Sites;

class SiteList
{
    public $sites = array();

    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::$pagetitle = "My Sites";
        
        \UNL\VisitorChat\Controller::requireOperatorLogin();
        
        $user = \UNL\VisitorChat\User\Service::getCurrentUser();
        
        if ($user->isAdmin()) {
            $this->sites = \UNL\VisitorChat\Controller::$registryService->getAllSites();
        } else {
            $this->sites = \UNL\VisitorChat\Controller::$registryService->getSitesForUser($user->uid);
        }
        
    }
}