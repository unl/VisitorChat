<?php 
namespace UNL\VisitorChat\Assignment;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array();
    }
    
    public static function getPostRoutes() 
    {
        return array('/^assignment\/(?<id>[\d]+)\/edit$/i' => 'Edit');
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