<?php
namespace Epoch;

class Controller
{
    /**
     * Options array
     * Will include $_GET vars
     */
    public $options = array(
        'format' => 'html'
    );

    public static $customNamespace = 'App';
    
    public static $applicationDir = false;

    //Set cacheRoutes here isntead of \Epoch\Router.  include path might not work until this class is constructed.
    public static $cacheRoutes = true;
    
    public static $url = '';

    protected static $db_settings = array(
        'host'     => 'localhost',
        'user'     => 'wub',
        'password' => 'wub',
        'dbname'   => 'wub'
    );
    
    public $actionable = array();
    
    public $router;
    
    public static $templater;
    
    function __construct($options = array(), $autoRoute = true)
    {
        $this->options = $options + $this->options;
        
        //Make sure we have the correct include path...
        $this->setIncludePath();
        
        //Set the cacheRoutes.
        \Epoch\Router::$cacheRoutes = self::$cacheRoutes;
        
        if (!self::$applicationDir) {
            self::$applicationDir = dirname(dirname(dirname(__FILE__)));
        }
        
        //Set up the router.
        $this->router = $router = new \Epoch\Router(array('baseURL' => \Epoch\Controller::$url, 'srcDir' => self::$applicationDir . "/src/" . $this->namespaceToDirector(\Epoch\Controller::$customNamespace) . "/"));
        
        //Set up the templater.
        self::$templater = new \Epoch\OutputController();
        
        //Will use $this->options to autoRoute.
        if ($autoRoute) {
            $this->autoRoute();
        }
        
        try {
            
            if (!empty($_POST)) {
                $this->handlePost();
            }
            
            $this->run();
        } catch(\Exception $e) {
            if (isset($this->options['ajaxupload'])) {
                echo $e->getMessage();
                exit();
            }

            if (false == headers_sent()
                && $code = $e->getCode()) {
                header('HTTP/1.1 '.$code.' '.$e->getMessage());
                header('Status: '.$code.' '.$e->getMessage());
            }

            $this->actionable = $e;
        }
    }
    
    function namespaceToDirector($namespace) {
        return str_replace(array("\\", "_"), "/", $namespace);
    }
    
    private function setIncludePath()
    {
        return set_include_path(
            implode(PATH_SEPARATOR, array(get_include_path())).PATH_SEPARATOR
            .dirname(dirname(dirname(__FILE__))).'/lib/php'.PATH_SEPARATOR
            .dirname(dirname(dirname(__FILE__))).'/lib/php/RegExpRouter/src'
        );
    }
    
    /**
     * auto routes based on the options for the controller.
     * 
     */
    public function autoRoute()
    {
        //Sanatize input.
        if (isset($this->options['model'])) {
            unset($this->options['model']);
        }
        
        //Do the routing.
        $url = "";
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        }
        $this->options = $this->router->route($url, $this->options);
    }

    public static function setDbSettings($settings = array())
    {
        self::$db_settings = $settings + self::$db_settings;
    }

    public static function getDbSettings()
    {
        return self::$db_settings;
    }

    /**
     * Handle data that is POST'ed to the controller.
     *
     * @return void
     */
    function handlePost()
    {
        if (!isset($_POST['_class'])) {
            // Nothing to do here
            return;
        }
        
        $object = new $_POST['_class']($this->options);
        
        //the function handle post is /not/ required, so we shouldn't bet on it being there.
        if (method_exists($object, 'handlePost')) {
            $object->handlePost($_POST);
        }
    }
    
    function run()
    {
         if (!isset($this->options['model'])) {
             throw new \Exception('Un-registered view', 404);
         }
         $this->actionable = new $this->options['model']($this->options);
    }
    
    /**
     * Connect to the database and return it
     *
     * @return mysqli
     */
    public static function getDB()
    {
        static $db = false;
        if (!$db) {
            $settings = self::getDbSettings();
            $db = new \mysqli($settings['host'], $settings['user'], $settings['password'], $settings['dbname']);
            if (mysqli_connect_error()) {
                throw new \Exception('Database connection error (' . mysqli_connect_errno() . ') '
                        . mysqli_connect_error());
            }
            $db->set_charset('utf8');
        }
        return $db;
    }
    
    static function redirect($url, $exit = true)
    {
        header('Location: '.$url);
        if (false !== $exit) {
            exit($exit);
        }
    }
    
    /**
     * Render the actionable items for this controller via savvy.
     * 
     * @return string the rendered output.
     */
    function render()
    {
        if ($this->options['format'] != 'html') {
            self::$templater->addTemplatePath(self::$applicationDir . '/www/templates/' . $this->options['format']);
            self::$templater->addTemplatePath(dirname(dirname(dirname(__FILE__))).'/www/templates/Epoch/formats/' . $this->options['format']);
            switch($this->options['format']) {
                case 'json':
                    header('Content-type:application/json;charset=UTF-8');
                    break;
                default:
                    header('Content-type:text/html;charset=UTF-8');
            }
        }
        
        // Always escape output, use $context->getRaw('var'); to get the raw data.
        self::$templater->setEscape('htmlentities');
        
        return self::$templater->render($this);
    }

    public static function makeClickableLinks($text) {
        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#+%-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
    }
}
