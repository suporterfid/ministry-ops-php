<?php
// Ministry Ops PHP - Gamification Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/Gamification.php';

class GamificationController {
    public function index(): void {
        $user = Auth::requireAuth();
        $tenantId = Auth::currentTenantId();

        $stats = ['total_points' => 0, 'current_streak' => 0, 'best_streak' => 0, 'badge_count' => 0];
        $leaderboard = [];
        $badges = [];

        if ($tenantId) {
            $stats = Gamification::getUserStats($tenantId, $user['id']);
            $leaderboard = Gamification::getLeaderboard($tenantId, 15);
            $badges = Gamification::getUserBadges($tenantId, $user['id']);
        }

        require __DIR__ . '/../../templates/gamification/index.php';
    }
}
