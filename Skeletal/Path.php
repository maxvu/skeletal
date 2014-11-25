<?php

  /*
    Path represents an endpoint or a Route (endpoint pattern).
    
    It does a simple explosion to vend a path string as a list of tokens
    to help comparison and pattern matching.
  */
  
  namespace Skeletal;

  class Path {
  
    private $asString;
    private $asTokens;
    
    public function __construct ( $path ) {
      // strip query string
      if ( ( $q = strpos( $path, '?' ) ) )
        $path = substr( $path, 0, $q );
      // explode()
      $this->asTokens = array_values(
        array_filter( explode( '/', $path ), function ( $s ) {
          return $s !== '';
        }
      ));
      
      // re-implode to normalize
      $this->asString = '/' . implode( '/', $this->asTokens );
    }
    
    public function matches ( Path $other ) {
      if ( $other === NULL ) return FALSE;
      return $other->tokenized === $me->tokenized;
    }
    
    public function __toString () {
      return $this->asString();
    }
    
    public function asString () { return $this->asString; }
    public function asTokens () { return $this->asTokens; }
  
  };

?>
