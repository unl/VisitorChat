<?php
namespace UNL\VisitorChat\Assignment;

class Service
{
    /**
     * Finds an avaiable operator from a set of operators and a conversation.
     * 
     * @param array $operators
     * @param \UNL\VisitorChat\Invitation\Record $invitation
     * 
     * @return mixed (string $id, false if failed)
     */
    function findAvaiableOperatorForInvitation($operators, \UNL\VisitorChat\Invitation\Record $invitation)
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
                        /* Make sure they are not already assigned */
                        AND (SELECT COUNT(assignments.id)
                                   FROM assignments
                                   WHERE assignments.users_id = users1.id
                                         AND assignments.status = 'ACCEPTED'
                                         AND assignments.conversations_id = " . (int)$invitation->conversations_id .")
                            = 0
                        /* Make sure we are not sending a request to the same person twice. */
                        AND (SELECT COUNT(assignments.id)
                                   FROM assignments
                                   LEFT JOIN conversations conv1 ON (conv1.id = assignments.conversations_id)
                                   WHERE assignments.users_id = users1.id
                                         AND assignments.invitations_id = " . (int)$invitation->id .")
                            = 0
                         AND (false";
        foreach ($operators as $operator) {
            $sql .= " OR users1.uid = '" . mysql_escape_string($operator) . "'";
        }
        
        $sql .= ") GROUP BY users1.uid";
        
        if (!$result = $db->query($sql)) {
            return false;
        }
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $operators = array();
        
        while ($row = $result->fetch_assoc()) {
            $operators[] = $row['id'];
        }
        
        //Select a random operator
        return $operators[rand(0,count($operators)-1)];
    }
    
    /* Finds an online operator and assigns them to this a chat/invitation.
     * 
     * Follows these requirements:
     * 1.Operator must have atleast 1 slot open.
     * 2.Operator must must be assigned to the initial url.
     * 3.Operator must be listed as avaiable
     * 4.Operator must not have already been assigned to this invitation.
     * 
     * @return bool
     */
    function assignOperator(\UNL\VisitorChat\Invitation\Record $invitation)
    {
        if ($invitation->isForSite()) {
            //search for a url
            if (!$operator = $this->findAvaiableOperatorForURL($invitation->getSiteURL(), $invitation)) {
                return false;
            }
        } else if ($to = $invitation->getAccountUID()) {
            //get a specific operator
            if (!$operator = $this->findAvaiableOperatorForInvitation(array($to), $invitation)) {
                return false;
            }
            
            //We expect to proceed with an array containing an operatorID and the responding site.
            $operator = array('operatorID'=>$operator, 'site'=>$invitation->invitee);
        } else {
            return false;
        }
        
        //Create a new assignment.
        return \UNL\VisitorChat\Assignment\Record::createNewAssignment($operator['operatorID'], $operator['site'], $invitation->conversations_id, $invitation->id);
    }
    
    function findAvaiableOperatorForURL($url, $invitation) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        //Get a list of sites associated with this url
        $sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($url);
        
        //Loop though those sites until am avaiable member can be found.
        $totalSearched = 0;
        foreach ($sites as $site) {
            //For personal assignments, do not fall back. (only allow system assignments to fall back).
            if ($totalSearched == 1 && $invitation->users_id !== 1) {
                return false;
            }
            
            $operators = $this->generateOperatorsArrayForSite($site);
            
            //Break out of the loop once we find someone.
            if ($operatorID = $this->findAvaiableOperatorForInvitation($operators, $invitation)) {
                return array('operatorID'=>$operatorID, 'site'=> $site->getURL());
            }

            $totalSearched++;
        }
        
        //Try to find an avaiable operator though other channels as a last resort.
        foreach (\UNL\VisitorChat\Controller::$fallbackURLs as $url) {
            $sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($url);
            
            //Loop though those sites until am avaiable member can be found.
            foreach ($sites as $site) {
                $operators = $this->generateOperatorsArrayForSite($site);
                
                //Break out of the loop once we find someone.
                if ($operatorID = $this->findAvaiableOperatorForInvitation($operators, $invitation)) {
                    return array('operatorID'=>$operatorID, 'site'=> $site->getURL());
                }
            }
        }
        
        return false;
    }
    
    function generateOperatorsArrayForSite($site)
    {
        $operators = array();
        
        //Loop though each member and add it to the operators array.
        foreach ($site->getMembers() as $member) {
            if ($member->getRole() != 'other') {
                $operators[] = $member->getUID();
            }
        }
        
        return $operators;
    }
    
    function rejectAllExpiredRequests()
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        $sql = "UPDATE assignments
                LEFT JOIN (conversations, users)
                ON (assignments.conversations_id = conversations.id AND users.id = assignments.users_id)
                SET assignments.status = 'EXPIRED',
                    users.status = 'BUSY',
                    users.status_reason = 'EXPIRED_REQUEST',
                    conversations.status = IF(conversations.status <> 'CHATTING', 'SEARCHING', 'CHATTING'),
                    assignments.date_finished = '" . \Epoch\RecordList::escapeString(\UNL\VisitorChat\Controller::epochToDateTime()) . "'
                WHERE NOW() >= (assignments.date_created + INTERVAL " . (int)(\UNL\VisitorChat\Controller::$chatRequestTimeout / 1000)  . " SECOND)
                    AND assignments.status = 'PENDING'";

        if ($db->query($sql)) {
            return true;
        }
        
        return false;
    }
}
