<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-receipt-cutoff me-2 text-primary"></i><?= lang('General.invoices') ?></span>
        <div class="d-flex gap-2">
            <a href="<?= base_url('invoices/export/excel') ?>" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="<?= base_url('invoices/create') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i><?= lang('General.new_invoice') ?>
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt-invoices" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= lang('General.invoice_no') ?></th>
                        <th><?= lang('General.date') ?></th>
                        <th><?= lang('General.quarry') ?></th>
                        <th><?= lang('General.due_date') ?></th>
                        <th>Received</th>
                        <th><?= lang('General.amount') ?></th>
                        <th><?= lang('General.status') ?></th>
                        <th>PDF</th>
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

$('#dt-invoices').DataTable({
    ajax: { url: BASE + 'invoices/data', dataSrc: 'data' },
    columns: [
        { data: 'id',             width: '50px' },
        { data: 'no_factura' },
        { data: 'fecha' },
        { data: 'nombre_cantera' },
        { data: 'due_date' },
        { data: 'fecha_recibida' },
        { data: 'monto_total' },
        { data: 'status',         orderable: false },
        { data: 'pdf',            orderable: false, width: '40px', className: 'text-center' },
        { data: 'actions',        orderable: false, width: '140px' },
    ],
    order: [[0, 'desc']],
});

window.deleteInvoice = (id, no) => {
    if (!confirm(`Delete invoice "${no}"?`)) return;
    tmAjax(BASE + 'invoices/' + id, { method: 'DELETE' })
        .then(res => { showToast(res.message); $('#dt-invoices').DataTable().ajax.reload(); })
        .catch(err => showToast(err.message, 'danger'));
};
</script>
<?= $this->endSection() ?>
