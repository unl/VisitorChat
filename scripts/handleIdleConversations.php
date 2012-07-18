<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

foreach (\UNL\VisitorChat\Conversation\RecordList::getAllIdleConversations() as $conversation) {
    $conversation->idle();
}