<?php

  namespace Skeletal\HTTP;
  
  abstract class ResponseCode {
  
    public static function all () {
      return array(
        '200' => 'OK',
        '204' => 'No Content',
        '206' => 'Partial Content',
        '301' => 'Moved Permanently',
        '304' => 'Not Modified',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '410' => 'Gone',
        '500' => 'Internal Server Error',
        '503' => 'Service Unavailable'
      );
    }
    
    public static function find ( $code ) {
      if ( isset( ResponseCode::all()[(string) $code] ) )
        return ResponseCode::all()[$code];
      return NULL;
    }
  
  };

?>