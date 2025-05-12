<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\SummaryArea;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $summaryArea = SummaryArea::first();

        $areas = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($areas as $name) {
            Area::create([
                'name' => $name,
                'summary_area_id' => $summaryArea->id,
            ]);
        }
    }
}
