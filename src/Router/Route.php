<?php

  namespace Skeletal\Router;
  
  class Route {
  
    protected $strPath;
    protected $path;
    protected $method;
    protected $closure;
    
    public function __construct ( $path, $method, callable $closure ) {
      $path = is_a( 'Path', $path ) ? $path : new Path( $path );
      $this->strPath = (string) $path;
      $this->path = array();
      foreach ( $path->getPath() as $token ) {
        if ( preg_match( '/^\{(\S+)\}$/', $token, $match ) === 1 )
          $this->path[] = RouteToken::wildcard( $match[1] );
        else
          $this->path[] = RouteToken::exactMatch( $token );
      }
      $this->method = $method;
      $this->closure = $closure;
    }
    
    public function getPath () {
      return $this->path;
    }
    
    public function getMethod () {
      return $this->method;
    }
    
    public function getClosure () {
      return $this->closure;
    }
    
    public function getPriority () {
      $len = sizeof( $this->path );
      $score = array_reduce( $this->path, function ( $carry, $token ) {
        return $carry + $token->getPriority();
      }, 0);
      return 7 * $len + 5 * $score;
    }
    
    public function matches ( $p ) {
      if ( !is_a( $p, 'Path' ) )
        $p = new Path( $p );
      $coeff = 1;
      $p = $p->getPath();
      if ( sizeof( $p ) !== sizeof( $this->path ) )
        return 0;
      for ( $i = 0; $i < sizeof( $this->path ); $i++ ) {
        if ( !$this->path[$i]->matches( $p[$i] ) )
          return 0;
        $coeff += $this->path[$i]->getPriority();
      }
      return $coeff;
    }
    
    public function apply ( $p ) {
      if ( !is_a( $p, 'Path' ) )
        $p = new Path( $p );
      if ( $this->matches( $p ) < 1 )
        return NULL;
      $p = $p->getPath();
      $result = array();
      for ( $i = 0; $i < sizeof( $this->path ); $i++ )
        if ( ( $param = $this->path[$i]->getName() ) !== NULL )
          $result[$param] = $p[$i];
      return $result;
    }
    
    public function __toString () {
      return $this->strPath;
    }
  
  };
  
?>