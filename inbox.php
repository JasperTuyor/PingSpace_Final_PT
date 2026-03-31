<?php define('PAGE_TITLE', 'Login'); ?>
<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="card-title mb-4 text-center">
          <i class="fa-solid fa-users-viewfinder text-primary"></i> <?= htmlspecialchars(SITE_NAME) ?>
        </h4>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= SITE_URL ?>/index.php?route=login">
          <div class="mb-3">
            <label class="form-label">Username or Email</label>
            <input type="text" name="login" class="form-control"
                   value="<?= htmlspecialchars($old_login ?? '') ?>" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-center mt-3">
          <small>No account? <a href="<?= SITE_URL ?>/index.php?route=register">Register</a></small>
        </div>
      </div>
    </div>
  </div>
</div>
