## skeletal

A bare-bones PHP web framework. 

#### Main features

* A router with basic pattern matching
* Easy, abbreviated Request and Response classes
* Basic error handling

#### Getting started

`require` `maxvu/skeletal` at `~1.0` in your `composer.json`.

```json
{
  "require" : {
    "maxvu/skeletal" : "~1.0"
  }
}
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

#### A basic service example

Copy the following boilerplate into your entry point and verify that it's working properly by seeing a plain HTML page with an HTTP response code 200:

```php
<?php
  require 'vendor/autoload.php';
  
  $demoService = new \Skeletal\Service\Service();
  
  $demoService->get( '/', function ( $rq, &$rs ) {
    $rs->body('<h1>HELLO WORLD</h1>');
  });
  
  $demoService->serve();
?>
```

##### Getting information from the Request

Access query string and post body parameters (`_GET` and `_POST`) with the `->get( $key )` and `->post( $key )` methods.

```php
  $demoBlog->get( '/search', function ( $rq, &$rs ) {
    $searchResult = $mySearchEngine->search( $rq->get('q') );
    $rq->body( sizeof( $searchResult ) . ' records found' );
  });
```

```php
  $demoMail->post( '/message', function ( $rq, &$rs ) {
    $to = $rq->post('to');
    $msg = $rq->post('msg');
    $mailer->send( $to, $msg );
  });
```

Methods to access the `Request`:

```
  path()              the request path 
  get( $key )         access query string parameter (or route matcher) $key
  post( $key )        access post parameter $key 
  header( $hName )    access value for the HTTP header $hName (if accessible by PHP)
  files()             access PHP $_FILES superglobal
  method()            HTTP request method ('GET', 'POST', etc.)
  ip()                remote host (as reported by PHP)
  ssl()               whether this request was sent over SSL (as reported by PHP)
```


#### Manipulating the Response

The `Response` argument is given as the second argument by-reference. By default, it will be sent as a `text/html` document with an empty body and code `200 OK`. Modify the body, headers and response code using any of its chainable methods:

```
  # CODES
  code( $code )              change the HTTP response code (status will match)
  redirect( $to )            set code 301, 'Location: $to'
  badRequest()               set code 400
  unauthorized()             set code 401
  forbidden()                set code 403
  notFound()                 set code 404
  notAcceptable()            set code 406
  unavailable()              set code 503
  serverError()              set code 500
  
  # BODY
  body( $str )               replace the body's content with $str
  apply( $str )              append string to body
  apply( $file )             include() file and append its (evaluated) contents to body
  
  # HEADERS
  header( $name )            get the value of header $name
  header( $name, $val )      set header $name to $val
  headers()                  get an array of all headers set
  cache( $secs )             set 'Cache-Control' header to 'max-age=$secs'
  language( $lang )          set 'Content-Language' to $lang
  type( $type, $charset )    set 'Content-Type' to '$type; charset=$charset'
  length( $nBytes )          set 'Content-Length' to $nBytes
  
  html( $html )              set body to file or string $html and set 'Content-Type' to 'text/html'
  json( $msg )               set body to encodable string or file and 'Content-Type' to 'application/json'
  text( $txt )               set body to file or string $txt and 'Content-Type' to text/plain'
  js( $js )                  set body to file or string $js and 'Content-Type' to 'application/javascript'
  css( $css )                set body to file or string $css and 'Content-Type' to 'text/css'
  download( $data, $name )   set body to file or string $data and 'Content-Disposition' to 'attachment; filename=$name'
    
  
```

#### Routing
Call HTTP-verb methods on the `Service` to start a route declaration. Provide it a path to match and a closure to perform when it's accessed. The path will match case-insensitive and recognizes parameters in the form of `{token}` (e.g. `/post/{id}`), which will be available as query string parameters (`get()`) in the `Response`. The callback should have the form `function ( $request, &$response )`.

#### Dependency injection

Assign arbitrary properties to the service and they will become available in the callback.

```php
  $forum = new Skeletal\Service();
  $forum->db = new Database();
  
  $demo->get( '/post/{id}', function ( $rq, &$rs ) {
    $post = this->db->getPost( $rq->get('id') );
    $rs->apply( 'viewpost.php' );
  });
```

#### Accessing the Session

`Service`'s `$session` is available on instantiation and will alias PHP's `$_SESSION`.

```php
  $demoLogin->post( '/login', function ( $rq, &$rs ) {
    if ( ($user = login( $rq->post('user'), $rq->post('pass') ) != null ) )
        $this->session->user = $user;
  });
```

#### Handling errors

Use the `Service`'s `onNotFound( $callback )` and `onException( $callback )` methods to define how error responses should be handled. They should have the same signature as normal requests (`onException()` will have the exception as a third argument) and will be called on events as their names suggest: `onNotFound` when a request path doesn't match any defined route and `onException` when any `Exception` is caught during a callback (including `onNotFound`).


