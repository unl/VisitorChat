<?php 
namespace UNL\VisitorChat\Manage;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^manage$/i'     => 'View',
                     '/^operator\/$/i' => 'View',
                     '/^$/i'           => 'View',);
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