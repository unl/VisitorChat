<?php
if (file_exists(dirname(dirname(__FILE__)) . '/config.inc.php')) {
    require_once dirname(dirname(__FILE__)) . '/config.inc.php';
} else {
    require dirname(dirname(__FILE__)) . '/config.sample.php';
}

function exec_sql($db, $sql, $message, $fail_ok = false)
{
    echo $message.'&hellip;'.PHP_EOL;
    try {
        if ($db->multi_query($sql)) {
            do {
                /* store first result set */
                if ($result = $db->store_result()) {
                    $result->free();
                }
            } while ($db->next_result());
        }
    } catch (Exception $e) {
        if (!$fail_ok) {
            echo 'The query failed:'.$result->errorInfo();
            exit();
        }
    }
    echo 'finished.<br />'.PHP_EOL;
}

$db = \UNL\VisitorChat\Controller::getDB();

$sql = "";


if (isset($argv[1]) && $argv[1] == '-f') {
    echo "Deleting old install" . PHP_EOL;
    $sql .= "SET FOREIGN_KEY_CHECKS=0;
             DROP TABLE IF EXISTS users;
             DROP TABLE IF EXISTS conversations;
             DROP TABLE IF EXISTS invitations;
             DROP TABLE IF EXISTS messages;
             DROP TABLE IF EXISTS assignments;
             SET FOREIGN_KEY_CHECKS=1;";
}
$sql .= file_get_contents(dirname(dirname(__FILE__)) . "/data/database.sql");

exec_sql($db, $sql, 'updatating database');

exec_sql($db, file_get_contents(dirname(dirname(__FILE__)) . "/data/users.last_active.sql"), 'adding user.last_active');

//1. Check if the system user is installed.
if (!$systemUser = \UNL\VisitorChat\User\Record::getByID(1)) {
    $systemUser = new \UNL\VisitorChat\User\Record();
    $systemUser->name         = "System";
    $systemUser->email        = null;
    $systemUser->type         = "operator";
    $systemUser->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
    $systemUser->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
    $systemUser->status       = "BUSY";
    $systemUser->max_chats    = 0;
    $systemUser->save();
}