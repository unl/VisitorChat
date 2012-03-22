<?php
namespace UNL\VisitorChat\Conversation;

class Email
{
    public $messages     = false;
    
    public $conversation = false;
    
    public $to_emails    = array();
    
    public $to_group     = "GENERAL";
    
    public $from         = "unlwdn@gmail.com";
    
    public $reply_to     = "unlwdn@gmail.com";
    
    public $subject      = "UNL VisitorChat System";
    
    function __construct(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $options = array())
    {
        //Set the path to the email directory.
        \UNL\VisitorChat\Controller::$templater->setTemplatePath(array(\UNL\VisitorChat\Controller::$applicationDir . "/www/templates/email/"));
        
        $this->conversation = $conversation;
        $this->messages     = $this->conversation->getMessages(array('itemClass' => '\UNL\VisitorChat\Message\View'));
        $this->subject      = 'UNL VisitorChat System ' . $this->conversation->id;
        $this->setTo($to);
    }
    
    function setReplyTo($replyTo = "") {
        if (!$replyTo == "") {
            $this->replyTo = $replyTo;
            
            return true;
        }
        
        $client = $this->conversation->getClient();
        
        if (\Validate::email($client->email)) {
            $this->replyTo = $client->email;
        }
        
        return true;
    }
    
    function setTo($to = array())
    {
        //Check to see if we need to get the site members
        if (empty($to)) {
            $this->to_group = "SITE";
            
            $siteMembers = \UNL\VisitorChat\Site\Members::getMembersByTypeAndSite('all', $this->conversation->initial_url);
            
            foreach ($siteMembers as $person) {
                $person = new \UNL\VisitorChat\Site\Member($person);
                if ($mail = $person->getDefaultEmail()) {
                    $to[] = $mail;
                }
            }
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
            
            foreach (\UNL\VisitorChat\Controller::$defaultOperators as $person) {
                $person = new \UNL\VisitorChat\Site\Member($person);
                if ($mail = $person->getDefaultEmail()) {
                    $to[] = $mail;
                }
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
    
    public static function sendConversation(\UNL\VisitorChat\Conversation\Record $conversation, $to = array(), $options = array())
    {
        $class = get_called_class();
        $email = new $class($conversation, $to, $options);
        return $email->send();
    }
    
    public function render()
    {
        return \UNL\VisitorChat\Controller::$templater->render($this, 'UNL/VisitorChat/Controller.tpl.php');
    }
    
    public function generateHeaders()
    {
        return array(
          'From'     => $this->reply_to,
          'Reply-To' => $this->from,
          'To'       => $this->generateToString(),
          'Subject'  => $this->subject);
    }
    
    public function send()
    {
        //can we send to anyone?
        if (empty($this->to_emails)) {
            //Nope.  Don't send emails, return false.
            return false;
        }
        
        $mime = new \Mail_mime("\n");
        $mime->setHTMLBody($this->render());
        
        $body = $mime->get();
        $hdrs = $mime->headers($this->generateHeaders());
        $mail =& \Mail::factory('sendmail');
        
        return $mail->send($this->generateToString(), $hdrs, $body);
    }
}
