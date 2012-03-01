<?php
class UNL_Peoplefinder_Renderer_XML
{

    protected $trustedIP = false;
    protected $displayStudentTelephone = false;
    
    protected $sent_headers = false;
    
    /**
     * Sends the headers and XML preamble.
     *
     * @return void
     */
    function sendHeaders()
    {
        if ($this->sent_headers) {
            return;
        }
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="utf-8"?>
<unl xmlns="http://wdn.unl.edu/xml">'.PHP_EOL;
        $this->sent_headers = true;
    }
    
    /**
     * Render an individual record
     *
     * @param UNL_Peoplefinder_Record $r
     */
    public function renderRecord(UNL_Peoplefinder_Record $r)
    {
        $this->sendHeaders();
        echo '<person>';
        foreach (get_object_vars($r) as $key=>$val) {
            $val = htmlspecialchars($val);
            echo "<$key>{$val}</$key>\n";
        }
        echo '</person>'.PHP_EOL;
    }
    
    public function renderSearchResults(array $records, $start=0, $num_rows=UNL_PF_DISPLAY_LIMIT)
    {
        $this->sendHeaders();
        foreach ($records as $record) {
            $this->renderRecord($record);
        }
    }
    
    public function renderError()
    {
        $this->sendHeaders();
        echo '<error>Please enter more information</error>';
    }
    
    function __destruct()
    {
        if ($this->sent_headers) {
            $this->sendFooter();
        }
    }
    
    function sendFooter()
    {
        echo '</unl>';
    }
}
?>