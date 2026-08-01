<?php $title = "Gestão de Membros — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Gestão de Membros & Equipe</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Voluntários ativos e solicitações de ingresso na igreja</p>
</div>

<!-- Active Members Card -->
<div class="card">
    <div class="card-title">
        <span>Voluntários Ativos (<?= count($members) ?>)</span>
    </div>

    <?php if (!empty($members)): ?>
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
            <?php foreach ($members as $m): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: var(--bg-dark); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div>
                        <strong style="color: #fff; font-size: 0.95rem; display: block;"><?= Helpers::e($m['full_name']) ?></strong>
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?= Helpers::e($m['email']) ?> <?= !empty($m['phone']) ? '• ' . Helpers::e($m['phone']) : '' ?></span>
                    </div>

                    <div>
                        <span class="badge badge-role"><?= Helpers::e($m['primary_role']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Sem membros registrados nesta organização.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
