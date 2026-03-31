<?php define('PAGE_TITLE', 'Edit Profile'); ?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h5 class="mb-4"><i class="fa fa-pen me-1 text-primary"></i> Edit Profile</h5>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= SITE_URL ?>/index.php?route=profile.update" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="full_name" class="form-control"
                   value="<?= htmlspecialchars($user['full_name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Bio</label>
            <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>

          <!-- Avatar upload with crop -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Profile Photo</label>
            <div class="d-flex align-items-center gap-3 mb-2">
              <img src="<?= SITE_URL ?>/assets/images/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>"
                   id="avatarPreview" class="rounded-circle" width="64" height="64"
                   style="object-fit:cover" alt="">
              <div>
                <label class="btn btn-outline-secondary btn-sm mb-0">
                  <i class="fa fa-upload"></i> Choose Photo
                  <input type="file" id="avatarInput" accept="image/*" class="d-none">
                </label>
                <small class="d-block text-muted mt-1">JPG, PNG, GIF or WEBP — max 2 MB</small>
              </div>
            </div>
            <input type="hidden" name="avatar_cropped" id="avatarCropped">
            <!-- hidden file proxy (filled by JS after crop) -->
          </div>

          <!-- Cover photo upload with crop -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Cover Photo</label>
            <div class="mb-2">
              <img src="<?= SITE_URL ?>/assets/images/uploads/covers/<?= htmlspecialchars($user['cover_photo']) ?>"
                   id="coverPreview" class="img-fluid rounded" style="max-height:120px;object-fit:cover;width:100%" alt="">
            </div>
            <label class="btn btn-outline-secondary btn-sm mb-0">
              <i class="fa fa-image"></i> Choose Cover Photo
              <input type="file" id="coverInput" name="cover_photo" accept="image/*" class="d-none">
            </label>
            <small class="d-block text-muted mt-1">Recommended: 1200×400. Max 2 MB.</small>
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="fa fa-save me-1"></i> Save Changes
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Crop Modal for avatar -->
<div class="modal fade" id="avatarCropModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crop Profile Photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="avatarCropTarget" src="" style="max-width:100%;max-height:400px" alt="">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="confirmAvatarCrop">Use Cropped Photo</button>
      </div>
    </div>
  </div>
</div>

<script>
// Avatar crop flow (profile edit page)
document.addEventListener('DOMContentLoaded', () => {
  let avatarCropper = null;
  const avatarInput    = document.getElementById('avatarInput');
  const cropTarget     = document.getElementById('avatarCropTarget');
  const cropModal      = new bootstrap.Modal(document.getElementById('avatarCropModal'));
  const avatarPreview  = document.getElementById('avatarPreview');
  const avatarCropped  = document.getElementById('avatarCropped');

  if (avatarInput) {
    avatarInput.addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        cropTarget.src = e.target.result;
        cropModal.show();
        setTimeout(() => {
          if (avatarCropper) avatarCropper.destroy();
          avatarCropper = new Cropper(cropTarget, { aspectRatio: 1, viewMode: 1 });
        }, 300);
      };
      reader.readAsDataURL(file);
    });
  }

  const confirmBtn = document.getElementById('confirmAvatarCrop');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      const canvas = avatarCropper.getCroppedCanvas({ width: 200, height: 200 });
      const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
      avatarPreview.src = dataUrl;
      avatarCropped.value = dataUrl;
      cropModal.hide();
      // Convert base64 to actual file and swap into form
      canvas.toBlob(blob => {
        const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(file);
        // We need a real file input to submit — inject one
        let realInput = document.getElementById('avatarFileReal');
        if (!realInput) {
          realInput = document.createElement('input');
          realInput.type = 'file';
          realInput.name = 'avatar';
          realInput.id   = 'avatarFileReal';
          realInput.style.display = 'none';
          confirmBtn.closest('form') && confirmBtn.closest('form').appendChild(realInput);
          avatarCropped.closest('form').appendChild(realInput);
        }
        realInput.files = dt.files;
      }, 'image/jpeg', 0.85);
    });
  }

  // Cover photo preview
  const coverInput   = document.getElementById('coverInput');
  const coverPreview = document.getElementById('coverPreview');
  if (coverInput) {
    coverInput.addEventListener('change', function () {
      const file = this.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => { coverPreview.src = e.target.result; };
      reader.readAsDataURL(file);
    });
  }
});
</script>
