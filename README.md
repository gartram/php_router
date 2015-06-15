# PHP Routing Class
A simple PHP routing class.

##Instantiate

```php
$router = new Router;
```

##Add Routes

Add a route to **/index**, class `Controller` will be instantiated and its method `index` called.

```php
$router->addRoute(array('GET /', 'GET /index'), 'Controller.index');
```

Add a route to **/about**, with existing class `$cls` and its method `about`.

```php
$router->addRoute('GET /about', array($cls, 'about'));
```

Add a route to **/contact**, with an inline function and parameters.

```php
$router->addRoute('GET /archive-<year>-<month>', function($route, $params) {
	// $route => the currently selected route, e.g. /archive-<year>-<month>
	// $params => the parameters <year> and <month> of the URL
	echo '<div>Year: ' . $params->year . '</div>';
	echo '<div>Month: ' . $params->month . '</div>';
});
```

##Run the Router

Use the following code to call the router with the current route (check the .htaccess file in the code listing for the path rewriting).

```php
$path = '/' . (array_key_exists('query', $_GET) ? $_GET['query'] : '');

$router->run($path);
```
