<?php
namespace UNL\VisitorChat\Conversation\Email;

class ConfirmationEmail
{
    public $conversation;

    function __construct($options = array())
    {
        //require a client login
        \UNL\VisitorChat\Controller::requireClientLogin();

        if (!isset($options['id'])) {
            throw new \Exception('Conversation id not provided', 400);
        }

        if (!$this->conversation = \UNL\VisitorChat\Conversation\Record::getByID($options['id'])) {
            throw new \Exception('Conversation could not be found', 400);
        }

        $user = \UNL\VisitorChat\User\Service::getCurrentUser();

        if (!in_array($user->id, $this->conversation->getInvolvedUsers())) {
            throw new \Exception('You do not have permission to send a confirmation email', 403);
        }
    }

    public function handlePost($post = array())
    {
        $user = \UNL\VisitorChat\User\Service::getCurrentUser();

        if (!isset($post['email'])) {
            throw new \Exception('No email address was provided', 400);
        }

        \UNL\VisitorChat\Conversation\ConfirmationEmail::sendConversation($this->conversation, $user->id, $post['email']);

        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("success", true, true));
    }
}