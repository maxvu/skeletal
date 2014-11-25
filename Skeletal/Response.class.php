<?php
  
  namespace Skeletal;

  class Response {
  
    public $code;
    public $body;
    private $line;
    public $headers;
    private $sent;
    
    public function __construct ( $body = '' ) {
      $this->code(200);
      $this->type = 'text/html';
      $this->body = $body;
      $this->headers = array();
      $this->sent = FALSE;
    }
    
    /*
      Basic properties
    */
    
    public function code ( $code ) {
      if ( isset( Response::$httpResponseCodes[(string) $code] ) ) {
        $this->code = intval( $code );
        $this->line =
          "{$_SERVER['SERVER_PROTOCOL']} $code "
          . Response::$httpResponseCodes[(string) $code];
      }
      return $this;
    }
    
    public function body ( $what ) {
     $this->body = "";
     return $this->apply( $what );
    }
    
    public function apply ( $what ) {
      if ( is_readable( $what ) ) {
        ob_start();
        require( $what );
        $this->body .= ob_get_clean();
      } else {
        $this->body .= $what;
      }
      return $this;
    }
    
    /*
      Headers
    */
    
    public function header ( $name, $value = NULL ) {
      if ( $value === NULL ) return $this;
      $this->headers[$name] = $value;
      return $this;
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
    
    /*
      Codes
    */
    
    public function redirect ( $to ) {
      if ( $to === NULL ) 
        return $this->header( 'Location' ) ? $this->header( 'Location' ) : NULL;
      return $this->header('Location', $to)->code(301);
    }
    
    public function notFound () {
      return $this->code(404);
    }
    
    public function unavailable () {
      return $this->code(503);
    }
    
    public function serverError ( $msg = '' ) {
      return $this->body( $msg )->code( 500 );
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
    
    public static function css ( $fileName ) {
      return $this->body( $js )->header( 'Content-type', 'text/css' );
    }
    
    public function download ( $data, $name ) {
      return $this->body( $data )->header(
        'Content-Disposition',
        "attachment; filename=\"$name\""
      );
    }
    
    // Dispatch headers and send message body.
    
    public function send () {
      if ( $this->sent ) return;
      $this->sent = TRUE;
      header( $this->line, true, intval( $this->code ) );
      foreach ( $this->headers as $a => $v )
        header( "$a: $v" );
      if ( is_string( $this->body ) && strlen( $this->body ) > 0 )
        echo $this->body;
    }
    
    public static $httpResponseCodes = array (
      '200' => 'OK',
      '204' => 'No Content',
      '206' => 'Partial Content',
      '301' => 'Moved Permanently',
      '304' => 'Not Modified',
      '401' => 'Unauthorized',
      '403' => 'Forbidden',
      '404' => 'Not Found',
      '405' => 'Method Not Allowed',
      '406' => 'Not Acceptable',
      '410' => 'Gone',
      '500' => 'Internal Server Error',
      '503' => 'Service Unavailable'
    );
    
  };

?>