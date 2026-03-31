<?php define('PAGE_TITLE', $profileUser['full_name']); ?>

<!-- Cover Photo -->
<div class="position-relative mb-5">
  <img src="<?= SITE_URL ?>/assets/images/uploads/covers/<?= htmlspecialchars($profileUser['cover_photo']) ?>"
       class="w-100 rounded" style="height:220px;object-fit:cover" alt="cover">
  <div class="position-absolute" style="bottom:-48px;left:24px">
    <img src="<?= SITE_URL ?>/assets/images/uploads/avatars/<?= htmlspecialchars($profileUser['avatar']) ?>"
         class="rounded-circle border border-4 border-white shadow"
         width="100" height="100" style="object-fit:cover" alt="avatar">
  </div>
</div>

<div class="row">
  <div class="col-lg-4">
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="mb-1"><?= htmlspecialchars($profileUser['full_name']) ?></h5>
        <div class="text-muted mb-2">@<?= htmlspecialchars($profileUser['username']) ?></div>
        <p class="small"><?= nl2br(htmlspecialchars($profileUser['bio'] ?? '')) ?></p>
        <div class="d-flex gap-3 mb-3">
          <div class="text-center">
            <div class="fw-bold follower-count"><?= $followerCount ?></div>
            <div class="text-muted small">Followers</div>
          </div>
          <div class="text-center">
            <div class="fw-bold"><?= $followingCount ?></div>
            <div class="text-muted small">Following</div>
          </div>
          <div class="text-center">
            <div class="fw-bold"><?= count($posts) ?></div>
            <div class="text-muted small">Posts</div>
          </div>
        </div>

        <?php if ($isOwner): ?>
          <a href="<?= SITE_URL ?>/index.php?route=profile.edit" class="btn btn-outline-primary w-100">
            <i class="fa fa-pen me-1"></i> Edit Profile
          </a>
        <?php else: ?>
          <button class="btn <?= $isFollowing ? 'btn-secondary' : 'btn-primary' ?> w-100 follow-btn"
                  data-target-id="<?= $profileUser['id'] ?>"
                  data-following="<?= $isFollowing ? '1' : '0' ?>">
            <i class="fa <?= $isFollowing ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
            <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
          </button>
          <a href="<?= SITE_URL ?>/index.php?route=messages.view&with=<?= $profileUser['id'] ?>"
             class="btn btn-outline-secondary w-100 mt-2">
            <i class="fa fa-envelope me-1"></i> Message
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <?php if (empty($posts)): ?>
      <div class="text-center text-muted py-5">
        <i class="fa fa-newspaper fa-3x mb-2"></i><p>No posts yet.</p>
      </div>
    <?php else: ?>
      <?php foreach ($posts as $post): ?>
        <?php $user = $viewer; require APP_ROOT . '/app/views/feed/_post_card.php'; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Edit Post Modal (reused) -->
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
