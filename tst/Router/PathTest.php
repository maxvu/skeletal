<?php

  use Skeletal\Router\Path;

  class PathTest extends \Sliver\TestSuite\TestSuite {
    
    private function p ( $path ) {
      return (string) new Path( $path );
    }
    
    public function path () {
      $this->assert( $this->p('') )->eq( '/' );
      $this->assert( $this->p('/') )->eq( '/' );
      $this->assert( $this->p('/a') )->eq( '/a' );
      $this->assert( $this->p('/:a') )->eq( '/:a' );
      $this->assert( $this->p('/a?garbage') )->eq( '/a' );
      $this->assert( $this->p('/a/b/c/d') )->eq( '/a/b/c/d' );
      $this->assert( $this->p('/a/b//c/d///') )->eq( '/a/b/c/d' );
    }
    
  };

?>