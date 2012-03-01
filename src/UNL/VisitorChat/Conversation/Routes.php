<?php 
namespace UNL\VisitorChat\Conversation;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^conversation$/i' => 'View',
                     '/^$/i'             => 'View',
                     '/^conversations$/i' => 'RecordList',);
    }
    
    public static function getPostRoutes() 
    {
        return array();
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