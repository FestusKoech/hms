<?php if(!empty($_SESSION['flash'])): ?>
  <div class="alert alert-danger small">
    <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<?php $token = \App\Core\Csrf::token(); ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card">
      <div class="card-header">Login</div>
      <div class="card-body">
        <form method="post" action="<?= APP_URL ?>/login">

        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf ?? \App\Core\Csrf::token()) ?>">


          <input type="hidden" name="_token" value="<?= $token ?>">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" type="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" name="password" type="password" required>
          </div>
          <button class="btn btn-primary w-100">Sign in</button>
        </form>
      </div>
    </div>
  </div>
</div>
