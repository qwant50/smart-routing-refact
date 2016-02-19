<?php

namespace SmartRouting;

use SmartRouting\Routing\AbstractRoute;
use SmartRouting\Routing\Exception\RoutingException;

class Route extends AbstractRoute
{
    protected $params = [];

    protected $filter = array(
        'num' => '[0-9]+',
        'string' => '[a-zA-Z]+',
        'any' => '[a-zA-Z0-9\-_]+'
    );

    protected $routes = [];

    public function __construct()
    {
        Routes::readRoutes();  // read routes from file
        $this->routes = Routes::getRoutes();
    }

    public function findRoute($path, $method)
    {

        $routes = $this->routes[$method];

        $queryArray = explode('/', trim($path, '/'));
        //   var_dump($queryArray, $method);   works
        foreach ($routes as $name => $route) {
            if (in_array($path, $route)) {
                return $this->parseController(end($route));
            }
            $pattern = $route['pattern'];
            $patternArray = explode('/', trim($pattern, '/'));
            if (!strpbrk($pattern, '?') && strpbrk($pattern, '(')) {
                $patternArrayFiltered = array_map(array($this, 'replaceForFilter'), $patternArray);
                $patternMatcher = '/' . implode('/', $patternArrayFiltered);
                preg_match('\'' . $patternMatcher . '\'', $path, $matched);
                if ($matched) {
                    $paramsNameArray = array_map(array($this, 'setParamsNames'), $patternArray);
                    foreach ($patternArray as $k => $v) {
                        if (strpbrk($v, '(')) {
                            $this->params[$paramsNameArray[$k]] = $queryArray[$k];
                        }
                    }
                    return $this->parseController(end($route));
                }
            } elseif (strpbrk($pattern, '?')) {
                foreach ($patternArray as $k => $v) {
                    if (strpbrk($v, '?')) {
                        $patternMatcherArray[$k] = '((\/)?([a-zA-Z]+)?)?';
                    } elseif (strpbrk($v, '(')) {
                        $patternMatcherArray[$k] = $this->replaceForFilter($v);
                    } elseif (!strpbrk($v, '(')) {
                        $patternMatcherArray[$k] = '\/' . $v . '\/';
                    }
                }
                $patternMatcher2 = implode('', $patternMatcherArray);
                preg_match('\'' . $patternMatcher2 . '\'', $path, $matched);
                if ($matched) {
                    $paramsNameArray = array_map(array($this, 'setParamsNames'), $patternArray);
                    foreach ($patternArray as $k => $v):
                        if (strpbrk($v, '(')) {
                            if (array_key_exists($k, $queryArray)) {
                                $this->params[$paramsNameArray[$k]] = $queryArray[$k];
                            }
                        }
                    endforeach;
                    return $this->parseController(end($route));
                }
            }
        }
    }

    private function replaceForFilter($a)
    {
        if (strpbrk($a, ':')) {
            $filterType = substr($a, strpos($a, ':') + 1, -1);
            return str_replace($a, $this->filter[$filterType], $a);
        } elseif (strpbrk($a, '(')) {
            return str_replace($a, $this->filter['any'], $a);
        }
        return $a;
    }

    public function buildRoute($name, array $params = [], $absolute = false)
    {
        $pattern = $this->getRoutePattern($name);
        if ($params) {
            $patternArray = explode('/', trim($pattern, '/'));
            $paramsNameArray = array_map(array($this, 'setParamsNames'), $patternArray);
            foreach ($params as $k => $v) {
                if (in_array($k, $paramsNameArray)) {
                    $key = array_search($k, $paramsNameArray);
                    $paramsNameArray[$key] = $params[$k];
                } else {
                    throw new RoutingException("Not not enough parameters");
                }
            }
        } else {
            throw new RoutingException("Not not enough parameters");
        }
    }

    public function addFilter($name, $filter)
    {
        $this->pattern[$name] = $filter;
    }

    private function setParamsNames($a)
    {
        if (strpbrk($a, ':')) {
            return substr($a, 1, strpos($a, ':') - 1);
        } elseif (strpbrk($a, '(')) {
            return substr($a, 1, -1);
        }
        return $a;
    }

    protected function parseController($data)
    {
        return explode(':', $data) + $this->params;
    }

    private function getRoutePattern($name)
    {
        foreach ($this->routes as $method => $routeName) {
            return $tempArray = trim($routeName[$name]['pattern'], '/');
        }
    }

/*    private function getRouteDefaultParams($name)
    {
        foreach ($this->routes as $method => $routeName) {
            return $tempArray = $routeName[$name]['defaultParams'];
        }
    }*/

    protected function buildPath($data)
    {
        return '/' . implode('/', (array)$data);
    }

    protected function buildAbsolutePath($data)
    {
        return $this->base . $this->buildPath($data);
    }
}

