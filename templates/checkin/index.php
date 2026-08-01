<?php $title = "Check-in por GPS — Ministry Ops"; require __DIR__ . '/../layout/header.php'; ?>

<div style="max-width: 480px; margin: 1rem auto 0 auto;">
    <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📍</div>
        <h2 style="font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 0.3rem;">Check-in de Presença</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
            Confirme sua presença no local do evento via GPS em tempo real.
        </p>

        <?php if ($assignment): ?>
            <div style="background: var(--bg-dark); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); text-align: left; font-size: 0.85rem; margin-bottom: 1.5rem; color: var(--text-muted);">
                <strong style="color: #fff; display: block; margin-bottom: 0.25rem;"><?= Helpers::e($assignment['operation_name'] ?? 'Operação Voluntária') ?></strong>
                <div>Turno: <?= Helpers::e($assignment['shift_name'] ?? 'Turno Padrão') ?></div>
                <div>Função: <?= Helpers::e($assignment['role_name'] ?? 'Servidor') ?></div>
                <div>Horário: <?= Helpers::formatDate($assignment['starts_at'], 'H:i') ?> - <?= Helpers::formatDate($assignment['ends_at'], 'H:i') ?></div>
            </div>

            <div id="geo-status" style="margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 500;">
                Clique no botão abaixo para capturar sua posição GPS.
            </div>

            <button type="button" id="btn-get-location" class="btn btn-primary" style="width: 100%; padding: 0.9rem;">
                📍 Validar Posição GPS e Fazer Check-in
            </button>

            <!-- Hidden Checkin Form -->
            <form id="checkin-form" action="<?= Helpers::url('checkin') ?>" method="POST" style="display:none;">
                <?= Helpers::csrfField() ?>
                <input type="hidden" name="assignment_id" value="<?= Helpers::e($assignment['id']) ?>">
                <input type="hidden" name="latitude" id="input-latitude">
                <input type="hidden" name="longitude" id="input-longitude">
            </form>

            <div style="margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modal-manual-override')">
                    Liberar sem validação de distância (Exceção)
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Nenhuma escala selecionada ou você não possui escalas para check-in no momento.
            </div>
            <a href="<?= Helpers::url('schedule') ?>" class="btn btn-secondary" style="width: 100%;">Voltar para Minhas Escalas</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($assignment): ?>
<!-- Manual Override Modal -->
<div id="modal-manual-override" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:2000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:400px; margin:0;">
        <h3 style="color:#fff; font-size:1.1rem; margin-bottom:0.5rem;">Check-in Manual (Exceção)</h3>
        <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:1rem;">
            Use esta opção se estiver no local mas seu GPS estiver impreciso. Esta ação gera um log de auditoria para os líderes.
        </p>

        <form action="<?= Helpers::url('checkin') ?>" method="POST">
            <?= Helpers::csrfField() ?>
            <input type="hidden" name="assignment_id" value="<?= Helpers::e($assignment['id']) ?>">
            <input type="hidden" name="latitude" value="-23.5505200">
            <input type="hidden" name="longitude" value="-46.6333080">
            <input type="hidden" name="bypass_geofence" value="1">

            <div class="btn-group">
                <button type="submit" class="btn btn-warning">Confirmar Exceção Manual</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-manual-override')">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
