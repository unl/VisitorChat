<?php
namespace UNL\VisitorChat\User;

class Logout
{
    function __construct() {
        $user = Record::getCurrentUser();
        
        if (!$user) {
            // Short-circuit, no one is logged in
            return;
        }
        
        if ($conversation = $user->getConversation()) {
            $conversation->close();
        }
        
        $user->status = "BUSY";
        $user->save();
        
        session_destroy();
        
        if ($user && $user->type == 'operator') {
            //Leave all current open chats.
            foreach (\UNL\VisitorChat\Assignment\RecordList::getAcceptedAssignmentsForUser($user->id) as $assignment) {
                $assignment->markAsLeft();
            }
            
            $auth = \UNL_Auth::factory('SimpleCAS');
            $auth->logout();
        }
    }
}