<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table          = 'invoices';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'no_factura',
        'fecha',
        'cantera_id',
        'due_date',
        'fecha_recibida',
        'pdf_file',
        'monto_total',
        'status',
        'notes',
    ];

    protected $validationRules = [
        'no_factura' => 'required|max_length[50]|is_unique[invoices.no_factura,id,{id}]',
        'fecha'      => 'required|valid_date[Y-m-d]',
        'cantera_id' => 'required|integer|is_not_unique[quarries.id]',
        'due_date'   => 'permit_empty|valid_date[Y-m-d]',
    ];

    protected $validationMessages = [
        'no_factura' => ['is_unique' => 'Invoice number already exists.'],
        'cantera_id' => ['is_not_unique' => 'Selected quarry does not exist.'],
    ];

    // ── Finders ─────────────────────────────────────────────────

    /** List with cantera name for DataTable */
    public function forDataTable(): array
    {
        return $this->db->table('invoices i')
            ->select('i.id, i.no_factura, i.fecha, q.nombre_cantera,
                      i.due_date, i.fecha_recibida, i.monto_total, i.status,
                      i.pdf_file, i.created_at')
            ->join('quarries q', 'q.id = i.cantera_id', 'left')
            ->where('i.deleted_at IS NULL')
            ->orderBy('i.fecha', 'DESC')
            ->get()->getResultArray();
    }

    /** Single invoice with cantera name */
    public function withQuarry(int $id): ?array
    {
        return $this->db->table('invoices i')
            ->select('i.*, q.nombre_cantera')
            ->join('quarries q', 'q.id = i.cantera_id', 'left')
            ->where('i.id', $id)
            ->where('i.deleted_at IS NULL')
            ->get()->getRowArray();
    }

    /** Summary grouped by quarry — for reports */
    public function summaryByQuarry(string $from, string $to): array
    {
        return $this->db->table('invoices i')
            ->select('q.nombre_cantera,
                      COUNT(i.id)          AS total_invoices,
                      COUNT(it.id)         AS total_tickets,
                      SUM(i.monto_total)   AS total_amount,
                      SUM(CASE WHEN i.status = "paid" THEN i.monto_total ELSE 0 END) AS paid_amount')
            ->join('quarries q',       'q.id = i.cantera_id',  'left')
            ->join('invoice_tickets it', 'it.invoice_id = i.id', 'left')
            ->where('i.deleted_at IS NULL')
            ->where('i.fecha >=', $from)
            ->where('i.fecha <=', $to)
            ->groupBy('i.cantera_id')
            ->orderBy('total_amount', 'DESC')
            ->get()->getResultArray();
    }

    /** Grand totals for a date range — for report footers */
    public function totals(string $from, string $to): array
    {
        return $this->db->table('invoices')
            ->select('COUNT(id) AS total_invoices, SUM(monto_total) AS grand_total')
            ->where('deleted_at IS NULL')
            ->where('fecha >=', $from)
            ->where('fecha <=', $to)
            ->get()->getRowArray() ?? ['total_invoices' => 0, 'grand_total' => 0];
    }
}
