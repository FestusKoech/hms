<?php
namespace App\Core;

abstract class Controller {
  protected function view(string $view, array $data=[]): void { View::render($view,$data); }
  protected function redirect(string $path): void { header("Location: ".APP_URL.$path); exit; }
  protected function json($data): void { header('Content-Type: application/json'); echo json_encode($data); exit; }
}
