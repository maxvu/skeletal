<?php

  /*
    A Route is a path pattern. Use curly braces in a path definition to indicate
    the name of the parameter that should be grepped. Use a safe collection of
    characters, provide it as a string and do no casting or coercion (string).
  */
  
  namespace Skeletal;

  class Route {
  
    private $pathspec;
    private $description;
    
    private $requires;
    private $accepts;
    private $returns;
    
    public function __construct ( $pathspec ) {
      if ( is_a( $pathspec, 'Path' ) )
        $this->pathspec = $pathspec;
      else if ( is_string( $pathspec ) )
        $this->pathspec = new Path ( $pathspec );
      else
        throw new Exception( "Bad pathspec." );
    }
    
    public function match ( Path $path ) {
      $path = $path->asTokens();
      $route = $this->pathspec->asTokens();
      if ( sizeof( $route ) !== sizeof( $path ) )
        return NULL;
      $params = array();
      for ( $i = 0; $i < sizeof( $route ); $i++ ) {
        if ( $path[$i] === $route[$i] ) continue;
        if ( ( $token_name = $this->tokenOf( $route[$i] ) ) === NULL )
          return NULL;
        $params[$token_name] = $path[$i];
      }
      return $params;
    }
    
    public function getPathspec () {
      return $this->pathspec;
    }
    
    private function tokenOf ( $t ) {
      $match = array();
      return preg_match( 
        '/^\{([a-zA-Z0-9\-\_\.]+)\}$/', $t, $match
      ) === 1 ? $match[1] : NULL;
    }
  
  };

?>