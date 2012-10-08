--TEST--
APPLICATION TEST - OperatorInvitation - no fallback test
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

$invitationService = new \UNL\VisitorChat\Invitation\Service();
$assignmentService = new \UNL\VisitorChat\Assignment\Service();

//Someone has to be in the conversation to invite another operator.
\UNL\VisitorChat\Invitation\Record::createNewInvitation($conversation->id, urlencode("http://www.test.com/test/"), 1);

foreach (\UNL\VisitorChat\Invitation\RecordList::getAllSearchingForConversation($conversation->id) as $invitation) {
    $assignmentService->assignOperator($invitation, 2);
    $invitation->complete();
}

//Go on with inviting someone.
\UNL\VisitorChat\Invitation\Record::createNewInvitation($conversation->id, urlencode("http://www.test.com/test/") . "::OP2", 2);


$invitationService->handleInvitations($conversation);

while (true) {
    $invitations = \UNL\VisitorChat\Invitation\RecordList::getAllSearchingForConversation($conversation->id);
    
    //If there are no invitations, break free.
    if ($invitations->count() == 0) {
        //No pending assignments are left... 
        break;
    }
    
    foreach ($invitations as $invitation) {
        $assignments = \UNL\VisitorChat\Assignment\RecordList::getPendingAssignmentsForInvitation($invitation->id);
        
        if ($assignments->count() == 0) {
            //No pending assignments are left... 
            break 2;
        }
        
        foreach (\UNL\VisitorChat\Assignment\RecordList::getPendingAssignmentsForInvitation($invitation->id) as $assignment) {
            echo "User ID: " . $assignment->users_id . PHP_EOL;
            $assignment->reject();
        }
    }

    $invitationService->handleInvitations($conversation);
}

?>
===DONE===
--EXPECT--
User ID: 3
===DONE===
