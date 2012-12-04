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
        
        $this->site = $this->site->current();
        
        \UNL\VisitorChat\Controller::$pagetitle = "Site Details: " . $this->site->getTitle();
    }
}