<?php
// Ministry Ops PHP - Schedule Controller (Minha Escala)

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/Assignment.php';

class ScheduleController {
    public function index(): void {
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $tab = $_GET['tab'] ?? 'upcoming';

        $assignments = [];
        $declineReasons = [];

        if ($tenantId) {
            $assignments = Assignment::getForUser($user['id'], $tenantId, $tab);
            $declineReasons = Assignment::getDeclineReasons($tenantId);
        }

        require __DIR__ . '/../../templates/schedule/index.php';
    }

    public function handleConfirm(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $assignmentId = $_POST['assignment_id'] ?? '';
        $comment = $_POST['comment'] ?? '';

        if (!$tenantId || !$assignmentId) {
            Helpers::setFlash('danger', 'Requisição inválida.');
            Helpers::redirect('schedule');
        }

        if (Assignment::confirm($assignmentId, $user['id'], $tenantId, $comment)) {
            Helpers::setFlash('success', 'Escala confirmada com sucesso! Você ganhou pontos de presença.');
        } else {
            Helpers::setFlash('danger', 'Não foi possível confirmar esta escala.');
        }

        Helpers::redirect('schedule');
    }

    public function handleDecline(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $assignmentId = $_POST['assignment_id'] ?? '';
        $reasonId = $_POST['decline_reason_id'] ?? '';
        $comment = $_POST['comment'] ?? '';

        if (!$tenantId || !$assignmentId) {
            Helpers::setFlash('danger', 'Requisição inválida.');
            Helpers::redirect('schedule');
        }

        if (Assignment::decline($assignmentId, $user['id'], $tenantId, $reasonId, $comment)) {
            Helpers::setFlash('warning', 'Sua recusa foi registrada. Caso deseje, você pode solicitar troca de escala.');
        } else {
            Helpers::setFlash('danger', 'Não foi possível recusar esta escala.');
        }

        Helpers::redirect('schedule');
    }
}
