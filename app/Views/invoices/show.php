<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
@media print {
    #sidebar, #header, #footer, .no-print { display: none !important; }
    #main { margin-left: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
.invoice-header { background: #435ebe; color: #fff; border-radius: 10px 10px 0 0; padding: 24px; }
.invoice-header h2 { margin: 0; font-weight: 700; }
.label-cell { font-weight: 600; color: #607080; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$total    = array_sum(array_column($tickets, 'rate'));
$statusMap = [
    'pending'  => 'bg-warning text-dark',
    'received' => 'bg-info text-dark',
    'paid'     => 'bg-success',
];
?>

<!-- Action bar (no-print) -->
<div class="d-flex gap-2 mb-3 no-print">
    <a href="<?= base_url('invoices/' . $invoice['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <a href="<?= base_url('invoices/' . $invoice['id'] . '/export/pdf') ?>" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-file-pdf me-1"></i>Export PDF
    </a>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i>Print
    </button>
    <?php if ($invoice['pdf_file']): ?>
    <a href="<?= base_url('invoices/' . $invoice['id'] . '/pdf') ?>" target="_blank" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-file-pdf-fill me-1 text-danger"></i>View PDF
    </a>
    <?php endif; ?>
    <a href="<?= base_url('invoices') ?>" class="btn btn-secondary btn-sm ms-auto">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card">
    <!-- Header -->
    <div class="invoice-header d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div style="font-size:.85rem;opacity:.8">TicketMaster.LT</div>
            <h2>Invoice <span style="opacity:.8">#<?= esc($invoice['no_factura']) ?></span></h2>
            <span class="badge <?= $statusMap[$invoice['status']] ?? 'bg-secondary' ?> mt-1">
                <?= ucfirst($invoice['status']) ?>
            </span>
        </div>
        <div class="text-end">
            <div style="font-size:.85rem;opacity:.8">Issue Date</div>
            <div style="font-size:1.1rem;font-weight:700"><?= date('m/d/Y', strtotime($invoice['fecha'])) ?></div>
            <?php if ($invoice['due_date']): ?>
            <div style="font-size:.8rem;opacity:.75;margin-top:4px">
                Due: <?= date('m/d/Y', strtotime($invoice['due_date'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Meta info row -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="label-cell mb-1">Quarry / Cantera</div>
                <div class="fw-semibold"><?= esc($invoice['nombre_cantera']) ?></div>
            </div>
            <div class="col-sm-4">
                <div class="label-cell mb-1">Date Received</div>
                <div><?= $invoice['fecha_recibida'] ? date('m/d/Y', strtotime($invoice['fecha_recibida'])) : '<span class="text-muted">—</span>' ?></div>
            </div>
            <?php if ($invoice['notes']): ?>
            <div class="col-sm-4">
                <div class="label-cell mb-1">Notes</div>
                <div><?= esc($invoice['notes']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <hr>

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
        Created <?= date('m/d/Y H:i', strtotime($invoice['created_at'])) ?> &bull;
        Ing: Jorge Luis Oliva Matos
    </div>
</div>

<?= $this->endSection() ?>
