<?php
/**
 * Class builds a pretty good LDAP filter for searching for people.
 * 
 * <code>
 * <?php
 * $filter = new UNL_Peoplefinder_StandardFilter('brett bieber','|',false);
 * echo $filter;
 * ?>
 * (|(sn=brett bieber)(cn=brett bieber)(&(| (givenname=brett) (sn=brett) (mail=brett) (unlemailnickname=brett) (unlemailalias=brett))(| (givenname=bieber) (sn=bieber) (mail=bieber) (unlemailnickname=bieber) (unlemailalias=bieber))))
 * </code>
 *
 * PHP version 5
 * 
 * @category  Default 
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Driver_LDAP_StandardFilter
{
    private $_filter;
    
    private $_excludeRecords = array();
    
    /**
     * Construct a standard filter.
     *
     * @param string $inquery  Search string 'bieber, brett' etc
     * @param string $operator LDAP operator to use & or |
     * @param bool   $wild     Append wildcard to search terms?
     */
    function __construct($inquery, $operator = '&', $wild = false)
    {
        if (!empty($inquery)) {
            //ignore grouping and wildcard characters
            $inquery = str_replace(array('"',',','*'),'',$inquery);

            //escape query
            $inquery = UNL_Peoplefinder_Driver_LDAP_Util::escape_filter_value($inquery);
            
            //put the query into an array of words
            $query = preg_split('/\s+/', $inquery, 4);

            if ($operator!='&') $operator = '|';

            //create our filter
            //search for the string parts
            $filter = "($operator";
            foreach ($query as $arg) {
                //determine if a wildcard should be used
                if ($wild) {
                    $arg = "*$arg*";
                }

                $filter .= '(|';
                $filter .= "(mail=$arg)(cn=$arg)(givenName=$arg)(sn=$arg)";

                //find hyphenated and multi-word surnames in the exact matches query
                if (!$wild) {
                    $filter .= "(sn=$arg-*)(sn=*$arg)";
                }

                $filter .= ")";
            }
            $filter .= ")";

            //determine if a wildcard should be used
            if ($wild) {
                $inquery = "*$inquery*";
            }

            //and search for the string as entered
            $filter = "(|" .
                    "(|(mail=$inquery)(cn=$inquery)(givenName=$inquery)(sn=$inquery))" .
                    "$filter)";
        }
        $this->_filter = $filter;
    }
    
    /**
     * Allows you to exclude specific records from a result set.
     *
     * @param array(string|UNL_Peoplefinder_Record) $records Records to exclude, can be just the uids or record objects
     */
    function excludeRecords($records = array())
    {
        $this->_excludeRecords = array_merge($this->_excludeRecords, $records);
    }
    
    function __toString()
    {
        if (count($this->_excludeRecords)) {
            $excludeFilter = '';
            foreach ($this->_excludeRecords as $record) {
                $excludeFilter .= '(uid='.$record->__toString().')';
            }
            $this->_filter = '(&'.$this->_filter.'(!(|'.$excludeFilter.')))';
        }
        $this->_filter = '(&'.$this->_filter.'(!(eduPersonPrimaryAffiliation=guest)))';
        return $this->_filter;
    }
}
