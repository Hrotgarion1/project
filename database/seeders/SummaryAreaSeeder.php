<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SummaryArea;

class SummaryAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SummaryArea::create([
            'name' => 'Resumen 1',
            'user_id' => 1, // Asumiendo un usuario admin con ID 1
        ]);

        SummaryArea::create([
            'name' => 'Resumen 2',
            'user_id' => 1,
        ]);
    }
}
