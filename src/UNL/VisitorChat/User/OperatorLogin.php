<?php 
namespace UNL\VisitorChat\User;

class OperatorLogin
{
    function __construct()
    {
        self::authenticate();
        
        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$url . "manage");
    }
    
    public static function authenticate($logoutonly = false)
    {
        if (isset($_GET['logout'])) {
            $auth = \UNL_Auth::factory('SimpleCAS');
            $auth->logout();
        }
        
        if ($logoutonly) {
            return true;
        }

        $auth = \UNL_Auth::factory('SimpleCAS');
        $auth->login();

        if (!$auth->isLoggedIn()) {
            throw new \Exception('You must log in to view this resource!');
            exit();
        }
        
        //check if we have a user or need to make a new one.
        if (!$user = \UNL\VisitorChat\User\Record::getByUID($auth->getUser())) {
            $user               = new \UNL\VisitorChat\User\Record();
            $user->name         = $auth->getUser();
            $user->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
            $user->type         = 'operator';
            $user->max_chats    = 3;
            $user->uid          = $auth->getUser();
            
            if ($json = file_get_contents("http://directory.unl.edu/?uid=" . $auth->getUser() . "&format=json")) {
                if ($data = json_decode($json, true)) {
                    $user->name  = $data['displayName'][0];
                    $user->email = $data['mail'][0];
                }
            }
        }
        
        $user->status = "BUSY";
        $user->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
        $user->save();
        
        $_SESSION['id'] = $user->id;
        
        return $user;
        
    }
}