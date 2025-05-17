<?php

namespace App\Http\Controllers;

use App\Models\AreaRecord;
use App\Models\Belonging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use function App\Helpers\mapArea;

class AreaResumenController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        Log::debug('Cargando AreaResumen para usuario', ['user_id' => $user->id]);

        $sortBy = $request->input('sort_by', 'belonging_name');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 3);

        Log::debug('Parámetros recibidos', [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'per_page' => $perPage,
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
                belongings.name as belonging_name,
                paises.name as pais_name,
                SUM(CASE WHEN area_records.status = 1 THEN area_records.value ELSE 0 END) as total_propuestos,
                SUM(CASE WHEN area_records.status = 2 THEN area_records.value ELSE 0 END) as total_verificados,
                SUM(area_records.value) as totales
            ')
            ->where('area_records.user_id', $user->id)
            ->whereNull('area_records.deleted_at')
            ->groupBy('area_records.belonging_id', 'area_records.pais_id', 'belongings.name', 'paises.name')
            ->join('belongings', 'area_records.belonging_id', '=', 'belongings.id')
            ->leftJoin('paises', 'area_records.pais_id', '=', 'paises.id')
            ->with(['belonging' => function ($query) {
                $query->select('id', 'name');
            }, 'pais' => function ($query) {
                $query->select('id', 'name');
            }]);

        if (in_array($sortBy, ['total_propuestos', 'total_verificados', 'totales'])) {
            $query->orderByRaw("$sortColumn $sortOrder");
        } else {
            $query->orderBy($sortColumn, $sortOrder);
        }

        $resumen = $query->paginate($perPage)->through(function ($item) {
            Log::debug('Procesando item de resumen', [
                'belonging_id' => $item->belonging_id,
                'pais_id' => $item->pais_id,
                'pais_name' => $item->pais_name ?? $item->pais?->name,
                'belonging_name' => $item->belonging_name ?? $item->belonging?->name,
                'total_propuestos' => $item->total_propuestos,
                'total_verificados' => $item->total_verificados,
                'totales' => $item->totales,
            ]);
            return [
                'belonging_id' => $item->belonging_id,
                'belonging_name' => $item->belonging_name ?? $item->belonging?->name ?? 'Sin belonging',
                'pais_name' => $item->pais_name ?? $item->pais?->name ?? 'Sin país',
                'total_propuestos' => (int) $item->total_propuestos,
                'total_verificados' => (int) $item->total_verificados,
                'totales' => (int) $item->totales,
            ];
        });

        Log::debug('Resumen paginado', ['resumen' => $resumen->toArray()]);

        return Inertia::render('Skyfall/AreaResumen', [
            'resumen' => $resumen,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        Log::debug('Cargando detalles de Belonging', ['belonging_id' => $id]);

        $sortBy = $request->input('sort_by', 'area_key');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);

        Log::debug('Parámetros recibidos en BelongingDetail', [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        $sortColumn = match ($sortBy) {
            'area_key' => 'recordable_type',
            'pais_name' => 'paises.name',
            'status' => 'area_records.status',
            'puntuacion_1' => 'area_records.puntuacion_1',
            'puntuacion_2' => 'area_records.puntuacion_2',
            'name' => 'recordable_name', // Usar el nuevo campo
            default => 'recordable_type',
        };

        $query = AreaRecord::where('belonging_id', $id)
            ->where('area_records.user_id', $user->id)
            ->whereNull('area_records.deleted_at')
            ->with(['recordable' => function ($query) {
                $query->select('id', 'name', 'area_id');
            }, 'pais']);

        $rawRecords = $query->get();
        Log::debug('Registros crudos recuperados', [
            'belonging_id' => $id,
            'total_registros' => $rawRecords->count(),
            'registros' => $rawRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'recordable_type' => $record->recordable_type,
                    'recordable_id' => $record->recordable_id,
                    'pais_id' => $record->pais_id,
                    'status' => $record->status,
                    'recordable_name' => $record->recordable_name,
                ];
            })->toArray(),
        ]);

        if ($sortBy === 'area_key') {
            $query->orderBy('recordable_type', $sortOrder);
        } else {
            $query->orderBy($sortColumn, $sortOrder);
        }

        $records = $query->paginate($perPage, ['*'], 'page', $page)->through(function ($record) {
            Log::debug('Procesando registro en BelongingDetail', [
                'record_id' => $record->id,
                'area_key' => $record->area_key,
                'pais_name' => $record->pais?->name,
                'status' => $record->status,
                'puntuacion_1' => $record->puntuacion_1,
                'puntuacion_2' => $record->puntuacion_2,
                'recordable_name' => $record->recordable_name,
            ]);

            return [
                'id' => $record->id,
                'area_key' => $record->area_key,
                'area_display_name' => mapArea($record->area_key),
                'pais_name' => $record->pais?->name ?? 'Sin país',
                'status' => $record->status,
                'puntuacion_1' => $record->puntuacion_1,
                'puntuacion_2' => $record->puntuacion_2,
                'name' => $record->recordable_name ?? 'Sin nombre', // Usar recordable_name
                'recordable_id' => $record->recordable_id,
            ];
        });

        $belonging = Belonging::where('id', $id)->select('id', 'name')->firstOrFail();

        Log::debug('Datos enviados a BelongingDetail', [
            'belonging' => $belonging->toArray(),
            'records' => $records->toArray(),
        ]);

        return Inertia::render('Skyfall/BelongingDetail', [
            'belonging' => [
                'id' => $belonging->id,
                'name' => $belonging->name,
            ],
            'records' => $records,
        ]);
    }
}
