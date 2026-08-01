<?php
// Ministry Ops PHP - Gamification & Ranking Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';

class Gamification {
    public static function awardPoints(string $tenantId, string $userId, string $ruleCode, ?string $assignmentId = null, ?string $note = null): bool {
        $db = Database::getConnection();

        // 1. Fetch score rule
        $stmtRule = $db->prepare("SELECT points FROM score_rules WHERE tenant_id = ? AND code = ?");
        $stmtRule->execute([$tenantId, $ruleCode]);
        $rule = $stmtRule->fetch();
        $points = $rule ? (int)$rule['points'] : 10;

        // 2. Record score event
        $stmtEvent = $db->prepare("
            INSERT INTO score_events (id, tenant_id, user_id, assignment_id, rule_code, points, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $metadata = json_encode(['note' => $note ?? 'Score event']);
        $stmtEvent->execute([Helpers::generateUuid(), $tenantId, $userId, $assignmentId, $ruleCode, $points, $metadata]);

        // 3. Update streak if applicable
        if ($ruleCode === 'CHECKIN_ON_TIME') {
            self::updateStreak($tenantId, $userId, 'on_time_checkin');
        }

        return true;
    }

    public static function updateStreak(string $tenantId, string $userId, string $streakType): void {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT current_value, best_value FROM streaks WHERE tenant_id = ? AND user_id = ? AND streak_type = ?");
        $stmt->execute([$tenantId, $userId, $streakType]);
        $streak = $stmt->fetch();

        if ($streak) {
            $newCurrent = (int)$streak['current_value'] + 1;
            $newBest = max($newCurrent, (int)$streak['best_value']);
            $stmtUpd = $db->prepare("UPDATE streaks SET current_value = ?, best_value = ?, updated_at = NOW() WHERE tenant_id = ? AND user_id = ? AND streak_type = ?");
            $stmtUpd->execute([$newCurrent, $newBest, $tenantId, $userId, $streakType]);
        } else {
            $stmtIns = $db->prepare("INSERT INTO streaks (id, tenant_id, user_id, streak_type, current_value, best_value, updated_at) VALUES (?, ?, ?, ?, 1, 1, NOW())");
            $stmtIns->execute([Helpers::generateUuid(), $tenantId, $userId, $streakType]);
        }
    }

    public static function getUserStats(string $tenantId, string $userId): array {
        $db = Database::getConnection();

        // Total score
        $stmtScore = $db->prepare("SELECT COALESCE(SUM(points), 0) as total_points FROM score_events WHERE tenant_id = ? AND user_id = ?");
        $stmtScore->execute([$tenantId, $userId]);
        $totalPoints = (int)($stmtScore->fetch()['total_points'] ?? 0);

        // Streak
        $stmtStreak = $db->prepare("SELECT current_value, best_value FROM streaks WHERE tenant_id = ? AND user_id = ? AND streak_type = 'on_time_checkin'");
        $stmtStreak->execute([$tenantId, $userId]);
        $streak = $stmtStreak->fetch() ?: ['current_value' => 0, 'best_value' => 0];

        // Badges count
        $stmtBadges = $db->prepare("SELECT COUNT(*) as badge_count FROM badge_awards WHERE tenant_id = ? AND user_id = ?");
        $stmtBadges->execute([$tenantId, $userId]);
        $badgeCount = (int)($stmtBadges->fetch()['badge_count'] ?? 0);

        return [
            'total_points' => $totalPoints,
            'current_streak' => (int)$streak['current_value'],
            'best_streak' => (int)$streak['best_value'],
            'badge_count' => $badgeCount
        ];
    }

    public static function getLeaderboard(string $tenantId, int $limit = 10): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 
                p.full_name,
                se.user_id,
                SUM(se.points) as total_points,
                COALESCE(st.current_value, 0) as streak
            FROM score_events se
            JOIN profiles p ON se.user_id = p.id
            LEFT JOIN streaks st ON se.user_id = st.user_id AND st.tenant_id = se.tenant_id AND st.streak_type = 'on_time_checkin'
            WHERE se.tenant_id = ?
            GROUP BY se.user_id, p.full_name, st.current_value
            ORDER BY total_points DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $tenantId, PDO::PARAM_STR);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getUserBadges(string $tenantId, string $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT b.*, ba.awarded_at
            FROM badge_awards ba
            JOIN badges b ON ba.badge_id = b.id
            WHERE ba.tenant_id = ? AND ba.user_id = ?
            ORDER BY ba.awarded_at DESC
        ");
        $stmt->execute([$tenantId, $userId]);
        return $stmt->fetchAll();
    }
}
