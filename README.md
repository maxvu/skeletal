# skeletal

A bare-bones PHP web framework. 

## Main features

* A router with basic pattern matching
* Abstracted Request/Response classes
* Input filtering/validation
* Dependency injection

## Getting started

Download and install `composer`.

```sh
curl -sS https://getcomposer.org/installer | php
```

`require` `maxvu/skeletal` at `~1.0` in your `composer.json`.

```json
{
  "require" : {
    "maxvu/skeletal" : "~1.0"
  }
}
```

Install

```
php composer.phar install
```

Take advantage of skeletal's router by rewriting all incoming requests to a common entry point. If that's an `index.php`, give Apache/httpd a directive like this in an `.htaccess` file:

```
Options +FollowSymLinks
RewriteEngine On
RewriteRule ^(.*)$ index.php [QSA,L]
```

On nginx, use a directive like this instead:
```
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

Copy the following boilerplate into your entry point and verify that it's working properly by seeing a plain HTML page with an HTTP response code 200:

```php
<?php
  require 'autoload.php';
  $demoService = new \Skeletal\Service();
  
  $demoService->router()->get( '/', function ( $rq, &$rs ) {
    $rs->body('<h1>HELLO WORLD</h1>');
  });
  
  $demoService->serve();
?>
```

## Usage

### Routing
Use the service's `->router()->get()`, `->router()->post()`, etc. methods to define routes for your application. Each of these methods accepts a string identifying a path to match to and callback function to invoke upon request. The path should take the general form `/A/B/C` and any named parameters that should be parsed out of the path should be marked with curly braces (e.g. `/post/{id}`). The callback function should have the form `function ( $request, &$response )`.

### Getting information from the Request

Access `_GET` and `_POST` variables:
```php
  $demoBlog->router()->get( '/search', function ( $rq, &$rs ) {
    $mySearchEngine->search( $rq->get('q') );
  });
```

```php
  $demoMail->router()->post( '/message', function ( $rq, &$rs ) {
    $to = $rq->post('to');
    $msg = $rq->post('msg');
    $mailer->send( $to, $msg );
  });
```

### Input filtering

Use the `->isA( $what )` and `->isAn( $what )` functions to return a filtered version of user input, where `$what` is the name of the filter to apply (`int`, `bool`, `slug` and `email` are included). A filter will take the argument and transform it into a usable type or return `NULL` on failure.

```php
  $demoNewsletter->router()->post( '/subscribe', function ( $rq, &$rs ) {
    if ( ($email = $rq->post('email')->asAn('email')) != NULL ) {
        $rs->apply('welcome.php');   
    } else {
        $rq->body("That's not a valid e-mail address!");
    }
  });
```

Define your own filters using `Service`'s `defineFilter( $name, $callback )` function, where `$callback` accepts one string argument to transform and returns the filtered value.

```php
    $demoBitcoinService->defineFilter( 'bitcoin-address', function ( $x ) {
        if ( is_string( $x ) && !preg_match("/^[^O0Il]{26,33}$/") )
            return $x;
        return NULL;
    });
```

### Dependency injection

Assign arbitrary properties to the service and they will become available in the callback.

```php
  $forum = new Skeletal\Service();
  $forum->db = new DatabaseClass();
  
  $demo->router()->get( '/post/{id}', function ( $rq, &$rs ) {
    $post = this->db->getPost( $rq->get('id')->asA('post-id') );
    $rs->apply( 'viewpost.php' );
  });
```

### Accessing the Session

Get and set arbitrary properties on `Service`'s `$session`. 

```php
  $demoLogin->router()->post( '/login', function ( $rq, &$rs ) {
    if ( ($user = login( $rq->post('user'), $rq->post('pass') ) != null ) )
        $this->session->user = $user;
  });
```

By default, `Service`'s public member `$session` is a flat wrapper for accessing `$_SESSION`. Replace it or use it conjunction with other with another dependencies to customize.


### Manipulating the Response
A reference to a newly-instantiated Response is provided as the second argument and will be returned to the router after the callback. (This way, you don't have to `new` and `return` yourself.) It defaults to an empty `text/html` document.

Add to the body string with `apply()` or replace it with `body()`. Use the content itself or give the name of a file to `include()`. Any definitions or variables in included files will be available in the callback.

```php
  $demoLogin->router()->post( '/secretpage', function ( $rq, &$rs ) {
    if ( isset( $this->session->user ) ) {
        $rs->body( 'user_portal.php' );
        $rs->apply( "Welcome back, {$user->name}!" );
    } else
        $rs->apply( 'BEAT IT, PUNK!' );
  });
```

Use `code( $num )` and `header( $name, $val )` to modify the response directly, or use a number of shorthand functions for common types of responses.

Redirection
```php
$demoSite->router()->post( '/oldpage', function ( $rq, &$rs ) {
    $rs->redirect( '/newpage' ); // code(301), header( 'Location', '/newpage' )
});
```
Serving JSON
```php
$demoAPI->router()->post( '/user/{id}', function ( $rq, &$rs ) {
    $rs->json( Users::find($id)->getInfo() ); // type( 'application/json' ), body ( ... )
});
```
Serving stylesheets
```php
$demoAPI->router()->post( '/style.css', function ( $rq, &$rs ) {
    $rs->css( '/public/master.css' ); // type( 'text/css' ), apply ( ... )
});
```

#### Chaining

All Response methods are chainable, so you may do things like `$rq->cache(604800)->css('style.css')`.

#### Available functions

* Headers
    * `header( $name, $value )` sets an arbirtrary header. Equivalent to `header( "$name: $value" )`.
    * `cache( $secs )` sets `Cache-control`, takes time in seconds.
    * `language( $lang = 'en' )` sets `Content-Language`.
    * `type( $type, $charset = 'utf-8' )` sets `Content-Type`.
* Codes
    * `redirect( $newPath )`
    * `notFound()`, 404
    * `unavailable()`, 503
    * `serverError( $msg = '' )`, 500
* Content-types
    * `html( $html )`
    * `json( $json )`
    * `text( $txt )`
    * `js( $js )`
    * `css( $css )`
    * `download( $data, $name )` prompts browser to download file.
