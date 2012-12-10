<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

/**
 * Script is used to init user status fields during the middle of system operation.
 * 
 * In order to get an accurate snap shot of site availability, we need to set all
 * operators as busy, then remove all history.
 */

//Set everyone to BUSY
foreach (\UNL\VisitorChat\User\RecordList::getAllOperators() as $operator) {
    $status = $operator->getStatus();
    
    if ($status->status = 'AVAILABLE') {
        $operator->setStatus('BUSY', 'MAINTENANCE');
    }
}

$db = \UNL\VisitorChat\Controller::getDB();

//Remove all current user statuses
$sql = "truncate user_statuses";

$db->query($sql);