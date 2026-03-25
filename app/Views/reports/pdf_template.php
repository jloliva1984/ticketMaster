<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report <?= esc($from) ?> to <?= esc($to) ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1a1a2e;
    }
    .page-header {
        background-color: #435EBE;
        color: #ffffff;
        padding: 10px 14px;
        margin-bottom: 14px;
    }
    .page-header h1 { font-size: 15px; margin-bottom: 2px; }
    .page-header p  { font-size: 9px; opacity: .85; }

    h2 {
        font-size: 11px;
        color: #435EBE;
        border-bottom: 1.5px solid #435EBE;
        padding-bottom: 3px;
        margin-bottom: 6px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
    }
    thead tr {
        background-color: #435EBE;
        color: #ffffff;
    }
    thead th {
        padding: 5px 6px;
        text-align: left;
        font-size: 9px;
        font-weight: bold;
    }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }

    tbody tr:nth-child(even) { background-color: #f0f3ff; }
    tbody td {
        padding: 4px 6px;
        border-bottom: 1px solid #e0e0e0;
    }
    tbody td.right  { text-align: right; }
    tbody td.center { text-align: center; }

    tfoot tr {
        background-color: #eef1fb;
        border-top: 1.5px solid #435EBE;
    }
    tfoot td {
        padding: 5px 6px;
        font-weight: bold;
    }
    tfoot td.right  { text-align: right; }
    tfoot td.center { text-align: center; }

    .section { margin-bottom: 18px; }

    .summary-row {
        display: inline-block;
        margin-right: 20px;
        font-size: 9px;
    }
    .summary-row strong { font-size: 11px; color: #435EBE; }

    .page-footer {
        position: fixed;
        bottom: 0;
        left: 0; right: 0;
        font-size: 8px;
        color: #777;
        border-top: 1px solid #ccc;
        padding: 4px 14px;
        display: flex;
        justify-content: space-between;
    }
</style>
</head>
<body>

<div class="page-header">
    <h1>TicketMaster.LT — Report</h1>
    <p>
        Period: <?= date('m/d/Y', strtotime($from)) ?> &ndash; <?= date('m/d/Y', strtotime($to)) ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Grouped by: <?= esc($report['group_label']) ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Generated: <?= date('m/d/Y H:i') ?>
    </p>
</div>

<!-- ── Tasks Section ─────────────────────────────────────────── -->
<div class="section">
    <h2>Tasks Summary</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <?php if ($groupBy === 'driver'): ?>
                    <th>Driver</th>
                    <th>Trucks</th>
                <?php else: ?>
                    <th>Truck No.</th>
                    <th>Driver</th>
                <?php endif; ?>
                <th class="center">Tasks</th>
                <th class="center">Tickets</th>
                <th class="center">Delivered</th>
                <th class="right">Total ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($report['tasks'])): ?>
                <tr><td colspan="7" class="center">No records found.</td></tr>
            <?php else: ?>
                <?php $n = 1; foreach ($report['tasks'] as $row): ?>
                <tr>
                    <td><?= $n++ ?></td>
                    <?php if ($groupBy === 'driver'): ?>
                        <td><?= esc($row['nombre_chofer']) ?></td>
                        <td><?= esc($row['camiones'] ?? '—') ?></td>
                    <?php else: ?>
                        <td><?= esc($row['no_camion']) ?></td>
                        <td><?= esc($row['nombre_chofer']) ?></td>
                    <?php endif; ?>
                    <td class="center"><?= $row['total_tasks'] ?></td>
                    <td class="center"><?= $row['total_tickets'] ?></td>
                    <td class="center"><?= $row['delivered_tasks'] ?></td>
                    <td class="right">$<?= number_format((float)$row['total_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;">TOTAL:</td>
                <td class="center"><?= $report['task_count'] ?></td>
                <td class="center"><?= $report['task_tickets'] ?></td>
                <td></td>
                <td class="right">$<?= number_format((float)$report['task_total'], 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ── Invoices Section ──────────────────────────────────────── -->
<div class="section">
    <h2>Invoices Summary — by Quarry</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Quarry</th>
                <th class="center">Invoices</th>
                <th class="center">Tickets</th>
                <th class="right">Total ($)</th>
                <th class="right">Paid ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($report['invoices'])): ?>
                <tr><td colspan="6" class="center">No records found.</td></tr>
            <?php else: ?>
                <?php $n = 1; foreach ($report['invoices'] as $row): ?>
                <tr>
                    <td><?= $n++ ?></td>
                    <td><?= esc($row['nombre_cantera']) ?></td>
                    <td class="center"><?= $row['total_invoices'] ?></td>
                    <td class="center"><?= $row['total_tickets'] ?? 0 ?></td>
                    <td class="right">$<?= number_format((float)$row['total_amount'], 2) ?></td>
                    <td class="right">$<?= number_format((float)($row['paid_amount'] ?? 0), 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;">TOTAL:</td>
                <td class="center"><?= $report['invoice_count'] ?></td>
                <td></td>
                <td class="right">$<?= number_format((float)$report['invoice_total'], 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="page-footer">
    <span>TicketMaster.LT — Ing: Jorge Luis Oliva Matos</span>
    <span>Report: <?= date('m/d/Y', strtotime($from)) ?> to <?= date('m/d/Y', strtotime($to)) ?></span>
</div>

</body>
</html>
