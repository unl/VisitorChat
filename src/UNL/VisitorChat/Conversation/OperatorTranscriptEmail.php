<?php
namespace UNL\VisitorChat\Conversation;

class OperatorTranscriptEmail extends Email
{
    public function __construct(Record $conversation, array $to = array(), $fromId = 1, array $options = array())
    {
        parent::__construct($conversation, $to, $fromId, $options);
        $this->setReplyTo();
        $this->subject = 'Operator transcript for UNLchat';
    }
}
