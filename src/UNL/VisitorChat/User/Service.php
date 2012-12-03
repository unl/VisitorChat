<?php
namespace UNL\VisitorChat\User;

class Service
{
    private static $user = false;
    
    public static function getCurrentUser()
    {
        if (!isset($_SESSION['id'])) {
            return false;
        }
        
        if (!self::$user) {
            if (!self::$user = Record::getByID($_SESSION['id'])) {
                return false;
            }
        }
        
        if (self::$user->id !== $_SESSION['id']) {
            self::$user = Record::getByID($_SESSION['id']);
        }
        
        return self::$user;
    }
    
    public static function setCurrentUser($user)
    {
        self::$user = $user;
        
        return true;
    }
}