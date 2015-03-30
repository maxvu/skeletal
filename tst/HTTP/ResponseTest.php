<?php

  namespace Skeletal\Tests\HTTP;

  use Skeletal\HTTP\Response;

  class ResponseTest extends \Sliver\TestSuite\TestSuite {
    
    public function getterSetters () {
      $rsp = new Response();
      $this->assert( $rsp->code( 400 )->code() )->eq( 400 );
      $this->assert( $rsp->body( 'HELLO' )->body() )->eq( 'HELLO' );
      $this->assert( $rsp->header( 'HELLO' ) )->null();
      $this->assert( $rsp->header( 'HELLO', 'WORLD' )->header( 'HELLO' ) )->eq( 'WORLD' );
      $this->assert( $rsp->headers() )->hasKey( 'HELLO' )->contains( 'WORLD' );
    }
    
    public function setABadStatusCode () {
      $this->shouldThrowException();
      (new Response())->code( 'SLKDJF' );
    }
    
    public function apply () {
      $tmpfile = stream_get_meta_data( tmpfile() )['uri'];
      file_put_contents( $tmpfile, "<?php echo 'HELLO '; ?>WORLD" );
      $rsp = (new Response())->apply( $tmpfile );
      $this->shouldOutput( '' );
      $this->assert( $rsp->body() )->eq( 'HELLO WORLD' );
    }
    
    public function json () {
      
      $this->assert(
        (new Response())->json( 1 )->body()
      )->eq( '1' );
      
      $this->assert(
        (new Response())->json( 1 )->headers()
      )->hasKey( 'Content-type' );
      
      $this->assert(
        (new Response())->json( [ "one" => 1 ] )->body()
      )->eq( '{"one":1}' );
      
      $this->assert(
        (new Response())->json( '{"one":1}' )->body()
      )->eq( '{"one":1}' );
      
    }
    
    public function dataStaging () {
      $rsp = new Response();
      $rsp->someData = '10101';
      $this->assert( $rsp->someData )->eq( '10101' );
    }
    
    public function codes () {
      $rsp = new Response();
      $this->assert( $rsp->redirect( '/' )->code() )->eq( 301 );
      $this->assert( $rsp->badRequest()->code() )->eq( 400 );
      $this->assert( $rsp->unauthorized()->code() )->eq( 401 );
      $this->assert( $rsp->forbidden()->code() )->eq( 403 );
      $this->assert( $rsp->notFound()->code() )->eq( 404 );
      $this->assert( $rsp->notAcceptable()->code() )->eq( 406 );
      $this->assert( $rsp->serverError()->code() )->eq( 500 );
      $this->assert( $rsp->unavailable()->code() )->eq( 503 );
    }
    
  };

?>