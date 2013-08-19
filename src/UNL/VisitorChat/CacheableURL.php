<?php
namespace UNL\VisitorChat;

abstract class CacheableURL
{
    public static $cacheTimeout = 18000;  //seconds (5 hours)
    
    public static $tmpDir       = "";
    
    abstract function getCacheTitle();
    
    public function getCachePath($url)
    {
        if (empty(self::$tmpDir)) {
            throw new \Exception('Cache temp directory not defined.');
        }
        
        $path = self::$tmpDir;

        //Some paths may not have a trailing separator.  Other may?  weird.
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path = $path . DIRECTORY_SEPARATOR;
        }
        
        return $path . $this->getCacheTitle() . md5($url);
    }

    public function getCache($path)
    {
        if (file_exists($path) && (filemtime($path) + self::$cacheTimeout > time())) {
            return unserialize(file_get_contents($path));
        }

        return false;
    }

    public function setCache($path, $data)
    {
        file_put_contents($path, serialize($data));
    }
}