<?php
namespace UNL\VisitorChat\User;

class Service
{
    public static function areUsersAvaiable($users)
    {
        foreach ($users as $user) {
            if ($user->getRole() == 'other') {
                continue;
            }
            
            if (!$user = \UNL\VisitorChat\User\Record::getByUID($user->getUID())) {
                continue;
            }
            
            if ($user->status == 'AVAILABLE') {
                return true;
            }
        }
        
        return false;
    }
}