<?php
// Ministry Ops PHP - Bulletins Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/Bulletin.php';

class BulletinsController {
    public function index(): void {
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();

        $bulletins = [];
        if ($tenantId) {
            $bulletins = Bulletin::getForUser($tenantId, $user['id']);
        }

        require __DIR__ . '/../../templates/bulletins/index.php';
    }

    public function handleAcknowledge(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();
        $bulletinId = $_POST['bulletin_id'] ?? '';

        if ($tenantId && $bulletinId) {
            Bulletin::acknowledge($bulletinId, $user['id'], $tenantId);
            Helpers::setFlash('success', 'Leitura confirmada com sucesso!');
        }

        Helpers::redirect('bulletins');
    }
}
