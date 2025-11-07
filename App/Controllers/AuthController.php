<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};

final class AuthController extends Controller {

  /** Show login form */
  public function showLogin(): void {
    // Always pass CSRF token to view
    $this->view('auth/login', ['csrf' => Csrf::token()]);
  }

  /** Handle login POST */
  public function login(): void {
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');

    // normalize email (trim + lowercase)
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $ok = Auth::attempt($email, $password);

    if ($ok) {
      $this->redirect('/dashboard');
    } else {
      // show friendly message
      $_SESSION['flash'] = 'Invalid email or password.';
      $this->redirect('/');
    }
  }

  /** Logout */
  public function logout(): void {
    \App\Core\Auth::logout();
    $this->redirect('/');
  }
}
