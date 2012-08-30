<?php
namespace UNL\VisitorChat\Captcha;

class Routes extends \RegExpRouter\RoutesInterface
{
    public static function getGetRoutes()
    {
        return array();
    }

    public static function getPostRoutes()
    {
        return array('/^captcha\/edit/i' => 'edit');
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