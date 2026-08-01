<?php $title = "Cadastro — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="max-width: 450px; margin: 2rem auto 0 auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #fff;">Criar Conta de Voluntário</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Cadastre-se para acompanhar suas escalas</p>
        </div>

        <form action="<?= Helpers::url('register') ?>" method="POST">
            <div class="form-group">
                <label class="form-label" for="full_name">Nome Completo</label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="João da Silva" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="seu.email@igreja.org" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Telefone / WhatsApp (Opcional)</label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="(11) 99999-9999">
            </div>

            <div class="form-group">
                <label class="form-label" for="tenant_code">Código da Igreja (ex: MATRIZ)</label>
                <input type="text" name="tenant_code" id="tenant_code" class="form-control" placeholder="MATRIZ" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Senha</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem; width: 100%;">Finalizar Cadastro</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.85rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 1rem;">
            Já tem uma conta? <a href="<?= Helpers::url('login') ?>" style="font-weight: 600;">Faça Login</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
