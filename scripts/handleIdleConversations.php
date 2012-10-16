<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

//We are running in CLI here.
\UNL\VisitorChat\Controller::$environment = "CLI";

//Set up the controller.
$controller = new \UNL\VisitorChat\Controller();

foreach (\UNL\VisitorChat\Conversation\RecordList::getAllConversationsWithStatus('chatting') as $conversation) {
    $conversation->idle();
}