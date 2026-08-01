<?php $title = "Ranking & Pontuação — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Gamificação & Engajamento</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Reconhecimento pela fidelidade e pontualidade na igreja</p>
</div>

<!-- My Stats Summary -->
<div class="stat-grid">
    <div class="stat-box">
        <div class="stat-value"><?= number_format($stats['total_points']) ?></div>
        <div class="stat-label">Meus Pontos</div>
    </div>
    <div class="stat-box">
        <div class="stat-value" style="color:#10b981;">⚡ <?= $stats['current_streak'] ?></div>
        <div class="stat-label">Sequência Atual</div>
    </div>
    <div class="stat-box">
        <div class="stat-value" style="color:#f59e0b;">🔥 <?= $stats['best_streak'] ?></div>
        <div class="stat-label">Melhor Sequência</div>
    </div>
</div>

<!-- Volunteer Leaderboard -->
<div class="card">
    <div class="card-title">
        <span>🏆 Ranking de Voluntários</span>
    </div>
    <p class="card-subtitle">Voluntários com maior pontuação por presença e engajamento</p>

    <?php if (!empty($leaderboard)): ?>
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
            <?php foreach ($leaderboard as $idx => $row): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: var(--bg-dark); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                        <div style="font-weight: 800; font-size: 1.1rem; width: 28px; text-align: center; color: <?= $idx === 0 ? '#f59e0b' : ($idx === 1 ? '#94a3b8' : ($idx === 2 ? '#b45309' : 'var(--text-muted)')) ?>;">
                            #<?= $idx + 1 ?>
                        </div>
                        <div>
                            <strong style="color: #fff; font-size: 0.95rem; display: block;">
                                <?= Helpers::e($row['full_name']) ?>
                                <?php if ($row['user_id'] === Auth::user()['id']): ?>
                                    <span class="badge badge-role" style="margin-left: 5px;">Você</span>
                                <?php endif; ?>
                            </strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">⚡ Sequência: <?= $row['streak'] ?> cultos</span>
                        </div>
                    </div>

                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary);">
                        <?= number_format($row['total_points']) ?> pts
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Ainda não há pontuações registradas nesta organização.</p>
    <?php endif; ?>
</div>

<!-- My Badges -->
<div class="card">
    <div class="card-title">
        <span>🎖️ Minhas Conquistas</span>
    </div>

    <?php if (!empty($badges)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem;">
            <?php foreach ($badges as $bg): ?>
                <div style="background: var(--bg-dark); padding: 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="font-weight: 700; color: #fff; margin-bottom: 0.2rem;"><?= Helpers::e($bg['label']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= Helpers::e($bg['description']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 1rem 0;">
            Conclua suas escalas e faça check-in no horário para desbloquear conquistas!
        </p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
