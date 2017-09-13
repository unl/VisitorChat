<?php
namespace UNL\VisitorChat\Conversation;

use UNL\VisitorChat\Controller;

class FallbackEmail extends Email
{
    function __construct(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $fromId = 1, $options = array())
    {
        parent::__construct($conversation, $to, $fromId, $options);

        $site_title = '';
        if ($sites = Controller::$registryService->getSitesByURL($conversation->initial_url)) {
            $site = $sites->current();
            $site_title = $site->getTitle();
        }

        $this->subject = 'Email from your website: ' . $site_title . ' (' . $conversation->id . ')';

        //Set the reply to
        $this->setReplyTo();
        
        foreach ($this->messages as $message) {
            if ($message->hasTitle9Words()) {
                $this->isUrgent();
                $this->setCC(Controller::$title9Emails);
                
                //No need to check again
                break;
            }
        }
    }
}
