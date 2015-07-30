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

        array_walk($domains, function(&$domain) {
            $domain = preg_quote($domain, "/");
        });
        $regex = implode($domains, "|");
        
        if (preg_match("/(" . $regex . ")$/", $parts['host'])) {
            return true;
        }
        
        return false;
    }
}