<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function showRegister(): void {
        if ($this->isLoggedIn()) $this->redirect('feed');
        $this->render('auth/register');
    }

    public function register(): void {
        $this->requirePost();
        $username  = $this->sanitize($_POST['username'] ?? '');
        $email     = $this->sanitize($_POST['email'] ?? '');
        $fullName  = $this->sanitize($_POST['full_name'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (strlen($username) < 3 || strlen($username) > 50)
            $errors[] = 'Username must be 3–50 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Invalid email address.';
        if (strlen($password) < 8)
            $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)
            $errors[] = 'Passwords do not match.';
        if ($this->userModel->findByUsername($username))
            $errors[] = 'Username already taken.';
        if ($this->userModel->findByEmail($email))
            $errors[] = 'Email already registered.';

        if ($errors) {
            $this->render('auth/register', ['errors' => $errors, 'old' => compact('username','email','fullName')]);
            return;
        }

        $userId = $this->userModel->create([
            'username'  => $username,
            'email'     => $email,
            'password'  => $password,
            'full_name' => $fullName,
        ]);

        $_SESSION['user_id'] = $userId;
        $this->redirect('feed');
    }

    public function showLogin(): void {
        if ($this->isLoggedIn()) $this->redirect('feed');
        $this->render('auth/login');
    }

    public function login(): void {
        $this->requirePost();
        $login    = $this->sanitize($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $this->userModel->findByEmail($login)
            : $this->userModel->findByUsername($login);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->render('auth/login', ['error' => 'Invalid credentials.', 'old_login' => $login]);
            return;
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['dark_mode'] = (bool)$user['dark_mode'];
        $this->redirect('feed');
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        $this->redirect('login');
    }
}
