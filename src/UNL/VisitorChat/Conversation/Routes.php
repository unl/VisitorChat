<?php 
namespace UNL\VisitorChat\Conversation;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^conversation$/i'  => 'View',
                     '/^history\/(?P<conversation_id>[\d]+)$/i' => 'Archived',
                     '/^$/i'              => 'View',
                     '/^conversations$/i' => 'RecordList',
                     '/^history$/i'       => 'History',);
    }
    
    public static function getPostRoutes() 
    {
        return array('/^conversation\/(?P<id>[\d]+)\/edit$/i' => 'Edit');
    }
    
    public static function getDeleteRoutes()
    {
        return array();
    }
    public static function getPutRoutes()
    {
        return array();
    }
}