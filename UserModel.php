<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/MessageModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class MessageController extends BaseController {
    private MessageModel     $messageModel;
    private UserModel        $userModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->messageModel = new MessageModel();
        $this->userModel    = new UserModel();
        $this->notifModel   = new NotificationModel();
    }

    public function inbox(): void {
        $this->requireAuth();
        $userId       = (int)$_SESSION['user_id'];
        $conversations = $this->messageModel->getInbox($userId);
        $user          = $this->currentUser();
        $this->render('messages/inbox', compact('conversations', 'user'));
    }

    public function conversation(): void {
        $this->requireAuth();
        $userId   = (int)$_SESSION['user_id'];
        $partnerId = (int)($_GET['with'] ?? 0);
        if (!$partnerId) $this->redirect('messages');
        $partner  = $this->userModel->findById($partnerId);
        if (!$partner) $this->redirect('messages');
        $messages = $this->messageModel->getConversation($userId, $partnerId);
        $this->messageModel->markRead($partnerId, $userId);
        $user = $this->currentUser();
        $this->render('messages/conversation', compact('messages', 'partner', 'user'));
    }

    /* --- AJAX: send message --- */
    public function send(): void {
        $this->requireAuth();
        $this->requireAjax();
        $userId     = (int)$_SESSION['user_id'];
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $body       = trim($_POST['body'] ?? '');
        if (!$body || !$receiverId) $this->renderJson(['success' => false, 'message' => 'Invalid.']);
        $msgId = $this->messageModel->send($userId, $receiverId, $body);
        $this->notifModel->create($receiverId, $userId, 'message', $msgId);
        $user   = $this->currentUser();
        ob_start();
        $isMine = true;
        $msg    = ['body' => $body, 'created_at' => date('Y-m-d H:i:s')];
        require __DIR__ . '/../views/messages/_bubble.php';
        $html = ob_get_clean();
        $this->renderJson(['success' => true, 'html' => $html, 'msg_id' => $msgId]);
    }

    /* --- AJAX: poll for new messages (simple polling) --- */
    public function poll(): void {
        $this->requireAuth();
        $this->requireAjax();
        $userId    = (int)$_SESSION['user_id'];
        $partnerId = (int)($_GET['with'] ?? 0);
        $lastId    = (int)($_GET['last_id'] ?? 0);
        // Fetch messages after lastId
        require_once __DIR__ . '/../../config/database.php';
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT m.*, u.username, u.full_name, u.avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
               AND m.id > ?
             ORDER BY m.created_at ASC'
        );
        $stmt->execute([$userId, $partnerId, $partnerId, $userId, $lastId]);
        $newMsgs = $stmt->fetchAll();
        $this->messageModel->markRead($partnerId, $userId);
        $html = '';
        if ($newMsgs) {
            foreach ($newMsgs as $msg) {
                $isMine = ($msg['sender_id'] == $userId);
                ob_start();
                require __DIR__ . '/../views/messages/_bubble.php';
                $html .= ob_get_clean();
            }
        }
        $lastIdNow = $newMsgs ? end($newMsgs)['id'] : $lastId;
        $this->renderJson(['success' => true, 'html' => $html, 'last_id' => $lastIdNow]);
    }
}
