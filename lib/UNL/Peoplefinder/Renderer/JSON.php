<?php
/**
 * Peoplefinder JSON renderer
 * 
 * PHP version 5
 * 
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */

class UNL_Peoplefinder_Renderer_JSON
{
    function __construct(array $options = null)
    {
        
    }
    
    /**
     * Renders a peoplefinder record object
     *
     * @param UNL_Peoplefinder_Record $r record to render
     */
    public function renderRecord(UNL_Peoplefinder_Record $r)
    {
        echo json_encode($r);
    }
    
    public function renderSearchResults(array $records, $start=0, $num_rows=UNL_PF_DISPLAY_LIMIT)
    {
        echo json_encode($records);
    }
}

?>