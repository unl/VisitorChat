<?php
namespace UNL\VisitorChat\Asset;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array();
    }

    public static function getPostRoutes()
    {
        return array('/^js\/chat.php$/i' => 'View',
                     '/^css\/remote.php$/i' => 'View',
                     '/^assets\/(?P<type>[\S]+)$/i' => 'View');
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