<?php
require_once __DIR__ . '/../../config/database.php';

class NotificationModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, int $actorId, string $type, ?int $referenceId = null): void {
        if ($userId === $actorId) return; // don't notify self
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, actor_id, type, reference_id)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $actorId, $type, $referenceId]);
    }

    public function getForUser(int $userId, int $limit = 30): array {
        $stmt = $this->db->prepare(
            'SELECT n.*, u.username AS actor_username, u.full_name AS actor_name, u.avatar AS actor_avatar
             FROM notifications n
             JOIN users u ON u.id = n.actor_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAllRead(int $userId): void {
        $this->db->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ?'
        )->execute([$userId]);
    }

    public function getNewSince(int $userId, int $lastId): array {
        $stmt = $this->db->prepare(
            'SELECT n.*, u.username AS actor_username, u.full_name AS actor_name, u.avatar AS actor_avatar
             FROM notifications n
             JOIN users u ON u.id = n.actor_id
             WHERE n.user_id = ? AND n.id > ? AND n.is_read = 0
             ORDER BY n.created_at DESC'
        );
        $stmt->execute([$userId, $lastId]);
        return $stmt->fetchAll();
    }
}
