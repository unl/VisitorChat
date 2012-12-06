<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

foreach (\UNL\VisitorChat\User\RecordList::getIdleOperators() as $operator) {
    $operator->setStatus('BUSY', 'SERVER_IDLE');
}