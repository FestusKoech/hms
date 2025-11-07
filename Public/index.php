<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/config.php';           // DB creds + APP_URL
spl_autoload_register(function ($class) {
  $prefix = 'App\\';
  $base = BASE_PATH . '/app/';
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  $rel = substr($class, strlen($prefix));
  $file = $base . str_replace('\\', '/', $rel) . '.php';
  if (file_exists($file)) require $file;
});

use App\Core\App;
(new App())->run();
