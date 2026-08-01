<?php
// Ministry Ops PHP - User Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';

class User {
    public static function create(string $email, string $password, string $fullName, ?string $phone = null): string {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $userId = Helpers::generateUuid();
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $db->prepare("INSERT INTO users (id, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, trim($email), $hash]);

            $stmtProfile = $db->prepare("INSERT INTO profiles (id, full_name, phone, created_at) VALUES (?, ?, ?, NOW())");
            $stmtProfile->execute([$userId, trim($fullName), trim($phone ?? '')]);

            $db->commit();
            return $userId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.email, p.full_name, p.phone
            FROM users u
            LEFT JOIN profiles p ON u.id = p.id
            WHERE LOWER(u.email) = LOWER(?)
        ");
        $stmt->execute([trim($email)]);
        return $stmt->fetch() ?: null;
    }

    public static function updateProfile(string $userId, string $fullName, ?string $phone = null): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE profiles SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([trim($fullName), trim($phone ?? ''), $userId]);
    }
}
