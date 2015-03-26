<?php

  namespace Skeletal\Service;
  
  use \Skeletal\HTTP\Method as Method;
  use \Skeletal\HTTP\Request as Request;
  use \Skeletal\HTTP\Response as Response;
  use \Skeletal\Router\Path as Path;
  use \Skeletal\Session\Session as Session;
  use \Skeletal\Session\CLISession as CLISession;
  
  class Service {
  
    protected $router;
    protected $session;

    protected $onException;   // ( $req, &$rsp, $ex )
    protected $onNotFound;    // ( $req, &$rsp )
    
    public function __construct () {
      $this->router = new \Skeletal\Router\Router();
      $this->session = new Session();
      
      $this->onNotFound = function ( $req, &$resp ) {
        $resp->badRequest()->text( '404 - Not Found' )->code(404);
      };
      $this->onException = function ( $req, &$resp, $ex ) {
        print_r( $e );
        $resp->serverError()->text( '500 - Server Error' )->code(500);
      };
    }
    
    /*
      Register these two events.
    */
    
    public function onNotFound ( $callback ) {
      if ( !is_callable( $callback ) )
        throw new \InvalidArgumentException( "onNotFound: not a valid callback" );
      $this->onNotFound = $callback;
    }
    
    public function onException ( $callback ) {
      if ( !is_callable( $callback ) )
        throw new \InvalidArgumentException( "onException: not a valid callback" );
      $this->onException = $callback;
    }
    
    /*
      Get and set arbitrary attributes on the Service to give Routes' closures
      access to dependencies.
    */
    
    public function __get ( $property ) {
      return isset( $this->{$property} ) ? $this->{$property} : NULL;
    }
    
    public function __set ( $property, $value ) {
      $this->{$property} = $value;
    }
    
    public function __isset ( $property ) {
      return isset( $this->{$property} );
    }
    
    public function __unset ( $property ) {
      unset( $this->{$property} );
    }
    
    /*
      Allow for get( $path, $callback ), post( $path, $callback ), etc.
    */
    
    public function __call ( $method, $args ) {
      $is_http_method = in_array( strtoupper( $method ), Method::ALL() );
      if ( $is_http_method && sizeof( $args ) === 2 )
        $this->router->addRoute(
          new Path( $args[0] ), strtoupper( $method ), $args[1]
        );
      else
        throw new \InvalidArgumentException( "No method $method" );
    }
    
    /*
      Turn a Request into a Response.
      Merge path variables (e.g. "/item/{id}") into _GET.
      Treat HEAD requests as GETs, but stripping the body.
      Call $this->onNotFound on no match.
    */
    
    public function route ( Request $request ) {
      $route = $this->router->findRoute( $request->path(), $request->method() );
      
      // Found the right path
      if ( $route !== NULL ) {
        $request->queryString = array_merge(
          $route->apply( $request->requestPath ),
          $request->queryString
        );
        return $this->invokeCallback( $request, $route->getClosure() );
      }
      
      // HEAD wasn't explicitly defined, but route to matching GET, set the 
      // appropriate 'Content-Length' and strip body.
      if ( $request->method() === Method::$HEAD ) {
        $route = $this->router->findRoute( $request->path(), Method::$GET );
        if ( $route !== NULL ) {
          $request->requestMethod = Method::$GET;
          $response = $this->route( $request );
          $request->requestMethod = Method::$HEAD;
          return $response->body('');
        }
      }
      
      // Not found
      return $this->invokeCallback( $request, $this->onNotFound );
    }
    
    public function serve () {
      Response::send( $this->route( Request::current() ) );
    }
    
    /*
      Execute the handler in $this scope.
      Intercept exceptions and give to $this->onException.
    */
    
    private function invokeCallback ( Request $request, $handler ) {
      $response = new Response();
      $handler = $handler->bindTo( $this );
      try
        call_user_func_array( $handler, array( $request, &$response ) );
      catch ( \Exception $ex )
        call_user_func_array( $this->onException, [ $request, &$response, $ex ] );
      return $response;
    }
  
  };

?>