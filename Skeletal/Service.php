<?php
  
  namespace Skeletal;

  class Service {
  
    private $router;
    private $dependencies;
    private $filters;
    public $session;
  
    public function __construct ( ) {
      $this->router = new Router();
      $this->session = new Session();
    }
    
    public function __set ( $property, $value ) {
      $this->dependencies[$property] = $value;
    }
    
    public function __get ( $property ) {
      return isset( $this->dependencies[$property] ) ?
        $this->dependencies[$property] : NULL;
    }
    
    public function &router () {
      return $this->router;
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