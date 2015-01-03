<?php

  namespace Skeletal;

  class PathTest extends \Sliver\TestSuite {
  
    public function __construct () {
    
      $this->test( 'empty defaults to "/"', function () {
        $p1 = new Path( '' );
        $p2 = new Path( '/' );
        return (string) $p1 === (string) $p2;
      })->equals( TRUE );
    
    }
  
  };

?>