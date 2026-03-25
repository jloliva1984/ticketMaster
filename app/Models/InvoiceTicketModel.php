<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceTicketModel extends Model
{
    protected $table         = 'invoice_tickets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'invoice_id',
        'no_ticket',
        'fecha',
        'tipo_trabajo',
        'cantera_id',
        'direccion',
        'rate',
    ];

    /** All tickets for a given invoice with cantera name */
    public function forInvoice(int $invoiceId): array
    {
        return $this->db->table('invoice_tickets t')
            ->select('t.*, q.nombre_cantera')
            ->join('quarries q', 'q.id = t.cantera_id', 'left')
            ->where('t.invoice_id', $invoiceId)
            ->orderBy('t.fecha', 'ASC')
            ->orderBy('t.no_ticket', 'ASC')
            ->get()->getResultArray();
    }

    /** Delete all tickets for an invoice (before re-inserting on update) */
    public function deleteForInvoice(int $invoiceId): void
    {
        $this->where('invoice_id', $invoiceId)->delete();
    }

    /**
     * All tickets (with invoice info) for Phase 8 unmatched comparison.
     * Returns no_ticket stripped of leading zeros for easy matching.
     */
    public function allWithInvoice(): array
    {
        return $this->db->table('invoice_tickets t')
            ->select('t.no_ticket, t.fecha, t.direccion, t.cantera_id,
                      i.no_factura, i.id AS invoice_id,
                      CAST(t.no_ticket AS UNSIGNED) AS no_ticket_num')
            ->join('invoices i', 'i.id = t.invoice_id')
            ->where('i.deleted_at IS NULL')
            ->get()->getResultArray();
    }
}
