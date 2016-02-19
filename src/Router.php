<?php

namespace SmartRouting;
use SmartRouting\Routing\AbstractRouter;
use SmartRouting\Route;
use HttpExchange\Request\Request;

class Router extends AbstractRouter
{
    protected $request;
    protected $method;
    protected $uri;
    public $route;

    /**
     * Router constructor.
     * @param $request
     */

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->uri = $this->request->getUri()->getPath();
        $this->method = strtoupper($this->request->getMethod());
        $this->route = new Route();   // find, build routes
    }

    /**
     * @return $this
     */
    public function getRoute()
    {
        $route = $this->route->findRoute($this->uri, $this->method);
        if ($route) {
            $this->setResult($route);
        }
    }

    /**
     * @param $routeArray
     */
    private function setResult(array $routeArray)
    {
        $this->controller = ucfirst(array_shift($routeArray)) . 'Controller';
        if ($routeArray) {
            $this->action = strtolower(array_shift($routeArray)) . 'Action';
            $this->params = $routeArray ? $routeArray : [];
        } else {
            $this->action = 'indexAction';
        }
    }
}