<?php
// Ministry Ops PHP - Dashboard Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/Assignment.php';
require_once __DIR__ . '/../Models/Bulletin.php';
require_once __DIR__ . '/../Models/Gamification.php';

class DashboardController {
    public function index(): void {
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();

        $nextAssignment = null;
        $bulletins = [];
        $gamification = ['total_points' => 0, 'current_streak' => 0, 'best_streak' => 0, 'badge_count' => 0];

        if ($tenantId) {
            $nextAssignment = Assignment::getNextUpcoming($user['id'], $tenantId);
            $bulletins = Bulletin::getForUser($tenantId, $user['id']);
            $gamification = Gamification::getUserStats($tenantId, $user['id']);
        }

        require __DIR__ . '/../../templates/dashboard.php';
    }
}
