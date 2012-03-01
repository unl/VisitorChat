<?php
namespace Example\Account;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^account$/i' => 'View'); //'View' refers to the 'View' class for THIS model.
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