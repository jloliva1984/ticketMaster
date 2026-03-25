<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; color: #333; margin: 0; }
    .header { background: #435ebe; color: #fff; padding: 20px 24px; }
    .header h1 { margin: 0; font-size: 18pt; }
    .header .sub { font-size: 9pt; opacity: .8; margin-top: 2px; }
    .meta-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
    .meta-table td { padding: 5px 8px; vertical-align: top; }
    .meta-table .label { font-weight: bold; color: #607080; font-size: 8pt;
                         text-transform: uppercase; letter-spacing: .04em; }
    .meta-table .value { font-size: 10pt; }
    hr { border: none; border-top: 1px solid #e0e0e0; margin: 12px 0; }
    table.tickets { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.tickets th { background: #435ebe; color: #fff; padding: 7px 8px;
                       font-size: 8.5pt; text-align: left; }
    table.tickets td { padding: 6px 8px; border-bottom: 1px solid #f0f0f0; font-size: 9pt; }
    table.tickets tr:nth-child(even) td { background: #f8f9fc; }
    table.tickets tfoot td { font-weight: bold; border-top: 2px solid #435ebe;
                              background: #eef1fb; }
    .total-right { text-align: right; }
    .footer { margin-top: 30px; font-size: 8pt; color: #888; text-align: center; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 99px;
             font-size: 8pt; font-weight: bold; }
    .badge-pending  { background: #fff3cd; color: #856404; }
    .badge-received { background: #cff4fc; color: #055160; }
    .badge-paid     { background: #d1e7dd; color: #0a3622; }
</style>
</head>
<body>

<div class="header">
    <table width="100%">
        <tr>
            <td>
                <h1>Invoice #<?= esc($invoice['no_factura']) ?></h1>
                <div class="sub">TicketMaster.LT</div>
            </td>
            <td style="text-align:right; vertical-align:top">
                <div style="font-size:9pt;opacity:.8">Issue Date</div>
                <div style="font-size:12pt;font-weight:bold"><?= date('m/d/Y', strtotime($invoice['fecha'])) ?></div>
                <?php if ($invoice['due_date']): ?>
                    <div style="font-size:8.5pt;opacity:.8">Due: <?= date('m/d/Y', strtotime($invoice['due_date'])) ?></div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<table class="meta-table" style="margin: 16px 24px; width: calc(100% - 48px)">
    <tr>
        <td>
            <div class="label">Quarry / Cantera</div>
            <div class="value"><?= esc($invoice['nombre_cantera']) ?></div>
        </td>
        <td>
            <div class="label">Status</div>
            <div class="value">
                <span class="badge badge-<?= $invoice['status'] ?>"><?= ucfirst($invoice['status']) ?></span>
            </div>
        </td>
        <td>
            <div class="label">Date Received</div>
            <div class="value">
                <?= $invoice['fecha_recibida'] ? date('m/d/Y', strtotime($invoice['fecha_recibida'])) : '—' ?>
            </div>
        </td>
        <?php if ($invoice['notes']): ?>
        <td>
            <div class="label">Notes</div>
            <div class="value"><?= esc($invoice['notes']) ?></div>
        </td>
        <?php endif; ?>
    </tr>
</table>

<hr style="margin: 0 24px">

<div style="padding: 0 24px">
    <h3 style="font-size:10pt; margin: 14px 0 8px; color:#435ebe">
        Tickets (<?= count($tickets) ?>)
    </h3>

    <?php if (empty($tickets)): ?>
        <p style="color:#888;font-style:italic">No tickets attached.</p>
    <?php else: ?>
    <table class="tickets">
        <thead>
            <tr>
                <th>No. Ticket</th>
                <th>Fecha</th>
                <th>Tipo Trabajo</th>
                <th>Cantera</th>
                <th>Dirección</th>
                <th style="text-align:right">Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $t): ?>
            <tr>
                <td><strong><?= esc($t['no_ticket']) ?></strong></td>
                <td><?= date('m/d/Y', strtotime($t['fecha'])) ?></td>
                <td><?= esc($t['tipo_trabajo'] ?? '—') ?></td>
                <td><?= esc($t['nombre_cantera'] ?? '—') ?></td>
                <td><?= esc($t['direccion'] ?? '—') ?></td>
                <td style="text-align:right">$<?= number_format($t['rate'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="total-right" style="padding-right:8px">TOTAL</td>
                <td class="total-right">$<?= number_format(array_sum(array_column($tickets, 'rate')), 2) ?></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<div class="footer">
    Generated <?= date('m/d/Y H:i') ?> &bull; TicketMaster.LT &bull; Ing: Jorge Luis Oliva Matos
</div>

</body>
</html>
