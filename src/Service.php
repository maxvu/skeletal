<?php
  
  namespace Skeletal;

  class Service {
  
    private $name;
    private $router;
    private $dependencies;
    private $filters;
    public $session;
  
    public function __construct ( $name = NULL ) {
      $this->name = $name;
      $this->router = new Router();
      $this->router->bindContext( $this );
      $this->session = new Session();
    }
    
    public function __set ( $property, $value ) {
      $this->dependencies[$property] = $value;
    }
    
    public function __get ( $property ) {
      return isset( $this->dependencies[$property] ) ?
        $this->dependencies[$property] : NULL;
    }
    
    public function addRoute ( $method, $uri, $callback ) {
      $this->router->addRoute( $method, $uri, $callback );
    }
    
    public function debug ( $uri ) {
      $router =& $this->router;
      $this->router->addRoute( 'GET', $uri, function ( $rq, $rs ) 
        use ( $router ) {
          $obj = array();
          foreach ( $this->router->getRoutes() as $route ) {
            $obj[] = $route->getPathSpec();
          }
          $rs->json( $obj );
      });
    }
    
    public function defineFilter ( $name, $filter ) {
      Parameter::addTypeFilter( $name, $filter );
    }
    
    public function serve ( $request = NULL ) {
      if ( !is_a( $request, 'Request' ) )
        $request = Request::current();
      $this->router->route( $request )->send();
    }
  
  };

?>