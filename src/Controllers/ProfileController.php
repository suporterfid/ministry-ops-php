<?php
// Ministry Ops PHP - Profile Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/User.php';

class ProfileController {
    public function index(): void {
        $user = Auth::requireAuth();
        require __DIR__ . '/../../templates/profile.php';
    }

    public function handleUpdate(): void {
        $user = Auth::requireAuth();
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';

        if (empty($fullName)) {
            Helpers::setFlash('danger', 'O nome completo é obrigatório.');
            Helpers::redirect('profile');
        }

        User::updateProfile($user['id'], $fullName, $phone);
        Helpers::setFlash('success', 'Perfil atualizado com sucesso.');
        Helpers::redirect('profile');
    }
}
