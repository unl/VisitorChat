<?php
namespace UNL\VisitorChat\Chatbot;

class Record extends \Epoch\Record
{
    public $id;
    public $user_id;
    public $active;
    public $name;
    public $description;

    public static function getByID($id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Chatbot\Record', 'id', (int)$id);
    }

    public static function getByUserID($user_id)
    {
        return self::getByAnyField('\UNL\VisitorChat\Chatbot\Record', 'user_id', (int)$user_id);
    }

    public static function getByName($name)
    {
        return self::getByAnyField('\UNL\VisitorChat\Chatbot\Record', 'name', $name);
    }

    public static function getSiteChatbots($site_url) {
        return \UNL\VisitorChat\Chatbot\RecordList::getSiteChatbots($site_url);
    }

/*
    function insert()
    {
        $result = parent::insert();
        return $result;
    }
*/
    function keys()
    {
        return array('id');
    }

    public static function getTable()
    {
        return 'chatbots';
    }
/*
    public function update()
    {
        $result = parent::update();
        return $result;
    }
*/
}