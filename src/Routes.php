<?php
/**
 * Created by PhpStorm.
 * User: roach
 * Date: 13.02.16
 * Time: 17:05
 */

namespace SmartRouting;


use SmartRouting\Routing\AbstractRouteCollection;
use SmartRouting\Routing\Exception\RoutingException;

/**
 * Class Routes
 * This is where all routes stored
 * @package SmartRouting
 */
class Routes extends AbstractRouteCollection
{
    /**
     * @var array
     */
    protected static $routes = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array(),
        'PATCH' => array()
    );


    /**
     * Add route
     * @param $name
     * @param $pattern
     * @param $controller
     * @param string $method
     * @throws RoutingException
     */
    public static function add($name, $pattern, $controller, $method = 'GET')
    {

        $method = strtoupper($method);

        if (array_key_exists($name, self::$routes[$method])){
            throw new RoutingException("Route $name already exists");
        }

        self::$routes[$method][$name] = array(
            'pattern' => $pattern,
            'controller' => $controller,
        );

        self::saveRoutes();

    }

    /**
     * Delete route
     * @param $name
     */
    public static function deleteRoute($name)
    {
        foreach (self::$routes as $method => $route) {
            if(array_key_exists($name, $route)){
                unset (self::$routes[$method][$name]);
            }
        }
    }

    /**
     * Read routes from file
     */
    public static function readRoutes()
    {
        $routesFile = dirname(__FILE__). '/config/config.php';
        self::$routes = include $routesFile;
        //var_dump(self::$routes);
    }

    /**
     * Save routes to file
     */
    public static function saveRoutes()
    {
        $routesFile = dirname(__FILE__). '/config/config.php';
        file_put_contents ( $routesFile , '<?php return '.var_export( self::$routes, true ).";\n");
    }

    /**
     * Get routes array
     * @return array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }
}