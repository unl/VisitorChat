<?php
namespace UNL\VisitorChat\Conversation\History;
class Site extends \UNL\VisitorChat\Conversation\RecordList
{
    public $url;
    
    public $user;
    
    function __construct($options = array())
    {
        \UNL\VisitorChat\Controller::$pagetitle = "Site history";
        
        //require that an operator is logged in.
        \UNL\VisitorChat\Controller::requireOperatorLogin();

        if (!isset($options['url'])) {
            throw new \Exception('no site url given', 400);
        }
        
        $this->url = $options['url'];
        
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \Exception('not a valid url', 400);
        }
        
        if (isset($options['users_id'])) {
            if (!$this->user = \UNL\VisitorChat\User\Record::getByID($options['users_id'])) {
                throw new \Exception('could not find that user', 404);
            }
        }

        if ($this->user) {
            \UNL\VisitorChat\Controller::$pagetitle = htmlentities($this->user->uid, ENT_QUOTES, "UTF-8") . "'s history for " . $this->url;
        } else {
            \UNL\VisitorChat\Controller::$pagetitle = "Site history for " . $this->url;
        }
        
        //Check if the current user has permission to view the site.
        $canView = false;
        if (\UNL\VisitorChat\User\Service::getCurrentUser()->managesSite($this->url)) {
            $canView = true;
        }
        
        if (\UNL\VisitorChat\User\Service::getCurrentUser()->isAdmin()) {
            $canView = true;
        }
        
        if (!$canView) {
            throw new \Exception("You do not have permission to view this site.", 400);
        }
        
        $options['returnArray'] = true;
        
        $options['limit'] = 30;

        if ($this->user) {
            $options['array'] = self::getConversationsForSiteAndUser($this->url, $this->user->id, $options);
        } else {
            $options['array'] = self::getConversationsForSite($this->url, $options);
        }
        
        parent::__construct($options);
    }
    
    function getPagerURL()
    {
        $url = 'sites/history?url=' . urlencode($this->url);
        
        if ($this->user) {
            $url .= '&users_id=' . $this->user->id;
        }
        
        return \UNL\VisitorChat\Controller::$URLService->generateSiteURL($url);
    }
}