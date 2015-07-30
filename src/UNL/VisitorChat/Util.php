<?php
namespace UNL\VisitorChat;

class Util
{
    public static function isAllowedDomain($url, array $domains)
    {
        if (!$parts = parse_url($url)) {
            //it isn't a valid URL, don't allow
            return false;
        }
        
        if (!isset($parts['host'])) {
            //it isn't a valid host, don't allow
            return false;
        }

        $possibilities = array();
        foreach ($domains as $domain) {
            //Root domains are okay
            $possibilities[] = '^' . preg_quote($domain, "/");
            //Sub-domains are okay
            $possibilities[] = '.*\.' . preg_quote($domain, "/");
        }
        
        $regex = implode($possibilities, "|");
        
        $regex = "/(" . $regex . ")$/i";
        
        if (preg_match($regex, $parts['host'])) {
            return true;
        }
        
        return false;
    }
}
