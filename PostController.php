<?php
class BaseController {
    protected function render(string $view, array $data = []): void {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }
        require_once __DIR__ . '/../views/layout/header.php';
        require_once $viewFile;
        require_once __DIR__ . '/../views/layout/footer.php';
    }

    protected function renderJson(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $route, array $params = []): void {
        $url = SITE_URL . '/index.php?route=' . $route;
        foreach ($params as $k => $v) {
            $url .= '&' . urlencode($k) . '=' . urlencode($v);
        }
        header("Location: $url");
        exit;
    }

    protected function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function escape(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    protected function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }

    protected function requireAuth(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ' . SITE_URL . '/index.php?route=login');
            exit;
        }
    }

    protected function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('feed');
        }
    }

    protected function requireAjax(): void {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ajax only']);
            exit;
        }
    }

    protected function currentUser(): ?array {
        if (!$this->isLoggedIn()) return null;
        static $user = null;
        if ($user === null) {
            require_once __DIR__ . '/../models/UserModel.php';
            $model = new UserModel();
            $user  = $model->findById((int)$_SESSION['user_id']);
        }
        return $user ?: null;
    }

    protected function handleUpload(string $fileKey, string $subDir): ?string {
        if (empty($_FILES[$fileKey]['name'])) return null;
        $file = $_FILES[$fileKey];
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new RuntimeException('File exceeds 2 MB limit.');
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ALLOWED_TYPES, true)) {
            throw new RuntimeException('Invalid file type. Only JPG/PNG/GIF/WEBP allowed.');
        }
        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('', true) . '.' . strtolower($ext);
        $dest = UPLOAD_DIR . $subDir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Upload failed.');
        }
        return $name;
    }
}
