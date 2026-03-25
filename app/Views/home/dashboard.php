<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- ── Stats row ─────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-primary-grad">
            <div class="stat-icon"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-value"><?= $stats['invoices'] ?? 0 ?></div>
                <div class="stat-label"><?= lang('General.invoices') ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-success-grad">
            <div class="stat-icon"><i class="bi bi-truck"></i></div>
            <div>
                <div class="stat-value"><?= $stats['tasks'] ?? 0 ?></div>
                <div class="stat-label"><?= lang('General.tasks') ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-warning-grad">
            <div class="stat-icon"><i class="bi bi-geo-alt"></i></div>
            <div>
                <div class="stat-value"><?= $stats['quarries'] ?? 0 ?></div>
                <div class="stat-label"><?= lang('General.quarries') ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card bg-danger-grad">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-value"><?= $stats['unmatched'] ?? 0 ?></div>
                <div class="stat-label"><?= lang('General.unmatched_tickets') ?></div>
            </div>
        </div>
    </div>

</div>

<!-- ── Recent activity placeholder ──────────────────────── -->
<div class="row g-3">

    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= lang('General.recent_invoices') ?></span>
                <a href="<?= base_url('invoices') ?>" class="btn btn-sm btn-outline-primary">
                    <?= lang('General.view_all') ?>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?= lang('General.invoice_no') ?></th>
                                <th><?= lang('General.date') ?></th>
                                <th><?= lang('General.quarry') ?></th>
                                <th><?= lang('General.amount') ?></th>
                                <th><?= lang('General.status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentInvoices)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        <?= lang('General.no_data') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentInvoices as $inv): ?>
                                <tr>
                                    <td>
                                        <a href="<?= site_url('invoices/' . $inv['id'] . '/show') ?>">
                                            <?= esc($inv['no_factura']) ?>
                                        </a>
                                    </td>
                                    <td><?= $inv['fecha'] ? date('m/d/Y', strtotime($inv['fecha'])) : '—' ?></td>
                                    <td><?= esc($inv['nombre_cantera'] ?? '—') ?></td>
                                    <td>$<?= number_format((float)$inv['monto_total'], 2) ?></td>
                                    <td>
                                        <?php
                                            $badge = match($inv['status']) {
                                                'paid'     => 'success',
                                                'received' => 'info',
                                                default    => 'warning',
                                            };
                                        ?>
                                        <span class="badge bg-<?= $badge ?>">
                                            <?= lang('General.status_' . $inv['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><?= lang('General.trucks_summary') ?></div>
            <div class="card-body">
                <?php if (empty($recentTrucks)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-truck-front fs-3 d-block mb-1"></i>
                        <?= lang('General.no_data') ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentTrucks as $truck): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-semibold"><?= esc($truck['no_camion']) ?></div>
                                <div class="text-muted font-sm"><?= esc($truck['nombre_chofer']) ?></div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">
                                $<?= number_format((float)($truck['total_amount'] ?? 0), 0) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
