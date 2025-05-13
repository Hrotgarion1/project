<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AreaA;
use App\Models\AreaB;
use App\Models\AreaC;
use App\Models\AreaD;
use App\Models\AreaE;
use App\Models\AreaF;
use App\Models\AreaG;
use App\Models\AreaH;
use App\Models\AreaRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecalculateValues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-values';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate values for records with currently = yes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $festivos = [
            '2025-01-01',
            '2025-05-01',
            '2025-12-25',
        ];
        $festivos = array_map(fn($date) => Carbon::parse($date)->startOfDay(), $festivos);
        $isWorkingDay = !$today->isWeekend() && !in_array($today->startOfDay(), $festivos);

        $models = [
            'A' => AreaA::class,
            'B' => AreaB::class,
            'C' => AreaC::class,
            'D' => AreaD::class,
            'E' => AreaE::class,
            'F' => AreaF::class,
            'G' => AreaG::class,
            'H' => AreaH::class,
        ];

        foreach ($models as $areaName => $model) {
            $records = $model::where('currently', 'yes')
                ->whereNull('deleted_at')
                ->get();

            foreach ($records as $record) {
                $lastCalculated = $record->last_calculated_date ? Carbon::parse($record->last_calculated_date) : Carbon::parse($record->init_date);
                
                $newHours = 0;
                if ($isWorkingDay && $lastCalculated->lt($today)) {
                    $newHours = $record->schedule + $record->overtime;
                }

                $newValue = $record->value + $newHours;
                $record->update([
                    'value' => $newValue,
                    'last_calculated_date' => $today,
                ]);

                $areaColumn = 'area_' . strtolower($areaName) . '_id';
                $areaRecord = AreaRecord::where($areaColumn, $record->id)
                    ->whereNull('deleted_at')
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($areaRecord) {
                    $areaRecord->update([
                        'value' => $newValue,
                        'puntuacion_1' => $areaRecord->status === '1' ? $newValue : $areaRecord->puntuacion_1,
                        'puntuacion_2' => $areaRecord->status === '2' ? $newValue : $areaRecord->puntuacion_2,
                    ]);
                }

                Log::info('Updated value for record:', [
                    'area' => $areaName,
                    'record_id' => $record->id,
                    'new_value' => $newValue,
                ]);
            }
        }

        $this->info('Values recalculated successfully.');
    }
}
