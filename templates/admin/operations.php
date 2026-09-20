<?php $title = "Operações & Eventos — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff;">Operações, Eventos & Turnos</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Cadastro de estrutura de serviços e programas de voluntariado</p>
    </div>

    <button class="btn btn-primary btn-sm" onclick="openModal('modal-new-operation')">+ Nova Operação</button>
</div>

<!-- Operations List -->
<div class="card">
    <div class="card-title">
        <span>Operações Cadastradas (<?= count($operations) ?>)</span>
    </div>

    <?php if (!empty($operations)): ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($operations as $op): ?>
                <div style="padding: 0.85rem 1rem; background: var(--bg-dark); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="color: #fff; font-size: 1rem;"><?= Helpers::e($op['name']) ?></strong>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="badge badge-confirmed"><?= Helpers::e($op['status']) ?></span>
                            <form action="<?= Helpers::url('admin/operation/delete') ?>" method="POST" onsubmit="return confirm('Deseja realmente excluir esta operação?');" style="margin:0;">
                                <?= Helpers::csrfField() ?>
                                <input type="hidden" name="operation_id" value="<?= Helpers::e($op['id']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">Excluir</button>
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($op['description'])): ?>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.3rem;"><?= Helpers::e($op['description']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Nenhuma operação cadastrada.</p>
    <?php endif; ?>
</div>

<!-- Event Instances List -->
<div class="card">
    <div class="card-title">
        <span>Instâncias de Eventos (Cultos / Programas)</span>
        <button class="btn btn-secondary btn-sm" onclick="openModal('modal-new-event')">+ Novo Evento</button>
    </div>

    <?php if (!empty($events)): ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($events as $ev): ?>
                <?php $eventShifts = array_filter($shifts, fn($s) => ($s['event_instance_id'] ?? '') === $ev['id']); ?>
                <div style="padding: 0.85rem 1rem; background: var(--bg-dark); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: #fff; font-size: 0.95rem;"><?= Helpers::e($ev['operation_name']) ?></strong>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                                📅 Data: <?= Helpers::formatDate($ev['starts_at'], 'd/m/Y H:i') ?> - <?= Helpers::formatDate($ev['ends_at'], 'H:i') ?>
                                <?= !empty($ev['location_name']) ? ' | 📍 ' . Helpers::e($ev['location_name']) : '' ?>
                            </div>
                        </div>
                        <form action="<?= Helpers::url('admin/event/delete') ?>" method="POST" onsubmit="return confirm('Deseja realmente excluir este evento?');" style="margin:0;">
                            <?= Helpers::csrfField() ?>
                            <input type="hidden" name="event_id" value="<?= Helpers::e($ev['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">Excluir</button>
                        </form>
                    </div>

                    <!-- Shifts for this event -->
                    <div style="margin-top: 0.6rem;">
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem;">Turnos do evento (<?= count($eventShifts) ?>)</div>
                        <?php if (!empty($eventShifts)): ?>
                            <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                                <?php foreach ($eventShifts as $sh): ?>
                                    <div style="padding: 0.5rem 0.65rem; background: var(--bg); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                                            <div style="font-size: 0.82rem;">
                                                <strong style="color: #fff;"><?= Helpers::e($sh['name']) ?></strong>
                                                <div style="color: var(--text-muted); font-size: 0.75rem;">🕒 <?= Helpers::formatDate($sh['starts_at'], 'd/m/Y H:i') ?> - <?= Helpers::formatDate($sh['ends_at'], 'H:i') ?></div>
                                            </div>
                                            <form action="<?= Helpers::url('admin/shift/delete') ?>" method="POST" onsubmit="return confirm('Deseja excluir este turno e suas escalas?');" style="margin:0;">
                                                <?= Helpers::csrfField() ?>
                                                <input type="hidden" name="shift_id" value="<?= Helpers::e($sh['id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">Excluir</button>
                                            </form>
                                        </div>

                                        <!-- Assignment form for this shift -->
                                        <?php if (!empty($roles) && !empty($members)): ?>
                                            <details style="margin-top: 0.4rem;">
                                                <summary style="cursor: pointer; color: var(--accent); font-size: 0.8rem;">+ Escalar voluntário</summary>
                                                <form action="<?= Helpers::url('admin/assignment/create') ?>" method="POST" style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                                    <?= Helpers::csrfField() ?>
                                                    <input type="hidden" name="operation_id" value="<?= Helpers::e($ev['operation_id']) ?>">
                                                    <input type="hidden" name="event_instance_id" value="<?= Helpers::e($ev['id']) ?>">
                                                    <input type="hidden" name="shift_id" value="<?= Helpers::e($sh['id']) ?>">
                                                    <input type="hidden" name="starts_at" value="<?= Helpers::e($sh['starts_at']) ?>">
                                                    <input type="hidden" name="ends_at" value="<?= Helpers::e($sh['ends_at']) ?>">

                                                    <div class="form-group" style="margin:0;">
                                                        <label class="form-label">Voluntário</label>
                                                        <select name="volunteer_user_id" class="form-control" required>
                                                            <option value="">Selecione...</option>
                                                            <?php foreach ($members as $member): ?>
                                                                <option value="<?= Helpers::e($member['user_id']) ?>"><?= Helpers::e($member['full_name'] ?? $member['email']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group" style="margin:0;">
                                                        <label class="form-label">Função</label>
                                                        <select name="required_role_id" class="form-control">
                                                            <option value="">Selecione...</option>
                                                            <?php foreach ($roles as $role): ?>
                                                                <option value="<?= Helpers::e($role['id']) ?>"><?= Helpers::e($role['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="btn-group" style="margin:0;">
                                                        <button type="submit" class="btn btn-primary btn-sm">Escalar</button>
                                                    </div>
                                                </form>
                                            </details>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.8rem;">Sem turnos cadastrados para este evento.</p>
                        <?php endif; ?>

                        <!-- Create shift form -->
                        <details style="margin-top: 0.5rem;">
                            <summary style="cursor: pointer; color: var(--accent); font-size: 0.8rem;">+ Criar turno</summary>
                            <form action="<?= Helpers::url('admin/shift/create') ?>" method="POST" style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                <?= Helpers::csrfField() ?>
                                <input type="hidden" name="event_instance_id" value="<?= Helpers::e($ev['id']) ?>">

                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Nome do Turno</label>
                                    <input type="text" name="name" class="form-control" placeholder="ex: Turno da Manhã" required>
                                </div>

                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Início</label>
                                    <input type="datetime-local" name="starts_at" class="form-control" required>
                                </div>

                                <div class="form-group" style="margin:0;">
                                    <label class="form-label">Fim</label>
                                    <input type="datetime-local" name="ends_at" class="form-control" required>
                                </div>

                                <div class="btn-group" style="margin:0;">
                                    <button type="submit" class="btn btn-primary btn-sm">Salvar Turno</button>
                                </div>
                            </form>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">Nenhum evento cadastrado.</p>
    <?php endif; ?>
</div>

<!-- Modal Create Operation -->
<div id="modal-new-operation" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:450px; margin:0;">
        <h3 style="color:#fff; font-size:1.1rem; margin-bottom:1rem;">Cadastrar Nova Operação</h3>
        <form action="<?= Helpers::url('admin/operation/create') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label class="form-label">Nome da Operação</label>
                <input type="text" name="name" class="form-control" placeholder="ex: Culto de Domingo Manhã" required>
            </div>

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Descrição das atividades e equipes envolvidas..."></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Salvar Operação</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-new-operation')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Create Event -->
<div id="modal-new-event" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:450px; margin:0;">
        <h3 style="color:#fff; font-size:1.1rem; margin-bottom:1rem;">Cadastrar Instância de Evento</h3>
        <form action="<?= Helpers::url('admin/event/create') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <div class="form-group">
                <label class="form-label">Operação</label>
                <select name="operation_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($operations as $op): ?>
                        <option value="<?= Helpers::e($op['id']) ?>"><?= Helpers::e($op['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Início</label>
                <input type="datetime-local" name="starts_at" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Fim</label>
                <input type="datetime-local" name="ends_at" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nome do Local (Opcional)</label>
                <input type="text" name="location_name" class="form-control" placeholder="ex: Auditório Principal">
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Salvar Evento</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-new-event')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
