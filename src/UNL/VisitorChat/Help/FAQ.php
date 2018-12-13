<?php
namespace UNL\VisitorChat\Help;

class FAQ
{
   public function __construct($options = array())
   {
       \UNL\VisitorChat\Controller::redirect('http://wdn.unl.edu/unl-chat-faq');
       \UNL\VisitorChat\Controller::$pagetitle = "FAQ";
   }
}
