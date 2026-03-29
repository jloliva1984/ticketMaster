<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table          = 'tasks';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'truck_id',
        'nombre_chofer',
        'fecha_inicio',
        'fecha_fin',
        'monto_total',
        'status',
        'delivered_at',
        'notes',
    ];

    protected $validationRules = [
        'truck_id'      => 'required|integer|is_not_unique[trucks.id]',
        'nombre_chofer' => 'required|min_length[2]|max_length[100]',
    ];

    // ── Finders ─────────────────────────────────────────────────

    /** List with truck number for DataTable */
    public function forDataTable(): array
    {
        return $this->db->table('tasks t')
            ->select('t.id, t.nombre_chofer, t.fecha_inicio, t.fecha_fin, t.monto_total,
                      t.status, t.delivered_at, t.created_at,
                      tr.no_camion')
            ->join('trucks tr', 'tr.id = t.truck_id', 'left')
            ->where('t.deleted_at IS NULL')
            ->orderBy('t.id', 'DESC')
            ->get()->getResultArray();
    }

    /** Single task with truck info */
    public function withTruck(int $id): ?array
    {
        return $this->db->table('tasks t')
            ->select('t.*, tr.no_camion')
            ->join('trucks tr', 'tr.id = t.truck_id', 'left')
            ->where('t.id', $id)
            ->where('t.deleted_at IS NULL')
            ->get()->getRowArray();
    }

    /** Mark as delivered */
    public function markDelivered(int $id): bool
    {
        return $this->update($id, [
            'status'       => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Summary grouped by truck — for reports */
    public function reportByTruck(string $from, string $to): array
    {
        return $this->db->table('tasks t')
            ->select('tr.no_camion,
                      t.nombre_chofer,
                      COUNT(DISTINCT t.id)                                            AS total_tasks,
                      COUNT(tt.id)                                                    AS total_tickets,
                      COALESCE(SUM(tt.rate), 0)                                       AS total_amount,
                      SUM(CASE WHEN t.status = "delivered" THEN 1 ELSE 0 END)         AS delivered_tasks')
            ->join('trucks tr',       'tr.id = t.truck_id',   'left')
            ->join('task_tickets tt', 'tt.task_id = t.id',    'left')
            ->where('t.deleted_at IS NULL')
            ->where('DATE(t.created_at) >=', $from)
            ->where('DATE(t.created_at) <=', $to)
            ->groupBy('t.truck_id')
            ->orderBy('total_amount', 'DESC')
            ->get()->getResultArray();
    }

    /** Summary grouped by driver (chofer) — for reports */
    public function reportByDriver(string $from, string $to): array
    {
        return $this->db->table('tasks t')
            ->select('t.nombre_chofer,
                      GROUP_CONCAT(DISTINCT tr.no_camion ORDER BY tr.no_camion SEPARATOR ", ") AS camiones,
                      COUNT(DISTINCT t.id)                                            AS total_tasks,
                      COUNT(tt.id)                                                    AS total_tickets,
                      COALESCE(SUM(tt.rate), 0)                                       AS total_amount,
                      SUM(CASE WHEN t.status = "delivered" THEN 1 ELSE 0 END)         AS delivered_tasks')
            ->join('trucks tr',       'tr.id = t.truck_id',   'left')
            ->join('task_tickets tt', 'tt.task_id = t.id',    'left')
            ->where('t.deleted_at IS NULL')
            ->where('DATE(t.created_at) >=', $from)
            ->where('DATE(t.created_at) <=', $to)
            ->groupBy('t.nombre_chofer')
            ->orderBy('total_amount', 'DESC')
            ->get()->getResultArray();
    }

    /** Detail rows for a single truck within a date range (used in PDF) */
    public function detailByTruck(int $truckId, string $from, string $to): array
    {
        return $this->db->table('tasks t')
            ->select('t.id, t.fecha_inicio, t.fecha_fin, t.status, t.monto_total, t.created_at,
                      COUNT(tt.id) AS ticket_count')
            ->join('task_tickets tt', 'tt.task_id = t.id', 'left')
            ->where('t.deleted_at IS NULL')
            ->where('t.truck_id', $truckId)
            ->where('DATE(t.created_at) >=', $from)
            ->where('DATE(t.created_at) <=', $to)
            ->groupBy('t.id')
            ->orderBy('t.created_at', 'ASC')
            ->get()->getResultArray();
    }

    /** @deprecated kept for dashboard compat */
    public function summaryByTruck(string $from, string $to): array
    {
        return $this->reportByTruck($from, $to);
    }
}
