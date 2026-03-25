<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskTicketModel extends Model
{
    protected $table         = 'task_tickets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'task_id',
        'no_ticket',
        'fecha',
        'tipo_trabajo',
        'cantera_id',
        'direccion',
        'rate',
    ];

    /** All tickets for a given task with cantera name */
    public function forTask(int $taskId): array
    {
        return $this->db->table('task_tickets t')
            ->select('t.*, q.nombre_cantera')
            ->join('quarries q', 'q.id = t.cantera_id', 'left')
            ->where('t.task_id', $taskId)
            ->orderBy('t.fecha', 'ASC')
            ->orderBy('t.no_ticket', 'ASC')
            ->get()->getResultArray();
    }

    /** Delete all tickets for a task (before re-inserting on update) */
    public function deleteForTask(int $taskId): void
    {
        $this->where('task_id', $taskId)->delete();
    }

    /**
     * All task tickets with task + truck info — used by Phase 8 unmatched tickets.
     * CAST(no_ticket AS UNSIGNED) strips leading zeros for numeric comparison.
     */
    public function allWithTask(): array
    {
        return $this->db->table('task_tickets tt')
            ->select('tt.no_ticket, tt.fecha, tt.direccion, tt.cantera_id,
                      tt.task_id, t.nombre_chofer, tr.no_camion,
                      CAST(tt.no_ticket AS UNSIGNED) AS no_ticket_num')
            ->join('tasks t',  't.id = tt.task_id')
            ->join('trucks tr', 'tr.id = t.truck_id', 'left')
            ->where('t.deleted_at IS NULL')
            ->get()->getResultArray();
    }

    /**
     * Task tickets that have no matching invoice ticket
     * (by CAST(no_ticket AS UNSIGNED) + cantera_id).
     * Used for the Phase 8 Unmatched Tickets screen.
     */
    public function unmatched(): array
    {
        $sql = "SELECT
                    tt.id,
                    tt.no_ticket,
                    tt.fecha,
                    tt.tipo_trabajo,
                    tt.cantera_id,
                    tt.direccion,
                    tt.rate,
                    tt.task_id,
                    t.nombre_chofer,
                    t.status AS task_status,
                    tr.no_camion,
                    q.nombre_cantera
                FROM task_tickets tt
                LEFT JOIN tasks    t  ON t.id  = tt.task_id  AND t.deleted_at IS NULL
                LEFT JOIN trucks   tr ON tr.id = t.truck_id
                LEFT JOIN quarries q  ON q.id  = tt.cantera_id
                WHERE t.deleted_at IS NULL
                  AND NOT EXISTS (
                      SELECT 1
                      FROM invoice_tickets it
                      JOIN invoices i ON i.id = it.invoice_id AND i.deleted_at IS NULL
                      WHERE it.cantera_id = tt.cantera_id
                        AND CAST(it.no_ticket AS UNSIGNED) = CAST(tt.no_ticket AS UNSIGNED)
                  )
                ORDER BY tt.fecha DESC, CAST(tt.no_ticket AS UNSIGNED)";

        return $this->db->query($sql)->getResultArray();
    }

    /** Count of unmatched task tickets — used by dashboard */
    public function unmatchedCount(): int
    {
        $sql = "SELECT COUNT(*) AS cnt
                FROM task_tickets tt
                LEFT JOIN tasks t ON t.id = tt.task_id AND t.deleted_at IS NULL
                WHERE t.deleted_at IS NULL
                  AND NOT EXISTS (
                      SELECT 1
                      FROM invoice_tickets it
                      JOIN invoices i ON i.id = it.invoice_id AND i.deleted_at IS NULL
                      WHERE it.cantera_id = tt.cantera_id
                        AND CAST(it.no_ticket AS UNSIGNED) = CAST(tt.no_ticket AS UNSIGNED)
                  )";

        $row = $this->db->query($sql)->getRowArray();
        return (int)($row['cnt'] ?? 0);
    }

    /** Single task ticket row (for modal prefill) */
    public function findWithDetails(int $id): ?array
    {
        return $this->db->table('task_tickets tt')
            ->select('tt.*, t.nombre_chofer, t.status AS task_status, tr.no_camion, q.nombre_cantera')
            ->join('tasks    t',  't.id  = tt.task_id', 'left')
            ->join('trucks   tr', 'tr.id = t.truck_id', 'left')
            ->join('quarries q',  'q.id  = tt.cantera_id', 'left')
            ->where('tt.id', $id)
            ->get()->getRowArray() ?: null;
    }
}
