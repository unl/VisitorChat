<?php
/**
 * RegExpRouter
 * 
 * This class is used to compile all of the routes for a php application and return
 * routes based on regex and a URI.
 * 
 * @author mfairchild365
 */
namespace RegExpRouter;

class Router
{
    //Determins if routes should be cached or not.
    public static $cacheRoutes = false;
    
    //The directory where your source is stored.
    protected $srcDir = "";
    
    //Array of routes
    protected $routes = array();
    
    /**
     * Constructor
     * 
     * @param array $options - array of options. Requires baseURL.  srcDir is required only if you want to scan for models (srcDir must be a full system path).
     * 
     * @throws Exception
     */
    function __construct(array $options = array())
    {
        //Check if the baseURL is set.
        if (!isset($options['baseURL']) || empty($options['baseURL'])) {
            throw new Exception("You must define the baseURL", 500);
        }
        
        //Set all class properties with the passed options.
        foreach ($options as $key=>$val) {
            $this->$key = $val;
        }
        
        //Get the default routes.
        $this->routes = $this->getDefaultRoutes();
    }
    
    /**
     * Routes based on a requestURI and options.
     * 
     * @param string $requestURI
     * @param array $options
     * 
     * @return array $options - with the model defined (if one was found).
     */
    public function route($requestURI, array $options = array())
    {
        //tidy up the requestURI
        if (!empty($_SERVER['QUERY_STRING'])) {
            $requestURI = substr($requestURI, 0, -strlen($_SERVER['QUERY_STRING']) - 1);
        }
        
        // Trim the base part of the URL
        $requestURI = substr($requestURI, strlen(parse_url($this->baseURL, PHP_URL_PATH)));
        
        //For older systems we used 'view' instead of 'model', this allows for backwards compatability.
        if (isset($options['view'], $routes[$options['view']])) {
            $options['model'] = $routes[$options['view']];
            return $options;
        }
        
        //Loop though all of the routes and check to see the current url matches any routes.
        foreach ($this->routes as $route_exp=>$model) {
            if ($route_exp[0] == '/' && preg_match($route_exp, $requestURI, $matches)) {
                $options += $matches;
                $options['model'] = $model;
                return $options;
            }
        }
        
        //No routes were found, don't return a model.
        return $options;
    }
    
    /**
     * Set the routes.
     * 
     * @param array $newRoutes
     */
    public function setRoutes(array $newRoutes)
    {
        $this->routes = $newRoutes;
    }
    
    /**
     * Get the routes
     * 
     * @return array $routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
    
    /**
     * Gets the default routes by using the cache if we are using cached
     * routes or by compiling the routes.
     * 
     * @return array $routes
     */
    public function getDefaultRoutes()
    {
        //if we are not caching routes, just compile them.
        if (!self::$cacheRoutes) {
            return $this->compileRoutes();
        }
        
        //We are caching routes, so check if we have them cached.
        if (file_exists($this->getCachePath())) {
            //We have them cached, so send them back.
            $cache = file_get_contents($this->getCachePath());
            return unserialize($cache);
        }
        
        //cache the routes because they haven't been cached yet.
        return $this->cacheRoutes();
    }
    
    /**
     * Caches the routes.
     * 
     * @return Array $routes
     */
    public function cacheRoutes()
    {
        //Get the routes.
        $routes = $this->compileRoutes();
        
        //Save the routes on the file system.
        file_put_contents($this->getCachePath(), serialize($routes));
        
        return $routes;
    }
    
    /**
     * Generates and returns the cache path for routes.
     * The path is determined by a hash of the class directory name and prefix.
     * 
     * @return string
     */
    public function getCachePath()
    {
        return sys_get_temp_dir() . "/RegExRouterCache_" . md5($this->srcDir) . ".php";
    }
    
    /**
     * Compiles the routes by looping though all of the models and getting the routes for each model.
     * 
     * @return array $routes
     */
    public function compileRoutes()
    {
        //Initialize an empty array.
        $routes = array();
        
        //Check if we are going to sift though directories.
        if (empty($this->srcDir)) {
            return $routes;
        }
        
        //Directory iterator
        $directory = new \DirectoryIterator($this->srcDir);
        
        //Loop though the src directory and find all sub directories (all models should have a sub directory).
        foreach ($directory as $file) {
            //Only check diretories.
            if ($file->getType() == 'dir' && !$file->isDot()) {
                //generate the filename of the routes class for this model.
                $fileName = $this->srcDir . "/" . $file->getFileName() . "/Routes.php";
                
                //If the file exists, include it.
                if (file_exists($fileName)) {
                    include $fileName;
                }
            }
        }
        
        //Now that we have included all of the routes classes, loop though them.
        foreach (get_declared_classes() as $class) {
            //Add all of the routes as long as the class extends the routes interface
            if (in_array('RegExpRouter\RoutesInterface', class_parents($class))) {
                $routes += call_user_func($class . "::getRoutes");
            }
        }
        
        return $routes;
    }
    
    /**
     * Adds a single route to the routes array.
     * 
     * @param array $route
     * @return RegExpRouter\Router $this
     */
    public function addRoute(array $route)
    {
        array_push($this->routes, $route);
        
        return $this;
    }
    
    public function __invoke($requestURI, array $options = array())
    {
        return $this->route($requestURI, $options);
    }
}
