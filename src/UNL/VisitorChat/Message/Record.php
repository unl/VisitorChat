<?php
namespace UNL\VisitorChat\Message;

class Record extends \Epoch\Record
{
    public $id;
    public $users_id;
    public $conversations_id;
    public $date_created;
    public $message;
    
    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Message\Record', 'id', (int)$id);
    }

    function insert()
    {
        $this->date_created = \UNL\VisitorChat\Controller::epochToDateTime();

        return parent::insert();
    }
    
    function keys()
    {
        return array('id');
    }
    
    public static function getTable()
    {
        return 'messages';
    }
    
    function getPoster()
    {
        return \UNL\VisitorChat\User\Record::getByID($this->users_id);
    }
    
    function getEditURL()
    {
        //Inorder for operators to stay in the current chat, we need to pass the conversation id along.
        $conversation_id = "";
        if (isset($_GET['conversation_id'])) {
            $conversation_id = "?conversation_id=" . $_GET['conversation_id'];
        }
        
        $last = "?";
        if (!empty($conversation_id)) {
            $last = "&";
        }
        
        $last = $last . "last=0";
        
        return \UNL\VisitorChat\Controller::$URLService->generateSiteURL("conversation" . $conversation_id . $last, true, true);
    }

    /**
     * determine the css class to use for this message.
     * This will help aid in the user quickly figuring out
     * who said what message.
     * 
     * @return string
     */
    function getDisplayClass()
    {
        $class = 'visitorChat_them';

        if ($this->users_id == $this->getConversation()->users_id) {
            $class = 'visitorChat_client';
        }

        if ($this->users_id == \UNL\VisitorChat\User\Service::getCurrentUser()->id) {
            $class = 'visitorChat_me';
        }
        
        return $class;
    }
    
    function save()
    {
        $this->message = nl2br($this->message);
        parent::save();
    }
    
    function getConversation()
    {
        return \UNL\VisitorChat\Conversation\Record::getByID($this->conversations_id);
    }

    /**
     * Check if this message contains title 9 words
     * 
     * @return bool
     */
    public function hasTitle9Words()
    {
        foreach (\UNL\VisitorChat\Controller::$title9Words as $word) {
            if (substr_count(strtolower($this->message), strtolower($word)) > 0) {
                return true;
            }
        }
        
        return false;
    }
}