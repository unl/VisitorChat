<?php
namespace UNL\VisitorChat\User;

class OperatorLogin
{
    function __construct($options = array())
    {
        self::authenticate();

        if (!isset($options['redirect']) || empty($options['redirect'])) {
            \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$url . "manage");
        }

        \Epoch\Controller::redirect($options['redirect']);
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
            
            //Set the status to BUSY for new user.
            $user->setStatus("BUSY", "NEW_USER");

            if ($json = file_get_contents("http://directory.unl.edu/?uid=" . $auth->getUser() . "&format=json")) {
                if ($data = json_decode($json, true)) {
                    //Default to an unknown name
                    $user->name = $auth->getUser();

                    //Try to get the name (displayName might be null or just 1 space)
                    if (isset($data['displayName'][0]) && $data['displayName'][0] !== " ") {
                        $user->name = $data['displayName'][0];
                    } else if (isset($data['givenName'][0], $data['sn'][0])) {
                        $user->name = $data['givenName'][0] . " " . $data['sn'][0];
                    }

                    $user->email = $data['mail'][0];
                }
            }
        }
        
        $user->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
        
        $user->setStatus("BUSY", "LOGIN");
        
        $user->save();

        $_SESSION['id'] = $user->id;

        return $user;

    }
}