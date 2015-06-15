<?php

class Router {
	protected $routes = [
		'GET' => [],
		'POST' => []
	];

	public function addRoute($request, $callback) {
		if (!is_array($request)) {
			$request = [$request];
		}

		foreach ($request as $r) {
			$method = strtoupper(preg_replace('/^(\w+)\s+(.*?)\s*$/', '$1', $r));
			if (!array_key_exists($method, $this->routes)) {
				continue;
			}
			$path = preg_replace('/^(\w+)\s+(\S*?)\s*$/', '$2', $r);
			$this->routes[$method][$path] = $callback;
		}
	}

	public function run($path) {		
		$method = $_SERVER['REQUEST_METHOD'];

		if (!array_key_exists($method, $this->routes)) {
			http_response_code(405);
			echo '<p>"' . $method . '" is not an accepted request method.</p>';
			echo '<p>Status code: 405</p>';
			return false;
		}
		
		$paths = $this->routes[$method];
		$parts = explode('/', $path);
		$last_route = null;

		foreach ($paths as $route => $callback) {
			if (strpos($route, '<') !== false) {
				$route_parts = explode('/', $route);
				if (count($parts) != count($route_parts)) {
					continue;
				}
				$valid = true;
				$params = new stdClass;
				
				for ($i = 0, $n = count($parts); $i < $n; ++$i) {
					if (strpos($route_parts[$i], '<') !== false) {
						$pattern = preg_quote($route_parts[$i], '/');
						$pattern = preg_replace('/\\\<(.+?)\\\>/', '(.+?)', $pattern);	

						if (!preg_match('/^' . $pattern . '$/', $parts[$i], $matches)) {
							$valid = false;
							break;
						}
						
						// remove complete pattern
						array_shift($matches);
						
						preg_match_all("/\<(.+?)\>/", $route_parts[$i], $keys);
						
						foreach ($matches as $idx => $match) {
							$params->{$keys[1][$idx]} = $match;
						}
					}
					elseif ($route_parts[$i] != $parts[$i]) {
						$valid = false;
						break;
					}
				}

				if ($valid) {
					return $this->invokeCallback($callback, $route, $params);
				}
			}
			elseif ($path == $route) {
				return $this->invokeCallback($callback, $route);
			}
		}
		
		http_response_code(404);
		echo '<p>The page you are looking for could not be found.</p>';
		echo '<p>Status code: 404</p>';
		return false;
	}

	protected function invokeCallback($callback, $route, $params = null) {
		if (!is_callable($callback)) {
			if (is_array($callback)) {
				return $callback[0]->{$callback[1]}($route, $params);
			}

			$parts = explode('.', $callback);
			$class = $parts[0];
			$method = $parts[1];
			
			if (!class_exists($class)) {
				echo '<code>' . $class . '</code> does not exist';
				return false;
			}

			$callback = new $class;
			
			return $callback->{$method}($route, $params);
		}
	
		return $callback($route, $params);
    }
}
