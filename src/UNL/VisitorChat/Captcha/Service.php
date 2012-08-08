<?php
namespace UNL\VisitorChat\Captcha;

class Service
{
    public static function isSpam($user, $message)
    {
        if ($message == 'spam') {
            return true;
        }
        
        return false;
    }
    
    
}