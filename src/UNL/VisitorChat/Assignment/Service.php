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
        //Determin if we need to handle assignments. If they are currently chatting however, we will check to see if their operator left and assign them a new one.
        if (in_array($conversation->status, array('EMAILED', 'CLOSED', 'OPERATOR_LOOKUP_FAILED'))) {
            //We don't need to continue.
            return true;
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
            if (in_array($assignment->status, array('ACCEPTED', 'PENDING', 'COMPLETED'))) {
                $currentOperators = true;
                break;
            }
        }
        
        //Find another operator if the current one left.
        if (!$currentOperators) {
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
            
            //Save here so that if multiple requests come in a REALLY short time, only one email is sent.
            $conversation->save();
            
            //Try to send an email to the team.
            if (\UNL\VisitorChat\Conversation\FallbackEmail::sendConversation($conversation)) {
                $conversation->status  = "EMAILED";
                $conversation->emailed = 1;
            }
        }
        
        return $conversation->save();
    }
    
    /**
     * Finds an avaiable operator from a set of operators and a conversation.
     * 
     * @param array $operators
     * @param \UNL\VisitorChat\Conversation\Record $conversation
     * 
     * @return mixed (string $id, false if failed)
     */
    public static function findAvaiableOperatorForConversation($operators, \UNL\VisitorChat\Conversation\Record $conversation)
    {
        //If there are no operators assigned to this site, bail out now.
        if (empty($operators)) {
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
        
        return $row['id'];
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
        
        //Get a list of sites associated with this url
        $sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($conversation->initial_url);
        
        //Loop though those sites until am avaiable member can be found.
        foreach ($sites as $site) {
            $operators = array();
            
            //Loop though each member and add it to the operators array.
            foreach ($site->getMembers() as $member) {
                if ($member->getRole() != 'other') {
                    $operators[] = $member->getUID();
                }
            }
            
            //Break out of the loop once we find someone.
            if ($operatorID = self::findAvaiableOperatorForConversation($operators, $conversation)) {
                continue;
            }
        }
        
        //Try to find an avaiable operator though other channels as a last resort.
        if (!$operatorID) {
            //No one was found, look at the default operators.
            $operators = \UNL\VisitorChat\Controller::$defaultOperators;
            
            $operatorID = self::findAvaiableOperatorForConversation($operators, $conversation);
        }
        
        if (!$operatorID) {
            //Couldn't find anyone
            return false;
        }
        
        //Create a new assignment.
        return \UNL\VisitorChat\Assignment\Record::createNewAssignment($operatorID, $conversation->id);
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