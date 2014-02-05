<?php
namespace UNL\VisitorChat\Conversation;

class Email
{
    public $messages     = false;
    
    public $conversation = false;
    
    public $to_emails    = array();
    
    public $to_group     = "GENERAL";
    
    //An array of email address to send the conversation to in the event that no address can be found.
    public static $fallbackEmails = array();
    
    //The from email address
    public static $default_from = "";
    
    public $from;
    
    //The reply-to email address
    public static $default_reply_to = "";
    
    public $reply_to;
    
    //The default subject of the email
    public static $default_subject = "";
    
    public $subject;

    public $fromId = 1;  //The id of the user sending the email
    
    function __construct(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $fromId = 1, $options = array())
    {
        //Always ensure that output is escaped
        \UNL\VisitorChat\Controller::$templater->setEscape('htmlentities');
        
        //Set the path to the email directory.
        \UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/email/"));
        
        $this->conversation = $conversation;
        $this->messages     = $this->conversation->getMessages(array('itemClass' => '\UNL\VisitorChat\Message\View'));
        $this->subject      = self::$default_subject . ' ' . $this->conversation->id;
        $this->fromId       = $fromId;
        $this->setTo($to);
    }
    
    function setReplyTo($replyTo = "") {
        if (!$replyTo == "") {
            $this->reply_to = $replyTo;
            
            return true;
        }
        
        $client = $this->conversation->getClient();

        if (\Validate::email($client->email)) {
            $this->reply_to = $client->email;

        }
        
        return true;
    }
    
    function setTo($to = array())
    {
        //Check to see if we need to get the site members
        if (empty($to)) {
            $this->to_group = "SITE";
            
            if (!$sites = \UNL\VisitorChat\Controller::$registryService->getSitesByURL($this->conversation->initial_url)) {
                return false;
            }
            
            //Get only site members for the top level site.
            $emails = $sites->current()->getEmail();
            
            $to = explode(', ', $emails);
        }
        
        /* Edge case.  If a site contains only students as team members or people who
         * do not have their email address public, we will have no one to send an email to.
         * And the default operators will not be selected at this point.
         * 
         * So, we need to determin if there are team members, but none of them have email addresses.
         * If that is the case, we need to email our default operators.  This is ugly, I don't like it.
         */
        if (empty($to)) {
            $this->to_group = "ADMINS";
            
            foreach (\UNL\VisitorChat\Controller::$fallbackURLs as $url) {
                $sites = \UNL\VisitorChat\Controller::$registryService->getByURL($url);
                
                $emails = $sites->current()->getEmail();
                
                $to = explode(', ', $emails);
            }
        }
        
        if (count($to) == 1 && $to[0] == $this->conversation->getClient()->email) {
            $this->to_group = "CLIENT";
        }
        
        $this->to_emails = $to;
    }
    
    function generateToString()
    {
        $to_address = "";
        foreach ($this->to_emails as $mail) {
            if (\Validate::email($mail)) {
                $to_address .= $mail . ", ";
            }
        }
        
        return trim($to_address, ", ");
    }
    
    public static function sendConversation(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $fromId = 1, $options = array())
    {
        $class = get_called_class();
        $email = new $class($conversation, $to, $fromId, $options);
        return $email->send();
    }
    
    public function render()
    {
        return \UNL\VisitorChat\Controller::$templater->render($this, 'UNL/VisitorChat/Controller.tpl.php');
    }
    
    public function generateHeaders()
    {
        if (empty($this->from)) {
            $this->from = self::$default_from;
        }

        if (empty($this->reply_to)) {
            $this->reply_to = self::$default_reply_to;
        }
        
        return array(
          'From'     => $this->from,
          'Reply-To' => $this->reply_to,
          'To'       => $this->generateToString(),
          'Subject'  => $this->subject);
    }
    
    public function send()
    {
        //can we send to anyone?
        if (empty($this->to_emails)) {
            //Nope.  Can't find anyone to send emails to... so send to the fallback list otherwise return false
            if (empty(self::$fallbackEmails) || !is_array(self::$fallbackEmails)) {
                return false;
            }
            
            $this->setTo(self::$fallbackEmails);
        }
        
        $mime = new \Mail_mime("\n");
        $mime->setHTMLBody($this->render());
        
        $body    = $mime->get();
        $headers = $mime->headers($this->generateHeaders());

        if (\UNL\VisitorChat\Controller::$mailService->send($this->generateToString(), $headers, $body)) {
            return Email\Record::recordSentEmail($headers['To'], $headers['From'], $headers['Reply-To'], $headers['Subject'], $this->fromId, $this->conversation->id);
        }

        return false;
    }
}
