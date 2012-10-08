--TEST--
APPLICATION TEST - SystemInvitation - fallback support
--FILE--
<?php
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/loadConfig.php';

$DBHelper->installDB("freshDB.sql");

$conversation = new \UNL\VisitorChat\Conversation\Record();
$conversation->initial_pagetitle = "Test";
$conversation->initial_url       = "http://www.test.com/test/";
$conversation->method            = "CHAT";
$conversation->users_id          = 6;
$conversation->status            = "CHATTING";

$conversation->save();


\UNL\VisitorChat\Invitation\Record::createNewInvitation($conversation->id, urlencode("http://www.test.com/test/"));

$invitationService = new \UNL\VisitorChat\Invitation\Service();

$invitationService->handleInvitations($conversation);

while (true) {
    foreach (\UNL\VisitorChat\Invitation\RecordList::getAllForConversation($conversation->id) as $invitation) {
        $assignments = \UNL\VisitorChat\Assignment\RecordList::getPendingAssignmentsForInvitation($invitation->id);

        if ($assignments->count() == 0) {
            //No pending assignments are left... 
            break 2;
        }

        foreach (\UNL\VisitorChat\Assignment\RecordList::getPendingAssignmentsForInvitation($invitation->id) as $assignment) {
            echo "Assignment Made for: " . $assignment->answering_site . PHP_EOL;
            $assignment->reject();
        }
    }

    $invitationService->handleInvitations($conversation);
}


?>
===DONE===
--EXPECT--
Assignment Made for: http://www.test.com/test/
Assignment Made for: http://www.test.com/test/
Assignment Made for: http://www.test.com/
Assignment Made for: http://www.test.com/
===DONE===