<?php
namespace UNL\VisitorChat\Captcha;

class Edit
{
    function __construct($options = array())
    {
        
    }

    function handlePost($post = array())
    {
        if (!isset($_POST['code'])) {
            throw new \Exception("No code was provided for the captcha");
        }

        if (!$user = \UNL\VisitorChat\User\Service::getCurrentUser()) {
            throw new \Exception("No user is logged in!");
        }
        
        if (!$conversation = $user->getConversation()) {
            throw new \Exception("No conversation was found!");
        }
        
        require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/www/captcha/securimage.php");
        
        $image = new \Securimage();
        
        if ($image->check($_POST['code']) == true) {
            $conversation->status = "SEARCHING";
            $conversation->save();
        }

        \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("conversation", true, true));
    }
}
