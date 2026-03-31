<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, full_name, bio, avatar, cover_photo, dark_mode, created_at FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = ?'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $values = [];
        foreach ($data as $key => $val) {
            $fields[] = "`$key` = ?";
            $values[] = $val;
        }
        $values[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    public function searchUsers(string $q, int $excludeId): array {
        $stmt = $this->db->prepare(
            'SELECT id, username, full_name, avatar FROM users
             WHERE (username LIKE ? OR full_name LIKE ?) AND id != ?
             LIMIT 20'
        );
        $like = "%$q%";
        $stmt->execute([$like, $like, $excludeId]);
        return $stmt->fetchAll();
    }

    /* ---- Follow helpers ---- */
    public function isFollowing(int $followerId, int $followingId): bool {
        $stmt = $this->db->prepare(
            'SELECT id FROM follows WHERE follower_id = ? AND following_id = ?'
        );
        $stmt->execute([$followerId, $followingId]);
        return (bool)$stmt->fetch();
    }

    public function follow(int $followerId, int $followingId): bool {
        try {
            $stmt = $this->db->prepare(
                'INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)'
            );
            return $stmt->execute([$followerId, $followingId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function unfollow(int $followerId, int $followingId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM follows WHERE follower_id = ? AND following_id = ?'
        );
        return $stmt->execute([$followerId, $followingId]);
    }

    public function getFollowerCount(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM follows WHERE following_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getFollowingCount(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM follows WHERE follower_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function toggleDarkMode(int $userId, int $mode): bool {
        return $this->update($userId, ['dark_mode' => $mode]);
    }
}
