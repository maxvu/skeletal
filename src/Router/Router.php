<?php

  namespace Skeletal\Router;
  use Skeletal\HTTP\Request as Request;
  
  class Router {
  
    protected $routes;
    
    public function __construct () {
      $this->routes = array();
    }
    
    public function getRoutes () {
      return $this->routes;
    }
    
    public function addRoute ( $path, $method, $closure ) {
      if ( !is_callable( $closure ) )
        throw new \Exception( 'Given handler is not a Callable.' );
      $this->routes[] = new Route( $path, strtoupper( $method ), $closure );
    }
    
    public function findRoute ( $path, $method ) {
      $retval = NULL;
      $hi_score = 0;
      $path = is_a( 'Path', $path ) ? $path : new Path( $path );
      $method = strtoupper( $method );
      foreach ( $this->routes as $route ) {
        $new_score = $route->matches( $path );
        $methods_match = strtoupper( $route->getMethod() ) === $method;
        if ( $methods_match && $new_score > $hi_score ) {
          $retval = $route;
          $hi_score = $new_score;
        }
      }
      return $retval;
    }
  
  };
  
?>