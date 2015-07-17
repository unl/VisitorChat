<?php
/**
 * This script will clean up old idle conversations with a chat method of 'EMAIL' that have not been finished (are stuck on the 'searching')
 * status.  In order for these emails to be sent and the conversations to be closed, at least 1 \UNL\VisitorChat\Conversation\Email::$fallbackEmails email address must be defined.
 */

if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

\UNL\VisitorChat\Controller::$environment = "CLI";
$app = new \UNL\VisitorChat\Controller();

foreach (\UNL\VisitorChat\Conversation\RecordList::getAllSearchingEmailConversations() as $conversation) {
    $service = new \UNL\VisitorChat\Invitation\Service();
    $result = $service->handleInvitations($conversation);
    $conversation =  \UNL\VisitorChat\Conversation\Record::getByID($conversation->id);
    echo $conversation->id . ": new status - " . $conversation->status . PHP_EOL;
}