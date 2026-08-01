<?php
// Ministry Ops PHP - Admin Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';

class AdminModel {
    public static function getDashboardStats(string $tenantId): array {
        $db = Database::getConnection();

        // 1. Pending Confirmations Count
        $stmtPending = $db->prepare("SELECT COUNT(*) as cnt FROM assignments WHERE tenant_id = ? AND status = 'pending_confirmation'");
        $stmtPending->execute([$tenantId]);
        $pendingConfirmations = (int)($stmtPending->fetch()['cnt'] ?? 0);

        // 2. Total Assignments Count
        $stmtTotal = $db->prepare("SELECT COUNT(*) as cnt FROM assignments WHERE tenant_id = ? AND status IN ('pending_confirmation', 'confirmed', 'declined')");
        $stmtTotal->execute([$tenantId]);
        $totalAssig = (int)($stmtTotal->fetch()['cnt'] ?? 0);

        // 3. Confirmed Count
        $stmtConf = $db->prepare("SELECT COUNT(*) as cnt FROM assignments WHERE tenant_id = ? AND status = 'confirmed'");
        $stmtConf->execute([$tenantId]);
        $confirmedAssig = (int)($stmtConf->fetch()['cnt'] ?? 0);

        $confirmationRate = $totalAssig > 0 ? round(($confirmedAssig / $totalAssig) * 100) : 0;

        // 4. Checkins Today
        $stmtCheckins = $db->prepare("SELECT COUNT(*) as cnt FROM attendance_checkins WHERE tenant_id = ? AND DATE(checked_in_at) = CURRENT_DATE()");
        $stmtCheckins->execute([$tenantId]);
        $checkInsToday = (int)($stmtCheckins->fetch()['cnt'] ?? 0);

        // 5. Open Swaps
        $stmtSwaps = $db->prepare("SELECT COUNT(*) as cnt FROM swap_requests WHERE tenant_id = ? AND status IN ('open', 'pending_approval')");
        $stmtSwaps->execute([$tenantId]);
        $swapsPending = (int)($stmtSwaps->fetch()['cnt'] ?? 0);

        // 6. Active Members
        $stmtMembers = $db->prepare("SELECT COUNT(*) as cnt FROM tenant_memberships WHERE tenant_id = ? AND status = 'active'");
        $stmtMembers->execute([$tenantId]);
        $activeMembers = (int)($stmtMembers->fetch()['cnt'] ?? 0);

        return [
            'pendingConfirmations' => $pendingConfirmations,
            'confirmationRatePct' => $confirmationRate,
            'checkInsToday' => $checkInsToday,
            'swapsPending' => $swapsPending,
            'activeMembers' => $activeMembers
        ];
    }

    public static function getMembers(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT tm.*, u.email, p.full_name, p.phone
            FROM tenant_memberships tm
            JOIN users u ON tm.user_id = u.id
            LEFT JOIN profiles p ON u.id = p.id
            WHERE tm.tenant_id = ?
            ORDER BY p.full_name ASC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function getJoinRequests(string $tenantId): array {
        $db = Database::getConnection();
        $stmtTenant = $db->prepare("SELECT code FROM tenants WHERE id = ?");
        $stmtTenant->execute([$tenantId]);
        $tenantCode = $stmtTenant->fetch()['code'] ?? '';

        $stmt = $db->prepare("
            SELECT tr.*, u.email, p.full_name, p.phone
            FROM tenant_join_requests tr
            JOIN users u ON tr.user_id = u.id
            LEFT JOIN profiles p ON u.id = p.id
            WHERE LOWER(tr.tenant_code) = LOWER(?)
            ORDER BY tr.created_at DESC
        ");
        $stmt->execute([$tenantCode]);
        return $stmt->fetchAll();
    }

    public static function reviewJoinRequest(string $requestId, string $action, string $adminUserId, string $tenantId): bool {
        $db = Database::getConnection();
        
        $stmtReq = $db->prepare("SELECT * FROM tenant_join_requests WHERE id = ?");
        $stmtReq->execute([$requestId]);
        $req = $stmtReq->fetch();

        if (!$req || $req['status'] !== 'pending') return false;

        $db->beginTransaction();
        try {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmtUpd = $db->prepare("
                UPDATE tenant_join_requests 
                SET status = ?, reviewed_by = ?, reviewed_at = NOW() 
                WHERE id = ?
            ");
            $stmtUpd->execute([$status, $adminUserId, $requestId]);

            if ($action === 'approve') {
                $memId = Helpers::generateUuid();
                $stmtMem = $db->prepare("
                    INSERT INTO tenant_memberships (id, tenant_id, user_id, primary_role, status, created_at)
                    VALUES (?, ?, ?, 'volunteer', 'active', NOW())
                    ON DUPLICATE KEY UPDATE status = 'active'
                ");
                $stmtMem->execute([$memId, $tenantId, $req['user_id']]);

                $volId = Helpers::generateUuid();
                $stmtVol = $db->prepare("
                    INSERT INTO volunteers (id, tenant_id, user_id, is_active, created_at)
                    VALUES (?, ?, ?, 1, NOW())
                    ON DUPLICATE KEY UPDATE is_active = 1
                ");
                $stmtVol->execute([$volId, $tenantId, $req['user_id']]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getConfirmationsQueue(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                a.*,
                p.full_name as volunteer_name,
                r.name as role_name,
                o.name as operation_name,
                s.name as shift_name,
                dr.label as decline_reason_label,
                ac.comment as confirmation_comment
            FROM assignments a
            JOIN users u ON a.volunteer_user_id = u.id
            LEFT JOIN profiles p ON u.id = p.id
            LEFT JOIN roles r ON a.required_role_id = r.id
            LEFT JOIN operations o ON a.operation_id = o.id
            LEFT JOIN shifts s ON a.shift_id = s.id
            LEFT JOIN assignment_confirmations ac ON a.id = ac.assignment_id AND ac.volunteer_user_id = a.volunteer_user_id
            LEFT JOIN decline_reasons dr ON ac.decline_reason_id = dr.id
            WHERE a.tenant_id = ?
            ORDER BY a.starts_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function getOperations(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM operations WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function createOperation(string $tenantId, string $name, ?string $description = null): string {
        $db = Database::getConnection();
        $id = Helpers::generateUuid();
        $stmt = $db->prepare("INSERT INTO operations (id, tenant_id, name, description, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
        $stmt->execute([$id, $tenantId, trim($name), trim($description ?? '')]);
        return $id;
    }

    public static function getEventInstances(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ei.*, o.name as operation_name
            FROM event_instances ei
            JOIN operations o ON ei.operation_id = o.id
            WHERE ei.tenant_id = ?
            ORDER BY ei.starts_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function createEventInstance(string $tenantId, string $operationId, string $startsAt, string $endsAt, ?string $locationName = null, ?float $lat = null, ?float $lng = null): string {
        $db = Database::getConnection();
        $id = Helpers::generateUuid();
        $stmt = $db->prepare("
            INSERT INTO event_instances (id, tenant_id, operation_id, starts_at, ends_at, location_name, location_lat, location_lng, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$id, $tenantId, $operationId, $startsAt, $endsAt, trim($locationName ?? ''), $lat, $lng]);
        return $id;
    }

    public static function getShifts(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.*, ei.starts_at as event_date, o.name as operation_name
            FROM shifts s
            JOIN event_instances ei ON s.event_instance_id = ei.id
            JOIN operations o ON ei.operation_id = o.id
            WHERE s.tenant_id = ?
            ORDER BY s.starts_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function createShift(string $tenantId, string $eventInstanceId, string $name, string $startsAt, string $endsAt): string {
        $db = Database::getConnection();
        $id = Helpers::generateUuid();
        $stmt = $db->prepare("INSERT INTO shifts (id, tenant_id, event_instance_id, name, starts_at, ends_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$id, $tenantId, $eventInstanceId, trim($name), $startsAt, $endsAt]);
        return $id;
    }

    public static function getRoles(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM roles WHERE tenant_id = ? ORDER BY name ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function createAssignment(string $tenantId, array $data): string {
        $db = Database::getConnection();
        $assignmentId = Helpers::generateUuid();
        $stmt = $db->prepare("
            INSERT INTO assignments (
                id, tenant_id, operation_id, event_instance_id, shift_id, 
                ministry_id, required_role_id, volunteer_user_id, leader_user_id, 
                instructions, status, starts_at, ends_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_confirmation', ?, ?, NOW())
        ");
        $stmt->execute([
            $assignmentId,
            $tenantId,
            $data['operation_id'] ?? null,
            $data['event_instance_id'] ?? null,
            $data['shift_id'] ?? null,
            $data['ministry_id'] ?? null,
            $data['required_role_id'] ?? null,
            $data['volunteer_user_id'],
            $data['leader_user_id'] ?? null,
            trim($data['instructions'] ?? ''),
            $data['starts_at'],
            $data['ends_at']
        ]);
        return $assignmentId;
    }

    public static function isTenantMember(string $tenantId, string $userId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM tenant_memberships WHERE tenant_id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$tenantId, $userId]);
        return (bool)$stmt->fetch();
    }

    public static function getAttendanceLogs(string $tenantId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                ac.*,
                p.full_name as volunteer_name,
                o.name as operation_name,
                a.starts_at as shift_starts_at,
                ax.reason as exception_reason
            FROM attendance_checkins ac
            JOIN users u ON ac.volunteer_user_id = u.id
            LEFT JOIN profiles p ON u.id = p.id
            JOIN assignments a ON ac.assignment_id = a.id
            LEFT JOIN operations o ON a.operation_id = o.id
            LEFT JOIN attendance_exceptions ax ON ax.checkin_id = ac.id
            WHERE ac.tenant_id = ?
            ORDER BY ac.checked_in_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function deleteOperation(string $tenantId, string $operationId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM operations WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$operationId, $tenantId]);
    }

    public static function deleteEventInstance(string $tenantId, string $eventId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM event_instances WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$eventId, $tenantId]);
    }

    public static function deleteShift(string $tenantId, string $shiftId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM shifts WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$shiftId, $tenantId]);
    }
}
