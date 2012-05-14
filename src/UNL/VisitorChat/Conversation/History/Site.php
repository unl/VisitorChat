<?php
namespace UNL\VisitorChat\Conversation\History;
class Site extends \UNL\VisitorChat\Conversation\RecordList
{
    public $url;
    
    function __construct($options = array())
    {
        if (!isset($options['site_url'])) {
            throw new \Exception('no site url given', 400);
        }
        
        $this->url = $options['site_url'];
        
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \Exception('not a valid url', 400);
        }
        
        //Check if the current user has permission to view the site.
        $canView = false;
        foreach (\UNL\VisitorChat\User\Service::getCurrentUser()->getManagedSites() as $site) {
            if ($site->getURL() == $this->url) {
                $canView = true;
            }
        }
        
        if (!$canView) {
            throw new \Exception("You do not have permission to view this site.", 400);
        }
        
        $options['returnArray'] = true;
        
        $options['limit'] = 30;
        
        $options['array'] = self::getConversationsForSite($this->url, $options);
        
        parent::__construct($options);
    }
    
    function getPagerURL()
    {
        return \UNL\VisitorChat\Controller::$URLService->generateSiteURL('history/site/' . $this->url);
    }
}