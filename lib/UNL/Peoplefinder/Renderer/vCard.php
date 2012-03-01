<?php
/**
 * Peoplefinder vcard renderer
 * 
 * PHP version 5
 * 
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */

class UNL_Peoplefinder_Renderer_vCard
{

    protected $displayStudentTelephone = false;
    
    public function renderRecord(UNL_Peoplefinder_Record $r)
    {
        header('Content-Type: text/x-vcard');
        header('Content-Disposition: attachment; filename="'.$r->sn.', '.$r->givenName.'.vcf"');
        //connect, taking in UID
        echo "BEGIN:VCARD\n";
        echo "VERSION:3.0\n";
        echo "N:".$r->sn.";".$r->givenName.";;;\n";
        echo "FN:".$r->givenName." ".$r->sn."\n";
        if(isset($r->unlHRPrimaryDepartment)) echo "ORG:University of Nebraska-Lincoln;".$r->unlHRPrimaryDepartment."\n";
        if (isset($r->unlEmailAlias)) {
            if (($r->eduPersonPrimaryAffiliation != 'student') && isset($r->unlEmailAlias)) echo "EMAIL;type=INTERNET;type=WORK;type=pref:".$r->unlEmailAlias."@unl.edu\n";
        }
        if ($r->eduPersonPrimaryAffiliation != "student" || $this->displayStudentTelephone==true) echo "TEL;type=WORK;type=pref:".$r->telephoneNumber."\n";
        //echo "TEL;type=CELL:(402) 555-1111\n";
        if (isset($r->unlSISLocalPhone)) {
            echo "TEL;type=HOME:{$r->unlSISLocalPhone}\n";
        }
        if (isset($r->unlSISLocalAddr1)) {
            echo "item1.ADR;type=WORK;type=pref:;;".$r->unlSISLocalAddr1;
            if (isset($r->unlSISLocalAddr2)) echo "\\n".$r->unlSISLocalAddr2;
            echo ";".$r->unlSISLocalCity.";".$r->unlSISLocalState.";".$r->unlSISLocalZip.";\n";
            echo "item1.X-ABLabel:local\n";
        }
        if (isset($r->unlSISPermaddr1)) {
            echo "item2.ADR;type=HOME;type=pref:;;".$r->unlSISPermAddr1;
            if(isset($r->unlSISPermAddr2)) echo "\\n".$r->unlSISPermAddr2;
            echo ";".@$r->unlSISPermCity.";".@$r->unlSISPermState.";".@$r->unlSISPermZip.";\n";
            echo "item2.X-ABLabel:permanent\n";
        }
        //echo "item1.X-ABADR:us\n";
        //echo "item2.X-ABADR:us\n";
        //echo "URL:http://www.unl.edu/\n";
        //echo "LOGO;VALUE=uri:http://www.unl.edu/unlpub/2004sharedgraphics/smcolor_wordmark.gif";
        if (isset($r->title)) {
            echo "item3.X-ABRELATEDNAMES;type=pref:".$r->title."\n";
            echo "item3.X-ABLabel:title\n";
        }
        echo "END:VCARD\n";
    }
}
?>