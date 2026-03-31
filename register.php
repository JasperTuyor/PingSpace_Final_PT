<?php
require_once __DIR__ . '/../../config/database.php';

class LikeModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function toggle(int $postId, int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT id FROM likes WHERE post_id = ? AND user_id = ?'
        );
        $stmt->execute([$postId, $userId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $this->db->prepare('DELETE FROM likes WHERE id = ?')->execute([$existing['id']]);
            $liked = false;
        } else {
            $this->db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)')->execute([$postId, $userId]);
            $liked = true;
        }
        $count = $this->getCount($postId);
        return ['liked' => $liked, 'count' => $count];
    }

    public function getCount(int $postId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = ?');
        $stmt->execute([$postId]);
        return (int)$stmt->fetchColumn();
    }
}
