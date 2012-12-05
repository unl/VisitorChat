<?php 
namespace UNL\VisitorChat\Conversation;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^conversation$/i'  => 'View',
                     '/^history\/(?P<conversation_id>[\d]+)$/i' => 'Archived',
                     '/^conversations$/i' => 'RecordList',
                     '/^history$/i'       => 'History\User',
                     '/^history\/sites$/i'  => 'History\SiteList', //historical
                     '/^history\/sites\/(?P<site_url>.+)$/i' => 'History\Site',  //historical
                     '/^sites\/history$/i'  => 'History\Site',);
    }
    
    public static function getPostRoutes() 
    {
        return array('/^conversation\/(?P<id>[\d]+)\/edit$/i' => 'Edit',
                     '/^conversation\/(?P<id>[\d]+)\/share$/i' => 'Share',
                     '/^conversation\/(?P<id>[\d]+)\/leave$/i' => 'Leave',
                     '/^conversation\/(?P<id>[\d]+)\/sendConfirmEmail$/i' => 'Email\ConfirmationEmail'
        );
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