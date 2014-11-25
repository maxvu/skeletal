<?php

  namespace Skeletal;

  class Parameter {
  
    public static $typeFilters = array();
  
    private $value;
    
    public function __construct( $value ) {
      if ( !is_string( $value ) )
        throw new Exception( 'Parameter value must be a string' );
      $this->value = $value;
    }
    
    public static function addTypeFilter( $name, $filter ) {
      if ( !is_string( $name ) || !is_callable( $filter ) )
        throw new Exception( 'addTypeFilter() expected ( string, callable )' );
      Parameter::$typeFilters[$name] = $filter;
    }
    
    public function asA ( $type ) {
      if ( !isset( Parameter::$typeFilters[$type] ) )
        throw new Exception( "No type filter for '$type' defined." );
      return call_user_func( Parameter::$typeFilters[$type], $this->value );
    }
    
    public function asAn ( $type ) {
      return $this->asA( $type );
    }
    
    public function trim () {
      $this->value = trim( $this->value );
      return $this;
    }
    
    public function __toString() {
      return $this->value;
    }
    
    public function str () {
      return $this->value;
    }
  
  };
  
  Parameter::addTypeFilter( 'bool', function ( $x ) {
    return ($x = filter_var( $x, FILTER_VALIDATE_BOOLEAN )) ? $x : NULL;
  });
  
  Parameter::addTypeFilter( 'email', function ( $x ) {
    return ($x = filter_var( $x, FILTER_VALIDATE_EMAIL )) ? $x : NULL;
  });
  
  Parameter::addTypeFilter( 'int', function ( $x ) {
    return ($x = filter_var( $x, FILTER_VALIDATE_INT )) ? $x : NULL;
  });
  
  Parameter::addTypeFilter( 'ip', function ( $x ) {
    return filter_var( $x, FILTER_VALIDATE_IP );
  });
  
  Parameter::addTypeFilter( 'slug', function ( $x ) {
    if ( !is_string( $x ) || empty( $x ) ) return NULL;
    $x = trim( $x );
    if ( !preg_match( '/^[a-z0-9\-\_\~]+$/', $x ) )
      return NULL;
    return $x;
  });

?>