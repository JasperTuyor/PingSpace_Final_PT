<?php
require_once __DIR__ . '/../../config/database.php';

class MessageModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getConversation(int $userId1, int $userId2): array {
        $stmt = $this->db->prepare(
            'SELECT m.*, u.username, u.full_name, u.avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE (m.sender_id = :a AND m.receiver_id = :b)
                OR (m.sender_id = :b2 AND m.receiver_id = :a2)
             ORDER BY m.created_at ASC'
        );
        $stmt->bindValue(':a',  $userId1, PDO::PARAM_INT);
        $stmt->bindValue(':b',  $userId2, PDO::PARAM_INT);
        $stmt->bindValue(':a2', $userId1, PDO::PARAM_INT);
        $stmt->bindValue(':b2', $userId2, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getInbox(int $userId): array {
        // Latest message per conversation partner
        $stmt = $this->db->prepare(
            'SELECT m.*, u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM messages m2
                     WHERE m2.receiver_id = :uid AND m2.sender_id = m.sender_id AND m2.is_read = 0) AS unread_count
             FROM messages m
             JOIN users u ON u.id = IF(m.sender_id = :uid2, m.receiver_id, m.sender_id)
             WHERE m.id IN (
                 SELECT MAX(id) FROM messages
                 WHERE sender_id = :uid3 OR receiver_id = :uid4
                 GROUP BY IF(sender_id < receiver_id, CONCAT(sender_id,"-",receiver_id), CONCAT(receiver_id,"-",sender_id))
             )
             ORDER BY m.created_at DESC'
        );
        $stmt->bindValue(':uid',  $userId, PDO::PARAM_INT);
        $stmt->bindValue(':uid2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':uid3', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':uid4', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function send(int $senderId, int $receiverId, string $body): int {
        $stmt = $this->db->prepare(
            'INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)'
        );
        $stmt->execute([$senderId, $receiverId, $body]);
        return (int)$this->db->lastInsertId();
    }

    public function markRead(int $senderId, int $receiverId): void {
        $this->db->prepare(
            'UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0'
        )->execute([$senderId, $receiverId]);
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
