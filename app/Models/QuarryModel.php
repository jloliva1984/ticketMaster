<?php

namespace App\Models;

use CodeIgniter\Model;

class QuarryModel extends Model
{
    protected $table          = 'quarries';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields  = ['nombre_cantera', 'is_active'];

    protected $validationRules = [
        'nombre_cantera' => 'required|min_length[2]|max_length[150]|is_unique[quarries.nombre_cantera,id,{id}]',
    ];

    protected $validationMessages = [
        'nombre_cantera' => [
            'is_unique' => 'A quarry with this name already exists.',
        ],
    ];

    /** All active quarries for select dropdowns */
    public function forSelect(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('nombre_cantera', 'ASC')
                    ->findAll();
    }

    /** All quarries (including inactive) for DataTable */
    public function forDataTable(): array
    {
        return $this->select('id, nombre_cantera, is_active, created_at')
                    ->findAll();
    }
}
