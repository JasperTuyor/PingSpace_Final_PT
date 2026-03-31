<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class ProfileController extends BaseController {
    private UserModel        $userModel;
    private PostModel        $postModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->userModel  = new UserModel();
        $this->postModel  = new PostModel();
        $this->notifModel = new NotificationModel();
    }

    public function view(): void {
        $this->requireAuth();
        $username = $this->sanitize($_GET['username'] ?? '');
        $viewerId = (int)$_SESSION['user_id'];

        $profileUser = $username
            ? $this->userModel->findByUsername($username)
            : $this->userModel->findById($viewerId);

        if (!$profileUser) {
            http_response_code(404);
            die('User not found.');
        }

        $posts          = $this->postModel->getByUser((int)$profileUser['id'], $viewerId);
        $isFollowing    = $this->userModel->isFollowing($viewerId, (int)$profileUser['id']);
        $followerCount  = $this->userModel->getFollowerCount((int)$profileUser['id']);
        $followingCount = $this->userModel->getFollowingCount((int)$profileUser['id']);
        $isOwner        = ($viewerId === (int)$profileUser['id']);
        $viewer         = $this->currentUser();

        $this->render('profile/view', compact(
            'profileUser', 'posts', 'isFollowing',
            'followerCount', 'followingCount', 'isOwner', 'viewer'
        ));
    }

    public function editForm(): void {
        $this->requireAuth();
        $user = $this->currentUser();
        $this->render('profile/edit', compact('user'));
    }

    public function update(): void {
        $this->requireAuth();
        $this->requirePost();
        $userId   = (int)$_SESSION['user_id'];
        $fullName = $this->sanitize($_POST['full_name'] ?? '');
        $bio      = $this->sanitize($_POST['bio'] ?? '');
        $data     = ['full_name' => $fullName, 'bio' => $bio];

        try {
            if (!empty($_FILES['avatar']['name'])) {
                $data['avatar'] = $this->handleUpload('avatar', 'avatars');
            }
            if (!empty($_FILES['cover_photo']['name'])) {
                $data['cover_photo'] = $this->handleUpload('cover_photo', 'covers');
            }
        } catch (RuntimeException $e) {
            $user = $this->currentUser();
            $this->render('profile/edit', ['user' => $user, 'error' => $e->getMessage()]);
            return;
        }

        $this->userModel->update($userId, $data);
        $this->redirect('profile');
    }

    /* --- AJAX: follow / unfollow --- */
    public function toggleFollow(): void {
        $this->requireAuth();
        $this->requireAjax();
        $targetId  = (int)($_POST['target_id'] ?? 0);
        $followerId = (int)$_SESSION['user_id'];

        if ($targetId === $followerId) $this->renderJson(['success' => false]);

        $isFollowing = $this->userModel->isFollowing($followerId, $targetId);
        if ($isFollowing) {
            $this->userModel->unfollow($followerId, $targetId);
        } else {
            $this->userModel->follow($followerId, $targetId);
            $this->notifModel->create($targetId, $followerId, 'follow');
        }
        $count = $this->userModel->getFollowerCount($targetId);
        $this->renderJson(['success' => true, 'following' => !$isFollowing, 'follower_count' => $count]);
    }

    /* --- AJAX: dark mode toggle --- */
    public function toggleDarkMode(): void {
        $this->requireAuth();
        $this->requireAjax();
        $mode   = (int)($_POST['dark_mode'] ?? 0);
        $userId = (int)$_SESSION['user_id'];
        $this->userModel->toggleDarkMode($userId, $mode);
        $_SESSION['dark_mode'] = (bool)$mode;
        $this->renderJson(['success' => true]);
    }
}
