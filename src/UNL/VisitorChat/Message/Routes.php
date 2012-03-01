<?php 
namespace UNL\VisitorChat\Message;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array();
    }
    
    public static function getPostRoutes() 
    {
        return array('/^messages\/edit$/i' => 'Edit',);
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