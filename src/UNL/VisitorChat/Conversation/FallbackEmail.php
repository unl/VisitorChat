<?php
namespace UNL\VisitorChat\Conversation;

class FallbackEmail extends Email
{
    function __construct(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $fromId = 1, $options = array())
    {
        parent::__construct($conversation, $to, $fromId, $options);

        //Set the reply to
        $this->setReplyTo();
    }
}