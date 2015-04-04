<?php
  
  namespace Skeletal\HTTP;

  class Response {
  
    protected $code;
    protected $body;
    protected $headers;
    protected $sent;
    protected $data;
    
    public function __construct ( $body = '' ) {
      $this->code(200);
      $this->type = 'text/html';
      $this->body = $body;
      $this->headers = array();
      $this->data = array();
    }
    
    /*
      Basic properties
    */
    
    public function code ( $code = NULL ) {
      if ( $code === NULL )
        return $this->code;
      if ( ResponseCode::find( $code ) === NULL )
        throw new \InvalidArgumentException( "Response: no code $code" );
      $this->code = intval( $code );
      return $this;
    }
    
    public function body ( $str = NULL ) {
      if ( $str === NULL ) return $this->body;
      $this->body = "";
      return $this->append( $str );
    }
    
    public function append ( $str ) {
      if ( is_readable( $str ) )
        $this->append( $this->includeFile( $str ) );
      else
        $this->body .= strval( $str );
      $this->length( strlen( $this->body ) );
      return $this;
    }
    
    public function apply ( $str ) {
      return $this->append( $str );
    }
    
    public function includeFile ( $res ) {
      if ( !is_readable( $res ) )
        throw new \Exception( "Include $res inaccessible.");      
      ob_start();
      require( $res );
      return ob_get_clean();
    }
    
    public function __call ( $name, $args ) {
      switch ( $name ) {
        case 'include':
          return call_user_func_array( array( $this, 'includeFile' ), $args );
        break;
      }
    }
    
    /*
      Headers
    */
    
    public function header ( $name, $value = NULL ) {
      if ( $value === NULL )
        if ( isset( $this->headers[$name] ) )
          return $this->headers[$name];
        else
          return NULL;
      $this->headers[$name] = $value;
      return $this;
    }
    
    public function headers ( $which = NULL ) {
      if ( is_array( $which ) ) {
        $this->headers = $which;
        return $this;
      }
      return $this->headers;
    }
    
    public function cache ( $secs ) {
      $secs = (string) intval( $sevs );
      $this->headers['Cache-Control'] = 'max-age=' . $secs;
      return $this;
    }
    
    public function language ( $lang = 'en' ) {
      $this->headers['Content-Language'] = $lang;
      return $this;
    }
    
    public function type ( $type, $charset = 'utf-8' ) {
      $this->headers['Content-Type'] = "$type; charset=$charset";
      return $this;
    }
    
    public function length ( $nBytes ) {
      $this->headers['Content-Length'] = $nBytes;
      return $this;
    }
    
    /*
      Codes
    */
    
    public function redirect ( $to ) {
      if ( $to === NULL ) 
        return $this->header( 'Location' ) ? $this->header( 'Location' ) : NULL;
      return $this->header( 'Location', $to )->code(301);
    }
    
    public function badRequest () {
      return $this->code( 400 );
    }
    
    public function unauthorized () {
      return $this->code( 401 );
    }
    
    public function forbidden () {
      return $this->code( 403 );
    }
    
    public function notFound () {
      return $this->code( 404 );
    }
    
    public function notAcceptable () {
      return $this->code( 406 );
    }
    
    public function unavailable () {
      return $this->code( 503 );
    }
    
    public function serverError () {
      return $this->code( 500 );
    }
    
    /*
      Content-types
    */
    
    public function html ( $html ) {
      return $this->body( $html )->header( 'Content-type', 'text/html' );
    }
    
    public function json ( $msg ) {
      if ( !is_string( $msg ) || json_decode( $msg ) === NULL )
        $msg = json_encode( $msg );
      return $this->body( $msg )->header( 'Content-type', 'application/json' );
    }
    
    public function text ( $txt ) {
      return $this->body( $txt )->header( 'Content-type', 'text/plain' );
    }
    
    public function js ( $js ) {
      return $this->body( $js )->header( 
        'Content-type', 'application/javascript'
      );
    }
    
    public function css ( $css ) {
      return $this->body( $css )->header( 'Content-type', 'text/css' );
    }
    
    public function download ( $data, $name ) {
      return $this->body( $data )->header(
        'Content-Disposition',
        "attachment; filename=\"$name\""
      );
    }
    
    /*
      Set arbitrary properties for data staging.
    */
    
    public function __set ( $name, $value ) {
      $this->data[$name] = $value;
    }
    
    public function __get ( $name ) {
      return $this->data[$name];
    }
    
    /*
      Dispatch headers and send message body.
    */
    
    public static function send ( $response ) {
      $protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ?
          $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
      $code = $response->code();
      $status = ResponseCode::find( $code );
      $body = $response->body();
      header( "$protocol $code $status", true, intval( $code ) );
      foreach ( $response->headers() as $a => $v )
        header( "$a: $v" );
      if ( is_string( $body ) && strlen( $body ) > 0 )
        echo $body;
    }
    
  };

?>