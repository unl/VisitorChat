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
    
    
    function getStartDate()
    {
        return date("Y-m-d", strtotime("-1 day", strtotime($this->start)));
    }
    
    function getEndDate()
    {
        return date("Y-m-d", strtotime("+1 day", strtotime($this->end)));
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

        return $statistics->getStats($userIDs, $this->getStartDate(), $this->getEndDate());
    }
    
    function getAssignmentStats()
    {
        $answering_site = $this->site->getURL();
        $assignments = array();
        $assignments['completed'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'COMPLETED');
        $assignments['expired'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'EXPIRED');
        $assignments['rejected'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'REJECTED');
        $assignments['failed'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'FAILED');
        $assignments['left'] = \UNL\VisitorChat\Assignment\RecordList::getAssignmentsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'LEFT');

        $totalAssignments = 0;

        foreach ($assignments as $type=>$list) {
            $totalAssignments += $list->count();
        }
        
        $stats = array();
        
        $stats['total'] = $totalAssignments;
        
        $stats['assignment_types'] = array();
        
        foreach ($assignments as $type=>$list) {
            $stats['assignment_types'][$type] = $list->count() . "(" . round(($list->count()/$totalAssignments)*100) . "%)";
        }
        
        return $stats;
    }
    
    function getConversationStats()
    {
        $conversations = array();
        $conversations['answered']   = \UNL\VisitorChat\Conversation\RecordList::getCompletedConversationsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'ANSWERED');
        $conversations['unanswered'] =  \UNL\VisitorChat\Conversation\RecordList::getCompletedConversationsForSite($this->site->getURL(), $this->getStartDate(), $this->getEndDate(), 'UNANSWERED');

        $totalConversations = 0;

        foreach ($conversations as $type=>$list) {
            $totalConversations += $list->count();
        }

        $stats = array();

        $stats['total'] = $totalConversations;

        $stats['conversation_types'] = array();
        
        foreach ($conversations as $type=>$list) {

            $stats['conversation_types'][$type] = $list->count() . "(" . round(($list->count()/$totalConversations)*100) . "%)";
        }
        
        return $stats;
    }
    
    function getURL()
    {
        return \UNL\VisitorChat\Controller::$url . "sites/statistics?url=" . urlencode($this->url);
    }
}