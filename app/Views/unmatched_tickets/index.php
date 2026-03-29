<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <p class="text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            <?= lang('General.unmatched_hint') ?>
        </p>
        <button class="btn btn-sm btn-outline-secondary" onclick="table.ajax.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i><?= lang('General.refresh') ?>
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="unmatchedTable" class="table table-hover mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th><?= lang('General.ticket_no') ?></th>
                        <th><?= lang('General.date') ?></th>
                        <th><?= lang('General.work_type') ?></th>
                        <th><?= lang('General.quarry') ?></th>
                        <th><?= lang('General.address') ?></th>
                        <th><?= lang('General.rate') ?></th>
                        <th><?= lang('General.truck') ?></th>
                        <th><?= lang('General.driver') ?></th>
                        <th><?= lang('General.status') ?></th>
                        <th><?= lang('General.actions') ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ── Match Modal ─────────────────────────────────────────── -->
<div class="modal fade" id="matchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-link-45deg me-1"></i><?= lang('General.match_ticket') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Task ticket info -->
                <div class="alert alert-warning d-flex gap-3 align-items-start mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                    <div>
                        <strong><?= lang('General.task_ticket') ?>:</strong>
                        <span id="mmTicketNo" class="fw-bold ms-1"></span>
                        &mdash;
                        <span id="mmQuarry"></span>
                        &mdash;
                        <span id="mmDate" class="text-muted"></span>
                    </div>
                </div>

                <p class="text-muted small"><?= lang('General.match_hint') ?></p>

                <!-- Invoice ticket selector -->
                <div id="mmLoading" class="text-center py-3 d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2"><?= lang('General.loading') ?>…</span>
                </div>

                <div id="mmCandidates">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="candidatesTable">
                            <thead class="table-light">
                                <tr>
                                    <th><?= lang('General.ticket_no') ?></th>
                                    <th><?= lang('General.date') ?></th>
                                    <th><?= lang('General.work_type') ?></th>
                                    <th><?= lang('General.invoice_no') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="candidateRows">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <?= lang('General.no_data') ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?= lang('General.cancel') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const MATCH_DATA_URL = '<?= site_url('unmatched-tickets/data') ?>';
const CANDIDATES_URL = '<?= site_url('unmatched-tickets/candidates') ?>';
const MATCH_URL      = '<?= site_url('unmatched-tickets/match') ?>';

let table;
let currentTaskTicketId = null;

document.addEventListener('DOMContentLoaded', function () {
    table = $('#unmatchedTable').DataTable({
        ajax:        { url: MATCH_DATA_URL, dataSrc: 'data' },
        processing:  true,
        order:       [[1, 'desc']],
        columns: [
            { data: 'no_ticket' },
            { data: 'fecha' },
            { data: 'tipo_trabajo' },
            { data: 'nombre_cantera' },
            { data: 'direccion' },
            { data: 'rate' },
            { data: 'no_camion' },
            { data: 'nombre_chofer' },
            { data: 'task_status', orderable: false },
            { data: 'actions',    orderable: false },
        ],
        language: {
            emptyTable: '<?= lang('General.no_unmatched') ?>',
        },
    });
});

function openMatch(taskTicketId, canterId) {
    currentTaskTicketId = taskTicketId;

    // Reset modal
    document.getElementById('mmTicketNo').textContent = '…';
    document.getElementById('mmQuarry').textContent   = '…';
    document.getElementById('mmDate').textContent     = '';
    document.getElementById('candidateRows').innerHTML =
        '<tr><td colspan="5" class="text-center text-muted"><?= lang('General.loading') ?>…</td></tr>';

    const modal = new bootstrap.Modal(document.getElementById('matchModal'));
    modal.show();

    // Load task ticket info from current table row
    const rowData = table.rows().data().toArray().find(r => r.id == taskTicketId);
    if (rowData) {
        document.getElementById('mmTicketNo').textContent = rowData.no_ticket;
        document.getElementById('mmQuarry').textContent   = rowData.nombre_cantera;
        document.getElementById('mmDate').textContent     = rowData.fecha;
    }

    // Fetch candidate invoice tickets
    tmAjax(CANDIDATES_URL + '?cantera_id=' + canterId, { method: 'GET' })
        .then(res => {
            const tbody = document.getElementById('candidateRows');
            if (!res.data || res.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3"><?= lang('General.no_candidates') ?></td></tr>';
                return;
            }
            tbody.innerHTML = res.data.map(r =>
                `<tr>
                    <td><strong>${escHtml(r.no_ticket)}</strong></td>
                    <td>${r.fecha ? fmtDate(r.fecha) : '—'}</td>
                    <td>${escHtml(r.tipo_trabajo ?? '—')}</td>
                    <td>${escHtml(r.no_factura ?? '—')}</td>
                    <td>
                        <button class="btn btn-sm btn-success"
                            onclick="doMatch(${r.id})">
                            <i class="bi bi-check-lg me-1"></i><?= lang('General.match') ?>
                        </button>
                    </td>
                </tr>`
            ).join('');
        })
        .catch(err => {
            document.getElementById('candidateRows').innerHTML =
                '<tr><td colspan="5" class="text-center text-danger">' + (err.message || '<?= lang('General.error_generic') ?>') + '</td></tr>';
        });
}

function doMatch(invoiceTicketId) {
    if (!currentTaskTicketId) return;

    const body = new FormData();
    body.append('invoice_ticket_id', invoiceTicketId);

    tmAjax(MATCH_URL + '/' + currentTaskTicketId, { method: 'POST', body })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('matchModal')).hide();
            showToast('<?= lang('General.matched') ?>', 'success');
            table.ajax.reload();
        })
        .catch(err => {
            showToast(err.message || '<?= lang('General.error_generic') ?>', 'danger');
        });
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function fmtDate(d) {
    if (!d) return '—';
    const p = d.split('-');
    return p.length === 3 ? p[1]+'/'+p[2]+'/'+p[0] : d;
}
</script>
<?= $this->endSection() ?>
