<?php
// Ministry Ops PHP - Tenant Model

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Core/Helpers.php';

class Tenant {
    public static function findByCode(string $code): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenants WHERE LOWER(code) = LOWER(?)");
        $stmt->execute([trim($code)]);
        return $stmt->fetch() ?: null;
    }

    public static function requestJoin(string $userId, string $tenantCode, ?string $message = null): bool {
        $tenant = self::findByCode($tenantCode);
        if (!$tenant) return false;

        $db = Database::getConnection();
        
        // Check if user already has pending request or active membership
        $stmtCheck = $db->prepare("
            SELECT id FROM tenant_join_requests 
            WHERE user_id = ? AND tenant_code = ? AND status = 'pending'
        ");
        $stmtCheck->execute([$userId, strtoupper(trim($tenantCode))]);
        if ($stmtCheck->fetch()) return true;

        $requestId = Helpers::generateUuid();
        $stmt = $db->prepare("
            INSERT INTO tenant_join_requests (id, user_id, tenant_code, message, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$requestId, $userId, strtoupper(trim($tenantCode)), trim($message ?? '')]);
        return true;
    }

    public static function create(string $name, string $code, string $ownerUserId): string {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $tenantId = Helpers::generateUuid();
            $stmt = $db->prepare("INSERT INTO tenants (id, name, code, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$tenantId, trim($name), strtoupper(trim($code))]);

            // Create settings
            $stmtSettings = $db->prepare("INSERT INTO tenant_settings (id, tenant_id, default_locale, default_timezone) VALUES (?, ?, 'pt-BR', 'America/Sao_Paulo')");
            $stmtSettings->execute([Helpers::generateUuid(), $tenantId]);

            // Add owner membership
            $stmtMember = $db->prepare("INSERT INTO tenant_memberships (id, tenant_id, user_id, primary_role, status) VALUES (?, ?, ?, 'owner', 'active')");
            $stmtMember->execute([Helpers::generateUuid(), $tenantId, $ownerUserId]);

            $db->commit();
            return $tenantId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
