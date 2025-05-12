<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Belonging;
use App\Models\AreaRecord;
use App\Models\Paises;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use function App\Helpers\mapArea;

class BelongingAreaController extends Controller
{
    public function records(Request $request, $belongingId, $areaName)
    {
        $user = Auth::user();
        if (!$user) {
            Log::warning('Intento de acceso no autorizado a registros', [
                'belonging_id' => $belongingId,
                'area_name' => $areaName,
            ]);
            return Inertia::render('Error', [
                'message' => 'No estás autorizado para acceder a esta página.',
                'status' => 401,
            ]);
        }

        try {
            // Mapear area_name a recordable_type
            $areaMap = [
                'A' => 'App\\Models\\AreaA',
                'B' => 'App\\Models\\AreaB',
                'C' => 'App\\Models\\AreaC',
                'D' => 'App\\Models\\AreaD',
                'E' => 'App\\Models\\AreaE',
                'F' => 'App\\Models\\AreaF',
                'G' => 'App\\Models\\AreaG',
                'H' => 'App\\Models\\AreaH',
            ];

            if (!isset($areaMap[$areaName])) {
                Log::error('Área no válida', ['area_name' => $areaName]);
                return Inertia::render('Error', [
                    'message' => 'El área especificada no es válida.',
                    'status' => 400,
                ]);
            }

            $recordableType = $areaMap[$areaName];
            $hasCategory = $areaName === 'A'; // Solo Área A tiene categorías

            // Obtener registros con relaciones
            $perPage = $request->input('per_page', 15);
            $recordsQuery = AreaRecord::with(['belonging', 'pais', 'recordable' . ($hasCategory ? '.category' : '')])
                ->where('user_id', $user->id)
                ->where('belonging_id', $belongingId)
                ->where('recordable_type', $recordableType)
                ->whereNull('deleted_at');

            // Aplicar ordenamiento
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $recordsQuery->orderBy($sortBy, $sortOrder);

            $records = $recordsQuery->paginate($perPage)->through(function ($record) use ($hasCategory) {
                $categoryName = null;
                if ($hasCategory) {
                    try {
                        $categoryName = $record->recordable && $record->recordable->category ? $record->recordable->category->name : null;
                    } catch (\Exception $e) {
                        Log::warning('No se pudo cargar la relación category', [
                            'record_id' => $record->id,
                            'recordable_type' => $record->recordable_type,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                return [
                    'id' => $record->recordable_id,
                    'category_name' => $categoryName,
                    'name' => $record->recordable ? $record->recordable->name : null,
                    'init_date' => $record->recordable ? $record->recordable->init_date : null,
                    'end_date' => $record->recordable ? $record->recordable->end_date : null,
                    'currently' => $record->recordable ? $record->recordable->currently : 'no',
                    'value' => $record->value,
                    'status' => $record->status,
                    'pais_name' => $record->pais ? $record->pais->name : null,
                ];
            });

            // Calcular totales
            $totales = AreaRecord::where('user_id', $user->id)
                ->where('belonging_id', $belongingId)
                ->where('recordable_type', $recordableType)
                ->whereNull('deleted_at')
                ->selectRaw('
                    SUM(CASE WHEN status = "1" THEN COALESCE(value, 0) ELSE 0 END) as total_propuestos,
                    SUM(CASE WHEN status = "2" THEN COALESCE(value, 0) ELSE 0 END) as total_verificados,
                    SUM(COALESCE(value, 0)) as total
                ')
                ->first();

            // Obtener belonging_name
            $belonging = Belonging::find($belongingId);
            $belongingName = $belonging ? $belonging->name : 'Desconocido';

            $props = [
                'records' => $records,
                'totales' => [
                    'total_propuestos' => (int) ($totales->total_propuestos ?? 0),
                    'total_verificados' => (int) ($totales->total_verificados ?? 0),
                    'total' => (int) ($totales->total ?? 0),
                ],
                'belonging_id' => (int) $belongingId,
                'belonging_name' => $belongingName,
                'area_name' => $areaName,
                'area_display_name' => mapArea($areaName), // Nombre mapeado
                'has_category' => $hasCategory,
                'paises' => Paises::all()->toArray(),
            ];

            Log::debug('Registros obtenidos:', $props);

            return Inertia::render('Skyfall/BelongingAreaList', $props);
        } catch (\Exception $e) {
            Log::error('Error fetching records', [
                'belonging_id' => $belongingId,
                'area_name' => $areaName,
                'error' => $e->getMessage(),
            ]);
            return Inertia::render('Error', [
                'message' => 'Ocurrió un error al cargar los registros. Por favor, intenta de nuevo.',
                'status' => 500,
            ]);
        }
    }
}
