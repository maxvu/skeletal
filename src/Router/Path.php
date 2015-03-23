<?php

  namespace Skeletal\Router;
  
  class Path {
  
    protected $path;
    
    public function __construct ( $path ) {
      if ( ( $q = strpos( $path, '?' ) ) ) $path = substr( $path, 0, $q );
      $this->path = array_values( array_filter( explode( '/', $path ) ) );
    }
    
    public function getPath () {
      return $this->path;
    }
    
    public function __toString () {
      return '/' . implode( '/', $this->path );
    }
  
  };
  
?>