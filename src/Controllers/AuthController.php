<?php
// Ministry Ops PHP - Auth Controller

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Core/Helpers.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Tenant.php';

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            Helpers::redirect('dashboard');
        }
        require __DIR__ . '/../../templates/auth/login.php';
    }

    public function handleLogin(): void {
        Auth::requireCsrf();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Helpers::setFlash('danger', 'Preencha o e-mail e a senha.');
            Helpers::redirect('login');
        }

        if (Auth::attempt($email, $password)) {
            $user = Auth::user();
            if (empty($user['memberships'])) {
                Helpers::setFlash('info', 'Login efetuado com sucesso! Solicite ingresso em uma igreja/organização.');
                Helpers::redirect('tenant/join');
            }
            Helpers::setFlash('success', 'Bem-vindo de volta, ' . $user['full_name'] . '!');
            Helpers::redirect('dashboard');
        } else {
            Helpers::setFlash('danger', 'E-mail ou senha incorretos.');
            Helpers::redirect('login');
        }
    }

    public function showRegister(): void {
        if (Auth::check()) {
            Helpers::redirect('dashboard');
        }
        require __DIR__ . '/../../templates/auth/register.php';
    }

    public function handleRegister(): void {
        Auth::requireCsrf();
        $fullName = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $tenantCode = $_POST['tenant_code'] ?? '';

        if (empty($fullName) || empty($email) || empty($password)) {
            Helpers::setFlash('danger', 'Preencha todos os campos obrigatórios.');
            Helpers::redirect('register');
        }

        if (User::findByEmail($email)) {
            Helpers::setFlash('danger', 'Este e-mail já está cadastrado.');
            Helpers::redirect('register');
        }

        try {
            $userId = User::create($email, $password, $fullName, $phone);

            if (!empty($tenantCode)) {
                Tenant::requestJoin($userId, $tenantCode, 'Solicitação inicial no cadastro');
            }

            Auth::attempt($email, $password);
            Helpers::setFlash('success', 'Conta criada com sucesso! Solicitação de ingresso enviada.');
            Helpers::redirect('dashboard');
        } catch (Exception $e) {
            Helpers::setFlash('danger', 'Erro ao criar conta: ' . $e->getMessage());
            Helpers::redirect('register');
        }
    }

    public function showJoinTenant(): void {
        $user = Auth::requireAuth();
        require __DIR__ . '/../../templates/auth/tenant_join.php';
    }

    public function handleJoinTenant(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $code = $_POST['tenant_code'] ?? '';
        $message = $_POST['message'] ?? '';

        if (empty($code)) {
            Helpers::setFlash('danger', 'Digite o código da organização (ex: MATRIZ).');
            Helpers::redirect('tenant/join');
        }

        $tenant = Tenant::findByCode($code);
        if (!$tenant) {
            Helpers::setFlash('danger', 'Organização com o código "' . Helpers::e($code) . '" não foi encontrada.');
            Helpers::redirect('tenant/join');
        }

        Tenant::requestJoin($user['id'], $code, $message);
        Helpers::setFlash('success', 'Solicitação enviada com sucesso! Aguarde a aprovação do líder.');
        Helpers::redirect('dashboard');
    }

    public function handleSelectTenant(): void {
        Auth::requireCsrf();
        $user = Auth::requireAuth();
        $tenantId = $_POST['tenant_id'] ?? '';
        if (!empty($tenantId)) {
            Auth::setTenant($tenantId);
            Helpers::setFlash('success', 'Organização alterada com sucesso.');
        }
        Helpers::redirect('dashboard');
    }

    public function handleLogout(): void {
        Auth::requireCsrf();
        Auth::logout();
        Helpers::setFlash('info', 'Você saiu da sua conta.');
        Helpers::redirect('login');
    }
}
