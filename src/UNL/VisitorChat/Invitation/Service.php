<?php
namespace UNL\VisitorChat\Invitation;

class Service
{
    /**
     * Handles the current assignments for this conversation.
     * 
     * It first checks to see if we need to assign an operator, then checks if there are no operators
     * left to check, and then falls back to email
     * 
     * Note: if the current operator logs out while chatting, this will look for another operator.
     * 
     * @return bool
     */
    function handleInvitations(\UNL\VisitorChat\Conversation\Record $conversation)
    {
        //Determin if we need to handle assignments. If they are currently chatting however, we will check to see if their operator left and assign them a new one.
        if (in_array($conversation->status, array('EMAILED', 'CLOSED', 'OPERATOR_LOOKUP_FAILED'))) {
            //We don't need to continue.
            return true;
        }
        
        //Only update a maximum of 1 time every second to save resources.
        if (strtotime($conversation->date_updated) == time() && $conversation->date_updated != $conversation->date_created) {
            return true;
        } else {
            //Continue, but update the last update time so that other requests will stop.
            $conversation->date_updated = \UNL\VisitorChat\Controller::epochToDateTime(time());
            $conversation->save();
        }
        
        //Are we communicating via email?
        if ($conversation->method == 'EMAIL') {
            //Send an email if it wasn't already sent.
            if (\UNL\VisitorChat\Conversation\FallbackEmail::sendConversation($conversation)) {
                $conversation->status = "EMAILED";
                $conversation->save();
            }
            return true;
        }
        
        //Check if there are no current operators.
        $currentOperators = false;
        foreach(\UNL\VisitorChat\Assignment\RecordList::getAllAssignmentsForConversation($conversation->id) as $assignment)
        {
            //If we are currently talking or if the assignment is pending, don't  find another operator.
            if (in_array($assignment->status, array('ACCEPTED', 'PENDING'))) {
                $currentOperators = true;
                break;
            }
        }
        
        //Find another operator if the current one left.
        if (!$currentOperators && $conversation->status == 'CHATTING') {
            $conversation->status = 'SEARCHING';
            //$conversation->save();
        }
        
        //Get the last invitation was auto created by the system
        $latest = \UNL\VisitorChat\Invitation\Record::getLatestForConversation($conversation->id);
        $autoinvite = true;
        if ($latest && $latest->users_id == 1) {
            $autoinvite = false;
        }
        
        //Create a new invitation if there are no operators, we are searching and the last invitation was not created by the system.
        if (!$currentOperators && $conversation->status == 'SEARCHING' && $autoinvite) {
            Record::createNewInvitation($conversation->id, $conversation->initial_url);
        }
        
        //Load the assignment service.
        $assignmentService = new \UNL\VisitorChat\Assignment\Service();
        
        //Loop over current searching invitations and assign operators based on those inviations.
        foreach (\UNL\VisitorChat\Invitation\RecordList::getAllSearchingForConversation($conversation->id) as $invitation) {
            //Get the latest assignment for this invitation
            $assignment = \UNL\VisitorChat\Assignment\Record::getLatestForInvitation($invitation->id);
            
            //Don't try to assign if we are currently pening.
            if (is_object($assignment) && $assignment->status == "PENDING") {
                continue;
            }
            
            //If it was accepted, close this invitation
            if (is_object($assignment) && $assignment->status == "ACCEPTED") {
                $invitation->complete();
                continue;
            }
            
            //Try to create a new assignment.
            if (!$assignmentResult = $assignmentService->assignOperator($invitation)) {
                $invitation->fail();
            }
            
            //Update the conversation status if needed.
            if ($conversation->status == "SEARCHING" && $assignmentResult) {
                $conversation->status = "OPERATOR_PENDING_APPROVAL";
                $conversation->save();
            }
        }
        
        return true;
    }
}