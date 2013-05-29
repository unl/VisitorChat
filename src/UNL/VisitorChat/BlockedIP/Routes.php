<?php
namespace UNL\VisitorChat\BlockedIP;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array(
            '/^blocks/i' => 'RecordList',
        );  
    }

    public static function getPostRoutes()
    {
        return array(
            '/^blocks\/((?P<id>[\d]+)\/)?edit$/i' => 'Edit',
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