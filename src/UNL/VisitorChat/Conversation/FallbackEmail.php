<?php
namespace UNL\VisitorChat\Conversation;

class FallbackEmail extends Email
{
    function __construct(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $options = array())
    {
        parent::__construct($conversation, $to, $options);

        //Set the reply to
        $this->setReplyTo();
    }
}