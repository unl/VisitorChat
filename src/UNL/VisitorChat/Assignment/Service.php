<?php
namespace UNL\VisitorChat\Assignment;

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
    public static function handleAssignments(\UNL\VisitorChat\Conversation\Record $conversation)
    {
        //Check if there are no current operators.
        $currentOperators = false;
        foreach(\UNL\VisitorChat\Assignment\RecordList::getAllAssignmentsForConversation($conversation->id) as $assignment)
        {
            //If we are currently talking or if the assignment is pending, don't  find another operator.
            if ($assignment->status == 'ACCEPTED' 
               || $assignment->status == 'PENDING'
               || $assignment->status == 'COMPLETED') {
                $currentOperators = true;
                break;
            }
        }
        
        //Find another operator if the current one left.
        if (!$currentOperators && !$conversation->emailed) {
            $conversation->status = 'SEARCHING';
            $conversation->save();
        }
        
        //Do we need to assign an operator?
        if ($conversation->status !== 'SEARCHING') {
            return true;
        }
        
        //Try to assign an operator.
        if (self::assignOperator($conversation)) {
            $conversation->status = "OPERATOR_PENDING_APPROVAL";
        } else {
            //Failed to assign an operator.
            $conversation->status = "OPERATOR_LOOKUP_FAILED";
            
            //Try to send an email to the team.
            if ($conversation->email()) {
                $conversation->status = "EMAILED";
            }
        }
        
        return $conversation->save();
    }
    
    /* Finds an online operator and assigns them to this chat.
     * 
     * Follows these requirements:
     * 1.Operator must have atleast 1 slot open.
     * 2.Operator must must be assigned to the initial url.
     * 3.Operator must be listed as avaiable
     * 4.Operator must not have already been assigned to this conversation.
     * 
     * @return bool
     */
    public static function assignOperator(\UNL\VisitorChat\Conversation\Record $conversation)
    {
        if ($conversation->initial_url == NULL) {
            return false;
        }

        //Get a list of operators for this site.
        $operators = \UNL\VisitorChat\Site\Members::getMembersByTypeAndSite('operator', $conversation->initial_url);

        //If there are no operators assigned to this site, bail out now.
        if ($operators->count() == 0) {
          return false;
        }
        
        $db = \UNL\VisitorChat\Controller::getDB();
        
        //Generate SQL
        $sql = "SELECT users1.id FROM users as users1
                    LEFT JOIN assignments as assignments ON (users1.id = assignments.users_id)
                    WHERE users1.status = 'AVAILABLE'
                    /* Only grab people who have an open chat slot. */
                        AND (SELECT COUNT(assignments.id)
                                   FROM assignments
                                   LEFT JOIN conversations conv1 ON (conv1.id = assignments.conversations_id)
                                   WHERE assignments.users_id = users1.id
                                         AND conv1.status <> 'CLOSED'
                                         AND assignments.status = 'ACCEPTED')
                            < users1.max_chats
                        /* Make sure we are not sending a request to the same person twice. */
                        AND (SELECT COUNT(assignments.id)
                                   FROM assignments
                                   LEFT JOIN conversations conv1 ON (conv1.id = assignments.conversations_id)
                                   WHERE assignments.users_id = users1.id
                                         AND assignments.conversations_id = " . (int)$conversation->id .")
                            = 0
                         AND (false";
        foreach ($operators as $operator) {
            $sql .= " OR users1.uid = '" . mysql_escape_string($operator) . "'";
        }
        
        $sql .= ") LIMIT 1";
        
        if (!$result = $db->query($sql)) {
            return false;
        }
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $row = $result->fetch_assoc();
        
        //Create a new assignment.
        return \UNL\VisitorChat\Assignment\Record::createNewAssignment($row['id'], $conversation->id);
    }
    
    public static function rejectAllExpiredRequests()
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        $sql = "UPDATE assignments
                LEFT JOIN conversations ON (assignments.conversations_id = conversations.id)
                SET assignments.status = 'EXPIRED', conversations.status = 'SEARCHING'
                WHERE NOW() >= (assignments.date_created + INTERVAL " . (int)(\UNL\VisitorChat\Controller::$chatRequestTimeout / 1000)  . " SECOND)
                    AND assignments.status = 'PENDING'";
        
        if ($db->query($sql)) {
            return true;
        }
        
        return false;
    }
}