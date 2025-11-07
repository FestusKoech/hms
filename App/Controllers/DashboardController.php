<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Gate};

final class DashboardController extends Controller {
  public function index(): void {
    if(!Auth::check()) $this->redirect('/');
    $this->view('dashboard/index', ['user'=>Auth::user()]);
  }
}
