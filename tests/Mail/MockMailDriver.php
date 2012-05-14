<?php
use UNL\VisitorChat\Mail;

class MockMailDriver implements Mail\DriverInterface
{
    /**
     * Always return true (won't actually send emails).
     * 
     * (non-PHPdoc)
     * @see UNL\VisitorChat\Mail.DriverInterface::send()
     */
    function send($to, $hdrs, $body)
    {
        return true;
    }
}