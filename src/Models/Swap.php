<?php
// Ministry Ops PHP - Swap Requests Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/Gamification.php';

class Swap {
    public static function createRequest(string $assignmentId, string $userId, string $tenantId, ?string $reason = null): bool {
        $db = Database::getConnection();
        
        // Verify assignment ownership
        $stmtCheck = $db->prepare("SELECT id FROM assignments WHERE id = ? AND volunteer_user_id = ? AND tenant_id = ?");
        $stmtCheck->execute([$assignmentId, $userId, $tenantId]);
        if (!$stmtCheck->fetch()) return false;

        $db->beginTransaction();
        try {
            $swapId = Helpers::generateUuid();
            $stmt = $db->prepare("
                INSERT INTO swap_requests (id, tenant_id, assignment_id, requester_user_id, reason, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'open', NOW())
            ");
            $stmt->execute([$swapId, $tenantId, $assignmentId, $userId, trim($reason ?? '')]);

            // Update assignment status
            $stmtAs = $db->prepare("UPDATE assignments SET status = 'swap_requested' WHERE id = ?");
            $stmtAs->execute([$assignmentId]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getOpenSwaps(string $tenantId, string $currentUserId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                sr.*,
                a.starts_at, a.ends_at,
                o.name as operation_name,
                r.name as role_name,
                rp.full_name as requester_name,
                rp.phone as requester_phone,
                sc.id as my_candidate_id,
                sc.status as my_candidate_status
            FROM swap_requests sr
            JOIN assignments a ON sr.assignment_id = a.id
            LEFT JOIN operations o ON a.operation_id = o.id
            LEFT JOIN roles r ON a.required_role_id = r.id
            JOIN profiles rp ON sr.requester_user_id = rp.id
            LEFT JOIN swap_candidates sc ON sr.id = sc.swap_request_id AND sc.volunteer_user_id = ?
            WHERE sr.tenant_id = ? AND sr.status IN ('open', 'pending_approval')
            ORDER BY sr.created_at DESC
        ");
        $stmt->execute([$currentUserId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function offerToCover(string $swapRequestId, string $volunteerUserId, string $tenantId): bool {
        $db = Database::getConnection();
        
        $stmtCheck = $db->prepare("SELECT id, requester_user_id FROM swap_requests WHERE id = ? AND tenant_id = ? AND status = 'open'");
        $stmtCheck->execute([$swapRequestId, $tenantId]);
        $swap = $stmtCheck->fetch();
        
        if (!$swap || $swap['requester_user_id'] === $volunteerUserId) return false;

        $db->beginTransaction();
        try {
            $candidateId = Helpers::generateUuid();
            $stmt = $db->prepare("
                INSERT INTO swap_candidates (id, tenant_id, swap_request_id, volunteer_user_id, status, created_at)
                VALUES (?, ?, ?, ?, 'accepted', NOW())
            ");
            $stmt->execute([$candidateId, $tenantId, $swapRequestId, $volunteerUserId]);

            // Update swap status to pending admin/leader approval
            $stmtSwap = $db->prepare("UPDATE swap_requests SET status = 'pending_approval', suggested_volunteer_id = ? WHERE id = ?");
            $stmtSwap->execute([$volunteerUserId, $swapRequestId]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function approveSwap(string $swapRequestId, string $adminUserId, string $tenantId): bool {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT * FROM swap_requests WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$swapRequestId, $tenantId]);
        $swap = $stmt->fetch();

        if (!$swap || !$swap['suggested_volunteer_id']) return false;

        $db->beginTransaction();
        try {
            // Reassign assignment to new volunteer
            $stmtReassign = $db->prepare("
                UPDATE assignments 
                SET volunteer_user_id = ?, status = 'confirmed' 
                WHERE id = ?
            ");
            $stmtReassign->execute([$swap['suggested_volunteer_id'], $swap['assignment_id']]);

            // Mark swap request as approved
            $stmtApprove = $db->prepare("
                UPDATE swap_requests 
                SET status = 'approved', approved_by = ?, approved_at = NOW() 
                WHERE id = ?
            ");
            $stmtApprove->execute([$adminUserId, $swapRequestId]);

            // Award gamification points to volunteer who covered
            Gamification::awardPoints($tenantId, $swap['suggested_volunteer_id'], 'SWAP_HELP', $swap['assignment_id'], 'Bônus por cobrir troca de escala');

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
