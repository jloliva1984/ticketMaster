<?php

namespace App\Models;

use CodeIgniter\Model;

class TruckModel extends Model
{
    protected $table          = 'trucks';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields  = ['no_camion', 'nombre_chofer', 'is_active'];

    protected $validationRules = [
        'no_camion'     => 'required|min_length[1]|max_length[50]|is_unique[trucks.no_camion,id,{id}]',
        'nombre_chofer' => 'required|min_length[2]|max_length[100]',
    ];

    protected $validationMessages = [
        'no_camion' => [
            'is_unique' => 'A truck with this number already exists.',
        ],
    ];

    /** Active trucks for select dropdowns */
    public function forSelect(): array
    {
        return $this->select('id, no_camion, nombre_chofer')
                    ->where('is_active', 1)
                    ->orderBy('no_camion', 'ASC')
                    ->findAll();
    }

    /** All trucks for DataTable */
    public function forDataTable(): array
    {
        return $this->select('id, no_camion, nombre_chofer, is_active, created_at')
                    ->findAll();
    }

    /** Find by truck number (for auto-fill of chofer in tasks) */
    public function findByNumber(string $noTruck): ?array
    {
        return $this->where('no_camion', $noTruck)
                    ->where('is_active', 1)
                    ->first();
    }
}
