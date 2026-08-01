<?php
// Ministry Ops PHP - Bulletin (Boletins / Comunicados) Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';

class Bulletin {
    public static function getForUser(string $tenantId, string $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                b.*,
                p.full_name as author_name,
                ack.acknowledged_at
            FROM bulletins b
            LEFT JOIN profiles p ON b.created_by = p.id
            LEFT JOIN acknowledgements ack ON b.id = ack.bulletin_id AND ack.user_id = ?
            WHERE b.tenant_id = ? AND b.status = 'published'
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function acknowledge(string $bulletinId, string $userId, string $tenantId): bool {
        $db = Database::getConnection();
        
        $stmtCheck = $db->prepare("SELECT id FROM acknowledgements WHERE bulletin_id = ? AND user_id = ?");
        $stmtCheck->execute([$bulletinId, $userId]);
        if ($stmtCheck->fetch()) return true;

        $stmt = $db->prepare("
            INSERT INTO acknowledgements (id, tenant_id, bulletin_id, user_id, acknowledged_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([Helpers::generateUuid(), $tenantId, $bulletinId, $userId]);
    }

    public static function create(string $tenantId, string $userId, string $title, string $body): string {
        $db = Database::getConnection();
        $bulletinId = Helpers::generateUuid();
        $stmt = $db->prepare("
            INSERT INTO bulletins (id, tenant_id, title, body, status, created_by, created_at)
            VALUES (?, ?, ?, ?, 'published', ?, NOW())
        ");
        $stmt->execute([$bulletinId, $tenantId, trim($title), trim($body), $userId]);
        return $bulletinId;
    }
}
