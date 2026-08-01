<?php
// Ministry Ops PHP - Swaps Controller (Trocas de Escala)

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/Swap.php';
require_once __DIR__ . '/../Models/Assignment.php';

class SwapsController {
    public function index(): void {
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();

        $openSwaps = [];
        $myAssignments = [];

        if ($tenantId) {
            $openSwaps = Swap::getOpenSwaps($tenantId, $user['id']);
            $myAssignments = Assignment::getForUser($user['id'], $tenantId, 'upcoming');
        }

        require __DIR__ . '/../../templates/swaps/index.php';
    }

    public function handleCreateRequest(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $assignmentId = $_POST['assignment_id'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (!$tenantId || !$assignmentId) {
            Helpers::setFlash('danger', 'Selecione uma escala válida para solicitar troca.');
            Helpers::redirect('swaps');
        }

        if (Swap::createRequest($assignmentId, $user['id'], $tenantId, $reason)) {
            Helpers::setFlash('success', 'Solicitação de troca publicada! Outros voluntários da sua igreja poderão se candidatar.');
        } else {
            Helpers::setFlash('danger', 'Não foi possível solicitar a troca para esta escala.');
        }

        Helpers::redirect('swaps');
    }

    public function handleCoverOffer(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $swapRequestId = $_POST['swap_request_id'] ?? '';

        if (!$tenantId || !$swapRequestId) {
            Helpers::setFlash('danger', 'Requisição de troca inválida.');
            Helpers::redirect('swaps');
        }

        if (Swap::offerToCover($swapRequestId, $user['id'], $tenantId)) {
            Helpers::setFlash('success', 'Oferta enviada! O líder aprovará a substituição em breve.');
        } else {
            Helpers::setFlash('danger', 'Não foi possível se candidatar a esta troca.');
        }

        Helpers::redirect('swaps');
    }
}
