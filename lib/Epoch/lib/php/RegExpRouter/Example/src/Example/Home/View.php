<?php
namespace Example\Home;

class View
{
    function __construct(array $options = array())
    {
        
    }
    
    function speak()
    {
        return "I am in " . __CLASS__;
    }
}