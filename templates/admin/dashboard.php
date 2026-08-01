<?php $title = "Painel Administrativo — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Painel Operacional da Liderança</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Visão geral de presenças, escalas, membros e trocas</p>
</div>

<!-- Admin Key Metrics -->
<div class="stat-grid">
    <div class="stat-box">
        <div class="stat-value" style="color: var(--warning);"><?= $stats['pendingConfirmations'] ?></div>
        <div class="stat-label">Pendentes</div>
    </div>
    <div class="stat-box">
        <div class="stat-value" style="color: var(--success);"><?= $stats['confirmationRatePct'] ?>%</div>
        <div class="stat-label">Taxa Confirmação</div>
    </div>
    <div class="stat-box">
        <div class="stat-value" style="color: var(--primary);"><?= $stats['checkInsToday'] ?></div>
        <div class="stat-label">Check-ins Hoje</div>
    </div>
    <div class="stat-box">
        <div class="stat-value" style="color: #a855f7;"><?= $stats['swapsPending'] ?></div>
        <div class="stat-label">Trocas Pendentes</div>
    </div>
</div>

<!-- Quick Navigation Bar for Admins -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 5px;">
    <a href="<?= Helpers::url('admin/dashboard') ?>" class="btn btn-primary btn-sm">Visão Geral</a>
    <a href="<?= Helpers::url('admin/members') ?>" class="btn btn-secondary btn-sm">Membros (<?= $stats['activeMembers'] ?>)</a>
    <a href="<?= Helpers::url('admin/confirmations') ?>" class="btn btn-secondary btn-sm">Fila de Escalas</a>
    <a href="<?= Helpers::url('admin/operations') ?>" class="btn btn-secondary btn-sm">Operações & Eventos</a>
    <a href="<?= Helpers::url('admin/attendance') ?>" class="btn btn-secondary btn-sm">Auditoria Check-in</a>
</div>

<!-- Pending Swap Approvals Queue -->
<?php if (!empty($openSwaps)): ?>
    <div class="card" style="border-left: 4px solid #a855f7;">
        <div class="card-title">
            <span>🔄 Fila de Trocas de Escala Pendentes de Aprovação</span>
        </div>

        <?php foreach ($openSwaps as $sw): ?>
            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 0.85rem; margin-bottom: 0.85rem;">
                <div style="font-size: 0.9rem; color: #fff; margin-bottom: 0.3rem;">
                    <strong><?= Helpers::e($sw['requester_name']) ?></strong> solicitou troca na operação <strong><?= Helpers::e($sw['operation_name']) ?></strong>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                    Escala: <?= Helpers::formatDate($sw['starts_at'], 'd/m/Y H:i') ?> | Motivo: <em>"<?= Helpers::e($sw['reason']) ?>"</em>
                </div>

                <?php if ($sw['status'] === 'pending_approval'): ?>
                    <form action="<?= Helpers::url('admin/swap/approve') ?>" method="POST" style="margin-top:0.5rem;">
                        <?= Helpers::csrfField() ?>
                        <input type="hidden" name="swap_request_id" value="<?= Helpers::e($sw['id']) ?>">
                        <button type="submit" class="btn btn-success btn-sm">
                            ✓ Aprovar Troca e Reatribuir Voluntário
                        </button>
                    </form>
                <?php else: ?>
                    <span class="badge badge-pending">Aguardando voluntário cobrir</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Pending Join Requests Widget -->
<?php if (!empty($joinRequests)): ?>
    <div class="card" style="border-left: 4px solid var(--warning);">
        <div class="card-title">
            <span>📩 Solicitações de Ingresso (<?= count($joinRequests) ?>)</span>
        </div>

        <?php foreach ($joinRequests as $req): ?>
            <?php if ($req['status'] === 'pending'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <strong style="color: #fff; font-size: 0.9rem; display: block;"><?= Helpers::e($req['full_name']) ?></strong>
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?= Helpers::e($req['email']) ?> — <?= Helpers::e($req['phone']) ?></span>
                        <?php if (!empty($req['message'])): ?>
                            <div style="font-size: 0.75rem; color: #94a3b8; font-style: italic; margin-top: 2px;">"<?= Helpers::e($req['message']) ?>"</div>
                        <?php endif; ?>
                    </div>

                    <div class="btn-group" style="width: auto;">
                        <form action="<?= Helpers::url('admin/join-request/review') ?>" method="POST" style="margin:0;">
                            <?= Helpers::csrfField() ?>
                            <input type="hidden" name="request_id" value="<?= Helpers::e($req['id']) ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success btn-sm">Aprovar</button>
                        </form>
                        <form action="<?= Helpers::url('admin/join-request/review') ?>" method="POST" style="margin:0;">
                            <?= Helpers::csrfField() ?>
                            <input type="hidden" name="request_id" value="<?= Helpers::e($req['id']) ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger btn-sm">Recusar</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
