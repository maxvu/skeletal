<?php

  namespace Skeletal\HTTP;

  abstract class Method {
    
    public static $HEAD = 'HEAD';
    public static $GET = 'GET';
    public static $POST = 'POST';
    public static $PUT = 'PUT';
    public static $DELETE = 'DELETE';
    public static $OPTIONS = 'OPTIONS';
    public static $CONNECT = 'CONNECT';
    
    public static function all () {
      return array(
        Method::$HEAD,
        Method::$GET,
        Method::$POST,
        Method::$PUT,
        Method::$DELETE,
        Method::$OPTIONS,
        Method::$CONNECT
      );
    }
    
    
  };

?>