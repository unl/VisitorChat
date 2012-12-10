<?php
/**
 * Simple Active Record implementation
 * 
 * PHP version 5
 * 
 * @category  Publishing
 * @package   Epoch
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2010 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 */
namespace Epoch;

abstract class Record
{
    function __construct() {
        
    }
    
    /**
     * Prepare the insert SQL for this record
     *
     * @param string &$sql The INSERT SQL query to prepare
     * 
     * @return array Associative array of field value pairs
     */
    protected function prepareInsertSQL(&$sql)
    {
        $sql    = 'INSERT INTO '.$this->getTable();
        $fields = $this->getFields();
        if (isset($fields['options'])) {
            unset($fields['options']);
        }
        $sql .= '(`'.implode('`,`', array_keys($fields)).'`)';
        $sql .= ' VALUES ('.str_repeat('?,', count($fields)-1).'?)';
        return $fields;
    }

    /**
     * Prepare the update SQL for this record
     * 
     * @param string &$sql The UPDATE SQL query to prepare
     *
     * @return array Associative array of field value pairs
     */
    protected function prepareUpdateSQL(&$sql)
    {
        $sql    = 'UPDATE '.$this->getTable().' ';
        $fields = $this->getFields();

        $sql .= 'SET `'.implode('`=?,`', array_keys($fields)).'`=? ';

        $sql .= 'WHERE ';
        foreach ($this->keys() as $key) {
            $sql .= $key.'=? AND ';
        }

        $sql = substr($sql, 0, -4);

        $fields = $fields + $this->getFields();
        return $fields;
    }
    
    /**
     * Save the record. This automatically determines if insert or update
     * should be used, based on the primary keys.
     * 
     * @return bool
     */
    function save()
    {
        $saveType = 'save';
        
        foreach ($this->keys() as $key) {
            if (empty($this->$key)) {
                $saveType = 'create';
            }
        }
        
        if ($saveType == 'create') {
            $result = $this->insert();
        } else {
            $result = $this->update();
        }
        
        return $result;
    }

    /**
     * Insert a new record into the database
     *
     * @return bool
     */
    function insert()
    {
        $sql      = '';
        $fields   = $this->prepareInsertSQL($sql);
        $values   = array();
        $values[] = $this->getTypeString(array_keys($fields));
        foreach ($fields as $key=>$value) {
            $values[] =& $this->$key;
        }
        return $this->prepareAndExecute($sql, $values);
    }

    /**
     * Update this record in the database
     *
     * @return bool
     */
    function update()
    {
        $sql      = '';
        $fields   = $this->prepareUpdateSQL($sql);
        $values   = array();
        $values[] = $this->getTypeString(array_keys($fields));
        foreach ($fields as $key=>$value) {
            $values[] =& $this->$key;
        }
        // We're doing an update, so add in the keys!
        $values[0] .= $this->getTypeString($this->keys());
        foreach ($this->keys() as $key) {
            $values[] =& $this->$key;
        }
        return $this->prepareAndExecute($sql, $values);
    }

    /**
     * Prepare the SQL statement and execute the query
     * 
     * @param string $sql    The SQL query to execute
     * @param array  $values Values used in the query
     * 
     * @throws Exception
     * 
     * @return true
     */
    protected function prepareAndExecute($sql, $values)
    {
        $mysqli = self::getDB();

        if (!$stmt = $mysqli->prepare($sql)) {
            throw new \Exception('Error preparing database statement! '.$mysqli->error, 500);
        }

        call_user_func_array(array($stmt, 'bind_param'), $values);
        if ($stmt->execute() === false) {
            throw new \Exception($stmt->error, 500);
        }

        if ($mysqli->insert_id !== 0) {
            $this->id = $mysqli->insert_id;
        }

        return true;

    }

    /**
     * Get the type string used with prepared statements for the fields given
     *
     * @param array $fields Array of field names
     * 
     * @return string
     */
    function getTypeString($fields)
    {
        $types = '';
        foreach ($fields as $name) {
            switch($name) {
                case 'id':
                    $types .= 'i';
                    break;
                default:
                    $types .= 's';
                    break;
            }
        }
        return $types;
    }

    /**
     * Convert the string given into a usable date for the RDBMS
     *
     * @param string $str A textual description of the date
     * 
     * @return string|false
     */
    function getDate($str)
    {
        if ($time = strtotime($str)) {
            return date('Y-m-d', $time);
        }

        if (strpos($str, '/') !== false) {
            list($month, $day, $year) = explode('/', $str);
            return $this->getDate($year.'-'.$month.'-'.$day);
        }
        // strtotime couldn't handle it
        return false;
    }

    /**
     * Simple method for getting a record by a single primary key
     *
     * @param string $table Table to retrieve record from
     * @param int    $id    The primary key/ID value
     * @param string $field The field that holds the primary key
     * 
     * @return false | \Epoch\Record
     */
    public static function getRecordByID($table, $id, $field = 'id')
    {
        $mysqli = self::getDB();
        $sql    = "SELECT * FROM $table WHERE $field = ".intval($id).' LIMIT 1;';
        if ($result = $mysqli->query($sql)) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    /**
     * Delete this record in the database
     *
     * @return bool
     */
    function delete()
    {
        $mysqli = self::getDB();
        $sql    = "DELETE FROM ".$this->getTable()." WHERE ";
        foreach ($this->keys() as $key) {
            if (empty($this->$key)) {
                throw new \Exception('Cannot delete this record.' .
                                    'The primary key, '.$key.' is not set!',
                                    400);
            }
            $value = $this->$key;
            if ($this->getTypeString(array($key)) == 's') {
                $value = '"'.$mysqli->escape_string($value).'"';
            }
            $sql .= $key.'='.$value.' AND ';
        }
        $sql  = substr($sql, 0, -4);
        $sql .= ' LIMIT 1;';
        if ($result = $mysqli->query($sql)) {
            return true;
        }
        return false;
    }

    /**
     * Magic method for static calls
     *
     * @param string $method Tsathod called
     * @param array  $args   Array of arguments passed to the method
     * 
     * @method getBy[FIELD NAME]
     * 
     * @throws Exception
     * 
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        switch (true) {
        case preg_match('/getBy([\w]+)/', $method, $matches):
            $class    = get_called_class();
            $field    = strtolower($matches[1]);
            $whereAdd = null;
            if (isset($args[1])) {
                $whereAdd = $args[1];
            }
            return self::getByAnyField($class, $field, $args[0], $whereAdd);
            
        }
        throw new \Exception('Invalid static method called.', 500);
    }

    public static function getByAnyField($class, $field, $value, $whereAdd = '')
    {
        $record = new $class;

        if (!empty($whereAdd)) {
            $whereAdd = $whereAdd . ' AND ';
        }

        $mysqli = self::getDB();
        $sql    = 'SELECT * FROM '
                    . $record->getTable()
                    . ' WHERE '
                    . $whereAdd
                    . $field . ' = "' . $mysqli->escape_string($value) . '"';
        $result = $mysqli->query($sql);

        if ($result === false
            || $result->num_rows == 0) {
            return false;
        }

        $record->synchronizeWithArray($result->fetch_assoc());
        return $record;
    }

    /**
     * Get the DB
     * 
     * @return mysqli
     */
    public static function getDB()
    {
        return \Epoch\Controller::getDB();
    }

    /**
     * Synchronize member variables with the values in the array
     * 
     * @param array $data Associative array of field=>value pairs
     * 
     * @return void
     */
    function synchronizeWithArray($data)
    {
        foreach ($this->getFields() as $key=>$default_value) {
            if (isset($data[$key]) && !empty($data[$key])) {
                $this->$key = $data[$key];
            }
        }
    }
    
    function getFields()
    {
        $object = new \ReflectionObject($this);
        
        $properties = array();
        
        foreach ($object->getProperties() as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($this);
        }
        
        return $properties;
    }
    
    /**
     * Reload data from the database and refresh member variables
     * 
     * @return void
     */
    function reload()
    {
        $record = self::getById($this->id);
        $this->synchronizeWithArray($record->toArray());
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    function toArray()
    {
        return $this->getFields();
    }
    
    /**
     * Get the primary keys for this table in the database
     *
     * @return array
     */
    abstract function keys();
    
     /**
     * Return a string containing the table name.
     *
     * @return string
     */
    abstract public static function getTable();
}