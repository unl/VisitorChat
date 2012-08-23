<?php
namespace UNL\VisitorChat\Help;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array('/^faq/i'  => 'FAQ');
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