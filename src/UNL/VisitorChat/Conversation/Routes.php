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
                     '/^history$/i'       => 'History\User',
                     '/^history\/sites$/i'  => 'History\SiteList',
                     '/^history\/sites\/(?P<site_url>.+)$/i'  => 'History\Site',);
    }
    
    public static function getPostRoutes() 
    {
        return array('/^conversation\/(?P<id>[\d]+)\/edit$/i' => 'Edit',
                     '/^conversation\/(?P<id>[\d]+)\/share$/i' => 'Share',
                     '/^conversation\/(?P<id>[\d]+)\/leave$/i' => 'Leave');
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