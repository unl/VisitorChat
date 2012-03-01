<?php
namespace Example\Home;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^home$/i' => 'View', //'View' points to the 'View' class for THIS model.
                     '/^$/i' => 'View', //Match to an empty string, thus this is now the default home page.
                    );
    }
    
    public static function getPostRoutes()
    {
        /**
         * The Regex: ((?<id>[\d]+)\/)? will match a return variable with the key name 'id' with its value being the digit following it.
         * The ? at the end of the statment makes the match optional.
         * 
         * thus /home/1/edit will return array('id'=>1, 'model'=>'Example\Home\Edit');
         * and /home/edit will return array('model'=>'Example\Home\Edit');
         */
        return array('/^home\/((?<id>[\d]+)\/)?edit$/i' => 'Edit'); //'Edit' points to the 'Edit' class for THIS model.
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