<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\InvoiceTicketModel;
use App\Models\QuarryModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Invoices extends BaseController
{
    private InvoiceModel       $model;
    private InvoiceTicketModel $ticketModel;
    private QuarryModel        $quarryModel;

    public function __construct()
    {
        $this->model       = new InvoiceModel();
        $this->ticketModel = new InvoiceTicketModel();
        $this->quarryModel = new QuarryModel();
    }

    // ── GET /invoices ────────────────────────────────────────────
    public function index(): string
    {
        return $this->render('invoices/index', [
            'pageTitle'   => lang('General.invoices'),
            'activeMenu'  => 'invoices',
            'breadcrumbs' => [lang('General.invoices') => null],
        ]);
    }

    // ── GET /invoices/data  (DataTable AJAX) ─────────────────────
    public function data(): ResponseInterface
    {
        $records = $this->model->forDataTable();
        $rows    = [];

        foreach ($records as $r) {
            $statusMap = [
                'pending'  => '<span class="badge bg-warning-subtle text-warning">Pending</span>',
                'received' => '<span class="badge bg-info-subtle text-info">Received</span>',
                'paid'     => '<span class="badge bg-success-subtle text-success">Paid</span>',
            ];

            $pdfIcon = $r['pdf_file']
                ? '<a href="' . base_url('invoices/' . $r['id'] . '/pdf') . '" target="_blank" class="text-danger" title="View PDF"><i class="bi bi-file-pdf-fill"></i></a>'
                : '<span class="text-muted" title="No PDF"><i class="bi bi-file-pdf"></i></span>';

            $rows[] = [
                'id'             => $r['id'],
                'no_factura'     => esc($r['no_factura']),
                'fecha'          => date('m/d/Y', strtotime($r['fecha'])),
                'nombre_cantera' => esc($r['nombre_cantera']),
                'due_date'       => $r['due_date'] ? date('m/d/Y', strtotime($r['due_date'])) : '—',
                'fecha_recibida' => $r['fecha_recibida'] ? date('m/d/Y', strtotime($r['fecha_recibida'])) : '—',
                'monto_total'    => '$' . number_format($r['monto_total'], 2),
                'status'         => $statusMap[$r['status']] ?? $r['status'],
                'pdf'            => $pdfIcon,
                'actions'        => $this->buildActions($r),
            ];
        }

        return $this->jsonResponse(['data' => $rows]);
    }

    // ── GET /invoices/create ─────────────────────────────────────
    public function create(): string
    {
        return $this->render('invoices/form', [
            'pageTitle'   => 'New Invoice',
            'activeMenu'  => 'invoices',
            'breadcrumbs' => [lang('General.invoices') => base_url('invoices'), 'New' => null],
            'invoice'     => null,
            'tickets'     => [],
            'quarries'    => $this->quarryModel->forSelect(),
        ]);
    }

    // ── POST /invoices ────────────────────────────────────────────
    public function store(): ResponseInterface
    {
        $validation = $this->validateInvoice();
        if ($validation !== true) {
            return $this->jsonError(lang('General.error_generic'), 422, $validation);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $invoiceId = $this->model->insert($this->buildInvoiceData(), true);
        $this->saveTickets($invoiceId, $this->request->getPost('tickets') ?? []);

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->jsonError('Failed to save invoice.', 500);
        }

        return $this->jsonSuccess(lang('General.saved'), ['id' => $invoiceId]);
    }

    // ── GET /invoices/{id}/edit ───────────────────────────────────
    public function edit(int $id): string
    {
        $invoice = $this->model->withQuarry($id);
        if (! $invoice) {
            return redirect()->to(base_url('invoices'))->with('error', 'Invoice not found.');
        }

        return $this->render('invoices/form', [
            'pageTitle'   => 'Edit Invoice #' . $invoice['no_factura'],
            'activeMenu'  => 'invoices',
            'breadcrumbs' => [lang('General.invoices') => base_url('invoices'), 'Edit' => null],
            'invoice'     => $invoice,
            'tickets'     => $this->ticketModel->forInvoice($id),
            'quarries'    => $this->quarryModel->forSelect(),
        ]);
    }

    // ── POST /invoices/{id} ───────────────────────────────────────
    public function update(int $id): ResponseInterface
    {
        $invoice = $this->model->find($id);
        if (! $invoice) {
            return $this->jsonError('Invoice not found.', 404);
        }

        $validation = $this->validateInvoice($id);
        if ($validation !== true) {
            return $this->jsonError(lang('General.error_generic'), 422, $validation);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Handle PDF: keep old file if no new upload
        $data = $this->buildInvoiceData();
        if (empty($data['pdf_file'])) {
            unset($data['pdf_file']);
        }

        $this->model->update($id, $data);
        $this->ticketModel->deleteForInvoice($id);
        $this->saveTickets($id, $this->request->getPost('tickets') ?? []);

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->jsonError('Failed to update invoice.', 500);
        }

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── DELETE /invoices/{id} ─────────────────────────────────────
    public function destroy(int $id): ResponseInterface
    {
        $invoice = $this->model->find($id);
        if (! $invoice) {
            return $this->jsonError('Invoice not found.', 404);
        }

        // Optionally delete PDF file
        if ($invoice['pdf_file']) {
            $path = WRITEPATH . 'uploads/' . $invoice['pdf_file'];
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->model->delete($id);

        return $this->jsonSuccess(lang('General.deleted'));
    }

    // ── GET /invoices/{id}/show ───────────────────────────────────
    public function show(int $id): string
    {
        $invoice = $this->model->withQuarry($id);
        if (! $invoice) {
            return redirect()->to(base_url('invoices'))->with('error', 'Invoice not found.');
        }

        return $this->render('invoices/show', [
            'pageTitle'   => 'Invoice #' . $invoice['no_factura'],
            'activeMenu'  => 'invoices',
            'breadcrumbs' => [lang('General.invoices') => base_url('invoices'), '#' . $invoice['no_factura'] => null],
            'invoice'     => $invoice,
            'tickets'     => $this->ticketModel->forInvoice($id),
        ]);
    }

    // ── GET /invoices/{id}/pdf  (serve uploaded PDF) ──────────────
    public function servePdf(int $id): ResponseInterface
    {
        $invoice = $this->model->find($id);
        if (! $invoice || ! $invoice['pdf_file']) {
            return $this->jsonError('PDF not found.', 404);
        }

        $path = WRITEPATH . 'uploads/' . $invoice['pdf_file'];
        if (! is_file($path)) {
            return $this->jsonError('PDF file missing on server.', 404);
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="invoice-' . $invoice['no_factura'] . '.pdf"')
            ->setBody(file_get_contents($path));
    }

    // ── GET /invoices/{id}/export/pdf ─────────────────────────────
    public function exportPdf(int $id): ResponseInterface
    {
        $invoice = $this->model->withQuarry($id);
        if (! $invoice) {
            return $this->jsonError('Invoice not found.', 404);
        }

        $tickets = $this->ticketModel->forInvoice($id);
        $html    = view('invoices/pdf_template', compact('invoice', 'tickets'));

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'invoice-' . $invoice['no_factura'] . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    // ── GET /invoices/export/excel ────────────────────────────────
    public function exportExcel(): ResponseInterface
    {
        $records = $this->model->forDataTable();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoices');

        // Header row style
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '435EBE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $headers = ['#', 'Invoice No.', 'Date', 'Quarry', 'Due Date', 'Date Received', 'Total', 'Status'];
        foreach ($headers as $col => $title) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $title);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // Data rows
        $row = 2;
        foreach ($records as $r) {
            $sheet->setCellValue("A{$row}", $r['id']);
            $sheet->setCellValue("B{$row}", $r['no_factura']);
            $sheet->setCellValue("C{$row}", date('m/d/Y', strtotime($r['fecha'])));
            $sheet->setCellValue("D{$row}", $r['nombre_cantera']);
            $sheet->setCellValue("E{$row}", $r['due_date'] ? date('m/d/Y', strtotime($r['due_date'])) : '');
            $sheet->setCellValue("F{$row}", $r['fecha_recibida'] ? date('m/d/Y', strtotime($r['fecha_recibida'])) : '');
            $sheet->setCellValue("G{$row}", number_format($r['monto_total'], 2));
            $sheet->setCellValue("H{$row}", ucfirst($r['status']));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="invoices-' . date('Y-m-d') . '.xlsx"')
            ->setBody($content);
    }

    // ── Private helpers ───────────────────────────────────────────

    private function validateInvoice(?int $id = null): array|true
    {
        $rules = [
            'no_factura' => 'required|max_length[50]' . ($id ? "|is_unique[invoices.no_factura,id,{$id}]" : '|is_unique[invoices.no_factura]'),
            'fecha'      => 'required',
            'cantera_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->validator->getErrors();
        }

        return true;
    }

    private function buildInvoiceData(): array
    {
        $data = [
            'no_factura'     => trim($this->request->getPost('no_factura')),
            'fecha'          => $this->parseDate($this->request->getPost('fecha')),
            'cantera_id'     => (int) $this->request->getPost('cantera_id'),
            'due_date'       => $this->parseDate($this->request->getPost('due_date')),
            'fecha_recibida' => $this->parseDate($this->request->getPost('fecha_recibida')),
            'monto_total'    => $this->calcTotal($this->request->getPost('tickets') ?? []),
            'status'         => $this->request->getPost('status') ?? 'pending',
            'notes'          => $this->request->getPost('notes'),
        ];

        // Handle PDF upload
        $pdf = $this->request->getFile('pdf_file');
        if ($pdf && $pdf->isValid() && ! $pdf->hasMoved()) {
            $uploadPath = WRITEPATH . 'uploads/invoices/';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = $pdf->getRandomName();
            $pdf->move($uploadPath, $newName);
            $data['pdf_file'] = 'invoices/' . $newName;
        }

        return $data;
    }

    private function saveTickets(int $invoiceId, array $tickets): void
    {
        if (empty($tickets)) {
            return;
        }

        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($tickets as $t) {
            if (empty($t['no_ticket'])) {
                continue;
            }
            $rows[] = [
                'invoice_id'   => $invoiceId,
                'no_ticket'    => trim($t['no_ticket']),
                'fecha'        => $this->parseDate($t['fecha'] ?? '') ?: date('Y-m-d'),
                'tipo_trabajo' => $t['tipo_trabajo'] ?? null,
                'cantera_id'   => ! empty($t['cantera_id']) ? (int) $t['cantera_id'] : null,
                'direccion'    => $t['direccion'] ?? null,
                'rate'         => (float) ($t['rate'] ?? 0),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (! empty($rows)) {
            $this->ticketModel->insertBatch($rows);
        }
    }

    private function calcTotal(array $tickets): float
    {
        return array_sum(array_map(fn($t) => (float) ($t['rate'] ?? 0), $tickets));
    }

    /** Convert mm/dd/yyyy or yyyy-mm-dd to yyyy-mm-dd for DB */
    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        // Already yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        // mm/dd/yyyy
        $d = \DateTime::createFromFormat('m/d/Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }

    private function buildActions(array $r): string
    {
        $showUrl   = base_url('invoices/' . $r['id'] . '/show');
        $editUrl   = base_url('invoices/' . $r['id'] . '/edit');
        $exportUrl = base_url('invoices/' . $r['id'] . '/export/pdf');

        return sprintf(
            '<div class="d-flex gap-1">
                <a href="%s" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye-fill"></i></a>
                <a href="%s" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                <a href="%s" class="btn btn-sm btn-outline-danger" title="Export PDF"><i class="bi bi-file-pdf-fill"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(%d,\'%s\')" title="Delete">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>',
            $showUrl, $editUrl, $exportUrl,
            $r['id'], esc($r['no_factura'], 'js')
        );
    }
}
