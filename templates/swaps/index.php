<?php $title = "Troca de Escalas — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Trocas de Escala Disponíveis</h2>
    <p style="color: var(--text-muted); font-size: 0.85rem;">Ajude um irmão cobrindo sua escala e ganhe pontos extras</p>
</div>

<?php if (!empty($openSwaps)): ?>
    <?php foreach ($openSwaps as $sw): ?>
        <div class="card">
            <div class="card-title">
                <span><?= Helpers::e($sw['operation_name'] ?? 'Operação Voluntária') ?></span>
                <span class="badge badge-pending"><?= Helpers::translateStatus($sw['status']) ?></span>
            </div>

            <div style="font-size: 0.85rem; color: var(--text-muted); margin: 0.5rem 0 1rem 0; line-height: 1.5;">
                <div>🙋‍♂️ <strong>Voluntário Solicitante:</strong> <?= Helpers::e($sw['requester_name']) ?></div>
                <div>👤 <strong>Função:</strong> <?= Helpers::e($sw['role_name'] ?? 'Servidor') ?></div>
                <div>📅 <strong>Data da Escala:</strong> <?= Helpers::formatDate($sw['starts_at'], 'd/m/Y H:i') ?> até <?= Helpers::formatDate($sw['ends_at'], 'H:i') ?></div>
                <?php if (!empty($sw['reason'])): ?>
                    <div style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg-dark); border-radius: var(--radius-sm);">
                        💬 <em>"<?= Helpers::e($sw['reason']) ?>"</em>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($sw['my_candidate_id'])): ?>
                <div class="alert alert-info" style="margin:0;">
                    ✓ Você já se candidatou para cobrir esta escala. Aguardando aprovação do líder.
                </div>
            <?php else: ?>
                <form action="<?= Helpers::url('swaps/cover') ?>" method="POST">
                    <input type="hidden" name="swap_request_id" value="<?= Helpers::e($sw['id']) ?>">
                    <button type="submit" class="btn btn-primary">
                        🤝 Me oferecer para cobrir esta escala
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
        <p>Não há solicitações de troca de escala abertas no momento.</p>
        <a href="<?= Helpers::url('schedule') ?>" class="btn btn-secondary btn-sm" style="margin-top: 0.75rem;">Ver minhas escalas</a>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
