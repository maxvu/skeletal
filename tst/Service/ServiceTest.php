<?php

  namespace Skeletal\Tests\Service;

  use Skeletal\Service\Service;
  use Skeletal\Session\CLISession;
  use Skeletal\HTTP\Request;
  use Skeletal\HTTP\Response;
  use Skeletal\HTTP\Method;
  
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
        $this->onNotFound = function ( $svc, $rq ) {
          return (new Reponse())->code(404)->text('notfound');
        };
        $this->onException = function ( $svc, $rq ) {
          return (new Response())->code(500)->text('error');
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
        $svc->get( $path, function ( $svc, $rq ) use ( $body ) {
          return (new Response())->text( $body );
        });
        $rq = $this->createRequest( $path, 'get' );
        $this->assert( $svc->route( $rq )->body() )->eq( $body );
      }
      
      $svc->get( '/exception', function ( $svc, $rq ) {
        throw new \Exception( '!!!' );
      });
    }
    
    public function doesRouteOtherVerbs () {
      $svc = $this->createService();
      $nop = function ( $svc, $rq ) {
        return (new Response())->text( $rq->method() );
      };
      $rq = $this->createRequest( '/', NULL );
      $rq->requestPath = '/';
      foreach ( Method::all() as $method )
        $svc->{strtolower($method)}( '/', $nop );
        
      foreach ( Method::all() as $method ) {
         $rq->requestMethod = $method;
        $this->assert( $svc->route( $rq )->body() )->eq( $method );
      }
    }
    
    public function notFoundCalled () {
      $svc = $this->createService();
      $svc->get( '/', function ( $svc, $rq ) {
        return (new Response())->body( '123' ); }
      );
      $svc->onNotFound( function ( $svc, $rq ) {
        return (new Response())->body( 'NOTFOUND' );
      } );
      
      // Wrong method
      $this->assert(
        $svc->route( $this->createRequest( '/', Method::$POST ) )->body()
      )->eq( 'NOTFOUND' );
      
      $this->assert(
        $svc->route( $this->createRequest( '/', Method::$PUT ) )->body()
      )->eq( 'NOTFOUND' );
      
      // Wrong path
      $this->assert(
        $svc->route( $this->createRequest( '/dne', Method::$GET ) )->body()
      )->eq( 'NOTFOUND' );
      
      // Right method, caps'ed path
      $this->assert(
        $svc->route( $this->createRequest( '/', Method::$GET ) )->body()
      )->eq( '123' );
    }
    
    public function canUseDependenciesInCallbackBody () {
      $svc = $this->createService();
      $svc->db = new Database();
      $svc->db->ABC = 123;
      
      $svc->get( '/document/{id}', function ( $svc, $rq ) {
        $record = $svc->db->{$rq->get('id')};
        echo $rq->get('id') . "\n";
        if ( $record === NULL )
          return (new Response())->code(404)->text( 'notfound' );
        else
          return (new Response())->body( $record );
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
      
      $svc->get( '/msg', function ( $svc, $rq ) {
        return (new Response())->body( 'HELLO WORLD' );
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
    
    public function render () {
      $svc = $this->createService();
      
      $tmpfile = stream_get_meta_data( tmpfile() )['uri'];
      file_put_contents( $tmpfile, "HELLO <?php echo \$name; ?>" );
      
      $this->assert(
        $svc->render( $tmpfile, array( 'name' => 'WORLD' ) )
      )->eq( 'HELLO WORLD' );
      
      $this->assert( isset( $svc->name ) )->false();
    }
    
  };

?>