<?php
class UNL_WDN_Emailer_Main
{
    public $to_address;

    public $from_address;

    public $subject;

    public $html_body;

    public $text_body;

    public $web_view_uri;

    public function toHTML()
    {

        $savvy = new Savvy();
        $savvy->setTemplatePath(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/data/pear.unl.edu/UNL_WDN_Emailer');

        $html = '<html>'.
                '<body style="word-wrap: break-word;" bgcolor="#ffffff">'.
                    $savvy->render($this, 'WDNEmailTemplate.tpl.php').
                '</body>'.
                '</html>';
        return $html;
    }
    
    public function toTxt()
    {
        return $this->text_body;
    }

    public function send()
    {

        $hdrs = array(
          'From'    => $this->from_address,
          'Subject' => $this->subject);

        require_once 'Mail/mime.php';
        $mime = new Mail_mime("\n");

        if (isset($this->text_body)) {
            $mime->setTXTBody($this->toTxt());
        }

        $mime->setHTMLBody($this->toHtml());

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);
        $mail =& Mail::factory('sendmail');


        // Send the email!
        $mail->send($this->to_address, $hdrs, $body);
        return true;
    }
}