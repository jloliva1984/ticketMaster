<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
@media print {
    #sidebar, #header, #footer, .no-print { display: none !important; }
    #main { margin-left: 0 !important; }
}
.task-header { background: #435ebe; color: #fff; border-radius: 10px 10px 0 0; padding: 24px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$total    = array_sum(array_column($tickets, 'rate'));
$isOpen   = $task['status'] === 'open';
?>

<!-- Action bar -->
<div class="d-flex gap-2 mb-3 no-print flex-wrap">
    <?php if ($isOpen): ?>
    <button id="btn-deliver" class="btn btn-success btn-sm">
        <i class="bi bi-check-circle me-1"></i>Mark as Delivered
    </button>
    <?php endif; ?>
    <a href="<?= base_url('tasks/' . $task['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i>Print
    </button>
    <a href="<?= base_url('tasks') ?>" class="btn btn-secondary btn-sm ms-auto">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card">
    <!-- Task header -->
    <div class="task-header d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div style="font-size:.85rem;opacity:.8">Task #<?= $task['id'] ?></div>
            <h3 style="margin:4px 0;font-weight:700">
                <i class="bi bi-truck-front-fill me-2"></i><?= esc($task['no_camion']) ?>
            </h3>
            <div style="opacity:.85"><?= esc($task['nombre_chofer']) ?></div>
            <?php if ($task['fecha_inicio']): ?>
                <div style="font-size:.85rem;opacity:.7;margin-top:4px">
                    <?= date('m/d/Y', strtotime($task['fecha_inicio'])) ?>
                    <?= $task['fecha_fin'] ? ' – ' . date('m/d/Y', strtotime($task['fecha_fin'])) : '' ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-end">
            <?php if ($isOpen): ?>
                <span class="badge bg-warning text-dark" style="font-size:.9rem">Open</span>
            <?php else: ?>
                <span class="badge bg-success" style="font-size:.9rem">Delivered</span>
                <?php if ($task['delivered_at']): ?>
                    <div style="font-size:.8rem;opacity:.75;margin-top:4px">
                        <?= date('m/d/Y H:i', strtotime($task['delivered_at'])) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">

        <?php if ($task['notes']): ?>
        <div class="alert alert-light border mb-3 py-2">
            <i class="bi bi-chat-left-text me-2 text-muted"></i><?= esc($task['notes']) ?>
        </div>
        <?php endif; ?>

        <!-- Tickets -->
        <h6 class="fw-bold mb-3">
            <i class="bi bi-ticket-perforated me-2 text-primary"></i>
            Tickets (<?= count($tickets) ?>)
        </h6>

        <?php if (empty($tickets)): ?>
            <p class="text-muted text-center py-3">No tickets attached.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No. Ticket</th>
                        <th>Fecha</th>
                        <th>Tipo Trabajo</th>
                        <th>Cantera</th>
                        <th>Dirección</th>
                        <th class="text-end">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($t['no_ticket']) ?></td>
                        <td><?= date('m/d/Y', strtotime($t['fecha'])) ?></td>
                        <td><?= esc($t['tipo_trabajo'] ?? '—') ?></td>
                        <td><?= esc($t['nombre_cantera'] ?? '—') ?></td>
                        <td><?= esc($t['direccion'] ?? '—') ?></td>
                        <td class="text-end">$<?= number_format($t['rate'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <td colspan="5" class="text-end fw-bold">TOTAL</td>
                        <td class="text-end fw-bold">$<?= number_format($total, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="card-footer text-end text-muted" style="font-size:.78rem">
        Created <?= date('m/d/Y H:i', strtotime($task['created_at'])) ?> &bull;
        Ing: Jorge Luis Oliva Matos
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>/';

document.getElementById('btn-deliver')?.addEventListener('click', async () => {
    if (!confirm('Mark this task as delivered? This cannot be undone.')) return;
    try {
        const res = await tmAjax(BASE + 'tasks/<?= $task['id'] ?>/deliver', { method: 'POST' });
        showToast(res.message);
        setTimeout(() => location.reload(), 900);
    } catch (err) {
        showToast(err.message, 'danger');
    }
});
</script>
<?= $this->endSection() ?>
