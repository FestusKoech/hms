<?php
namespace App\Core;

final class Router {
  private array $routes=['GET'=>[],'POST'=>[]];
  public function get(string $uri, callable|array $action){ $this->routes['GET'][$uri]=$action; }
  public function post(string $uri, callable|array $action){ $this->routes['POST'][$uri]=$action; }
  public function dispatch() {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/'); // /hms/public
    $uri = '/'.ltrim(str_replace($base,'',$path),'/');    // normalize

    $action = $this->routes[$method][$uri] ?? null;
    if(!$action) { header("HTTP/1.0 404 Not Found"); echo "Route not found: {$uri}"; return; }
    if (is_array($action)) { [$class,$func] = $action; (new $class)->{$func}(); }
    else { $action(); }
  }
}
