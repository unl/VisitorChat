<?php
/**
 * This class is a routes interface.  All Routes class must
 * implement this interface to work properly.
 * 
 * @author mfairchild365
 *
 */
namespace RegExpRouter;
abstract class RoutesInterface
{
    /**
     * All of the Post POST for this model.
     * @return array
     */
    abstract public static function getPostRoutes();
    
    /**
     * All of the GET Routes for this model.
     * @return array
     */
    abstract public static function getGetRoutes();
    
    /**
     * All of the DELETE Routes for this model.
     * @return array
     */
    abstract public static function getDeleteRoutes();
    
    /**
     * All of the PUT Routes for this model.
     * @return array
     */
    abstract public static function getPutRoutes();
    
    /**
     * Gathers all of the Routes for this object.
     * It then adds the called class's parent's namespace to all of the routes.
     * Finally it returns the routs with the added namespace.
     * 
     * @return array $routes
     */
    public static function getRoutes()
    {
        $class     = get_called_class();
        $routes    = array();
        $namespace = substr($class, 0, strlen($class)-6);
        $routes += call_user_func($class . "::getPostRoutes");
        $routes += call_user_func($class . "::getGetRoutes");
        $routes += call_user_func($class . "::getDeleteRoutes");
        $routes += call_user_func($class . "::getPutRoutes");
        
        return self::addNamesapces($namespace, $routes);
    }
    
    /**
     * Adds a namespace to the routes's model class.
     * 
     * @param string $nameSpace
     * @param array $routes
     * 
     * @return array $newRoutes
     */
    protected static function addNamesapces($nameSpace, array $routes) {
        $newRoutes = array();
        
        foreach ($routes as $regex=>$route) {
            $route = $nameSpace.$route;
            $newRoutes[$regex] = $route;
        }
        
        return $newRoutes;
    }
}