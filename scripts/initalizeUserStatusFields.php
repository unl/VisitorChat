<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

/**
 * Script is used to init user status fields during the middle of system operation.
 * 
 * In order to get an accurate snap shot of site availability, we need to find out who
 * is online at any given moment and track any status changes.  If people are online when
 * we start to track status changes, and their status isn't in the user_statuses table, we
 * may get an inaccurate count.
 */

$db = \UNL\VisitorChat\Controller::getDB();

//Remove all current user statuses
$sql = "truncate user_statuses";

$db->query($sql);

foreach (\UNL\VisitorChat\User\RecordList::getAllOperators() as $operator) {
    $status = $operator->getStatus();
    
    $newStatus = new \UNL\VisitorChat\User\Status\Record();
    $newStatus->users_id = $operator->id;
    $newStatus->setStatus($status->status, $status->reason);
    $newStatus->save();
}