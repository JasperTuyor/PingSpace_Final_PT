<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController extends BaseController {
    private NotificationModel $notifModel;

    public function __construct() {
        $this->notifModel = new NotificationModel();
    }

    public function index(): void {
        $this->requireAuth();
        $userId        = (int)$_SESSION['user_id'];
        $notifications = $this->notifModel->getForUser($userId);
        $this->notifModel->markAllRead($userId);
        $user = $this->currentUser();
        $this->render('notifications/index', compact('notifications', 'user'));
    }

    /* --- AJAX polling endpoint --- */
    public function poll(): void {
        $this->requireAuth();
        $this->requireAjax();
        $userId = (int)$_SESSION['user_id'];
        $lastId = (int)($_GET['last_id'] ?? 0);
        $items  = $this->notifModel->getNewSince($userId, $lastId);
        $count  = $this->notifModel->getUnreadCount($userId);
        $lastIdNow = $items ? (int)$items[0]['id'] : $lastId;
        $this->renderJson([
            'success'  => true,
            'count'    => $count,
            'last_id'  => $lastIdNow,
            'items'    => $items,
        ]);
    }

    /* --- AJAX mark all read --- */
    public function markRead(): void {
        $this->requireAuth();
        $this->requireAjax();
        $this->notifModel->markAllRead((int)$_SESSION['user_id']);
        $this->renderJson(['success' => true]);
    }
}
