<?php
namespace UNL\VisitorChat\Sites;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^sites$/i'  => 'SiteList',
                     '/^sites\/(?P<url>.+)$/i' => 'Site', //Historical
                     '/^sites\/site$/i' => 'Site',);
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