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
}
