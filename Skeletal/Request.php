<?php

  namespace Skeletal;

  class Request {
  
    public $requestPath;
    public $queryString;
    public $postData;
    public $filesData;
    public $requestMethod;
    public $headersData;
    public $remoteAddress;
    public $usesSSL;
    
    public $filters;
    
    public function __construct (
      $requestPath,
      $queryString,
      $postData,
      $filesData,
      $requestMethod,
      $headersData,
      $remoteAddress,
      $usesSSL
    ) {
      $this->requestPath = new Path( urldecode($requestPath) );
      $this->queryString = $queryString;
      $this->postData = $postData;
      $this->filesData = $filesData;
      $this->requestMethod = $requestMethod;
      $this->headersData = $headersData;
      $this->remoteAddress = $remoteAddress;
      $this->usesSSL = $usesSSL;
    }
    
    /*
      Construct a request from PHP's superglobals.
    */
    
    public static function current () {
      if ( php_sapi_name() == 'cli' )
        throw new Exception ( 'Cannot generate an HTTP request from CLI.' );
      
      // collect headers from _SERVER
      $headers = array();
      foreach ( Request::$phpHeaderMap as $phpName => $httpName )
        if ( isset( $_SERVER[$phpName] ) )
          $headers[$httpName] = $_SERVER[$phpName];
        
      return new Request (
        $_SERVER['REQUEST_URI'],
        $_REQUEST,
        $_POST,
        $_FILES,
        strtoupper( $_SERVER['REQUEST_METHOD'] ),
        $headers,
        $_SERVER['REMOTE_ADDR'],
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? TRUE : FALSE
      );
    }
    
    /* Accessors */
    
    public function path () {
      return $this->requestPath;
    }
    
    public function get ( $key ) {
      return 
        !empty( $this->queryString ) && isset( $this->queryString[$key] ) ?
          new Parameter( $this->queryString[$key] ) : 
          new Parameter( '' );
    }
    
    public function post ( $key ) {
      return 
        !empty( $this->postData ) && isset( $this->postData[$key] ) ?
          new Parameter( $this->postData[$key] ) :
          new Parameter( '' );
    }
    
    public function header ( $h ) {
      return
        !empty( $this->headersData[$h] ) && isset( $this->headersData[$h] )
          ? $this->headersData[$h] : 
          new Parameter( '' );
    }
    
    public function files () { return $this->filesData; }
    public function method () { return $this->requestMethod; }
    public function ip () { return $this->remoteAddress; }
    public function ssl () { return $this->usesSSL === TRUE; }
    
    public function toJSON ( $pretty = FALSE ) {
      return json_encode( array(
        'path' => $this->requestPath,
        'qs' => $this->queryString,
        'post' => $this->postData,
        'files' => $this->filesData,
        'method' => $this->requestMethod,
        'headers' => $this->headersData,
        'ip' => $this->remoteAddress,
        'ssl' => $this->ssl() ? TRUE : FALSE
      ), $pretty ? JSON_PRETTY_PRINT : FALSE );
    }
    
    public static $phpHeaderMap = array(
      'HTTP_CONNECTION' => 'Connection',
      'HTTP_PRAGMA' => 'Pragma',
      'HTTP_CACHE_CONTROL' => 'Cache-Control',
      'HTTP_ACCEPT' => 'Accept',
      'HTTP_USER_AGENT' => 'User-Agent',
      'HTTP_DNT' => 'DNT',
      'HTTP_ACCEPT_ENCODING' => 'Accept-Encoding',
      'HTTP_ACCEPT_LANGUAGE' => 'Accept-Language'
    );
  
  };

?>
