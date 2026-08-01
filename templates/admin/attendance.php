<?php $title = "Auditoria de Presença (Check-ins) — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Auditoria de Presença & Check-ins</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Histórico detalhado de marcações de presença via GPS e exceções manuais</p>
</div>

<div class="card">
    <div class="card-title">
        <span>Log de Check-ins Registrados</span>
    </div>

    <?php if (!empty($logs)): ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($logs as $log): ?>
                <div style="padding: 0.85rem 1rem; background: var(--bg-dark); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem;">
                        <strong style="color: #fff; font-size: 0.95rem;"><?= Helpers::e($log['volunteer_name'] ?? 'Voluntário') ?></strong>
                        <?php if ($log['was_auto_validated']): ?>
                            <span class="badge badge-confirmed">GPS Validado ✓</span>
                        <?php else: ?>
                            <span class="badge badge-pending">Exceção Manual ⚠️</span>
                        <?php endif; ?>
                    </div>

                    <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                        <div>Operação: <strong><?= Helpers::e($log['operation_name'] ?? 'Geral') ?></strong></div>
                        <div>Horário do Check-in: <?= Helpers::formatDate($log['checked_in_at'], 'd/m/Y H:i:s') ?></div>
                        <div>Coordenadas GPS: <code><?= $log['latitude'] ?>, <?= $log['longitude'] ?></code> (Fonte: <?= Helpers::e($log['source']) ?>)</div>
                        <?php if (!empty($log['exception_reason'])): ?>
                            <div style="color: var(--warning); margin-top: 0.2rem;">Motivo da exceção: <?= Helpers::e($log['exception_reason']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Sem registros de check-in efetuados.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
