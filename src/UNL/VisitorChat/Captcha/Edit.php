<?php
namespace UNL\VisitorChat\Captcha;

class Edit
{
    function __construct($options = array())
    {
        
    }

    function handlePost($post = array())
    {

        \UNL\VisitorChat\Controller::redirect(\UNL\VisitorChat\Controller::$url . "success");
    }
}
