<?php
namespace UNL\VisitorChat\Mail;

class Driver implements DriverInterface
{
    function send($to, $hdrs, $body)
    {
        $mail =& \Mail::factory('sendmail');
        
        return $mail->send($to, $hdrs, $body);
    }
}