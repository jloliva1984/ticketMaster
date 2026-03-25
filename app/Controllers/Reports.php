<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Models\InvoiceModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Reports extends BaseController
{
    private TaskModel    $taskModel;
    private InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->taskModel    = new TaskModel();
        $this->invoiceModel = new InvoiceModel();
    }

    // ── GET /reports ─────────────────────────────────────────────
    public function index(): string
    {
        // Default: current month
        $from    = $this->request->getGet('from')     ?? date('m/01/Y');
        $to      = $this->request->getGet('to')       ?? date('m/d/Y');
        $groupBy = $this->request->getGet('group_by') ?? 'truck';

        $results = null;
        if ($this->request->getGet('from')) {
            $results = $this->buildReport(
                $this->parseDate($from),
                $this->parseDate($to),
                $groupBy
            );
        }

        return $this->render('reports/index', [
            'pageTitle'   => lang('General.reports'),
            'activeMenu'  => 'reports',
            'breadcrumbs' => [lang('General.reports') => null],
            'from'        => $from,
            'to'          => $to,
            'groupBy'     => $groupBy,
            'results'     => $results,
        ]);
    }

    // ── GET /reports/export/excel ─────────────────────────────────
    public function exportExcel(): ResponseInterface
    {
        $from    = $this->parseDate($this->request->getGet('from') ?? date('m/01/Y'));
        $to      = $this->parseDate($this->request->getGet('to')   ?? date('m/d/Y'));
        $groupBy = $this->request->getGet('group_by') ?? 'truck';

        $report = $this->buildReport($from, $to, $groupBy);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle('TicketMaster Report');

        // ── Sheet 1: Tasks ────────────────────────────────────────
        $sheet1 = $spreadsheet->getActiveSheet()->setTitle('Tasks');
        $this->writeTaskSheet($sheet1, $report, $from, $to, $groupBy);

        // ── Sheet 2: Invoices ─────────────────────────────────────
        $sheet2 = $spreadsheet->createSheet()->setTitle('Invoices');
        $this->writeInvoiceSheet($sheet2, $report, $from, $to);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="report-' . $from . '-to-' . $to . '.xlsx"')
            ->setBody($content);
    }

    // ── GET /reports/export/pdf ───────────────────────────────────
    public function exportPdf(): ResponseInterface
    {
        $from    = $this->parseDate($this->request->getGet('from') ?? date('m/01/Y'));
        $to      = $this->parseDate($this->request->getGet('to')   ?? date('m/d/Y'));
        $groupBy = $this->request->getGet('group_by') ?? 'truck';

        $report = $this->buildReport($from, $to, $groupBy);

        $html = view('reports/pdf_template', compact('report', 'from', 'to', 'groupBy'));

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="report-' . $from . '-to-' . $to . '.pdf"')
            ->setBody($dompdf->output());
    }

    // ── Private helpers ───────────────────────────────────────────

    /**
     * Build the full report data structure.
     */
    private function buildReport(string $from, string $to, string $groupBy): array
    {
        $taskRows = $groupBy === 'driver'
            ? $this->taskModel->reportByDriver($from, $to)
            : $this->taskModel->reportByTruck($from, $to);

        $invoiceRows = $this->invoiceModel->summaryByQuarry($from, $to);
        $invoiceTotals = $this->invoiceModel->totals($from, $to);

        $taskGrandTotal   = array_sum(array_column($taskRows,    'total_amount'));
        $taskTotalTickets = array_sum(array_column($taskRows,    'total_tickets'));
        $taskTotalTasks   = array_sum(array_column($taskRows,    'total_tasks'));

        return [
            'tasks'           => $taskRows,
            'invoices'        => $invoiceRows,
            'task_total'      => $taskGrandTotal,
            'task_tickets'    => $taskTotalTickets,
            'task_count'      => $taskTotalTasks,
            'invoice_total'   => $invoiceTotals['grand_total']   ?? 0,
            'invoice_count'   => $invoiceTotals['total_invoices'] ?? 0,
            'group_label'     => $groupBy === 'driver' ? 'Driver (Chofer)' : 'Truck (Camión)',
        ];
    }

    /** Write tasks summary to a spreadsheet sheet */
    private function writeTaskSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $report,
        string $from,
        string $to,
        string $groupBy
    ): void {
        $blue = ['rgb' => '435EBE'];
        $white = ['rgb' => 'FFFFFF'];

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => $white],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => $blue],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $totalStyle = [
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF1FB']],
            'borders'   => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ];

        // Title
        $sheet->setCellValue('A1', 'TASK REPORT — ' . date('m/d/Y', strtotime($from)) . ' to ' . date('m/d/Y', strtotime($to)));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => $blue],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A2', 'Grouped by: ' . $report['group_label']);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        // Headers
        $cols = $groupBy === 'driver'
            ? ['#', 'Driver', 'Trucks', 'Tasks', 'Tickets', 'Delivered', 'Total ($)']
            : ['#', 'Truck No.', 'Driver', 'Tasks', 'Tickets', 'Delivered', 'Total ($)'];

        foreach ($cols as $i => $col) {
            $cell = chr(65 + $i) . '4';
            $sheet->setCellValue($cell, $col);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // Data
        $row = 5;
        $num = 1;
        foreach ($report['tasks'] as $r) {
            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $groupBy === 'driver' ? $r['nombre_chofer'] : $r['no_camion']);
            $sheet->setCellValue("C{$row}", $groupBy === 'driver' ? ($r['camiones'] ?? '') : $r['nombre_chofer']);
            $sheet->setCellValue("D{$row}", $r['total_tasks']);
            $sheet->setCellValue("E{$row}", $r['total_tickets']);
            $sheet->setCellValue("F{$row}", $r['delivered_tasks']);
            $sheet->setCellValue("G{$row}", number_format((float) $r['total_amount'], 2));
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        // Totals row
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("D{$row}", $report['task_count']);
        $sheet->setCellValue("E{$row}", $report['task_tickets']);
        $sheet->setCellValue("G{$row}", number_format((float) $report['task_total'], 2));
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($totalStyle);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /** Write invoices summary to a spreadsheet sheet */
    private function writeInvoiceSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $report,
        string $from,
        string $to
    ): void {
        $blue  = ['rgb' => '435EBE'];
        $white = ['rgb' => 'FFFFFF'];
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => $white],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => $blue],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $sheet->setCellValue('A1', 'INVOICE REPORT — ' . date('m/d/Y', strtotime($from)) . ' to ' . date('m/d/Y', strtotime($to)));
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => $blue],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['#', 'Quarry', 'Invoices', 'Tickets', 'Total ($)'];
        foreach ($headers as $i => $h) {
            $cell = chr(65 + $i) . '3';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        $row = 4;
        $num = 1;
        foreach ($report['invoices'] as $r) {
            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $r['nombre_cantera']);
            $sheet->setCellValue("C{$row}", $r['total_invoices']);
            $sheet->setCellValue("D{$row}", $r['total_tickets'] ?? 0);
            $sheet->setCellValue("E{$row}", number_format((float) $r['total_amount'], 2));
            $row++;
        }

        // Total
        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("C{$row}", $report['invoice_count']);
        $sheet->setCellValue("E{$row}", number_format((float) $report['invoice_total'], 2));
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function parseDate(?string $date): string
    {
        if (empty($date)) {
            return date('Y-m-d');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        $d = \DateTime::createFromFormat('m/d/Y', $date);
        return $d ? $d->format('Y-m-d') : date('Y-m-d');
    }
}
