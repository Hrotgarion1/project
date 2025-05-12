<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AreaRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use function App\Helpers\mapArea;

class AreaGlobalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            Log::warning('Intento de acceso no autorizado a AreaGlobal');
            return redirect('/login');
        }

        Log::debug('Cargando AreaGlobal para usuario', ['user_id' => $user->id]);

        $sortBy = $request->input('sort_by', 'belonging_name');
        $sortOrder = $request->input('sort_order', 'asc');

        Log::debug('Parámetros recibidos', [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ]);

        $sortColumn = match ($sortBy) {
            'pais_name' => 'paises.name',
            'belonging_name' => 'belongings.name',
            'total_propuestos' => 'total_propuestos',
            'total_verificados' => 'total_verificados',
            'totales' => 'totales',
            default => 'belongings.name',
        };

        $query = AreaRecord::selectRaw('
                area_records.belonging_id,
                area_records.pais_id,
                paises.name as pais_name,
                belongings.name as belonging_name,
                SUM(CASE WHEN area_records.status = 1 THEN area_records.value ELSE 0 END) as total_propuestos,
                SUM(CASE WHEN area_records.status = 2 THEN area_records.value ELSE 0 END) as total_verificados,
                SUM(area_records.value) as totales
            ')
            ->where('area_records.user_id', $user->id)
            ->whereNull('area_records.deleted_at')
            ->groupBy('area_records.belonging_id', 'area_records.pais_id', 'paises.name', 'belongings.name')
            ->join('belongings', 'area_records.belonging_id', '=', 'belongings.id')
            ->leftJoin('paises', 'area_records.pais_id', '=', 'paises.id');

        $sumasQuery = AreaRecord::selectRaw('
                SUM(CASE WHEN area_records.status = 1 THEN area_records.value ELSE 0 END) as total_propuestos,
                SUM(CASE WHEN area_records.status = 2 THEN area_records.value ELSE 0 END) as total_verificados,
                SUM(area_records.value) as totales
            ')
            ->where('area_records.user_id', $user->id)
            ->whereNull('area_records.deleted_at')
            ->first();

        $sumas = [
            'total_propuestos' => (int) ($sumasQuery->total_propuestos ?? 0),
            'total_verificados' => (int) ($sumasQuery->total_verificados ?? 0),
            'totales' => (int) ($sumasQuery->totales ?? 0),
        ];

        Log::debug('Sumas calculadas', $sumas);

        if (in_array($sortBy, ['total_propuestos', 'total_verificados', 'totales'])) {
            $query->orderByRaw("$sortColumn $sortOrder");
        } else {
            $query->orderBy($sortColumn, $sortOrder);
        }

        $resumen = $query->get()->map(function ($item) {
            Log::debug('Procesando item de resumen', [
                'belonging_id' => $item->belonging_id,
                'pais_id' => $item->pais_id,
                'pais_name' => $item->pais_name,
                'belonging_name' => $item->belonging_name,
                'total_propuestos' => $item->total_propuestos,
                'total_verificados' => $item->total_verificados,
                'totales' => $item->totales,
            ]);
            return [
                'belonging_id' => $item->belonging_id,
                'belonging_name' => $item->belonging_name ?? 'Sin belonging',
                'pais_name' => $item->pais_name ?? 'Sin país',
                'total_propuestos' => (int) ($item->total_propuestos ?? 0),
                'total_verificados' => (int) ($item->total_verificados ?? 0),
                'totales' => (int) ($item->totales ?? 0),
            ];
        });

        Log::debug('Resumen completo', ['resumen' => $resumen->toArray()]);

        return Inertia::render('Skyfall/AreaGlobal', [
            'resumen' => ['data' => $resumen],
            'sumas' => $sumas,
        ]);
    }

    public function getSumas(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            Log::warning('Intento de acceso no autorizado a getSumas');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $belongingId = $request->input('belonging_id');

        $globalQuery = AreaRecord::selectRaw('
                SUM(CASE WHEN area_records.status = 1 THEN area_records.value ELSE 0 END) as total_propuestos,
                SUM(CASE WHEN area_records.status = 2 THEN area_records.value ELSE 0 END) as total_verificados,
                SUM(area_records.value) as totales
            ')
            ->where('area_records.user_id', $user->id)
            ->whereNull('area_records.deleted_at');

        $belongingQuery = AreaRecord::selectRaw('
                area_records.belonging_id,
                SUM(CASE WHEN area_records.status = 1 THEN area_records.value ELSE 0 END) as total_propuestos,
                SUM(CASE WHEN area_records.status = 2 THEN area_records.value ELSE 0 END) as total_verificados
            ')
            ->where('area_records.user_id', $user->id)
            ->whereNull('area_records.deleted_at')
            ->groupBy('area_records.belonging_id');

        if ($belongingId !== null) {
            $globalQuery->where('area_records.belonging_id', $belongingId);
            $belongingQuery->where('area_records.belonging_id', $belongingId);
        }

        $sumas = [
            'global' => [
                'total_propuestos' => (int) ($globalQuery->first()->total_propuestos ?? 0),
                'total_verificados' => (int) ($globalQuery->first()->total_verificados ?? 0),
                'totales' => (int) ($globalQuery->first()->totales ?? 0),
            ],
            'by_belonging' => $belongingQuery->get()->keyBy('belonging_id')->map(function ($item) {
                return [
                    'total_propuestos' => (int) ($item->total_propuestos ?? 0),
                    'total_verificados' => (int) ($item->total_verificados ?? 0),
                ];
            })->toArray(),
        ];

        Log::debug('Sumas devueltas por API', ['sumas' => $sumas]);

        return response()->json($sumas);
    }

    public function getAreas(Request $request, $belongingId)
    {
        $user = Auth::user();
        if (!$user) {
            Log::warning('Intento de acceso no autorizado a getAreas', ['belonging_id' => $belongingId]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Fetching areas for belonging_id', ['belonging_id' => $belongingId, 'user_id' => $user->id]);

        try {
            // Consultar area_records para obtener totales por área
            $areas = AreaRecord::selectRaw("
                CASE
                    WHEN TRIM(recordable_type) = ? THEN 'A'
                    WHEN TRIM(recordable_type) = ? THEN 'B'
                    WHEN TRIM(recordable_type) = ? THEN 'C'
                    WHEN TRIM(recordable_type) = ? THEN 'D'
                    WHEN TRIM(recordable_type) = ? THEN 'E'
                    WHEN TRIM(recordable_type) = ? THEN 'F'
                    WHEN TRIM(recordable_type) = ? THEN 'G'
                    WHEN TRIM(recordable_type) = ? THEN 'H'
                    ELSE NULL
                END as name,
                SUM(CASE WHEN status = '1' THEN COALESCE(value, 0) ELSE 0 END) as total_propuestos,
                SUM(CASE WHEN status = '2' THEN COALESCE(value, 0) ELSE 0 END) as total_verificados,
                SUM(COALESCE(value, 0)) as totales
            ", [
                'App\\Models\\AreaA',
                'App\\Models\\AreaB',
                'App\\Models\\AreaC',
                'App\\Models\\AreaD',
                'App\\Models\\AreaE',
                'App\\Models\\AreaF',
                'App\\Models\\AreaG',
                'App\\Models\\AreaH',
            ])
                ->where('user_id', $user->id)
                ->where('belonging_id', $belongingId)
                ->whereNull('deleted_at')
                ->groupBy('recordable_type')
                ->get();

            Log::debug('Raw areas from query', [
                'belonging_id' => $belongingId,
                'areas' => $areas->toArray(),
            ]);

            // Mapear resultados al formato esperado
            $data = $areas->map(function ($item) {
                return [
                    'name' => $item->name,
                    'area_display_name' => mapArea($item->name), // Añadimos el nombre traducido
                    'total_propuestos' => (int) $item->total_propuestos,
                    'total_verificados' => (int) $item->total_verificados,
                    'totales' => (int) $item->totales,
                ];
            })->filter(function ($item) {
                return $item['name'] !== null && $item['totales'] > 0; // Filtrar áreas sin nombre o sin registros
            })->values();

            Log::debug('Areas processed', [
                'belonging_id' => $belongingId,
                'areas' => $data->toArray(),
            ]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error fetching areas for belonging', [
                'belonging_id' => $belongingId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
