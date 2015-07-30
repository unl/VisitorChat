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

        $regex = "";
        foreach ($domains as $domain) {
            $regex .= preg_quote($domain, ".-/") . "|";
        }

        $regex = trim($regex, "|");
        if (preg_match("/" . $regex . "$/", $parts['host'])) {
            return true;
        }
        
        return false;
    }
}