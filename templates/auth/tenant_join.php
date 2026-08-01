<?php $title = "Solicitar Ingresso — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="max-width: 450px; margin: 2rem auto 0 auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Participar de uma Organização</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Insira o código fornecido pelo líder do seu ministério</p>
        </div>

        <form action="<?= Helpers::url('tenant/join') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="tenant_code">Código da Igreja / Tenant</label>
                <input type="text" name="tenant_code" id="tenant_code" class="form-control" placeholder="MATRIZ" style="text-transform: uppercase;" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="message">Mensagem ao Líder (Opcional)</label>
                <textarea name="message" id="message" class="form-control" rows="3" placeholder="Olá! Sou voluntário no ministério de recepção..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Solicitação</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
