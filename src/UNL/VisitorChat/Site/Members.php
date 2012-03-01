<?php
namespace UNL\VisitorChat\Site;
class Members extends \LimitIterator implements \Countable
{
    //By default, do not limit.
    public $options = array('limit'=>-1, 'offset'=>0, 'array'=>array());
    
    function __construct($options = array())
    {
        $this->options = $options + $this->options;
        
        $list = new \ArrayIterator($this->options['array']);
        
        parent::__construct($list, $this->options['offset'], $this->options['limit']);
    }
    
    public static function getMembersByTypeAndSite($type, $site, $options = array())
    {
        $options['array'] = \UNL\VisitorChat\Controller::$defaultOperators;
        
        //Get Site Details
        $data = @file_get_contents("http://ucommfairchild.unl.edu/UNL_WDN/www/registry/?u=" . urlencode($site) . "&output=php&memberType=" . $type);
        if ($data) {
            $data = unserialize($data);
        }
        
        if (is_array($data)) {
            $options['array'] = $data;
        }
        
        return new self($options);
    }
    
    function count()
    {
        $iterator = $this->getInnerIterator();
        if ($iterator instanceof EmptyIterator) {
            return 0;
        }
        
        return count($this->getInnerIterator());
    }
}