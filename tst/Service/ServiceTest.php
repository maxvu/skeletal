<?php

  namespace Skeletal\Tests\Service;

  use Skeletal\Service\Service;
  use Skeletal\Session\CLISession;
  use Skeletal\HTTP\Request as Request;
  
  class Database {
  
    public function __set ( $name, $value ) {
      $this->{$name} = $value;
    }
    
    public function __get ( $name ) {
      return isset( $this->{$name} ) ? $this->{$name} : NULL;
    }
  
  }

  class ServiceTest extends \Sliver\TestSuite\TestSuite {
    
    private function createService () {
      // Service constructor automatically includes \Session\Session,
      // which calls web-specific session functions. Stub with CLISession.
       
      $cliConstructor = function () {
        $this->router = new \Skeletal\Router\Router();
        $this->session = new CLISession();
        $this->onNotFound = function ( $rq, $rs ) {
          $rs->code(404)->text('notfound');
        };
        $this->onException = function ( $rq, $rs ) {
          $rs->code(500)->text('error');
        };
      };
      
      $service = $this->spy( '\Skeletal\Service\Service' );
      $service->summon()->stub( '__construct', $cliConstructor );
      $service->summon()->construct();
      return $service;
    }
    
    private function createRequest ( $path, $method ) {
      return new Request(
        $path, array(), array(), array(), $method, array(), null, false
      );
    }
    
    public function basicAccess () {
      $svc = $this->createService();
      
      $svc->someDependency = 'SERVICE';
      $this->assert( isset( $svc->someDependency ) )->true();
      $this->assert( $svc->someDependency )->eq( 'SERVICE' );
    }
    
    public function doesRoute () {
      
      $svc = $this->createService();
      
      $svc->get( '/', function ( $rq, $rs ) { $rs->text( 'index' ); });
      $svc->get( '/one', function ( $rq, $rs ) { $rs->text( 'one' ); });
      $svc->get( '/{one}', function ( $rq, $rs ) { $rs->text( 'X' ); });
      $svc->get( '/one/two', function ( $rq, $rs ) { $rs->text( 'Y' ); });
      $svc->get( '/one/{two}', function ( $rq, $rs ) { $rs->text( 'Z' ); });
      $svc->get( '/{one}/{two}', function ( $rq, $rs ) { $rs->text( 'ZZZ' ); });
      $svc->get( '/exception', function ( $rq, $rs ) { throw new \Exception( '!!!' ); });
      
      $cases = [
        '/' => 'index',
        '//' => 'index',
        '/one' => 'one',
        '/HELLO' => 'X',
        '/one/two' => 'Y',
        '/one/twenty' => 'Z',
        '/AAA/BBB' => 'ZZZ',
        '/DNE/DNE/DNE' => 'notfound',
        '/exception' => 'error'
      ];
      
      foreach ( $cases as $path => $body ) {
        $rq = $this->createRequest( $path, 'get' );
        $this->assert( $svc->route( $rq )->body() )->eq( $body );
      }
    }
    
    public function canUseDependenciesInCallbackBody () {
      $svc = $this->createService();
      $svc->db = new Database();
      $svc->db->ABC = 123;
      
      $svc->get( '/document/{id}', function ( $rq, $rs ) {
        $record = $this->db->{$rq->get('id')};
        echo $rq->get('id') . "\n";
        if ( $record === NULL )
          $rs->code(404)->text( 'notfound' );
        else
          $rs->body( $record );
      });
      
      $this->assert(
        $svc->route( $this->createRequest( '/document/DEF', 'GET' ) )->body()
      )->eq( 'notfound' );
      
      $this->assert(
        $svc->route( $this->createRequest( '/document/ABC', 'GET' ) )->body()
      )->eq( '123' );
    }
    
    public function headRequestsRoutedAndSized () {
      $svc = $this->createService();
      
      $svc->get( '/msg', function ( $rq, $rs ) {
        $rs->body( 'HELLO WORLD' );
      });
      
      $getResponse = $svc->route( $this->createRequest( '/msg', 'GET' ) );
      $headResponse = $svc->route( $this->createRequest( '/msg', 'HEAD' ) );
      
      $this->assert( $getResponse->code() )->eq( 200 );
      $this->assert( $getResponse->body() )->eq( 'HELLO WORLD' );
      $this->assert( $getResponse->header('Content-Length') )->eq(
        strlen( 'HELLO WORLD' )
      );
      
      $this->assert( $headResponse->code() )->eq( 200 );
      $this->assert( $headResponse->body() )->eq( '' );
      $this->assert( $headResponse->header('Content-Length') )->eq(
        strlen( 'HELLO WORLD' )
      );
      
    }
    
  };

?>