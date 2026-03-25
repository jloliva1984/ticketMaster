<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.ticket-table th, .ticket-table td { vertical-align: middle; padding: 6px 8px; }
.ticket-table .form-control, .ticket-table .form-select { font-size: .82rem; padding: 5px 8px; }
.ticket-table .btn-sm { padding: 4px 6px; }
.rate-input { width: 90px; }
#total-display { font-size: 1.4rem; font-weight: 700; color: #435ebe; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$isEdit   = isset($invoice) && $invoice !== null;
$formUrl  = $isEdit ? base_url('invoices/' . $invoice['id']) : base_url('invoices');
?>

<form id="invoice-form" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $isEdit ? $invoice['id'] : '' ?>">

<!-- ── Header fields ──────────────────────────────────── -->
<div class="card mb-3">
    <div class="card-header">Invoice Details</div>
    <div class="card-body">

        <div id="form-errors" class="alert alert-danger d-none mb-3 py-2"></div>

        <div class="row g-3">
            <!-- No. Factura -->
            <div class="col-md-3">
                <label class="form-label">No. Factura <span class="text-danger">*</span></label>
                <input type="text" name="no_factura" class="form-control"
                       value="<?= esc($invoice['no_factura'] ?? '') ?>"
                       placeholder="e.g. INV-0001" required>
            </div>

            <!-- Fecha -->
            <div class="col-md-2">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="text" name="fecha" class="form-control date-input"
                       value="<?= $isEdit ? date('m/d/Y', strtotime($invoice['fecha'])) : '' ?>"
                       placeholder="mm/dd/yyyy" required>
            </div>

            <!-- Cantera -->
            <div class="col-md-3">
                <label class="form-label">Cantera <span class="text-danger">*</span></label>
                <select name="cantera_id" class="form-select" required>
                    <option value="">— Select —</option>
                    <?php foreach ($quarries as $q): ?>
                        <option value="<?= $q['id'] ?>"
                            <?= ($isEdit && $invoice['cantera_id'] == $q['id']) ? 'selected' : '' ?>>
                            <?= esc($q['nombre_cantera']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Due Date -->
            <div class="col-md-2">
                <label class="form-label">Due Date</label>
                <input type="text" name="due_date" class="form-control date-input"
                       value="<?= ($isEdit && $invoice['due_date']) ? date('m/d/Y', strtotime($invoice['due_date'])) : '' ?>"
                       placeholder="mm/dd/yyyy">
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="pending"  <?= ($isEdit && $invoice['status'] === 'pending')  ? 'selected' : '' ?>>Pending</option>
                    <option value="received" <?= ($isEdit && $invoice['status'] === 'received') ? 'selected' : '' ?>>Received</option>
                    <option value="paid"     <?= ($isEdit && $invoice['status'] === 'paid')     ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>

            <!-- Fecha Recibida -->
            <div class="col-md-2">
                <label class="form-label">Fecha Recibida</label>
                <input type="text" name="fecha_recibida" class="form-control date-input"
                       value="<?= ($isEdit && $invoice['fecha_recibida']) ? date('m/d/Y', strtotime($invoice['fecha_recibida'])) : '' ?>"
                       placeholder="mm/dd/yyyy">
            </div>

            <!-- PDF Upload -->
            <div class="col-md-4">
                <label class="form-label">
                    Upload PDF
                    <?php if ($isEdit && ! empty($invoice['pdf_file'])): ?>
                        <a href="<?= base_url('invoices/' . $invoice['id'] . '/pdf') ?>" target="_blank"
                           class="ms-2 text-danger" title="View current PDF">
                            <i class="bi bi-file-pdf-fill"></i> View current
                        </a>
                    <?php endif; ?>
                </label>
                <input type="file" name="pdf_file" class="form-control" accept=".pdf">
                <div class="form-text">Max 10 MB. Leave blank to keep current PDF.</div>
            </div>

            <!-- Notes -->
            <div class="col-md-6">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control"
                       value="<?= esc($invoice['notes'] ?? '') ?>"
                       placeholder="Optional notes">
            </div>
        </div>
    </div>
</div>

<!-- ── Tickets table ──────────────────────────────────── -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-ticket-perforated me-2"></i>Tickets</span>
        <button type="button" class="btn btn-success btn-sm" id="btn-add-row">
            <i class="bi bi-plus-lg me-1"></i>Add Ticket
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table ticket-table mb-0">
                <thead>
                    <tr>
                        <th style="width:120px">No. Ticket</th>
                        <th style="width:120px">Fecha</th>
                        <th style="width:130px">Tipo Trabajo</th>
                        <th style="width:150px">Cantera</th>
                        <th>Dirección</th>
                        <th style="width:100px">Rate ($)</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="tickets-body">
                    <?php foreach ($tickets as $i => $t): ?>
                    <tr>
                        <td><input type="text" name="tickets[<?= $i ?>][no_ticket]"
                                   class="form-control" value="<?= esc($t['no_ticket']) ?>" required></td>
                        <td><input type="text" name="tickets[<?= $i ?>][fecha]"
                                   class="form-control date-input"
                                   value="<?= date('m/d/Y', strtotime($t['fecha'])) ?>"></td>
                        <td><input type="text" name="tickets[<?= $i ?>][tipo_trabajo]"
                                   class="form-control" value="<?= esc($t['tipo_trabajo'] ?? '') ?>"></td>
                        <td>
                            <select name="tickets[<?= $i ?>][cantera_id]" class="form-select">
                                <option value="">—</option>
                                <?php foreach ($quarries as $q): ?>
                                    <option value="<?= $q['id'] ?>"
                                        <?= ($t['cantera_id'] == $q['id']) ? 'selected' : '' ?>>
                                        <?= esc($q['nombre_cantera']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="tickets[<?= $i ?>][direccion]"
                                   class="form-control" value="<?= esc($t['direccion'] ?? '') ?>"></td>
                        <td><input type="number" name="tickets[<?= $i ?>][rate]"
                                   class="form-control rate-input" step="0.01" min="0"
                                   value="<?= number_format($t['rate'], 2) ?>"></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end align-items-center gap-3">
        <span class="text-muted">Total:</span>
        <span id="total-display">$0.00</span>
        <input type="hidden" name="monto_total" id="monto_total" value="0">
    </div>
</div>

<!-- ── Actions ────────────────────────────────────────── -->
<div class="d-flex gap-2 justify-content-end">
    <a href="<?= base_url('invoices') ?>" class="btn btn-secondary">
        <i class="bi bi-x-lg me-1"></i>Cancel
    </a>
    <button type="submit" class="btn btn-primary" id="btn-submit">
        <i class="bi bi-save me-1"></i>Save Invoice
    </button>
</div>

</form>

<!-- Row template (hidden) -->
<template id="ticket-row-template">
    <tr>
        <td><input type="text" name="tickets[__IDX__][no_ticket]" class="form-control" required></td>
        <td><input type="text" name="tickets[__IDX__][fecha]" class="form-control date-input" placeholder="mm/dd/yyyy"></td>
        <td><input type="text" name="tickets[__IDX__][tipo_trabajo]" class="form-control"></td>
        <td>
            <select name="tickets[__IDX__][cantera_id]" class="form-select">
                <option value="">—</option>
                <?php foreach ($quarries as $q): ?>
                    <option value="<?= $q['id'] ?>"><?= esc($q['nombre_cantera']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="text" name="tickets[__IDX__][direccion]" class="form-control"></td>
        <td><input type="number" name="tickets[__IDX__][rate]" class="form-control rate-input" step="0.01" min="0" value="0.00"></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE    = '<?= base_url() ?>/';
const IS_EDIT = <?= $isEdit ? 'true' : 'false' ?>;
const FORM_URL = IS_EDIT
    ? BASE + 'invoices/' + document.querySelector('input[name="id"]').value
    : BASE + 'invoices';

let rowIndex = <?= count($tickets) ?>;

// ── Ticket row management ────────────────────────────────
document.getElementById('btn-add-row').addEventListener('click', addRow);

function addRow() {
    const tpl  = document.getElementById('ticket-row-template').innerHTML;
    const html = tpl.replaceAll('__IDX__', rowIndex++);
    document.getElementById('tickets-body').insertAdjacentHTML('beforeend', html);
    recalcTotal();
}

document.getElementById('tickets-body').addEventListener('click', (e) => {
    if (e.target.closest('.btn-remove-row')) {
        e.target.closest('tr').remove();
        recalcTotal();
    }
});

document.getElementById('tickets-body').addEventListener('input', (e) => {
    if (e.target.classList.contains('rate-input')) recalcTotal();
});

function recalcTotal() {
    const rates = [...document.querySelectorAll('.rate-input')].map(i => parseFloat(i.value) || 0);
    const total  = rates.reduce((a, b) => a + b, 0);
    document.getElementById('total-display').textContent = '$' + total.toFixed(2);
    document.getElementById('monto_total').value = total.toFixed(2);
}

// Initial calc
recalcTotal();

// Add one blank row if creating and no tickets
if (!IS_EDIT && document.querySelectorAll('#tickets-body tr').length === 0) {
    addRow();
}

// ── Form submit ──────────────────────────────────────────
document.getElementById('invoice-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errEl = document.getElementById('form-errors');
    errEl.classList.add('d-none');

    const formData = new FormData(e.target);
    const btn      = document.getElementById('btn-submit');
    btn.disabled   = true;
    btn.innerHTML  = '<i class="bi bi-hourglass-split me-1"></i>Saving...';

    try {
        const res = await tmAjax(FORM_URL, { method: 'POST', body: formData });
        showToast(res.message);
        // Redirect to invoice show page
        const id = IS_EDIT
            ? document.querySelector('input[name="id"]').value
            : res.data?.id;
        if (id) {
            window.location.href = BASE + 'invoices/' + id + '/show';
        } else {
            window.location.href = BASE + 'invoices';
        }
    } catch (err) {
        if (err.errors) {
            const items = Object.values(err.errors).map(e => `<li>${e}</li>`).join('');
            errEl.innerHTML = `<ul class="mb-0 ps-3">${items}</ul>`;
            errEl.classList.remove('d-none');
            window.scrollTo(0, 0);
        }
        showToast(err.message, 'danger');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i>Save Invoice';
    }
});
</script>
<?= $this->endSection() ?>
