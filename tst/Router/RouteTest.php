<?php

  namespace Skeletal\Tests\Router;

  use Skeletal\Router\Route;
  use Skeletal\Router\RouteToken;

  class RouteTest extends \Sliver\TestSuite\TestSuite {
    
    public function basicAccess () {
      $route = new Route( '/one/two/three', 'POST', function () {} );
      $this->assert( (string) $route )->eq( '/one/two/three' );
      $this->assert( $route->getMethod() )->eq( 'POST' );
      $this->assert( is_callable( $route->getClosure() ) );
    }
    
  };

?>