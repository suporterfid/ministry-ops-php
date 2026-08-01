<?php
// Ministry Ops PHP - Checkin Model (Geolocation & Attendance Verification)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/Gamification.php';

class Checkin {
    public static function process(string $userId, string $assignmentId, string $tenantId, float $lat, float $lng, bool $bypassGeofence = false): array {
        $db = Database::getConnection();

        // 1. Fetch assignment details
        $stmtAs = $db->prepare("
            SELECT a.*, cl.center_lat, cl.center_lng, cl.radius_m, cl.window_start_mins, cl.window_end_mins
            FROM assignments a
            LEFT JOIN checkin_locations cl ON cl.tenant_id = a.tenant_id AND (cl.event_instance_id = a.event_instance_id OR cl.service_area_id = a.service_area_id)
            WHERE a.id = ? AND a.tenant_id = ?
        ");
        $stmtAs->execute([$assignmentId, $tenantId]);
        $assignment = $stmtAs->fetch();

        if (!$assignment || $assignment['volunteer_user_id'] !== $userId) {
            return ['ok' => false, 'code' => 'no_assignment', 'message' => 'Escala não encontrada para este usuário.'];
        }

        // 2. Validate Time Window
        $startsAtTimestamp = strtotime($assignment['starts_at']);
        $now = time();
        $windowStartMins = $assignment['window_start_mins'] !== null ? (int)$assignment['window_start_mins'] : 120;
        $windowEndMins = $assignment['window_end_mins'] !== null ? (int)$assignment['window_end_mins'] : 120;

        // Add 60s buffer margin for time boundary safety
        $opensAt = $startsAtTimestamp - ($windowStartMins * 60) - 60;
        $closesAt = $startsAtTimestamp + ($windowEndMins * 60) + 60;

        if ($now < $opensAt || $now > $closesAt) {
            return [
                'ok' => false,
                'code' => 'outside_window',
                'message' => 'Horário fora da janela de check-in.',
                'opensAt' => date('d/m/Y H:i', $opensAt),
                'closesAt' => date('d/m/Y H:i', $closesAt)
            ];
        }

        // 3. Geofence Distance Validation
        $centerLat = $assignment['center_lat'] !== null ? (float)$assignment['center_lat'] : null;
        $centerLng = $assignment['center_lng'] !== null ? (float)$assignment['center_lng'] : null;
        $radiusM = $assignment['radius_m'] !== null ? (int)$assignment['radius_m'] : 200;

        $distanceMeters = 0;
        if ($centerLat !== null && $centerLng !== null) {
            $distanceMeters = Helpers::haversineDistanceMeters($lat, $lng, $centerLat, $centerLng);
            if ($distanceMeters > $radiusM && !$bypassGeofence) {
                return [
                    'ok' => false,
                    'code' => 'outside_radius',
                    'message' => 'Você está fora do raio permitido para check-in no local.',
                    'distanceM' => round($distanceMeters),
                    'radiusM' => $radiusM
                ];
            }
        }

        // 4. Save Check-in
        $db->beginTransaction();
        try {
            $checkinId = Helpers::generateUuid();
            $stmtInsert = $db->prepare("
                INSERT INTO attendance_checkins (id, tenant_id, assignment_id, volunteer_user_id, latitude, longitude, was_auto_validated, source, checked_in_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'app', NOW())
            ");
            $stmtInsert->execute([$checkinId, $tenantId, $assignmentId, $userId, $lat, $lng, $bypassGeofence ? 0 : 1]);

            // Update assignment status to checked_in
            $stmtUpdateAs = $db->prepare("UPDATE assignments SET status = 'checked_in' WHERE id = ?");
            $stmtUpdateAs->execute([$assignmentId]);

            // Save attendance exception if geofence was bypassed
            if ($bypassGeofence) {
                $stmtExc = $db->prepare("
                    INSERT INTO attendance_exceptions (id, tenant_id, checkin_id, assignment_id, actor_user_id, reason, created_at)
                    VALUES (?, ?, ?, ?, ?, 'manual_override', NOW())
                ");
                $stmtExc->execute([Helpers::generateUuid(), $tenantId, $checkinId, $assignmentId, $userId]);
            }

            // Award gamification points for on-time checkin
            Gamification::awardPoints($tenantId, $userId, 'CHECKIN_ON_TIME', $assignmentId, 'Check-in presencial realizado com sucesso');

            $db->commit();
            return [
                'ok' => true,
                'checkinId' => $checkinId,
                'message' => 'Check-in realizado com sucesso!'
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
