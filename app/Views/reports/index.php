<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= lang('General.reports') ?></h5>
            </div>
            <div class="card-body">
                <form method="get" action="<?= site_url('reports') ?>" id="reportForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><?= lang('General.date') ?> (<?= lang('General.from') ?>)</label>
                            <input type="text" class="form-control datepicker" name="from"
                                   id="inputFrom" value="<?= esc($from) ?>"
                                   placeholder="mm/dd/yyyy" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><?= lang('General.date') ?> (<?= lang('General.to') ?>)</label>
                            <input type="text" class="form-control datepicker" name="to"
                                   id="inputTo" value="<?= esc($to) ?>"
                                   placeholder="mm/dd/yyyy" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold"><?= lang('General.group_by') ?></label>
                            <select class="form-select" name="group_by" id="inputGroupBy">
                                <option value="truck"  <?= $groupBy === 'truck'  ? 'selected' : '' ?>><?= lang('General.truck') ?></option>
                                <option value="driver" <?= $groupBy === 'driver' ? 'selected' : '' ?>><?= lang('General.driver') ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i><?= lang('General.search') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($results !== null): ?>

<!-- Export buttons -->
<div class="row mb-3">
    <div class="col-12 d-flex gap-2 justify-content-end">
        <a href="<?= site_url('reports/export/excel') ?>?<?= http_build_query(['from' => $from, 'to' => $to, 'group_by' => $groupBy]) ?>"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i><?= lang('General.export') ?> Excel
        </a>
        <a href="<?= site_url('reports/export/pdf') ?>?<?= http_build_query(['from' => $from, 'to' => $to, 'group_by' => $groupBy]) ?>"
           class="btn btn-danger btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i><?= lang('General.export') ?> PDF
        </a>
    </div>
</div>

<!-- Tasks Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clipboard2-check me-1 text-primary"></i>
                    <?= lang('General.tasks') ?> — <?= esc($results['group_label']) ?>
                </h6>
                <span class="badge bg-primary"><?= $results['task_count'] ?> <?= lang('General.tasks') ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <?php if ($groupBy === 'driver'): ?>
                                    <th><?= lang('General.driver') ?></th>
                                    <th><?= lang('General.trucks') ?></th>
                                <?php else: ?>
                                    <th><?= lang('General.truck') ?></th>
                                    <th><?= lang('General.driver') ?></th>
                                <?php endif; ?>
                                <th class="text-center"><?= lang('General.tasks') ?></th>
                                <th class="text-center"><?= lang('General.tickets') ?></th>
                                <th class="text-center"><?= lang('General.status_delivered') ?></th>
                                <th class="text-end"><?= lang('General.amount') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results['tasks'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <?= lang('General.no_data') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $n = 1; foreach ($results['tasks'] as $row): ?>
                                <tr>
                                    <td class="text-muted"><?= $n++ ?></td>
                                    <?php if ($groupBy === 'driver'): ?>
                                        <td><strong><?= esc($row['nombre_chofer']) ?></strong></td>
                                        <td><span class="text-muted"><?= esc($row['camiones'] ?? '—') ?></span></td>
                                    <?php else: ?>
                                        <td><strong><?= esc($row['no_camion']) ?></strong></td>
                                        <td><?= esc($row['nombre_chofer']) ?></td>
                                    <?php endif; ?>
                                    <td class="text-center"><?= $row['total_tasks'] ?></td>
                                    <td class="text-center"><?= $row['total_tickets'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= $row['delivered_tasks'] ?></span>
                                    </td>
                                    <td class="text-end fw-semibold">$<?= number_format((float)$row['total_amount'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end"><?= lang('General.total') ?>:</td>
                                <td class="text-center"><?= $results['task_count'] ?></td>
                                <td class="text-center"><?= $results['task_tickets'] ?></td>
                                <td></td>
                                <td class="text-end text-primary">$<?= number_format((float)$results['task_total'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="bi bi-receipt me-1 text-success"></i>
                    <?= lang('General.invoices') ?> — <?= lang('General.by_quarry') ?>
                </h6>
                <span class="badge bg-success"><?= $results['invoice_count'] ?> <?= lang('General.invoices') ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th><?= lang('General.quarry') ?></th>
                                <th class="text-center"><?= lang('General.invoices') ?></th>
                                <th class="text-center"><?= lang('General.tickets') ?></th>
                                <th class="text-end"><?= lang('General.amount') ?></th>
                                <th class="text-end"><?= lang('General.paid') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results['invoices'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <?= lang('General.no_data') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $n = 1; foreach ($results['invoices'] as $row): ?>
                                <tr>
                                    <td class="text-muted"><?= $n++ ?></td>
                                    <td><strong><?= esc($row['nombre_cantera']) ?></strong></td>
                                    <td class="text-center"><?= $row['total_invoices'] ?></td>
                                    <td class="text-center"><?= $row['total_tickets'] ?? 0 ?></td>
                                    <td class="text-end fw-semibold">$<?= number_format((float)$row['total_amount'], 2) ?></td>
                                    <td class="text-end text-success">$<?= number_format((float)($row['paid_amount'] ?? 0), 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end"><?= lang('General.total') ?>:</td>
                                <td class="text-center"><?= $results['invoice_count'] ?></td>
                                <td></td>
                                <td class="text-end text-success">$<?= number_format((float)$results['invoice_total'], 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <span><?= lang('General.report_hint') ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.querySelectorAll('.datepicker').forEach(el => {
    flatpickr(el, { dateFormat: 'm/d/Y', allowInput: true });
});
</script>
<?= $this->endSection() ?>
