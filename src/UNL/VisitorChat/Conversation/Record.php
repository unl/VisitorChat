<?php
namespace UNL\VisitorChat\Conversation;

class Record extends \Epoch\Record
{
    //The id of the current conversation
    public $id;
    
    //The client user's id.
    public $users_id;
    
    //The date the chat was created.
    public $date_created;
    
    //The date the chat was first updated.
    public $date_updated;
    
    //The date the chat was closed.
    public $date_closed;
    
    //The initial url of the chat.
    public $initial_url;
    
    public $emailed;  //Was an email sent for a fallback?
    
    /* Does the client want a response via email if
     * we can't find an available operator
     * 
     * 0 or null = no.
     * 1 = yes.
     */
    public $email_fallback; 
    
    /* The Current status of the conversation.  Possible values include:
     * SEARCHING                 : Start the searching loop. 
     * OPERATOR_PENDING_APPROVAL : Waiting on an operator to approve or reject the assignment.
     * OPERATOR_LOOKUP_FAILED    : Could not find an avaiable operator.
     * CHATTING                  : Currently chatting with an operator.
     * EMAILED                   : The chat was Emailed.
     * CLOSED                    : The chat was closed.
     */
    public $status;
    
    /* The chosen method of communication.
     * Either CHAT or EMAIL
     */
    public $method;
    
    /**
     * Returns a conversation record by ID.
     * 
     * @param int $id
     */
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Conversation\Record', 'id', (int)$id);
    }
    
    /**
     * Updates the date_updated field to the current time upon saving.
     * @see Epoch.Record::save()
     */
    function save()
    {
        $this->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();;
        return parent::save();
    }
    
    /**
     * (non-PHPdoc)
     * @see Epoch.Record::keys()
     */
    function keys()
    {
        return array('id');
    }
    
    /**
     * The table name for the conversation record.
     * @return string $tablename
     */
    public static function getTable()
    {
        return 'conversations';
    }
    
    /**
     * returns the edit url for a conversation.
     * 
     * @return string url
     */
    function getEditURL()
    {
        return \UNL\VisitorChat\Controller::$url . "message/edit";
    }
    
    /**
     * This function will email the latest message to the given email address
     * or, if none are given, it will email to all the members of the site team
     * the latest message in the conversation.
     * 
     * TODO: remove this function and replace with an email class to allow for 
     * templating.  The new class should allow for the entire conversation to be 
     * sent via email.
     * 
     * @param array $to
     */
    function email($to = array()) {
        //Check to see if we need to get the site members
        if (empty($to)) {
            $to = \UNL\VisitorChat\Site\Members::getMembersByTypeAndSite('all', $this->initial_url);
        }
        
        //can we send to anyone?
        if (!is_array($to) && $to->count() == 0) {
            //Nope.  Don't send emails, return false.
            return false;
        }
        
        $to_address = "";
        
        foreach ($to as $person) {
            $person = new \UNL\VisitorChat\Site\Member($person);
            if ($mail = $person->getDefaultEmail()) {
                $to_address .= $mail . ", ";
            }
        }
        
        /* Edge case.  If a site contains only students as team members or people who
         * do not have their email address public, we will have no one to send an email to.
         * And the default operators will not be selected at this point.
         * 
         * So, we need to determin if there are team members, but none of them have email addresses.
         * If that is the case, we need to email our default operators.  This is ugly, I don't like it.
         */
        if (empty($to_address)) {
            foreach (\UNL\VisitorChat\Controller::$defaultOperators as $person) {
                $person = new \UNL\VisitorChat\Site\Member($person);
                if ($mail = $person->getDefaultEmail()) {
                    $to_address .= $mail . ", ";
                }
            }
        }
        
        if (empty($to_address)) {
            // Could not find anyone to email
            return false;
        }

        $to_address = trim($to_address, ", ");
        
        $client = $this->getClient();
        
        if (\Validate::email($client->email)) {
            $from = $client->email;
        } else {
            $from = 'unlwdn@gmail.com';
        }
        
        $from = 'unlwdn@gmail.com';
        
        $response = "";
        if ($this->email_fallback) {
            $response = "The user requests a response <br />";
        }
        
        $html = "<html>" .
                "<body bgcolor='#ffffff'>" .
                    "<p>" .
                        "A comment has been submitted on " . $this->date_created .
                    "</p>" .
                    "<p>" .
                        "Comment from " . $client->name . "<br />
                        IP: " . $client->ip . "<br />
                        on " . $this->initial_url . "<br />" . $response .
                    "</p>" .
                    "<p>" . nl2br($this->getLastMessage()->message) . "</p>" .
                "</body>".
            "</html>";
        $crlf = "\n";
        
        $hdrs = array(
          'From'     => 'unlwdn@gmail.com',
          'Reply-To' => $from,
          'To'       => $to_address,
          'Subject'  => 'UNL VisitorChat System ' . $this->id);
        
        $mime = new \Mail_mime($crlf);
        $mime->setHTMLBody($html);
        
        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);
        $mail =& \Mail::factory('sendmail');
        $mail->send($to_address, $hdrs, $body);
        
        $this->emailed = true;
        $this->save();
        
        return true;
    }
    
    /**
     * Returns the lastest conversation record for a given client.
     * 
     * @param int $userID
     */
    public static function getLatestForClient($userID)
    {
        $db = \Epoch\Controller::getDB();
        
        if (!$result = $db->query("SELECT * from conversations where id = (select max(id) from conversations WHERE users_id = " . (int)$userID . ") LIMIT 1")) {
            return false;
        }
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $record = new self();
        
        $record->synchronizeWithArray($result->fetch_assoc());
        
        return $record;
    }
    
    /**
     * Returns the client record associated with this conversation.
     * 
     * @return \UNL\VisitorChat\User\Record
     */
    function getClient()
    {
        return \UNL\VisitorChat\User\Record::getByID($this->users_id);
    }
    
    /**
     * returns a list of messages for thsi conversation after a time.
     * 
     * @param string $time
     * @param array $options
     * 
     * @return \UNL\VisitorChat\Message\RecordList
     */
    function getMessagesSinceTime($time, $options = array())
    {
        return \UNL\VisitorChat\Message\RecordList::getMessagesAfterTime($this->id, $time, $options);
    }
    
    /**
     * retrieves the latest message in the chat.
     * 
     * @return \UNL\VisitorChat\Message\Record
     */
    function getLastMessage()
    {
        $db = \UNL\VisitorChat\Controller::getDB();
        $sql = "SELECT id FROM messages WHERE conversations_id = " . (int)$this->id . " ORDER BY date_created DESC LIMIT 1";
        
        if (!$result = $db->query($sql)) {
            return false;
        }
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $result = $result->fetch_assoc();
        
        return \UNL\VisitorChat\Message\Record::getByID($result['id']);
    }
    
    /**
     * retrieves a list of all messages since the last update. (date the 
     * client was last updated in the database)
     * 
     * @param int $userID
     * @param array $options
     * 
     * @return \UNL\VisitorChat\Message\RecordList
     */
    function getMessagesSinceLastUpdate($userID, $options = array()) {
        $time = \UNL\VisitorChat\User\Record::getByID($userID)->date_updated;
        
        return \UNL\VisitorChat\Message\RecordList::getMessagesAfterTime($this->id, $time, $options);
    }
    
    /**
     * retrieves all messages for this conversation.
     * 
     * @param array $options
     * 
     * @return \UNL\VisitorChat\Message\RecordList
     */
    function getMessages($options = array())
    {
        return \UNL\VisitorChat\Message\RecordList::getAllMessagesForConversation($this->id, $options);
    }
    
    /**
     * (non-PHPdoc)
     * @see Epoch.Record::insert()
     */
    function insert()
    {
        $this->date_created = \UNL\VisitorChat\Controller::epochToDateTime();;
        return parent::insert();
    }
    
    /**
     * Update the date_updated time for this record.
     * 
     * @return null
     */
    function ping()
    {
        $this->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();;
        $this->save();
    }
    
    /**
     * Retrieves the total message count for this conversation.
     * 
     * @return int
     */
    function getMessageCount()
    {
        return $this->getMessages()->count();
    }
    
    /**
     * get UnreadMessage Count
     * 
     * Generates the current unread message count based on session data for
     * the currently logged in user.
     * 
     * @return int (false, or the number of unread messages)
     */
    function getUnreadMessageCount()
    {
        if (!isset($_SESSION['last_viewed'][$this->id])) {
            $_SESSION['last_viewed'][$this->id] = \UNL\VisitorChat\Controller::epochToDateTime(1);
        }
        
        $db  = \UNL\VisitorChat\Controller::getDB();
        $sql = "SELECT count(id) as unread FROM messages WHERE conversations_id = " . (int)$this->id . " AND date_created > '" . mysql_escape_string($_SESSION['last_viewed'][$this->id]) . "'";
        
        if (!$result = $db->query($sql)) {
            return 0;
        }
        
        $row = $result->fetch_assoc();
        
        return $row['unread'];
    }
    
    /**
     * Closes the conversation and marks all currently accepted assignments
     * for the conversation as completed.
     * 
     * @return null
     */
    function close()
    {
        //Update the chat and mark it as closed.
        $this->date_closed = \UNL\VisitorChat\Controller::epochToDateTime();
        $this->status = "CLOSED";
        $this->save();
        
        foreach(\UNL\VisitorChat\Assignment\RecordList::getAllAssignmentsForConversation($this->id) as $assignment) {
            $assignment->markAsCompleted();
        }
    }
    
    function getAssignments()
    {
        return \UNL\VisitorChat\Assignment\RecordList::getAllAssignmentsForConversation($this->id);
    }
    
    function getInvolvedUsers()
    {
        $users = array($this->users_id);
        
        foreach ($this->getAssignments() as $assignment) {
            $users[] = $assignment->users_id;
        }
        
        return $users;
    }
}
