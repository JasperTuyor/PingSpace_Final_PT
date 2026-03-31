<?php
require_once __DIR__ . '/../../config/database.php';

class PostModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFeed(int $userId, int $limit = 20, int $offset = 0): array {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM likes   WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes   WHERE post_id = p.id AND user_id = :uid) AS user_liked
             FROM posts p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC
             LIMIT :lim OFFSET :off'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, int $viewerId): array {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM likes   WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes   WHERE post_id = p.id AND user_id = :vid) AS user_liked
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.user_id = :uid
             ORDER BY p.created_at DESC'
        );
        $stmt->bindValue(':vid', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find a post with full user data and counts — used when rendering a
     * newly-created post card via AJAX.
     */
    public function findByIdFull(int $id, int $viewerId): array|false {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username, u.full_name, u.avatar,
                    (SELECT COUNT(*) FROM likes    WHERE post_id = p.id) AS like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM likes    WHERE post_id = p.id AND user_id = :vid) AS user_liked
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = :id'
        );
        $stmt->bindValue(':vid', $viewerId, PDO::PARAM_INT);
        $stmt->bindValue(':id',  $id,       PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(int $userId, string $content, ?string $image = null): int {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $content, $image]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $content): bool {
        $stmt = $this->db->prepare(
            'UPDATE posts SET content = ? WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$content, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
        return $stmt->execute([$id, $userId]);
    }
    public function searchPosts(string $q, int $viewerId): array {
    $stmt = $this->db->prepare(
        "SELECT p.*, u.username, u.full_name, u.avatar,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = :vid) AS user_liked
         FROM posts p
         JOIN users u ON u.id = p.user_id
         WHERE p.content LIKE :q
         ORDER BY p.created_at DESC LIMIT 30"
    );
    $stmt->bindValue(':vid', $viewerId, PDO::PARAM_INT);
    $stmt->bindValue(':q', "%$q%", PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}
}
