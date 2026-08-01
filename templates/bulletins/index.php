<?php $title = "Boletins & Comunicados — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Boletins & Comunicados</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Avisos oficiais da liderança</p>
    </div>

    <?php if (in_array(Auth::user()['current_role'] ?? '', ['owner', 'org_admin', 'unit_admin', 'ministry_leader', 'team_leader'])): ?>
        <button class="btn btn-primary btn-sm" onclick="openModal('modal-new-bulletin')">+ Novo Boletim</button>
    <?php endif; ?>
</div>

<?php if (!empty($bulletins)): ?>
    <?php foreach ($bulletins as $b): ?>
        <div class="card">
            <div class="card-title">
                <span><?= Helpers::e($b['title']) ?></span>
                <?php if (!empty($b['acknowledged_at'])): ?>
                    <span class="badge badge-confirmed">Lido ✓</span>
                <?php else: ?>
                    <span class="badge badge-pending">Novo</span>
                <?php endif; ?>
            </div>

            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                Publicado por <strong><?= Helpers::e($b['author_name'] ?? 'Liderança') ?></strong> em <?= Helpers::formatDate($b['created_at'], 'd/m/Y H:i') ?>
            </div>

            <div style="font-size: 0.9rem; color: #e2e8f0; line-height: 1.6; white-space: pre-line; margin-bottom: 1rem;">
                <?= Helpers::e($b['body']) ?>
            </div>

            <?php if (empty($b['acknowledged_at'])): ?>
                <form action="<?= Helpers::url('bulletins/acknowledge') ?>" method="POST">
                    <?= Helpers::csrfField() ?>
                    <input type="hidden" name="bulletin_id" value="<?= Helpers::e($b['id']) ?>">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        ✓ Marcar como Lido
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
        <p>Nenhum boletim publicado no momento.</p>
    </div>
<?php endif; ?>

<!-- New Bulletin Modal for Leaders -->
<div id="modal-new-bulletin" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:500px; margin:0;">
        <h3 style="color:#fff; font-size:1.1rem; margin-bottom:1rem;">Publicar Novo Boletim</h3>
        <form action="<?= Helpers::url('admin/bulletin/create') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label class="form-label">Título do Comunicado</label>
                <input type="text" name="title" class="form-control" placeholder="ex: Reunião Geral de Voluntários" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mensagem / Conteúdo</label>
                <textarea name="body" class="form-control" rows="5" placeholder="Escreva a orientação para os voluntários..." required></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Publicar para Todos</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-new-bulletin')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
