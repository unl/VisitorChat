<?php
namespace Epoch;

class OutputController extends \Savvy
{
    function __construct($options = array())
    {
        parent::__construct();
        $this->setTemplatePath(array(dirname(dirname(dirname(__FILE__))).'/www/templates/Epoch', 
                                     \Epoch\Controller::$applicationDir.'/www/templates/default'));
    }
    
    public function renderObject($object, $template = null)
    {
        return parent::renderObject($object, $template);
    }
    
    private function getFullname($file)
    {
        foreach ($this->template_path as $path) {
            // get the path to the file
            $fullname = $path . $file;
            
            //Check a default path for format templates BEFORE we check the default directory.
            if ($path == \Epoch\Controller::$applicationDir.'/www/templates/default/' && isset($_GET['format'])) {
                $tempFile     = str_replace(\Epoch\Controller::$customNamespace . "/", '', $file);
                $tempPath     = dirname(dirname(dirname(__FILE__))).'/www/templates/Epoch/formats/' . $_GET['format'];
                $tempFullname = $tempPath . "/" . $tempFile;
                
                if (@is_readable($tempFullname)) {;
                    return $tempFullname;
                }
            }
            
            if (isset($this->templateMap[$fullname])) {
                return $fullname;
            }
            
            if (!@is_readable($fullname)) {;
                continue;
            }

            return $fullname;
        }
        
        return false;
    }
    
    public function findTemplateFile($file)
    {
        if (false !== strpos($file, '..')) {
            // checking for weird path here removes directory traversal threat
            throw new \Savvy_UnexpectedValueException('upper directory reference .. cannot be used in template filename');
        }
        
        //try to find the full name.
        if (!$fullname = $this->getFullname($file)) {
            //we couldn't find it...  lets see if there is a default fall back.  Note: we are probably falling back to the epoch controller here.
            $fullname = $this->getFullname(str_replace(\Epoch\Controller::$customNamespace . "/", '', $file));
        }
        
        if ($fullname) {
            return $fullname;
        }
        
        // could not find the file in the set of paths
        throw new \Savvy_TemplateException('Could not find the template ' . $file);
    }
    
    /**
     * 
     * @param timestamp $expires timestamp
     * 
     * @return void
     */
    function sendCORSHeaders($expires = null)
    {
        // Specify domains from which requests are allowed
        header('Access-Control-Allow-Origin: *');

        // Specify which request methods are allowed
        header('Access-Control-Allow-Methods: GET, OPTIONS');

        // Additional headers which may be sent along with the CORS request
        // The X-Requested-With header allows jQuery requests to go through

        header('Access-Control-Allow-Headers: X-Requested-With');

        // Set the ages for the access-control header to 20 days to improve speed/caching.
        header('Access-Control-Max-Age: 1728000');

        if (isset($expires)) {
            // Set expires header for 24 hours to improve speed caching.
            header('Expires: '.date('r', $expires));
        }
    }
}