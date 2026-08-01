<?php $title = "Fila de Escalas — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Fila de Confirmações & Escalas</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Status em tempo real de todas as atribuições da equipe</p>
</div>

<div class="card">
    <div class="card-title">
        <span>Fila Operacional</span>
    </div>

    <?php if (!empty($queue)): ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($queue as $item): ?>
                <div style="padding: 0.85rem 1rem; background: var(--bg-dark); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <strong style="color: #fff; font-size: 0.95rem;"><?= Helpers::e($item['volunteer_name'] ?? 'Voluntário') ?></strong>
                        <span class="badge badge-<?= Helpers::e($item['status']) ?>"><?= Helpers::translateStatus($item['status']) ?></span>
                    </div>

                    <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                        <div>Operação: <strong><?= Helpers::e($item['operation_name'] ?? 'Geral') ?></strong> | Turno: <?= Helpers::e($item['shift_name'] ?? 'Padrão') ?></div>
                        <div>Função: <?= Helpers::e($item['role_name'] ?? 'Servidor') ?> | Data: <?= Helpers::formatDate($item['starts_at'], 'd/m/Y H:i') ?></div>
                        <?php if (!empty($item['decline_reason_label'])): ?>
                            <div style="color: var(--danger); margin-top: 0.2rem;">Motivo da recusa: <?= Helpers::e($item['decline_reason_label']) ?> <?= !empty($item['confirmation_comment']) ? '(' . Helpers::e($item['confirmation_comment']) . ')' : '' ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Sem atribuições de escala registradas.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
