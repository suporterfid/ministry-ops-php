<?php $title = "Meu Perfil — Ministry Ops"; require __DIR__ . '/layout/header.php'; ?>

<div style="max-width: 500px; margin: 1rem auto 0 auto;">
    <div class="card" style="padding: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="brand-icon" style="width: 54px; height: 54px; font-size: 1.5rem; border-radius: 50%;">
                <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <h3 style="color: #fff; font-size: 1.2rem; font-weight: 700;"><?= Helpers::e($user['full_name']) ?></h3>
                <span class="badge badge-role"><?= Helpers::e($user['current_role'] ?? 'Voluntário') ?></span>
            </div>
        </div>

        <form action="<?= Helpers::url('profile/update') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" class="form-control" value="<?= Helpers::e($user['email']) ?>" disabled style="opacity: 0.6;">
            </div>

            <div class="form-group">
                <label class="form-label">Nome Completo</label>
                <input type="text" name="full_name" class="form-control" value="<?= Helpers::e($user['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Telefone / WhatsApp</label>
                <input type="text" name="phone" class="form-control" value="<?= Helpers::e($user['phone']) ?>" placeholder="(11) 99999-9999">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Salvar Alterações</button>
        </form>

        <div style="margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="<?= Helpers::url('tenant/join') ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                + Participar de outra Organização / Igreja
            </a>

            <form action="<?= Helpers::url('logout') ?>" method="POST">
                <?= Helpers::csrfField() ?>
                <button type="submit" class="btn btn-danger btn-sm" style="width: 100%;">
                    Sair da Conta (Logout)
                </button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
