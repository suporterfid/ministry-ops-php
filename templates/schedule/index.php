<?php $title = "Minha Escala — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Minha Escala</h2>
    
    <div style="display: flex; gap: 0.5rem; background: var(--bg-card); padding: 3px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
        <a href="<?= Helpers::url('schedule?tab=upcoming') ?>" class="btn btn-sm <?= ($tab ?? 'upcoming') === 'upcoming' ? 'btn-primary' : 'btn-secondary' ?>">Próximas</a>
        <a href="<?= Helpers::url('schedule?tab=past') ?>" class="btn btn-sm <?= ($tab ?? '') === 'past' ? 'btn-primary' : 'btn-secondary' ?>">Anteriores</a>
    </div>
</div>

<?php if (!empty($assignments)): ?>
    <?php foreach ($assignments as $as): ?>
        <div class="card">
            <div class="card-title">
                <span><?= Helpers::e($as['operation_name'] ?? 'Operação Voluntária') ?></span>
                <span class="badge badge-<?= Helpers::e($as['status']) ?>"><?= Helpers::translateStatus($as['status']) ?></span>
            </div>

            <div style="font-size: 0.85rem; color: var(--text-muted); margin: 0.5rem 0 1rem 0; line-height: 1.5;">
                <div>📍 <strong>Local:</strong> <?= Helpers::e($as['service_area_name'] ?? 'Auditório Principal') ?></div>
                <div>👤 <strong>Função:</strong> <?= Helpers::e($as['role_name'] ?? 'Servidor') ?> (<?= Helpers::e($as['ministry_name'] ?? 'Geral') ?>)</div>
                <div>📅 <strong>Data:</strong> <?= Helpers::formatDate($as['starts_at'], 'd/m/Y (l) H:i') ?> até <?= Helpers::formatDate($as['ends_at'], 'H:i') ?></div>
                <?php if (!empty($as['leader_name'])): ?>
                    <div>👨‍💼 <strong>Líder Responsável:</strong> <?= Helpers::e($as['leader_name']) ?></div>
                <?php endif; ?>
                <?php if (!empty($as['instructions'])): ?>
                    <div style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: var(--bg-dark); border-radius: var(--radius-sm); border-left: 3px solid var(--primary);">
                        <strong>Instruções:</strong> <?= Helpers::e($as['instructions']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="btn-group">
                <?php if ($as['status'] === 'pending_confirmation'): ?>
                    <form action="<?= Helpers::url('schedule/confirm') ?>" method="POST" style="flex:1;">
                        <?= Helpers::csrfField() ?>
                        <input type="hidden" name="assignment_id" value="<?= Helpers::e($as['id']) ?>">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Confirmar Presença</button>
                    </form>

                    <button type="button" class="btn btn-danger btn-sm" onclick="openModal('modal-decline-<?= $as['id'] ?>')">Recusar</button>
                <?php elseif ($as['status'] === 'confirmed'): ?>
                    <a href="<?= Helpers::url('checkin?assignment_id=' . $as['id']) ?>" class="btn btn-primary" style="flex:1;">
                        📍 Realizar Check-in
                    </a>
                <?php endif; ?>

                <?php if (in_array($as['status'], ['pending_confirmation', 'confirmed'])): ?>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modal-swap-<?= $as['id'] ?>')">Solicitar Troca</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Decline Modal -->
        <div id="modal-decline-<?= $as['id'] ?>" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center; padding:1rem;">
            <div class="card" style="width:100%; max-width:400px; margin:0;">
                <h3 style="color:#fff; font-size:1.1rem; margin-bottom:1rem;">Recusar Escala</h3>
                <form action="<?= Helpers::url('schedule/decline') ?>" method="POST">
                    <?= Helpers::csrfField() ?>
                    <input type="hidden" name="assignment_id" value="<?= Helpers::e($as['id']) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Motivo da Recusa</label>
                        <select name="decline_reason_id" class="form-control" required>
                            <option value="">Selecione o motivo...</option>
                            <?php foreach ($declineReasons as $dr): ?>
                                <option value="<?= Helpers::e($dr['id']) ?>"><?= Helpers::e($dr['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observação (Opcional)</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Descreva brevemente..."></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-danger">Confirmar Recusa</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-decline-<?= $as['id'] ?>')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Swap Modal -->
        <div id="modal-swap-<?= $as['id'] ?>" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center; padding:1rem;">
            <div class="card" style="width:100%; max-width:400px; margin:0;">
                <h3 style="color:#fff; font-size:1.1rem; margin-bottom:1rem;">Solicitar Troca de Escala</h3>
                <form action="<?= Helpers::url('swaps/create') ?>" method="POST">
                    <?= Helpers::csrfField() ?>
                    <input type="hidden" name="assignment_id" value="<?= Helpers::e($as['id']) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Motivo da Troca</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Tenho um compromisso inadiável nesta data..." required></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Publicar Troca</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-swap-<?= $as['id'] ?>')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
        <p>Nenhuma escala encontrada nesta categoria.</p>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
