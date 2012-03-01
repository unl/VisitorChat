<?php
interface UNL_Peoplefinder_RendererInterface
{
    function renderRecord(UNL_Peoplefinder_Record $r);
    function renderListRecord(UNL_Peoplefinder_Record $r);
    function renderSearchResults(array $records, $start=0, $num_rows=UNL_PF_DISPLAY_LIMIT);
    function renderError($message = null);
    function displayInstructions();
}
?>