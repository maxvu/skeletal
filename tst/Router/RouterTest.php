<?php

  namespace Skeletal\Tests\Router;

  use Skeletal\Router\Router;

  class RouterTest extends \Sliver\TestSuite\TestSuite {
    
    public function basicAccess () {
      $r = new Router();
      $r->addRoute( '/one', 'POST', function () {} );
      $this->assert( $r->getRoutes() )->len( 1 );
      $r->addRoute( '/two', 'POST', function () {} );
      $this->assert( $r->getRoutes() )->len( 2 );
    }
    
    public function findRoute () {
      $r = new Router();
      
      $r->addRoute( '/', 'GET', function () { return 'A'; } );
      $r->addRoute( '/one', 'GET', function () { return 'B'; } );
      $r->addRoute( '/one/two', 'GET', function () { return 'C'; } );
      $r->addRoute( '/one/{two}', 'GET', function () { return 'D'; } );
      $r->addRoute( '/{one}/{two}', 'GET', function () { return 'E'; } );
      
      // Ambiguous routes are routed by score.
      
      $cases = [
        '/' => 'A',
        '/one' => 'B',
        '/one/two' => 'C',
        '/one/not-two' => 'D',
        '/not-one/two' => 'E',
        '/not-one/not-two' => 'E'
      ];
      
      foreach ( $cases as $path => $expected ) {
        $result = call_user_func( $r->findRoute( $path, 'GET' )->getClosure() );
        $this->assert( $result )->eq( $expected );
      }
      
      // Unmatchable routes get NULL.
      
      $this->assert( $r->findRoute( '/DNE', 'GET' ) )->null();
      $this->assert( $r->findRoute( '/', 'POST' ) )->null();
      
      // Case doesn't matter
      $this->assert(
        call_user_func( $r->findRoute( '/one', 'gEt' )->getClosure() )
      )->eq( 'B' );
      $this->assert(
        call_user_func( $r->findRoute( '/oNE/tWO', 'get' )->getClosure() )
      )->eq( 'C' );
      
    }
    
    public function badCallableThrowsException () {
      $this->shouldThrowException();
      $r = new Router();
      $r->addRoute( '', '', 'not-a-callable' );
    }
    
  };

?>