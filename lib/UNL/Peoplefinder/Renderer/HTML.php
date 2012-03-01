<?php
/**
 * Peoplefinder HTML Renderer
 *
 * PHP version 5
 *
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */

/**
 * Determines if a network in the form of 192.168.17.1/16 or
 * 127.0.0.1/255.255.255.255 or 10.0.0.1 matches a given ip
 * @param $network The network and mask
 * @param $ip The ip to check
 * @return bool true or false
 */
function net_match($network, $ip) {
     $ip_arr = explode('/', $network);
     $network_long = ip2long($ip_arr[0]);
     $x = ip2long($ip_arr[1]);
     $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
     $ip_long = ip2long($ip);
     return ($ip_long & $mask) == ($network_long & $mask);
}

/**
 * Class to render html output for results
 *
 * PHP version 5
 *
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Renderer_HTML
{
    
    protected $trustedIP = false;
    public $uri;
    
    public $displayLimit;
    
    /** This can be set to a javascript function name to send the UID to when clicking a uid */
    public $uid_onclick;
    /** This defines a mode in which the directory is searched to return one user. */
    public $choose_uid = false;
    public $page_onclick;
    
    function __construct(array $options = null)
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $validIPs = array('129.93.0.0/16','65.123.32.0/19','64.39.240.0/20','216.128.208.0/20');
            foreach ($validIPs as $range) {
                if (net_match($range, $_SERVER['REMOTE_ADDR'])) {
                    $this->trustedIP = true;
                    break;
                }
            }
        }
        $this->displayLimit = UNL_Peoplefinder::$displayResultLimit;
        $this->uri          = $_SERVER['SCRIPT_NAME'];
        if (isset($options)) {
            $this->setOptions($options);
        }
    }
    
    /**
     * This function sets parameters for this class.
     *
     * @param array $options an associative array of options to set.
     */
    function setOptions(array $options)
    {
        foreach ($options as $option=>$val) {
            if (property_exists($this,$option)) {
                $this->$option = $val;
            } else {
                echo 'Warning: Trying to set unkown option ['.$option.'] for object '.get_class($this)."\n";
            }
        }
    }

    /**
     * Renders a peoplefinder record object
     *
     * @param UNL_Peoplefinder_Record $r record to render
     */
    public function renderRecord(UNL_Peoplefinder_Record $r)
    {
        echo "<div class='vcard {$r->eduPersonPrimaryAffiliation}'>\n";
        if (isset($r->mail)
            && ($r->eduPersonPrimaryAffiliation != 'student' || $this->displayStudentEmail==true)) {
            $displayEmail = true;
        } else {
            $displayEmail = false;
        }
        if ($displayEmail && isset($r->unlEmailAlias)) echo "<a class='email' href='mailto:{$r->unlEmailAlias}@unl.edu'>";
        if ($r->ou == 'org') {
            echo '<span class="cn">'.$r->cn.'</span>'.PHP_EOL;
        } else {
            echo '<span class="fn">'.$r->displayName.'</span>'.PHP_EOL;
            if (isset($r->eduPersonNickname)) echo '<span class="nickname">'.$r->eduPersonNickname.'</span>'.PHP_EOL;
        }
        if ($displayEmail && isset($r->unlEmailAlias)) echo "</a>\n";
        if (!empty($r->eduPersonPrimaryAffiliation)) echo '<span class="eppa">('.$r->eduPersonPrimaryAffiliation.')</span>'.PHP_EOL;
        echo '<div class="vcardInfo">'.PHP_EOL;
        echo '<a class="planetred_profile" href="http://planetred.unl.edu/pg/profile/unl_'.$r->uid.'" title="Planet Red Profile for '.$r->cn.'"><img class="photo frame" src="http://planetred.unl.edu/mod/profile/icondirect.php?username=unl_'.$r->uid.'&amp;size=medium"  alt="Photo of '.$r->displayName.'" /></a>';
        if (isset($r->unlSISClassLevel)) {
            switch ($r->unlSISClassLevel) {
                case 'FR':
                    $class = 'Freshman,';
                    break;
                case 'SR':
                    $class = 'Senior,';
                    break;
                case 'SO':
                    $class = 'Sophomore,';
                    break;
                case 'JR':
                    $class = 'Junior,';
                    break;
                case 'GR':
                    $class = 'Graduate Student,';
                    break;
                default:
                    $class = $r->unlSISClassLevel;
            }
            echo '<span class="title">'.$class." ".$this->formatMajor($r->unlSISMajor).'&ndash;'.$this->formatCollege($r->unlSISCollege).'</span>';
        }
        
//        if (isset($r->unlSISLocalAddr1)) {
//            $localaddr = array($r->unlSISLocalAddr1, $r->unlSISLocalAddr2, $r->unlSISLocalCity, $r->unlSISLocalState, $r->unlSISLocalZip);
//            $this->renderAddress($localaddr, 'Local', 'workAdr');
//        }
//        
//        if (isset($r->unlSISPermAddr1)) {
//            $permaddr  = array($r->unlSISPermAddr1, $r->unlSISPermAddr2, $r->unlSISPermCity, $r->unlSISPermState, $r->unlSISPermZip);
//            $this->renderAddress($permaddr, 'Home', 'homeAdr');
//        }
        
        if (isset($r->title)) {
            echo "<span class='title'>{$r->title}</span>\n";
        }
        
        if (isset($r->unlHRPrimaryDepartment)) {
            $org_name = 'University of Nebraska&ndash;Lincoln';
            if ($r->unlHRPrimaryDepartment == 'Office of the President') {
                $org_name = 'University of Nebraska';
            }
            $dept_url = UNL_PEOPLEFINDER_URI.'departments/?d='.urlencode($r->unlHRPrimaryDepartment);
            echo "<span class='org'>\n\t<span class='organization-unit'><a href='{$dept_url}'>{$r->unlHRPrimaryDepartment}</a></span>\n\t<span class='organization-name'>$org_name</span></span>\n";
        }
        
        if (isset($r->postalAddress)) {
            if (strpos($r->postalAddress,'UNL')!= -1 || strpos($r->postalAddress,'UNO')!= -1) {
                $address = $r->formatpostalAddress();

                if( strpos($address['postal-code'],'68588') == 0 )
                {
                    $address['street-address'] = $this->replaceBuildingCode($address['street-address']);
                }

                echo '<div class="adr workAdr">
                     <span class="type">Work</span>
                     <span class="street-address">'. $address['street-address'] . '</span>
                     <span class="locality">' . $address['locality'] . '</span>
                     <span class="region">' . $address['region'] . '</span>
                     <span class="postal-code">' . $address['postal-code'] . '</span>
                     <div class="country-name">USA</div>
                    </div>'.PHP_EOL;
            } else {
                echo "<span class='adr'>{$r->postalAddress}</span>\n";
            }
        }
        
        if (strpos($_SERVER['HTTP_USER_AGENT'], "iPhone") === false) {
            $href = "wtai://wp/mc;";
            $isIPhone = false;
        } else {
            $href = "tel:";
            $isIPhone = true;
        }
        if (isset($r->telephoneNumber)) {
            
            echo '<div class="tel workTel">
                     <span class="type">Work</span>
                     <span class="value">'.$this->formatPhone($r->telephoneNumber).'</span>
                    </div>'.PHP_EOL;
        }
        
        if (isset($r->unlSISLocalPhone)) {
            echo '<div class="tel homeTel">
                     <span class="type">Phone</span>
                     <span class="value">'.$this->formatPhone($r->unlSISLocalPhone).'</span>
                    </div>'.PHP_EOL;
        }
        
        if ($displayEmail) {
            if ($r->unlEmailAlias != 'president') {
                $email = $r->unlEmailAlias.'@unl.edu';
            } else {
                $email = $r->unlEmailAlias.'@nebraska.edu';
            }
            echo "<span class='email'><a class='email' href='mailto:$email'>$email</a></span>\n";
            if ($this->trustedIP===true) echo "<span class='email delivery'>Delivery Address: {$r->mail}</span>\n";
        }
        $linktext = '<img src="/ucomm/templatedependents/templatecss/images/mimetypes/text-vcard.gif" alt="vCard" /> <span class="caption">vCard</span>'.PHP_EOL;
        echo $this->getVCardLink($r->uid, $linktext, null, 'Download V-Card for '.$r->givenName.' '.$r->sn);
        echo '</div>'.PHP_EOL.'</div>'.PHP_EOL;
    }
    
    public function renderAddress($address, $type, $class = null)
    {
        if (!isset($class)) {
            $class = '';
        }
        $addr = '
        <div class="adr '.$class.'">
         <span class="type">'.$type.'</span>
         <span class="street-address">'.$address[0].'</span>
         <span class="locality">'.$address[2].'</span>
         <span class="region">'.$address[3].'</span>
         <span class="postal-code">'.$address[4].'</span>';
        if (isset($address[5])) {
            $addr .= '<div class="country-name">'.$address[5].'</div>';
        }
        $addr .= '</div>';
        echo $addr;
    }
    
    /**
     * Takes in a street address of a staff or faculty member, a building
     * code in a string with a link to the building in the virtual tour
     *
     * @param string $streetaddress Street Address of a staff or faculty member
     *
     * @return string
     */
    private function replaceBuildingCode($streetaddress)
    {
        require_once 'UNL/Common/Building.php';
        $regex = "/([A-Za-z0-9].) ([A-Z0-9\&]{2,4})/" ; //& is for M&N Building
        
        if (preg_match($regex, $streetaddress, $matches)) {
            $bldgs = new UNL_Common_Building();
            
            if ($bldgs->buildingExists($matches[2])) {
                
                $replace = '${1} <a class="location mapurl" href="http://www1.unl.edu/tour/${2}">${2}</a>';
                return preg_replace($regex, $replace, $streetaddress);
            }
        }
        
        return $streetaddress;
    }
    
    /**
     * This function takes in a string representing a phone number
     * and formats it to be rendered as a clickable calling link
     * 
     * @param string $phone A telephone number
     * @return string
     */
    public function formatPhone($phone)
    {
        $link = '<a href="';
        if (strpos($_SERVER['HTTP_USER_AGENT'], "iPhone") === false) {
            $link .= "wtai://wp/mc;".str_replace(array("(", ")", "-"), "", $phone);
        } else {
            $link .= "tel:".$phone;
        }
        $link .= '">'.$phone.'</a>';
        return $link;
    }
    
    /**
     * This function takes in an array of address information and formats it
     *
     * @param array $addressArray Address information
     * <code>
     * $addressArray[0] = Address line 1
     * $addressArray[1] = Address line 2
     * $addressArray[2] = City
     * $addressArray[3] = State
     * $addressArray[4] = Zip
     * $addressArray[5] = Country
     * </code>
     *
     * @return string
     */
    public function formatAddress($addressArray)
    {
        if (isset($addressArray[0])) {
            $address = $addressArray[0]."<br />";
            if (isset($addressArray[1])) $address .= $addressArray[1]."<br />";
            $address .= $addressArray[2].", ".$addressArray[3]." ".$addressArray[4];
            if (isset($addressArray[5])) $address .= "<br />".$addressArray[4];
        } else {
            $address = 'Unlisted';
        }
        return $address;
    }
    
    public function displayPageLinks($num_records, $start, $end)
    {
        //Display Page information
        $page = (isset($_GET['p']))?$_GET['p']:0;
        $next = $page + 1;
        if ($page>=1) $prevLink = '<a class="previous" href="'.$this->uri.'?'.preg_replace('/[&]?p=\d/','',$_SERVER['QUERY_STRING']).'&amp;p='.($page-1).'">&lt;&lt;&nbsp;</a>';
        else $prevLink = '&lt;&lt;&nbsp;';
        if ($end < $num_records) $nextLink = "<a class='next' href='".$this->uri."?".preg_replace("/[&]?p=\d/","",$_SERVER['QUERY_STRING'])."&amp;p=$next'>&nbsp;&gt;&gt;</a>";
        else $nextLink = '&nbsp;&gt;&gt;';
        return '<div class="cNav">'.$prevLink.$nextLink.'</div>';
    }
    
    public function renderListRecord(UNL_Peoplefinder_Record $r)
    {
        if ($r->ou == 'org') {
            $linktext = $r->cn;
        } else {
            $linktext = $r->sn . ',&nbsp;'. $r->givenName;
            if (isset($r->eduPersonNickname)) {
                $linktext .= ' "'.$r->eduPersonNickname.'"';
            }
        }
        
        echo '<div class="fn">'.$this->getUIDLink($r->uid, $linktext, $this->uid_onclick).'</div>'.PHP_EOL;
        if (isset($r->eduPersonPrimaryAffiliation)) echo '<div class="eppa">('.$r->eduPersonPrimaryAffiliation.')</div>'.PHP_EOL;
        if (isset($r->unlHRPrimaryDepartment)) echo '<div class="organization-unit">'.$r->unlHRPrimaryDepartment.'</div>'.PHP_EOL;
        if (isset($r->title)) echo '<div class="title">'.$r->title.'</div>'.PHP_EOL;
        if (isset($r->telephoneNumber)) echo '<div class="tel">'.$this->formatPhone($r->telephoneNumber).'</div>'.PHP_EOL;
        
        echo $this->getUIDLink($r->uid, 'contact info', $this->uid_onclick, 'cInfo');
		if ($this->choose_uid) {
		    echo '<div class="pfchooser"><a href="#" onclick="return pfCatchUID(\''.$r->uid.'\');">Choose this person</a></div>'.PHP_EOL;
		}
    }
    
    public function renderSearchResults(array $records, $start=0, $num_rows=UNL_PF_DISPLAY_LIMIT)
    {
        if (($start+$num_rows)>count($records)) {
            $end = count($records);
        } else {
            $end = $start+$num_rows;
        }
        if ($start > 0 || $end < count($records)) {
            $navlinks = $this->displayPageLinks(count($records), $start, $end);
        } else {
            $navlinks = '';
        }
        echo "<div class='result_head'>Results ".($start+1)." - $end out of ".count($records).':'.$navlinks.'</div>'.PHP_EOL;
        echo '<ul>';
        for ($i = $start; $i<$end; $i++) {
            $even_odd = ($i % 2) ? '' : 'alt';
            if ($records[$i]->ou == 'org') {
                $class = 'org_Sresult';
            } else {
                $class = 'ppl_Sresult';
            }
            $class .= ' '.$records[$i]->eduPersonPrimaryAffiliation;
            echo '<li class="'.$class.' '.$even_odd.'">';
            $this->renderListRecord($records[$i]);
            echo '</li>'.PHP_EOL;
        }
        echo '</ul>';
        echo "<div class='result_head'>$navlinks</div>";
    }
    
    public function getUIDLink($uid, $linktext = null, $onclick = null, $class = null)
    {
        $uri = $this->uri.'?uid='.$uid;
        if (isset($linktext)) {
            $link = '<a href="'.$uri.'"';
            if (isset($onclick)) {
                $link .= ' onclick="return '.$this->uid_onclick.'(\''.$uid.'\');"';
            }
            if (isset($class)) {
                $link .= ' class="'.$class.'"';
            }
            $link .= '>'.$linktext.'</a>';
            return $link;
        } else {
            return $uri;
        }
    }
    
    /**
     * Formats a major subject code into a text description.
     *
     * @param string $subject Subject code for the major eg: MSYM
     * 
     * @return string
     */
    public function formatMajor($subject)
    {

        include_once 'Cache/Lite.php';
        $c = new Cache_Lite();
        if ($subject_xml = $c->get('catalog subjects')) {
            
        } else {
            if ($subject_xml = file_get_contents('http://bulletin.unl.edu/?view=subjects&format=xml')) {
                $c->save($subject_xml);
            } else {
                $c->extendLife();
                $c->get('catalog subjects');
            }
        }
        
        $d = new DOMDocument();
        $d->loadXML($subject_xml);
        if ($subject_el = $d->getElementById($subject)) {
            return $subject_el->textContent;
        }
        
        switch ($subject) {
            case 'UNDL':
                return 'Undeclared';
            case 'PBAC':
                return 'Non-Degree Post-Baccalaureate';
            default:
                return $subject;
        }
    }
    
    /**
     * Format a three letter college abbreviation into the full college name.
     *
     * @param string $college College abbreviation = FPA
     * 
     * @return string College of Fine &amp; Performing Arts
     */
    public function formatCollege($college)
    {
        include_once 'UNL/Common/Colleges.php';
        $colleges = new UNL_Common_Colleges();
        if (isset($colleges->colleges[$college])) {
            return htmlentities($colleges->colleges[$college]);
        }
        
        return $college;
    }
    
    public function getVCardLink($uid, $linktext = null,$onclick = null,$title = null)
    {

        $uri = $this->uri.'vcards/'.$uid;
        if (isset($linktext)) {
            $link = '<a href="'.$uri.'"';
            if (isset($onclick)) {
                $link .= ' onclick="return '.$onclick.'(\''.$uid.'\');"';
            }
            if (isset($title)) {
                $link .= ' title="'.$title.'"';
            }
            $link .= ' class="vcf">'.$linktext.'</a>';
            return $link;
        } else {
            return $uri;
        }
    }
    
    
    
    public function renderError()
    {
        echo "<p>Please enter more information or <a href='".$_SERVER['PHP_SELF']."?adv=y' title='Click here to perform a detailed Peoplefinder search'>try a Detailed Search.</a></p>";
    }
    
    /**
     * Displays the instructions for using peoplefinder.
     *
     * @param bool $adv Show advanced instructions or default instructions.
     * 
     * @return void
     */
    function displayInstructions($adv=false)
    {
        echo '<div style="padding-top:10px;width:270px;" id="instructions">';
        if ($adv) {
            echo 'Enter in as much of the first and/or last name you know, ' .
                 'you can also select a primary affiliation to refine your search.';
        } else {
            echo 'Enter in as much of the name as you know, first and/or last '.
                 'name in any order.<br /><br />Reverse telephone number lookup: '.
                 'enter last three or more digits.';
        }
        echo '</div>';
    }
    
    /**
     * Display the standard search form.
     *
     * @return void
     */
    function displayStandardForm()
    {
        include 'standardForm.php';
    }

    /**
     * Display the advanced form.
     *
     * @return void
     */
    function displayAdvancedForm()
    {
        include 'advancedForm.php';
    }
}

?>