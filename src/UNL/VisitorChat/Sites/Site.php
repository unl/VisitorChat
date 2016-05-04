<?php
namespace UNL\VisitorChat\Sites;

class Site
{
    public $url = "";
    
    public $site;

    function __construct($options = array())
    {
        $this->url = urldecode($options['url']);

        $this->site = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($this->url);
        
        if (!$this->site) {
            throw new \Exception('Sorry, that site was not found.', 400);
        }
        
        $this->site = $this->site->current();
        
        \UNL\VisitorChat\Controller::$pagetitle = "Site Details: " . $this->site->getTitle();
    }

    /**
     * Determine if a given user manages this site
     *
     * @param \UNL\VisitorChat\User\Record $user
     * @return bool
     */
    public function userManagesSite(\UNL\VisitorChat\User\Record $user)
    {
        return $user->managesSite($this->url);
    }
}