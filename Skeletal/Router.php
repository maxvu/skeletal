<?php

  /*
    Router houses a simple collection of Routes, categorized by HTTP verb.
    Use get(), post(), etc. to map a Route to a simple callable that accepts a
    Request as the first parameter and a reference to a Response as the second.
    Use route() to obtain the response. For Routes with curly-brace variables 
    (e.g. /user/{id}), destructively merge it into the query string 
    (i.e. get('id')).
  */
  
  namespace Skeletal;

  class Router {
  
    private $routes;
    
    private $unroutableAction;
    private $badRouteAction;
    
    public function __construct ( $context = NULL ) {
      $this->routes = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array()
      );
      $this->unroutableAction = function ( $req, &$rsp ) {
        $rsp->code(404);
      };
      $this->badRouteAction = function ( $req, &$rsp ) {
        $rsp->body('Bad route definition.')->code(500);
      };
      $this->context = NULL;
    }
    
    private function addRoute ( $method, $uri, $handler ) {
      if ( !isset( $this->routes[$method] ) )
        throw new Exception( 'No method {$method} defined.' );
      if ( !is_callable( $handler ) || is_string( $handler ) )
        throw new Exception( 'Handler uncallable.' );
      $this->routes[$method][] = array(
        'route' => new Route( $uri ),
        'handler' => $handler
      );
    }
    
    public function bindContext ( &$context ) {
      $this->context = $context;
    }
    
    /*
      Methods to define routes.
    */
    
    public function get ( $uri, $handler ) {
      $this->addRoute( 'GET', $uri, $handler );
    }
    
    public function post ( $uri, $handler ) {
      $this->addRoute( 'POST', $uri, $handler );
    }
    
    public function put ( $uri, $handler ) {
      $this->addRoute( 'PUT', $uri, $handler );
    }
    
    public function delete ( $uri, $handler ) {
      $this->addRoute( 'DELETE', $uri, $handler );
    }
    
    public function route ( Request $req ) {
      foreach ( $this->routes[$req->method()] as $r ) {
        $params = array();
        if ( ( $params = $r['route']->match( $req->path() ) ) !== NULL ) {
          $req->queryString = array_merge( $req->queryString, $params );
          return $this->invokeHandler( $r['handler'], $req );
        }
      }
      
      if ( $req->requestMethod === 'HEAD' ) {
        $req->requestMethod = 'GET';
        return $this->route( $req )->body('');
      }
      
      return $this->invokeHandler( $this->unroutableAction, $req );
    }
    
    private function invokeHandler ( $handler, $req ) {
      $respRef = new Response();
      $tk = null;
      if ( $this->context != null )
        $handler = $handler->bindTo( $this->context );
      
      $respRet = $handler( $req, $respRef );
      if ( is_a( $respRet, 'Response' ) )
        return $respRet;
      return $respRef;
        
      return $this->invokeHandler( $this->badActionRoute, $req );
    }
  
  };

?>