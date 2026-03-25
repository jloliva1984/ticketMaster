<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder — master seeder
 * Run with: php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('UserSeeder');
        $this->call('QuarrySeeder');
        $this->call('TruckSeeder');
    }
}
