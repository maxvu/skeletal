<?php
  
  namespace Skeletal\Session;
  
  class CLISession {
    
    public function __get ( $key ) {
      return isset( $this->{$key} ) ? $this->{$key} : NULL;
    }
    
    public function __set( $key, $value ) {
      if ( is_string( $key ) )
        $this->{$key} = $value;
    }
    
    public function __isset ( $key ) {
      return isset( $this->{$key} );
    }
    
    public function id () {
      return "";
    }
    
    public function destroy() {
      foreach ( get_object_vars( $this ) as $k => $v )
        unset( $this->{$k} );
    }
  
  };

?>