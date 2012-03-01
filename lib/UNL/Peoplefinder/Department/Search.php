<?php
class UNL_Peoplefinder_Department_Search implements Countable, Iterator
{
    public $q;
    
    /**
     * SimpleXMLElement for the HR XML Tree
     * 
     * @var SimpleXMLElement
     */
    protected $xml;
    
    protected $results;
    
    protected $current = 0;
    
    function __construct($q)
    {
        $q = strtolower(str_replace('"', '', $q));
        $this->xml = new SimpleXMLElement(file_get_contents(dirname(__FILE__).'/../../../data/hr_tree.xml'));
        $this->results = $this->xml->xpath('//attribute[@name="org_unit"][@value="50000003"]/..//attribute[@name="name"][contains(translate(@value,"ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"),"'.$q.'")]');
    }
    
    function current()
    {
        return new UNL_Peoplefinder_Department($this->results[$this->current]['value']);
    }
    
    function next()
    {
        $this->current++;
    }
    
    function valid()
    {
        if ($this->current < count($this->results)) {
            return true;
        }
        return false;
    }
    
    function count()
    {
        return count($this->results);
    }
    
    function rewind()
    {
        $this->current = 0;
    }
    
    function key()
    {
        return $this->current()->name;
    }
}
?>