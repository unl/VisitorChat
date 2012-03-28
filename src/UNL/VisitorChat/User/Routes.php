<?php 
namespace UNL\VisitorChat\User;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^logout$/i' => 'Logout',
                     '/^user\/info$/i' => 'Info');
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