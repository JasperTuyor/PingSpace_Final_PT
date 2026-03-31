<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PostModel.php';
require_once __DIR__ . '/../models/CommentModel.php';
require_once __DIR__ . '/../models/LikeModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class PostController extends BaseController {
    private PostModel        $postModel;
    private CommentModel     $commentModel;
    private LikeModel        $likeModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->postModel    = new PostModel();
        $this->commentModel = new CommentModel();
        $this->likeModel    = new LikeModel();
        $this->notifModel   = new NotificationModel();
    }

    /* --- Feed (newsfeed) --- */
    public function feed(): void {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $posts  = $this->postModel->getFeed($userId, 20, $offset);
        $user   = $this->currentUser();
        $this->render('feed/index', compact('posts', 'user', 'offset'));
    }

    /* --- AJAX: create post --- */
    public function createPost(): void {
        $this->requireAuth();
        $this->requireAjax();
        $userId  = (int)$_SESSION['user_id'];
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $this->renderJson(['success' => false, 'message' => 'Post cannot be empty.']);
        }
        $image = null;
        try {
            $image = $this->handleUpload('image', 'posts');
        } catch (RuntimeException $e) {
            $this->renderJson(['success' => false, 'message' => $e->getMessage()]);
        }
        $postId = $this->postModel->create($userId, $content, $image);
        $post   = $this->postModel->findByIdFull($postId, $userId);
        $user   = $this->currentUser();
        ob_start();
        require __DIR__ . '/../views/feed/_post_card.php';
        $html = ob_get_clean();
        $this->renderJson(['success' => true, 'html' => $html, 'post_id' => $postId]);
    }

    /* --- AJAX: update post --- */
    public function updatePost(): void {
        $this->requireAuth();
        $this->requireAjax();
        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId  = (int)$_SESSION['user_id'];
        if (!$content) $this->renderJson(['success' => false, 'message' => 'Empty content.']);
        $ok = $this->postModel->update($postId, $userId, $content);
        $this->renderJson(['success' => $ok, 'content' => htmlspecialchars($content, ENT_QUOTES)]);
    }

    /* --- AJAX: delete post --- */
    public function deletePost(): void {
        $this->requireAuth();
        $this->requireAjax();
        $postId = (int)($_POST['post_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];
        $ok = $this->postModel->delete($postId, $userId);
        $this->renderJson(['success' => $ok]);
    }

    /* --- AJAX: like/unlike --- */
    public function toggleLike(): void {
        $this->requireAuth();
        $this->requireAjax();
        $postId = (int)($_POST['post_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];
        $result = $this->likeModel->toggle($postId, $userId);
        // Notify post owner if liked
        if ($result['liked']) {
            $post = $this->postModel->findById($postId);
            if ($post) $this->notifModel->create($post['user_id'], $userId, 'like', $postId);
        }
        $this->renderJson(['success' => true, 'liked' => $result['liked'], 'count' => $result['count']]);
    }

    /* --- AJAX: add comment --- */
    public function addComment(): void {
        $this->requireAuth();
        $this->requireAjax();
        $postId  = (int)($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $userId  = (int)$_SESSION['user_id'];
        if (!$content) $this->renderJson(['success' => false, 'message' => 'Comment cannot be empty.']);
        $commentId = $this->commentModel->create($postId, $userId, $content);
        $comment   = $this->commentModel->findById($commentId);
        $user      = $this->currentUser();
        // Notify post owner
        $post = $this->postModel->findById($postId);
        if ($post) $this->notifModel->create($post['user_id'], $userId, 'comment', $postId);
        ob_start();
        require __DIR__ . '/../views/feed/_comment.php';
        $html = ob_get_clean();
        $this->renderJson(['success' => true, 'html' => $html, 'comment_id' => $commentId]);
    }

    /* --- AJAX: edit comment --- */
    public function editComment(): void {
        $this->requireAuth();
        $this->requireAjax();
        $commentId = (int)($_POST['comment_id'] ?? 0);
        $content   = trim($_POST['content'] ?? '');
        $userId    = (int)$_SESSION['user_id'];
        if (!$content) $this->renderJson(['success' => false, 'message' => 'Empty content.']);
        $ok = $this->commentModel->update($commentId, $userId, $content);
        $this->renderJson(['success' => $ok, 'content' => htmlspecialchars($content, ENT_QUOTES)]);
    }

    /* --- AJAX: delete comment --- */
    public function deleteComment(): void {
        $this->requireAuth();
        $this->requireAjax();
        $commentId = (int)($_POST['comment_id'] ?? 0);
        $userId    = (int)$_SESSION['user_id'];
        $ok = $this->commentModel->delete($commentId, $userId);
        $this->renderJson(['success' => $ok]);
    }

    /* --- AJAX: get comments for a post --- */
    public function getComments(): void {
        $this->requireAuth();
        $this->requireAjax();
        $postId   = (int)($_GET['post_id'] ?? 0);
        $comments = $this->commentModel->getByPost($postId);
        $user     = $this->currentUser();
        ob_start();
        foreach ($comments as $comment) {
            require __DIR__ . '/../views/feed/_comment.php';
        }
        $html = ob_get_clean();
        $this->renderJson(['success' => true, 'html' => $html, 'count' => count($comments)]);
    }
}
