<?php
namespace Example;

class Controller
{
    /**
    * Options array
    * Will include $_GET vars
    */
    public $options = array(
        'format' => 'html'
    );
    
    public static $url = '';
    
    public $actionable = array();

    function __construct($options = array())
    {
        $this->options = $options + $this->options;
        
        $model = $this->run();
        echo $model->speak();
    }

    /**
    * Run
    *
    * @throws Exception if view is unregistered
    */
    function run()
    {
         if (!isset($this->options['model'])) {
             throw new Exception('Un-registered view', 404);
         }
         
         return new $this->options['model']($this->options);
    }

}