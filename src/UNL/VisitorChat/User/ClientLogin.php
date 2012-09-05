<?php 
namespace UNL\VisitorChat\User;

class ClientLogin extends \UNL\VisitorChat\User\Record
{
    function __construct($options = array())
    {
        if (\UNL\VisitorChat\User\Service::getCurrentUser()) {
            \Epoch\Controller::redirect(\UNL\VisitorChat\Controller::$URLService->generateSiteURL("conversation", true, true));
        }
    }
    
    function getEditURL()
    {
        return \UNL\VisitorChat\Controller::$url . "clientLogin";
    }
    
    function handlePost($post = array())
    {
        if (!isset($post['initial_url']) || empty($post['initial_url'])) {
            throw new \Exception("No initial url was found", 400);
        }

        //Check the domain.
        $regex = "";
        foreach (\UNL\VisitorChat\Controller::$allowedDomains as $domain) {
            $regex .= addcslashes($domain, ".-/") . "|";
        }
        
        //Check for allowed domains
        $regex = trim($regex, "|")  ;
        if (!preg_match("/" . $regex . "/", $post['initial_url'])) {
            // Silently fail
            throw new \Exception("This chat system can not run on the given domain.", 400);
        }
        
        if (!isset($post['initial_pagetitle']) || empty($post['initial_pagetitle'])) {
            throw new \Exception("No initial pagetitle url was found", 400);
        }
        
        if (!isset($post['email']) || empty($post['email'])) {
            $post['email'] = null;
        }
        
        //no name? (check if name (optional) due to bug in <= ie9)
        if (!isset($post['name']) || empty($post['name']) || strtolower($post['name']) == "name (optional)") {
            $post['name'] = "Guest";
        }
        
        if (!isset($post['message']) || empty($post['message'])) {
            throw new \Exception("No message was provided", 400);
        }
        
        $fallback = 1;
        if (!isset($post['email_fallback']) || empty($post['email_fallback'])) {
            $fallback = 0;
        }
        
        $method = "CHAT";
        if (isset($post['method']) && $post['method'] == "EMAIL") {
            $method = "EMAIL";
        }
        
        $user = new self();
        $user->name         = $post['name'];
        $user->email        = $post['email'];
        $user->date_created = \UNL\VisitorChat\Controller::epochToDateTime();
        $user->type         = 'client';
        $user->max_chats    = 3;
        $user->status       = 'BUSY';
        $user->date_updated = \UNL\VisitorChat\Controller::epochToDateTime();
        
        $user->save();
        
        //Append a unique ID to the end of an guest's user's name
        if ($user->name == "Guest") {
            $user->name = $user->name . $user->id;
            $user->save();
        }
        
        //Check if this may be spam...
        $status = "SEARCHING";
        $spam   = 0;
        if (\UNL\VisitorChat\Captcha\Service::isSpam($post['initial_url'], $post['message'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'])) {
            $status = "CAPTCHA";
            $spam   = true;
        }
        
        //Start up a new conversation for the user.
        $conversation = new \UNL\VisitorChat\Conversation\Record();
        $conversation->users_id          = $user->id;
        $conversation->method            = $method;
        $conversation->initial_url       = $post['initial_url'];
        $conversation->initial_pagetitle = $post['initial_pagetitle'];
        $conversation->status            = $status;
        $conversation->email_fallback    = $fallback;
        $conversation->auto_spam         = $spam;
        
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $conversation->ip_address = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $conversation->user_agent = $_SERVER['HTTP_USER_AGENT'];
        }
        
        $conversation->save();
        
        //Save the first message.
        $message = new \UNL\VisitorChat\Message\Record();
        $message->users_id         = $user->id;
        $message->date_created     = \UNL\VisitorChat\Controller::epochToDateTime();
        $message->conversations_id = $conversation->id;
        $message->message          = $post['message'];
        $message->save();
        $user->ping();
        
        //Prevent session fixation attacks
        session_regenerate_id();
        
        $_SESSION['id'] = $user->id;
    }
}