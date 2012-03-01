<?php
class UNL_Peoplefinder_Driver_LDAP implements UNL_Peoplefinder_DriverInterface
{
    /**
     * Connection credentials
     * 
     * @param string
     */
    static public $ldapServer = 'ldap.unl.edu ldap-backup.unl.edu';
    
    /**
     * LDAP Connection bind distinguised name
     *
     * @var string
     * @ignore
     */
    static public $bindDN = 'uid=insertyouruidhere,ou=service,dc=unl,dc=edu';
    
    /**
     * LDAP connection password.
     *
     * @var string
     * @ignore
     */
    static public $bindPW             = 'putyourpasswordhere';
    static public $baseDN             = 'dc=unl,dc=edu';
    static public $ldapTimeout        = 10;
    
    /**
     * Attribute arrays
     * Attributes are the fields retrieved in an LDAP QUERY, limit this to
     * ONLY what is USED/DISPLAYED!
     */
    
    /**
     * List attributes are the attributes displayed in a list of results
     * 
     * @var array
     */
    public $listAttributes = array(
        'cn',
        'eduPersonNickname',
        'eduPersonPrimaryAffiliation',
        'givenName',
        'sn',
        'telephoneNumber',
        'uid',
        'unlHRPrimaryDepartment');
    
    /**
     * Details are for UID detail display only.
     * @var array
     */
    public $detailAttributes = array(
        'ou',
        'cn',
        'eduPersonNickname',
        'eduPersonPrimaryAffiliation',
        'givenName',
        'displayName',
        'mail',
        'postalAddress',
        'sn',
        'telephoneNumber',
        'title',
        'uid',
        'unlHRPrimaryDepartment',
        'unlHRAddress',
        'unlSISClassLevel',
        'unlSISCollege',
        'unlSISLocalAddr1',
        'unlSISLocalAddr2',
        'unlSISLocalCity',
        'unlSISLocalState',
        'unlSISLocalZip',
        'unlSISPermAddr1',
        'unlSISPermAddr2',
        'unlSISPermCity',
        'unlSISPermState',
        'unlSISPermZip',
        'unlSISMajor',
        'unlEmailAlias');
    
    /** Connection details */
    public $connected = false;
    public $linkID;

    /** Result Info */
    public $lastQuery;
    public $lastResult;
    public $lastResultCount = 0;
    
    function __construct()
    {
        
    }
    
    /**
     * Binds to the LDAP directory using the bind credentials stored in
     * bindDN and bindPW
     *
     * @return bool
     */
    function bind()
    {
        $this->linkID = ldap_connect(self::$ldapServer);
        if ($this->linkID) {
            $this->connected = ldap_bind($this->linkID,
                                         self::$bindDN,
                                         self::$bindPW);
            if ($this->connected) {
                return $this->connected;
            }
        }
        throw new Exception('Cound not connect to LDAP directory.');
    }
    
    /**
     * Disconnect from the ldap directory.
     *
     * @return unknown
     */
    function unbind()
    {
        $this->connected = false;
        return ldap_unbind($this->linkID);
    }
    
    /**
     * Send a query to the ldap directory
     *
     * @param string $filter     LDAP filter (uid=blah)
     * @param array  $attributes attributes to return for the entries
     * @param bool   $setResult  whether or not to set the last result
     * 
     * @return mixed
     */
    function query($filter,$attributes,$setResult=true)
    {
        $this->bind();
        $this->lastQuery = $filter;
        $sr              = @ldap_search($this->linkID, 
                                        self::$baseDN,
                                        $filter,
                                        $attributes,
                                        0,
                                        UNL_Peoplefinder::$resultLimit,
                                        self::$ldapTimeout);
        if ($setResult) {
            $this->lastResultCount = @ldap_count_entries($this->linkID, $sr);
            $this->lastResult      = @ldap_get_entries($this->linkID, $sr);
            $this->unbind();
            //sort the results
            for ($i=0;$i<$this->lastResult['count'];$i++) {
                if (isset($this->lastResult[$i]['givenname'])) {
                    $name = $this->lastResult[$i]['sn'][0]
                          . ', '
                          . $this->lastResult[$i]['givenname'][0];
                } else {
                    $name = $this->lastResult[$i]['sn'][0];
                }
                $this->lastResult[$i]['insensitiveName'] = strtoupper($name);
            }
            @reset($this->lastResult);
            $this->lastResult = @UNL_Peoplefinder_Driver_LDAP_Util::array_csort(
                                                    $this->lastResult,
                                                    'insensitiveName',
                                                    SORT_ASC);
            return $this->lastResult;
        } else {
            $result = ldap_get_entries($this->linkID, $sr);
            $this->unbind();
            return $result;
        }
    }

    
    /**
     * Get records which match the query exactly.
     *
     * @param string $q Search string.
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getExactMatches($q)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_StandardFilter($q, '&', false);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }
    
    /**
     * Returns an array of UNL_Peoplefinder_Record objects from the ldap
     * query result.
     *
     * @return array(UNL_Peoplefinder_Record)
     */
    protected function getRecordsFromResults()
    {
        $r = array();
        if ($this->lastResultCount > 0) {
            for ($i = 0; $i < $this->lastResultCount; $i++) {
                $r[] = UNL_Peoplefinder_Record::fromLDAPEntry($this->lastResult[$i]);
            }
        }
        return $r;
    }
    
    /**
     * Get results for an advanced/detailed search.
     *
     * @param string $sn   Surname/last name
     * @param string $cn   Common name/first name
     * @param string $eppa Primary Affiliation
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getAdvancedSearchMatches($sn, $cn, $eppa)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_AdvancedFilter($sn, $cn, $eppa, '&', true);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }
    
    /**
     * Find matches similar to the query given
     *
     * @param string $q                Search query
     * @param array  $excluded_records Array of records to exclude.
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getLikeMatches($q, $excluded_records = array())
    {
        // Build filter excluding those displayed above
        $filter = new UNL_Peoplefinder_Driver_LDAP_StandardFilter($q, '&', true);
        $filter->excludeRecords($excluded_records);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }
    
    /**
     * Get an array of records which matche by the phone number.
     *
     * @param string $q EG: 472-1598
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getPhoneMatches($q)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_TelephoneFilter($q);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }

    /**
     * Get the ldap record for a specific uid eg:bbieber2
     *
     * @param string $uid The unique ID for the user you want to get.
     * 
     * @return UNL_Peoplefinder_Record
     */
    function getUID($uid)
    {
        $r = $this->query("(&(uid=$uid))", $this->detailAttributes, false);
        if (isset($r[0])) {
            return UNL_Peoplefinder_Record::fromLDAPEntry($r[0]);
        } else {
            header('HTTP/1.0 404 Not Found');
            throw new Exception('Cannot find that UID.');
        }
    }
    
    
}
