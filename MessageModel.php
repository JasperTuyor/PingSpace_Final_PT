<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/PostModel.php';

class SearchController extends BaseController {
    private UserModel $userModel;
    private PostModel $postModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->postModel = new PostModel();
    }

    public function index(): void {
        $this->requireAuth();
        $query = $this->sanitize($_GET['q'] ?? '');
        $viewerId = (int)$_SESSION['user_id'];

        $users = [];
        $posts = [];

        if (!empty($query)) {
            // Search Users
            $users = $this->userModel->searchUsers($query, $viewerId);
            // Search Posts
            $posts = $this->postModel->searchPosts($query, $viewerId);
        }

        $this->render('search/results', [
            'query' => $query,
            'users' => $users,
            'posts' => $posts
        ]);
    }
}