<?php

  namespace Skeletal\Router;

  class RouteToken {
  
    protected $name;
    protected $matcher;
    protected $priority;
    
    public function __construct ( $name, callable $matcher, $priority = 1 ) {
      $this->name = $name;
      $this->matcher = $matcher;
      $this->priority = $priority;
    }
    
    public static function exactMatch ( $str ) {
      return new RouteToken( NULL, function ( $in ) use ( $str ) {
        return strtolower( $in ) === strtolower( $str );
      }, 99 );
    }
    
    public static function wildcard ( $name ) {
      return new RouteToken( $name, function ( $in ) {
        return TRUE;
      }, 10 );
    }
    
    public function getName () {
      return $this->name;
    }
    
    public function getMatcher () {
      return $this->matcher;
    }
    
    public function getPriority () {
      return $this->priority;
    }
    
    public function matches ( $str ) {
      return call_user_func( $this->matcher, $str ) === TRUE;
    }
  
  };

?>