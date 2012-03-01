<?php
/**
 * Builds an advanced filter for searching for people records.
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
class UNL_Peoplefinder_Driver_LDAP_AdvancedFilter
{
    private $_filter;
    
    /**
     * Construct an advanced filter.
     *
     * @param string $sn       Surname 'Bieber'
     * @param string $cn       Common name 'Brett'
     * @param string $eppa     Primary affiliation: student/staff/faculty
     * @param string $operator LDAP operator to use & or |
     * @param bool   $wild     Append wildcard character to search terms? 
     */
    function __construct($sn='',$cn='',$eppa='',$operator='&', $wild=false)
    {
        // Advanced Query, search by LastName (sn) and First Name (cn), and affiliation
        if ($wild == false) {
            $wildcard = '';
        } else {
            $wildcard = '*';
        }
        $filterfields = array();
        $filterfields['sn'] = $sn.$wildcard;
        $filterfields['cn'] = $cn.$wildcard;
        $primaryAffiliation ='';
        // Determine the eduPersonPrimaryAffiliation to query by
        switch ($eppa) {
            case 'stu':
            case 'student':
                $primaryAffiliation = '(eduPersonPrimaryAffiliation=student)';
                break;
            case 'fs':
            case 'faculty':
            case 'staff':
                $primaryAffiliation = '(|(eduPersonPrimaryAffiliation=faculty)(eduPersonPrimaryAffiliation=staff))';
                break;
            default:
                $primaryAffiliation = '(eduPersonPrimaryAffiliation=*)';
                break;
        }
        $this->_filter = '('.$operator.$this->buildFilter($filterfields).$primaryAffiliation.')';
    }
    
    private function buildFilter(&$field_arr, $op='')
    {
        $filter='';
        foreach ($field_arr as $key=>$value) {
            if (is_array($value)) {
                $tmpvar = array();
                $tmpvar[$key]=$value;
                $filter .= buildFilter($tmpvar);
            } else {
                $filter .= "($key=$value)";
            }
        }
        if ($op!='') $filter = "({$op}{$filter})";
        return $filter;
    }
    
    function __toString()
    {
        $this->_filter = '(&'.$this->_filter.'(!(eduPersonPrimaryAffiliation=guest)))';
        return $this->_filter;
    }

}
