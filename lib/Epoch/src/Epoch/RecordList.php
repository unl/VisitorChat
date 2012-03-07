<?php
namespace Epoch;

abstract class RecordList extends \LimitIterator implements \Countable
{

    //By default, do not limit.
    public $options = array('limit'=>-1, 'offset'=>0);
    
    /**
     * Get the defualt options for the list class
     * Required to set the following:
     *     $options['listClass'] the class of the list.
     *     $options['itemClass'] the class of each item in the list.
     * @return array
     */
    abstract function getDefaultOptions();
    
    /*
     * @param $options Requires Listclass and ItemClass to work properlly.
     * 
     *     listClass: The class name of the list.
     *     itemClass: The class name of the items in the list.
     */
    function __construct($options = array())
    {
        $this->options = $options + $this->options;
        
        $this->options = $this->options + $this->getDefaultOptions();
        
        if (!isset($this->options['listClass'])) {
            Throw New Exception("No List Class was set", 500);
        }
        
        if (!isset($this->options['itemClass'])) {
            Throw New Exception("No Item Class was set", 500);
        }
        
        if (!isset($this->options['array'])) {
            //get a lit of all of them by default.
            $this->options['array'] = $this->getAllForConstructor();
        }
        
        $list = new \ArrayIterator($this->options['array']);

        parent::__construct($list, $this->options['offset'], $this->options['limit']);
    }
    
    private function getAllForConstructor()
    {
        $class = new $this->options['itemClass'];
        $options['sql']         = "SELECT id FROM " . $class->getTable() . "";
        $options['returnArray'] = true;
        return $this->getBySql($options);
    }
    
     /**
     * generate a list by sql.
     *
     * @param $options
     *        $options['sql'] = the sql string. (required)
     *        $options['listClass'] the class of the list. (optional (required if returning an iterator))
     *        $options['itemClass'] the class of each item in the list. (optional (required if returning an iterator))
     *        $options['returnArray'] return an array instead of an iterator. (optional).
     *        
     * @return mixed
     */
    public static function getBySql(array $options) {
        
        if (!isset($options['sql'])) {
            throw new exception("options['sql'] was not set!", 500);
        }
        
        $mysqli           = \Epoch\Controller::getDB();
        $options['array'] = array();
        
        if ($result = $mysqli->query($options['sql'])) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $options['array'][] = $row['id'];
            }
        }
        
        if (isset($options['returnArray']) && $options['returnArray'] == true) {
            return $options['array'];
        }
        
        if (!isset($options['listClass'], $options['itemClass'])) {
            throw new Exception("options['listClass'] or options['itemClass'] were not set!", 500);
        }
        
        return new $options['listClass']($options);
    }
    
    public static function escapeString($string)
    {
        $mysqli = \Epoch\Controller::getDB();
        return $mysqli->escape_string($string);
    }
    
    function current() {
        return call_user_func($this->options['itemClass'] . "::getByID", parent::current());
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