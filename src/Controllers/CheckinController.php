<?php
// Ministry Ops PHP - Checkin Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/Assignment.php';
require_once __DIR__ . '/../Models/Checkin.php';

class CheckinController {
    public function index(): void {
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $assignmentId = $_GET['assignment_id'] ?? '';

        $assignment = null;
        if ($tenantId && $assignmentId) {
            $assignment = Assignment::findById($assignmentId, $tenantId);
        }

        if (!$assignment) {
            $next = Assignment::getNextUpcoming($user['id'], $tenantId ?? '');
            if ($next) {
                $assignment = $next;
            }
        }

        require __DIR__ . '/../../templates/checkin/index.php';
    }

    public function handleCheckin(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        
        $assignmentId = $_POST['assignment_id'] ?? '';
        $lat = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $lng = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        $bypass = !empty($_POST['bypass_geofence']);

        // Check if request is AJAX JSON
        $isJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        if (!$tenantId || !$assignmentId || $lat === null || $lng === null) {
            $msg = 'Coordenadas de geolocalização ou escala inválidas.';
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => $msg]);
                exit;
            }
            Helpers::setFlash('danger', $msg);
            Helpers::redirect('checkin?assignment_id=' . $assignmentId);
        }

        $result = Checkin::process($user['id'], $assignmentId, $tenantId, $lat, $lng, $bypass);

        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        if ($result['ok']) {
            Helpers::setFlash('success', $result['message']);
            Helpers::redirect('schedule');
        } else {
            Helpers::setFlash('danger', $result['message']);
            Helpers::redirect('checkin?assignment_id=' . $assignmentId);
        }
    }
}
