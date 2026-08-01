<?php
// Ministry Ops PHP - Assignment (Escala) Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/Gamification.php';

class Assignment {
    public static function getForUser(string $userId, string $tenantId, ?string $filter = 'upcoming'): array {
        $db = Database::getConnection();
        
        $where = "a.volunteer_user_id = ? AND a.tenant_id = ?";
        if ($filter === 'upcoming') {
            $where .= " AND a.starts_at >= NOW()";
        } elseif ($filter === 'past') {
            $where .= " AND a.starts_at < NOW()";
        }

        $sql = "
            SELECT 
                a.*,
                o.name as operation_name,
                s.name as shift_name,
                m.name as ministry_name,
                r.name as role_name,
                sa.name as service_area_name,
                sa.anchor_lat, sa.anchor_lng, sa.checkin_radius_m,
                lp.full_name as leader_name,
                ac.decision as my_confirmation_decision,
                ac.created_at as confirmed_at
            FROM assignments a
            LEFT JOIN operations o ON a.operation_id = o.id
            LEFT JOIN shifts s ON a.shift_id = s.id
            LEFT JOIN ministries m ON a.ministry_id = m.id
            LEFT JOIN roles r ON a.required_role_id = r.id
            LEFT JOIN service_areas sa ON a.service_area_id = sa.id
            LEFT JOIN profiles lp ON a.leader_user_id = lp.id
            LEFT JOIN assignment_confirmations ac ON a.id = ac.assignment_id AND ac.volunteer_user_id = a.volunteer_user_id
            WHERE {$where}
            ORDER BY a.starts_at ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function findById(string $assignmentId, string $tenantId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                a.*,
                o.name as operation_name,
                s.name as shift_name,
                m.name as ministry_name,
                r.name as role_name,
                sa.name as service_area_name,
                sa.anchor_lat, sa.anchor_lng, sa.checkin_radius_m,
                lp.full_name as leader_name
            FROM assignments a
            LEFT JOIN operations o ON a.operation_id = o.id
            LEFT JOIN shifts s ON a.shift_id = s.id
            LEFT JOIN ministries m ON a.ministry_id = m.id
            LEFT JOIN roles r ON a.required_role_id = r.id
            LEFT JOIN service_areas sa ON a.service_area_id = sa.id
            LEFT JOIN profiles lp ON a.leader_user_id = lp.id
            WHERE a.id = ? AND a.tenant_id = ?
        ");
        $stmt->execute([$assignmentId, $tenantId]);
        return $stmt->fetch() ?: null;
    }

    public static function getNextUpcoming(string $userId, string $tenantId): ?array {
        $assignments = self::getForUser($userId, $tenantId, 'upcoming');
        return $assignments[0] ?? null;
    }

    public static function confirm(string $assignmentId, string $userId, string $tenantId, ?string $comment = null): bool {
        $db = Database::getConnection();
        $assignment = self::findById($assignmentId, $tenantId);
        if (!$assignment || $assignment['volunteer_user_id'] !== $userId) return false;

        $db->beginTransaction();
        try {
            // Update assignment status
            $stmt = $db->prepare("UPDATE assignments SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$assignmentId]);

            // Save confirmation record
            $confId = Helpers::generateUuid();
            $stmtConf = $db->prepare("
                INSERT INTO assignment_confirmations (id, tenant_id, assignment_id, volunteer_user_id, decision, comment, created_at)
                VALUES (?, ?, ?, ?, 'confirmed', ?, NOW())
            ");
            $stmtConf->execute([$confId, $tenantId, $assignmentId, $userId, trim($comment ?? '')]);

            // Award gamification points for early confirmation
            Gamification::awardPoints($tenantId, $userId, 'CONFIRM_EARLY', $assignmentId, 'Confirmação antecipada realizada no app');

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function decline(string $assignmentId, string $userId, string $tenantId, ?string $declineReasonId = null, ?string $comment = null): bool {
        $db = Database::getConnection();
        $assignment = self::findById($assignmentId, $tenantId);
        if (!$assignment || $assignment['volunteer_user_id'] !== $userId) return false;

        $db->beginTransaction();
        try {
            // Update assignment status
            $stmt = $db->prepare("UPDATE assignments SET status = 'declined' WHERE id = ?");
            $stmt->execute([$assignmentId]);

            // Save confirmation decision as declined
            $confId = Helpers::generateUuid();
            $stmtConf = $db->prepare("
                INSERT INTO assignment_confirmations (id, tenant_id, assignment_id, volunteer_user_id, decision, decline_reason_id, comment, created_at)
                VALUES (?, ?, ?, ?, 'declined', ?, ?, NOW())
            ");
            $stmtConf->execute([$confId, $tenantId, $assignmentId, $userId, $declineReasonId ?: null, trim($comment ?? '')]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getDeclineReasons(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM decline_reasons WHERE tenant_id = ? AND is_active = 1 ORDER BY label ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }
}
