<?php
namespace UNL\VisitorChat\Sites\Site;

class Statistics
{
    public $url;
    
    public $site;
    
    function __construct($options = array())
    {
        $this->url = urldecode($options['url']);

        $this->site = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($this->url);

        if (!$this->site) {
            throw new \Exception('Sorry, that site was not found.', 400);
        }

        $this->site = $this->site->current();

        \UNL\VisitorChat\Controller::$pagetitle = "Site Statistics: " . $this->site->getTitle();
    }

    function getStatusStatistics()
    {
        $sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($this->url);

        if (!$sites->count()) {
            return false;
        }

        $site = $sites->current();

        $userIDs = array();

        foreach ($site->getMembers() as $member) {
            $account = $member->getAccount();
            $userIDs[] = $account->id;
        }

        $statistics = new \UNL\VisitorChat\User\Status\Statistics();

        return $statistics->getStats($userIDs, '2012-12-03 10:18:31', false);
    }
}