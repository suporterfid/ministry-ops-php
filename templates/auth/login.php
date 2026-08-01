<?php $title = "Login — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="max-width: 420px; margin: 3rem auto 0 auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div class="brand-icon" style="width: 50px; height: 50px; font-size: 1.5rem; margin: 0 auto 0.75rem auto;">M</div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #fff;">Acessar Ministry Ops</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Gestão de escalas e voluntários</p>
        </div>

        <form action="<?= Helpers::url('login') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="seu.email@igreja.org" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Senha</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem; width: 100%;">Entrar</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.85rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 1rem;">
            Ainda não possui conta? <a href="<?= Helpers::url('register') ?>" style="font-weight: 600;">Cadastre-se</a>
        </div>
    </div>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
        <strong>Acesso de Teste Demo:</strong><br>
        Admin: <code>admin@ministry-ops.test</code> / <code>password123</code><br>
        Voluntário: <code>volunteer@ministry-ops.test</code> / <code>password123</code>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
