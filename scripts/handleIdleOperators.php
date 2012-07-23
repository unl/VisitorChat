<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

$db = \UNL\VisitorChat\Controller::getDB();

//Set all operators who are currently AVAILABLE but have not had activity for atleast 10min to busy.
$sql = "UPDATE users SET status = 'BUSY', status_reason = 'SERVER_IDLE' WHERE users.last_active < ADDTIME(now(), '-00:10:00') AND users.status = 'AVAILABLE'";

$db->query($sql);