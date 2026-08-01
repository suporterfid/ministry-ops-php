<?php
// Ministry Ops PHP - Core Helpers

class Helpers {
    public static function url(string $path = ''): string {
        $path = ltrim($path, '/');
        return BASE_URL . ($path !== '' ? '/' . $path : '');
    }

    public static function redirect(string $path): void {
        header("Location: " . self::url($path));
        exit;
    }

    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type' => $type, // 'success', 'danger', 'warning', 'info'
            'message' => $message
        ];
    }

    public static function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function generateUuid(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Calculates distance between two GPS coordinates in meters using the Haversine formula
     */
    public static function haversineDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = 6371000.0; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public static function formatDate(?string $datetime, string $format = 'd/m/Y H:i'): string {
        if (!$datetime) return '—';
        $timestamp = strtotime($datetime);
        return date($format, $timestamp);
    }

    public static function translateStatus(string $status): string {
        $map = [
            'pending_confirmation' => 'Pendente',
            'confirmed' => 'Confirmado',
            'declined' => 'Recusado',
            'swap_requested' => 'Troca Solicitada',
            'swap_open' => 'Troca Aberta',
            'checked_in' => 'Presente (Check-in)',
            'completed' => 'Concluído',
            'absent' => 'Ausente',
            'cancelled' => 'Cancelado',
            'open' => 'Aberto',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'published' => 'Publicado',
            'draft' => 'Rascunho'
        ];
        return $map[$status] ?? ucfirst($status);
    }
}
