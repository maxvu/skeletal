<?php
  
  namespace Skeletal;
  
  class Session {

    public function __construct () {
      if ( session_id() === '' )
        session_start();
    }
    
    public function __get ( $key ) {
      return isset( $_SESSION[$key] ) ? $_SESSION[$key] : NULL;
    }
    
    public function __set( $key, $value ) {
      if ( is_string( $key ) )
        $_SESSION[$key] = $value;
    }
    
    public function __isset ( $key ) {
      return isset( $_SESSION[$key] );
    }
    
    public function __invoke () {
      return $_SESSION;
    }
    
    public function id () {
      return session_id();
    }
    
    public function destroy() {
      session_destroy();
    }
  
  };

?>
