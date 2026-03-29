<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-truck-fill me-2 text-primary"></i><?= lang('General.tasks') ?></span>
        <a href="<?= base_url('tasks/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i><?= lang('General.new_task') ?>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt-tasks" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Camión</th>
                        <th>Chofer</th>
                        <th>Período / Fechas</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Delivered</th>
                        <th>Created</th>
                        <th><?= lang('General.actions') ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE = '<?= base_url() ?>/';

const table = $('#dt-tasks').DataTable({
    ajax: { url: BASE + 'tasks/data', dataSrc: 'data' },
    columns: [
        { data: 'id',            width: '50px' },
        { data: 'no_camion' },
        { data: 'nombre_chofer' },
        { data: 'periodo' },
        { data: 'monto_total' },
        { data: 'status',      orderable: false },
        { data: 'delivered',   orderable: false },
        { data: 'created_at' },
        { data: 'actions',     orderable: false, width: '110px' },
    ],
    order: [[0, 'desc']],
});

window.markDelivered = (id) => {
    if (!confirm('Mark this task as delivered? This cannot be undone.')) return;
    tmAjax(BASE + 'tasks/' + id + '/deliver', { method: 'POST' })
        .then(res => { showToast(res.message); table.ajax.reload(); })
        .catch(err => showToast(err.message, 'danger'));
};

window.deleteTask = (id) => {
    if (!confirm('Delete this task and all its tickets?')) return;
    tmAjax(BASE + 'tasks/' + id, { method: 'DELETE' })
        .then(res => { showToast(res.message); table.ajax.reload(); })
        .catch(err => showToast(err.message, 'danger'));
};
</script>
<?= $this->endSection() ?>
