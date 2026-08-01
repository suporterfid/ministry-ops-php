<?php $title = "Início — Ministry Ops"; require __DIR__ . '/layout/header.php'; ?>

<!-- Volunteer Stat Banner -->
<div class="stat-grid">
    <div class="stat-box">
        <div class="stat-value"><?= number_format($gamification['total_points']) ?></div>
        <div class="stat-label">Pontos</div>
    </div>

    <div class="stat-box">
        <div class="stat-value" style="color:#10b981;">⚡ <?= $gamification['current_streak'] ?></div>
        <div class="stat-label">Sequência (Streak)</div>
    </div>

    <div class="stat-box">
        <div class="stat-value" style="color:#f59e0b;">🏆 <?= $gamification['badge_count'] ?></div>
        <div class="stat-label">Conquistas</div>
    </div>
</div>

<!-- Next Assignment Hero Card -->
<div class="card" style="border-left: 4px solid var(--primary);">
    <div class="card-title">
        <span>Próxima Escala</span>
        <?php if ($nextAssignment): ?>
            <span class="badge badge-<?= Helpers::e($nextAssignment['status']) ?>">
                <?= Helpers::translateStatus($nextAssignment['status']) ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($nextAssignment): ?>
        <h3 style="font-size: 1.15rem; color:#fff; margin-bottom: 0.3rem;">
            <?= Helpers::e($nextAssignment['operation_name'] ?? 'Operação Especial') ?>
        </h3>

        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;">
            <div><strong>Turno:</strong> <?= Helpers::e($nextAssignment['shift_name'] ?? 'Padrão') ?></div>
            <div><strong>Função:</strong> <?= Helpers::e($nextAssignment['role_name'] ?? 'Voluntário') ?> (<?= Helpers::e($nextAssignment['ministry_name'] ?? 'Geral') ?>)</div>
            <div><strong>Horário:</strong> <?= Helpers::formatDate($nextAssignment['starts_at']) ?> até <?= Helpers::formatDate($nextAssignment['ends_at'], 'H:i') ?></div>
            <?php if (!empty($nextAssignment['instructions'])): ?>
                <div style="margin-top: 0.4rem; padding: 0.5rem; background: var(--bg-dark); border-radius: var(--radius-sm); color: #cbd5e1;">
                    💡 <em><?= Helpers::e($nextAssignment['instructions']) ?></em>
                </div>
            <?php endif; ?>
        </div>

        <div class="btn-group" style="margin-top: 1rem;">
            <?php if ($nextAssignment['status'] === 'pending_confirmation'): ?>
                <form action="<?= Helpers::url('schedule/confirm') ?>" method="POST" style="flex:1;">
                    <?= Helpers::csrfField() ?>
                    <input type="hidden" name="assignment_id" value="<?= Helpers::e($nextAssignment['id']) ?>">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Confirmar Presença</button>
                </form>
                <a href="<?= Helpers::url('schedule') ?>" class="btn btn-secondary" style="flex:1;">Opções</a>
            <?php elseif ($nextAssignment['status'] === 'confirmed'): ?>
                <a href="<?= Helpers::url('checkin?assignment_id=' . $nextAssignment['id']) ?>" class="btn btn-primary" style="width:100%;">
                    📍 Fazer Check-in por GPS
                </a>
            <?php elseif ($nextAssignment['status'] === 'checked_in'): ?>
                <div class="alert alert-success" style="width:100%; margin:0;">
                    ✓ Check-in realizado com sucesso! Bom serviço voluntário.
                </div>
            <?php else: ?>
                <a href="<?= Helpers::url('schedule') ?>" class="btn btn-secondary" style="width:100%;">Ver detalhes da escala</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 1.5rem 0; color: var(--text-muted);">
            <p>Você não possui escalas agendadas nos próximos dias.</p>
            <a href="<?= Helpers::url('schedule') ?>" class="btn btn-secondary btn-sm" style="margin-top: 0.75rem;">Ver histórico de escalas</a>
        </div>
    <?php endif; ?>
</div>

<!-- Bulletins & Announcements Feed -->
<div class="card">
    <div class="card-title">
        <span>Boletins & Comunicados</span>
        <a href="<?= Helpers::url('bulletins') ?>" style="font-size: 0.8rem; font-weight: normal;">Ver todos &rarr;</a>
    </div>

    <?php if (!empty($bulletins)): ?>
        <?php foreach (array_slice($bulletins, 0, 2) as $b): ?>
            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem; margin-bottom: 0.85rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.3rem;">
                    <strong style="color: #fff; font-size: 0.95rem;"><?= Helpers::e($b['title']) ?></strong>
                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= Helpers::formatDate($b['created_at'], 'd/m/Y') ?></span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                    <?= nl2br(Helpers::e(mb_strimwidth($b['body'], 0, 140, '...'))) ?>
                </p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 1rem 0;">Sem avisos recentes da liderança.</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
