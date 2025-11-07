<?php
namespace App\Core;

final class View {
  public static function render(string $view, array $data = []): void {
    extract($data);
    $viewFile = BASE_PATH."/app/Views/{$view}.php";
    $layout = BASE_PATH.'/app/Views/layouts/app.php';
    ob_start();
    if (file_exists($viewFile)) require $viewFile; else echo "View not found: {$view}";
    $content = ob_get_clean();
    require $layout;
  }
}
