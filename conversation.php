<?php define('PAGE_TITLE', 'Register'); ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="card-title mb-4 text-center">
          <i class="fa-solid fa-users-viewfinder text-primary"></i> Create Account
        </h4>
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <form method="POST" action="<?= SITE_URL ?>/index.php?route=register">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control"
                   value="<?= htmlspecialchars($old['fullName'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password <small class="text-muted">(min 8 chars)</small></label>
            <input type="password" name="password" class="form-control" required minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <div class="text-center mt-3">
          <small>Already have an account? <a href="<?= SITE_URL ?>/index.php?route=login">Login</a></small>
        </div>
      </div>
    </div>
  </div>
</div>
