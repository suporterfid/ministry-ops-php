<?php
// Ministry Ops PHP - Admin Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../Models/Swap.php';
require_once __DIR__ . '/../Models/Bulletin.php';

class AdminController {
    public function dashboard(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();

        $stats = AdminModel::getDashboardStats($tenantId ?? '');
        $joinRequests = AdminModel::getJoinRequests($tenantId ?? '');
        $openSwaps = Swap::getOpenSwaps($tenantId ?? '', $user['id']);

        require __DIR__ . '/../../templates/admin/dashboard.php';
    }

    public function members(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();

        $members = AdminModel::getMembers($tenantId ?? '');
        $joinRequests = AdminModel::getJoinRequests($tenantId ?? '');

        require __DIR__ . '/../../templates/admin/members.php';
    }

    public function handleReviewJoinRequest(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $requestId = $_POST['request_id'] ?? '';
        $action = $_POST['action'] ?? '';

        if ($tenantId && $requestId && in_array($action, ['approve', 'reject'])) {
            if (AdminModel::reviewJoinRequest($requestId, $action, $user['id'], $tenantId)) {
                Helpers::setFlash('success', 'Solicitação de ingresso ' . ($action === 'approve' ? 'aprovada' : 'rejeitada') . ' com sucesso.');
            } else {
                Helpers::setFlash('danger', 'Não foi possível processar esta solicitação.');
            }
        }

        Helpers::redirect('admin/members');
    }

    public function confirmations(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();

        $queue = AdminModel::getConfirmationsQueue($tenantId ?? '');

        require __DIR__ . '/../../templates/admin/confirmations.php';
    }

    public function operations(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();

        $operations = AdminModel::getOperations($tenantId ?? '');
        $events = AdminModel::getEventInstances($tenantId ?? '');
        $shifts = AdminModel::getShifts($tenantId ?? '');

        require __DIR__ . '/../../templates/admin/operations.php';
    }

    public function handleCreateOperation(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if ($tenantId && !empty($name)) {
            AdminModel::createOperation($tenantId, $name, $description);
            Helpers::setFlash('success', 'Operação cadastrada com sucesso!');
        } else {
            Helpers::setFlash('danger', 'Preencha o nome da operação.');
        }

        Helpers::redirect('admin/operations');
    }

    public function handleCreateEvent(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $operationId = $_POST['operation_id'] ?? '';
        $startsAt = $_POST['starts_at'] ?? '';
        $endsAt = $_POST['ends_at'] ?? '';
        $locationName = $_POST['location_name'] ?? '';

        if ($tenantId && !empty($operationId) && !empty($startsAt) && !empty($endsAt)) {
            $startTime = strtotime($startsAt);
            $endTime = strtotime($endsAt);
            if (!$startTime || !$endTime || $endTime <= $startTime) {
                Helpers::setFlash('danger', 'A data/horário de término deve ser posterior à data/horário de início.');
                Helpers::redirect('admin/operations');
            }

            AdminModel::createEventInstance($tenantId, $operationId, $startsAt, $endsAt, $locationName);
            Helpers::setFlash('success', 'Evento/Instância cadastrado com sucesso!');
        } else {
            Helpers::setFlash('danger', 'Preencha todos os campos obrigatórios do evento.');
        }

        Helpers::redirect('admin/operations');
    }

    public function handleCreateShift(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $eventInstanceId = $_POST['event_instance_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $startsAt = $_POST['starts_at'] ?? '';
        $endsAt = $_POST['ends_at'] ?? '';

        if ($tenantId && !empty($eventInstanceId) && !empty($name) && !empty($startsAt) && !empty($endsAt)) {
            $startTime = strtotime($startsAt);
            $endTime = strtotime($endsAt);
            if (!$startTime || !$endTime || $endTime <= $startTime) {
                Helpers::setFlash('danger', 'A data/horário de término do turno deve ser posterior ao início.');
                Helpers::redirect('admin/operations');
            }

            AdminModel::createShift($tenantId, $eventInstanceId, $name, $startsAt, $endsAt);
            Helpers::setFlash('success', 'Turno cadastrado com sucesso!');
        } else {
            Helpers::setFlash('danger', 'Preencha todos os campos do turno.');
        }

        Helpers::redirect('admin/operations');
    }

    public function handleCreateAssignment(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();

        $volunteerUserId = $_POST['volunteer_user_id'] ?? '';
        $operationId = $_POST['operation_id'] ?? '';
        $eventInstanceId = $_POST['event_instance_id'] ?? '';
        $shiftId = $_POST['shift_id'] ?? '';
        $roleId = $_POST['required_role_id'] ?? '';
        $startsAt = $_POST['starts_at'] ?? '';
        $endsAt = $_POST['ends_at'] ?? '';
        $instructions = $_POST['instructions'] ?? '';

        if ($tenantId && !empty($volunteerUserId) && !empty($startsAt) && !empty($endsAt)) {
            if (!AdminModel::isTenantMember($tenantId, $volunteerUserId)) {
                Helpers::setFlash('danger', 'O voluntário selecionado não possui vínculo ativo com esta organização.');
                Helpers::redirect('admin/confirmations');
            }

            AdminModel::createAssignment($tenantId, [
                'volunteer_user_id' => $volunteerUserId,
                'leader_user_id' => $user['id'],
                'operation_id' => $operationId ?: null,
                'event_instance_id' => $eventInstanceId ?: null,
                'shift_id' => $shiftId ?: null,
                'required_role_id' => $roleId ?: null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'instructions' => $instructions
            ]);
            Helpers::setFlash('success', 'Voluntário escalado com sucesso!');
        } else {
            Helpers::setFlash('danger', 'Preencha o voluntário e as datas de início/fim.');
        }

        Helpers::redirect('admin/confirmations');
    }

    public function attendance(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();

        $logs = AdminModel::getAttendanceLogs($tenantId ?? '');

        require __DIR__ . '/../../templates/admin/attendance.php';
    }

    public function handleApproveSwap(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $swapRequestId = $_POST['swap_request_id'] ?? '';

        if ($tenantId && $swapRequestId) {
            if (Swap::approveSwap($swapRequestId, $user['id'], $tenantId)) {
                Helpers::setFlash('success', 'Troca de escala aprovada e voluntário reatribuído com sucesso!');
            } else {
                Helpers::setFlash('danger', 'Não foi possível aprovar a troca.');
            }
        }

        Helpers::redirect('admin/dashboard');
    }

    public function handleCreateBulletin(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $title = $_POST['title'] ?? '';
        $body = $_POST['body'] ?? '';

        if ($tenantId && !empty($title) && !empty($body)) {
            Bulletin::create($tenantId, $user['id'], $title, $body);
            Helpers::setFlash('success', 'Boletim publicado com sucesso para todos os voluntários.');
        } else {
            Helpers::setFlash('danger', 'Preencha o título e o conteúdo do boletim.');
        }

        Helpers::redirect('bulletins');
    }

    public function handleDeleteOperation(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $operationId = $_POST['operation_id'] ?? '';

        if ($tenantId && $operationId) {
            AdminModel::deleteOperation($tenantId, $operationId);
            Helpers::setFlash('success', 'Operação removida com sucesso.');
        }

        Helpers::redirect('admin/operations');
    }

    public function handleDeleteEvent(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $eventId = $_POST['event_id'] ?? '';

        if ($tenantId && $eventId) {
            AdminModel::deleteEventInstance($tenantId, $eventId);
            Helpers::setFlash('success', 'Evento removido com sucesso.');
        }

        Helpers::redirect('admin/operations');
    }

    public function handleDeleteShift(): void {
        $user = Auth::requireAdmin();
        $tenantId = Auth::currentTenantId();
        $shiftId = $_POST['shift_id'] ?? '';

        if ($tenantId && $shiftId) {
            AdminModel::deleteShift($tenantId, $shiftId);
            Helpers::setFlash('success', 'Turno removido com sucesso.');
        }

        Helpers::redirect('admin/operations');
    }
}
