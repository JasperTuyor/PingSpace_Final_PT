<?php define('PAGE_TITLE', 'Newsfeed'); ?>
<div class="row">
  <!-- Feed column -->
  <div class="col-lg-7 mx-auto">

    <!-- Create Post Card -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex gap-2 align-items-start">
          <img src="<?= SITE_URL ?>/assets/images/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>"
               class="rounded-circle" width="42" height="42" style="object-fit:cover" alt="">
          <div class="flex-grow-1">
            <textarea class="form-control mb-2" id="newPostContent" rows="2"
                      placeholder="What's on your mind?"></textarea>
            <!-- Image preview (shows immediately on file select) -->
            <div id="postImagePreview" class="d-none mb-2 position-relative">
              <img id="postImageThumb" src="" class="img-fluid rounded w-100"
                   style="max-height:260px;object-fit:cover" alt="preview">
              <button type="button" id="removePostImage"
                      class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                      title="Remove image">
                <i class="fa fa-times"></i>
              </button>
              <span class="badge bg-secondary position-absolute bottom-0 start-0 m-1" id="openCropAgain"
                    style="cursor:pointer" title="Click to crop image">
                <i class="fa fa-crop-alt me-1"></i>Crop image
              </span>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <label class="btn btn-outline-secondary btn-sm mb-0">
                <i class="fa fa-image"></i> Photo
                <input type="file" id="newPostImage" accept="image/*" class="d-none">
              </label>
              <button class="btn btn-primary btn-sm" id="submitPost">
                <i class="fa fa-paper-plane"></i> Post
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Posts Feed -->
    <div id="postsFeed">
      <?php if (empty($posts)): ?>
        <div class="text-center text-muted py-5">
          <i class="fa fa-newspaper fa-3x mb-3"></i>
          <p>No posts yet. Be the first to post!</p>
        </div>
      <?php else: ?>
        <?php foreach ($posts as $post): ?>
          <?php require __DIR__ . '/_post_card.php'; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Load More -->
    <div class="text-center my-4">
      <button class="btn btn-outline-secondary" id="loadMoreBtn"
              data-offset="<?= count($posts) ?>"
              <?= count($posts) < 20 ? 'style="display:none"' : '' ?>>
        <i class="fa fa-chevron-down"></i> Load More
      </button>
    </div>
  </div>
</div>

<!-- Edit Post Modal -->
<div class="modal fade" id="editPostModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea class="form-control" id="editPostContent" rows="4"></textarea>
        <input type="hidden" id="editPostId">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="saveEditPost">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Image Crop Modal -->
<div class="modal fade" id="cropModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crop Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img id="cropTarget" src="" style="max-width:100%" alt="">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="confirmCrop">Use Cropped Image</button>
      </div>
    </div>
  </div>
</div>
