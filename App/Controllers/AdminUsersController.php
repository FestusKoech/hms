<?php
namespace App\Controllers;
use App\Core\{Controller,Auth,Csrf};
use App\Models\User;
use App\Core\DB;

final class AdminUsersController extends Controller {
  private function ensureAdmin(): void {
    if(!Auth::check()) $this->redirect('/');
    if(Auth::user()['role'] !== 'admin'){ header('HTTP/1.1 403 Forbidden'); exit('Forbidden'); }
  }

  public function index(): void {
    $this->ensureAdmin();
    $items = DB::pdo()->query("SELECT id,name,email,role,created_at FROM users ORDER BY id DESC")->fetchAll();
    $this->view('admin/users/index', ['items'=>$items,'csrf'=>Csrf::token()]);
  }

  public function create(): void {
    $this->ensureAdmin();
    $this->view('admin/users/create', ['csrf'=>Csrf::token()]);
  }

  public function store(): void {
    $this->ensureAdmin();
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'receptionist';
    $password = $_POST['password'] ?? '';
    if($name==='' || $email==='' || $password==='') { $this->redirect('/admin/users/create'); }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $st = DB::pdo()->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
    $st->execute([$name,$email,$hash,$role]);

    $this->redirect('/admin/users');
  }

  public function edit(): void {
    $this->ensureAdmin();
    $id = (int)($_GET['id'] ?? 0);
    $st = DB::pdo()->prepare("SELECT id,name,email,role FROM users WHERE id=?");
    $st->execute([$id]); $u = $st->fetch();
    if(!$u) $this->redirect('/admin/users');
    $this->view('admin/users/edit',['u'=>$u,'csrf'=>Csrf::token()]);
  }

  public function update(): void {
    $this->ensureAdmin();
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');

    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'receptionist';
    $password = $_POST['password'] ?? '';

    if($password!==''){
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $sql="UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?";
      DB::pdo()->prepare($sql)->execute([$name,$email,$role,$hash,$id]);
    } else {
      $sql="UPDATE users SET name=?, email=?, role=? WHERE id=?";
      DB::pdo()->prepare($sql)->execute([$name,$email,$role,$id]);
    }
    $this->redirect('/admin/users');
  }

  public function delete(): void {
    $this->ensureAdmin();
    if(!Csrf::check($_POST['_token'] ?? '')) exit('CSRF');
    $id = (int)$_POST['id'];
    // prevent deleting your own account by accident
    if($id === (int)Auth::user()['id']) $this->redirect('/admin/users');

    DB::pdo()->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    $this->redirect('/admin/users');
  }
}
