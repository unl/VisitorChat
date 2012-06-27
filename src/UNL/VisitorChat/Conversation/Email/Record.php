<?php
namespace UNL\VisitorChat\Conversation\Email;

class Record extends \Epoch\Record
{
    //The id of the current email record
    public $id;

    //The email address of the receiver
    public $to;

    //the from email address
    public $from;

    //the reply_to email address
    public $reply_to;

    //The subject line of the email
    public $subject;

    //The date the email was sent
    public $date_sent;

    //The id of the person sending the email
    public $users_id;

    //The id of the associated conversation
    public $conversations_id;

    /**
     * Returns a conversation record by ID.
     *
     * @param int $id
     */
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Conversation\Email\Record', 'id', (int)$id);
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
        return 'emails';
    }

    public function insert()
    {
        //Set the date_sent field
        $this->date_sent = \UNL\VisitorChat\Controller::epochToDateTime();

        parent::insert();
    }

    public static function recordSentEmail($to, $from, $replyTo, $subject, $userId, $conversationId)
    {
        $record = new \UNL\VisitorChat\Conversation\Email\Record();

        $record->to               = $to;
        $record->from             = $from;
        $record->reply_to         = $replyTo;
        $record->subject          = $subject;
        $record->users_id         = $userId;
        $record->conversations_id = $conversationId;

        return $record->save();
    }
}
