<?php
namespace UNL\VisitorChat\Sites\Site;

class Statistics
{
    public $url;
    
    public $site;
    
    public $start;
    
    public $end;
    
    function __construct($options = array())
    {
        $this->url = urldecode($options['url']);

        $this->site = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($this->url);

        if (!$this->site) {
            throw new \Exception('Sorry, that site was not found.', 400);
        }
        
        $this->start = date("Y-m-d", strtotime("-1 month"));
        if (isset($options['start']) && !empty($options['start'])) {
            $this->start = $options['start'];
        }

        $this->end = date("Y-m-d", time());
        if (isset($options['end'])  && !empty($options['end'])) {
            $this->end = $options['end'];
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

        return $statistics->getStats($userIDs, $this->start, $this->end);
    }
    
    function getURL()
    {
        return \UNL\VisitorChat\Controller::$url . "sites/statistics?url=" . urlencode($this->url);
    }
}