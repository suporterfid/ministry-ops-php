<?php
// Ministry Ops PHP - Auth & Session Manager

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Helpers.php';

class Auth {
    public static function check(): bool {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.id, u.email, p.full_name, p.phone, p.locale_override
            FROM users u
            LEFT JOIN profiles p ON u.id = p.id
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $user['memberships'] = self::getUserMemberships($user['id']);
            $user['current_tenant_id'] = $_SESSION['tenant_id'] ?? ($user['memberships'][0]['tenant_id'] ?? null);
            $user['current_role'] = self::getCurrentRole($user);
        }

        return $user ?: null;
    }

    public static function getUserMemberships(string $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT tm.*, t.name as tenant_name, t.code as tenant_code
            FROM tenant_memberships tm
            JOIN tenants t ON tm.tenant_id = t.id
            WHERE tm.user_id = ? AND tm.status = 'active'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private static function getCurrentRole(array $user): string {
        $tenantId = $user['current_tenant_id'];
        if (!$tenantId || empty($user['memberships'])) return 'guest';

        foreach ($user['memberships'] as $m) {
            if ($m['tenant_id'] === $tenantId) {
                return $m['primary_role'];
            }
        }
        return 'volunteer';
    }

    public static function attempt(string $email, string $password): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            
            // Set initial tenant context
            $memberships = self::getUserMemberships($user['id']);
            if (!empty($memberships)) {
                $_SESSION['tenant_id'] = $memberships[0]['tenant_id'];
            } else {
                unset($_SESSION['tenant_id']);
            }
            
            return true;
        }

        return false;
    }

    public static function setTenant(string $tenantId): void {
        if (!self::check()) return;
        $user = self::user();
        foreach ($user['memberships'] as $m) {
            if ($m['tenant_id'] === $tenantId) {
                $_SESSION['tenant_id'] = $tenantId;
                return;
            }
        }
    }

    public static function logout(): void {
        unset($_SESSION['user_id']);
        unset($_SESSION['tenant_id']);
        session_destroy();
    }

    public static function requireAuth(): array {
        if (!self::check()) {
            Helpers::setFlash('warning', 'Faça login para acessar esta página.');
            Helpers::redirect('login');
        }
        $user = self::user();
        if (!$user) {
            self::logout();
            Helpers::redirect('login');
        }
        return $user;
    }

    public static function requireAdmin(): array {
        $user = self::requireAuth();
        $role = $user['current_role'] ?? '';
        if (!in_array($role, ['owner', 'org_admin', 'unit_admin', 'ministry_leader', 'team_leader'])) {
            Helpers::setFlash('danger', 'Acesso restrito a administradores e líderes.');
            Helpers::redirect('dashboard');
        }
        return $user;
    }

    public static function currentTenantId(): ?string {
        return $_SESSION['tenant_id'] ?? null;
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool {
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
