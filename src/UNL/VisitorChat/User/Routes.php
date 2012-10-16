<?php 
namespace UNL\VisitorChat\User;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^logout$/i' => 'Logout',
                     '/^user\/sites$/i' => 'SiteList',
                     '/^user\/info$/i' => 'Info',
                    '/^users\/(?P<id>[\d]+)$/i' => 'View');
    }
    
    public static function getPostRoutes() 
    {
        return array('/^clientLogin$/i' => 'ClientLogin',
                     '/^operatorLogin$/i' => 'OperatorLogin',
                     '/^user\/settings$/i' => 'Edit',
                     '/^users\/(?P<id>[\d]+)\/edit$/i' => 'Edit');
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