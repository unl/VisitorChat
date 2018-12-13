<?php
namespace UNL\VisitorChat\Captcha;

class Service
{
    public static function isSpam($url, $message, $ip, $ua)
    {
        $detector = new \SpamDetector();
        
        if (file_exists(dirname(dirname(dirname(dirname(dirname(__file__))))) . "/spamRules.inc.php")) {
            $rules = require_once(dirname(dirname(dirname(dirname(dirname(__file__))))) . "/spamRules.inc.php");
        } else {
            $rules = require_once(dirname(dirname(dirname(dirname(dirname(__file__))))) . "/spamRules.sample.php");
        }

        $detector->setRules($rules);
        
        if ($detector->isSpam($url, 'url')) {
            return true;
        }
        
        if ($detector->isSpam($message, 'text')) {
            return true;
        }
        
        if ($detector->isSpam($ip, 'ip')) {
            return true;
        }
        
        if ($detector->isSpam($ua, 'ua')) {
            return true;
        }
        
        return false;
    }
}