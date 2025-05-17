<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AreaRecord;
use App\Models\Paises;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use function App\Helpers\mapArea;

class AbstractAreaController extends Controller
{
    use AreaControllerTrait;

    protected $modelClass;
    protected $areaName;
    protected $routePrefix;

    public function __construct()
    {
        if (!isset($this->modelClass, $this->areaName, $this->routePrefix)) {
            throw new \Exception('modelClass, areaName, and routePrefix must be defined in child class');
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $user = auth()->user();
    if (!$user) {
        return redirect('/login');
    }

    $area = Area::where('name', $this->areaName)->firstOrFail();

    // Validar area_id
    $expectedAreaIds = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4,
        'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8,
    ];

    if ($area->id !== $expectedAreaIds[$this->areaName]) {
        Log::error("Inconsistencia en area_id para {$this->areaName}", [
            'expected_id' => $expectedAreaIds[$this->areaName],
            'actual_id' => $area->id,
        ]);
        return redirect()->route('dashboard')->with('error', __('Invalid area configuration'));
    }

    $perPage = $request->input('per_page', 3);
    $sortBy = $request->input('sort_by', 'init_date');
    $sortOrder = $request->input('sort_order', 'asc');

    // Validar sort_by
    $sortableColumns = ['belonging_name', 'init_date', 'end_date', 'name', 'value', 'status'];
    if (!in_array($sortBy, $sortableColumns)) {
        $sortBy = 'init_date';
    }
    if (!in_array($sortOrder, ['asc', 'desc'])) {
        $sortOrder = 'asc';
    }

    // Construir la consulta base
    $query = $this->modelClass::with(['area', 'records' => function ($query) use ($user) {
        $query->with(['belonging', 'pais'])
              ->where('user_id', $user->id)
              ->whereNull('deleted_at')
              ->orderBy('updated_at', 'desc');
    }])
        ->where('area_id', $area->id)
        ->where('user_id', $user->id)
        ->whereNull('deleted_at');

    // Manejar ordenación
    if ($sortBy === 'belonging_name') {
        $query->join('area_records', function ($join) {
            $join->on('area_records.recordable_id', '=', "{$this->modelClass->getTable()}.id")
                 ->where('area_records.recordable_type', $this->modelClass)
                 ->whereNull('area_records.deleted_at');
        })
        ->join('belongings', 'area_records.belonging_id', '=', 'belongings.id')
        ->orderBy('belongings.name', $sortOrder)
        ->select("{$this->modelClass->getTable()}.*");
    } elseif ($sortBy === 'status' || $sortBy === 'value') {
        $query->join('area_records', function ($join) {
            $join->on('area_records.recordable_id', '=', "{$this->modelClass->getTable()}.id")
                 ->where('area_records.recordable_type', $this->modelClass)
                 ->whereNull('area_records.deleted_at');
        })
        ->orderBy("area_records.{$sortBy}", $sortOrder)
        ->select("{$this->modelClass->getTable()}.*");
    } else {
        $query->orderBy($sortBy, $sortOrder);
    }

    // Ejecutar la consulta con paginación
    $definiciones = $query->paginate($perPage);

    // Mapear los resultados
    $mappedDefiniciones = $definiciones->getCollection()->map(function ($definicion) {
        $latestRecord = $definicion->records->first();

        // Log para depuración
        Log::debug('Mapeando definición', [
            'definicion_id' => $definicion->id,
            'name' => $definicion->name,
            'has_record' => !empty($latestRecord),
            'record_status' => $latestRecord ? $latestRecord->status : null,
        ]);

        if (!$latestRecord) {
        return null; // O asignar status por defecto: 'status' => 1
        }

        return [
            'id' => $definicion->id,
            'area_id' => $definicion->area_id,
            'name' => $definicion->name,
            'description' => $definicion->description,
            'init_date' => $definicion->init_date ? $definicion->init_date->toDateString() : null,
            'end_date' => $definicion->end_date ? $definicion->end_date->toDateString() : null,
            'schedule' => $definicion->schedule,
            'overtime' => $definicion->overtime,
            'currently' => $definicion->currently,
            'value' => $latestRecord ? (int) $latestRecord->value : (int) $definicion->value,
            'status' => $latestRecord ? $latestRecord->status : null,
            'belonging_id' => $latestRecord ? $latestRecord->belonging_id : null,
            'pais_id' => $latestRecord ? $latestRecord->pais_id : null,
            'details' => $definicion->details,
            'area' => $definicion->area ? $definicion->area->toArray() : null,
            'area_display_name' => mapArea($this->areaName),
            'belonging' => $latestRecord ? $latestRecord->belonging : null,
            'belonging_name' => $latestRecord && $latestRecord->belonging ? $latestRecord->belonging->name : null,
            'pais' => $latestRecord ? $latestRecord->pais : null,
            'category' => $definicion->category ? [
                'id' => $definicion->category->id,
                'name' => $definicion->category->name,
                'value' => (int) $definicion->category->value,
                'pais_id' => $definicion->category->pais_id,
            ] : null,
        ];
    })->filter()->values(); // Filtrar registros nulos y reindexar

    // Actualizar la colección paginada
    $definiciones->setCollection(collect($mappedDefiniciones));

    // Calcular totales
    $records = AreaRecord::where('recordable_type', $this->modelClass)
        ->where('user_id', $user->id)
        ->whereNull('deleted_at')
        ->get();

    $totalPropuestos = $records->where('status', '1')->sum('puntuacion_1');
    $totalVerificados = $records->where('status', '2')->sum('puntuacion_2');
    $total = $totalPropuestos + $totalVerificados;

    // Construir props para la vista
    $props = [
        'paises' => Paises::all(['id', 'name']),
        'categorias' => $this->areaName === 'A' ? Categoria::all(['id', 'name', 'value']) : [],
        'festivos' => $this->getFestivosNacionales(),
        'area_name' => $this->areaName,
        'area_display_name' => mapArea($this->areaName),
        'area_id' => $area->id,
        'definiciones' => [
            'data' => $mappedDefiniciones,
            'current_page' => $definiciones->currentPage(),
            'last_page' => $definiciones->lastPage(),
            'per_page' => (int) $perPage,
            'total' => $definiciones->total(),
            'from' => $definiciones->firstItem() ?? 0, // Incluir from
            'to' => $definiciones->lastItem() ?? 0,   // Incluir to
        ],
        'totales' => [
            'propuestos' => (int) $totalPropuestos,
            'verificados' => (int) $totalVerificados,
            'total' => (int) $total,
        ],
    ];

    // Log para depuración
    Log::debug('Props enviadas a AreaList', [
        'area_name' => $this->areaName,
        'definiciones_count' => count($mappedDefiniciones),
        'pagination' => [
            'current_page' => $props['definiciones']['current_page'],
            'last_page' => $props['definiciones']['last_page'],
            'per_page' => $props['definiciones']['per_page'],
            'total' => $props['definiciones']['total'],
            'from' => $props['definiciones']['from'],
            'to' => $props['definiciones']['to'],
        ],
    ]);

    if ($request->wantsJson()) {
        return response()->json($props);
    }

    return Inertia::render('Skyfall/AreaList', $props);
}

    public function create()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        $area = Area::where('name', $this->areaName)->firstOrFail();
        return Inertia::render('Skyfall/AreaForm', [
            'paises' => Paises::all(['id', 'name']),
            'categorias' => $this->areaName === 'A' ? Categoria::all(['id', 'name', 'value']) : [],
            'festivos' => $this->getFestivosNacionales(),
            'area_name' => $this->areaName,
            'area_display_name' => mapArea($this->areaName), // Nombre mapeado
            'area_id' => $area->id,
            'belonging_name' => $area->belonging ? $area->belonging->name : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        try {
            $validationRules = [
                'area_id' => 'required|exists:areas,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'init_date' => 'required|date|before_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:init_date|before_or_equal:today',
                'currently' => 'required|in:yes,no',
                'belonging_id' => 'required|exists:belongings,id',
                'pais_id' => 'required|exists:paises,id',
                'details' => 'nullable|string|max:1000',
            ];

            if ($this->areaName === 'A') {
                $validationRules['category_id'] = 'required|exists:categorias,id';
                $validationRules['schedule'] = [
                    'integer',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->currently === 'yes' && ($value < 1 || $value > 8)) {
                            $fail(__('validation.between.numeric', ['attribute' => $attribute, 'min' => 1, 'max' => 8]));
                        }
                        if ($request->currently === 'no' && $value !== 0) {
                            $fail(__('validation.in', ['attribute' => $attribute, 'values' => '0']));
                        }
                    },
                ];
                $validationRules['value'] = 'required_if:currently,yes|numeric';
            } else {
                $validationRules['end_date'] = 'required_if:currently,no|nullable|date|after_or_equal:init_date|before_or_equal:today';
                $validationRules['schedule'] = 'required|integer|between:1,8';
                $validationRules['overtime'] = 'required|integer|min:0|max:4';
                $validationRules['value'] = 'required|numeric';
            }

            $validated = $request->validate($validationRules);

            $validated['user_id'] = $user->id;

            if ($this->areaName === 'A' && isset($validated['category_id'])) {
                $category = Categoria::findOrFail($validated['category_id']);
                if ($validated['currently'] === 'no') {
                    $validated['value'] = $category->value;
                    $validated['schedule'] = $validated['schedule'] ?? 0;
                } else {
                    $validated['value'] = $validated['value'] ?? $this->calculateValue($validated);
                }
            } else {
                $validated['value'] = $validated['value'] ?? $this->calculateValue($validated);
            }

            $areaData = array_diff_key($validated, array_flip(['belonging_id', 'pais_id']));
            if ($this->areaName !== 'A') {
                unset($areaData['category_id']);
            }

            $record = $this->modelClass::create($areaData);

            AreaRecord::create([
                'recordable_id' => $record->id,
                'recordable_type' => $this->modelClass,
                'user_id' => $user->id,
                'belonging_id' => $validated['belonging_id'],
                'pais_id' => $validated['pais_id'],
                'status' => 1,
                'value' => $validated['value'],
                'puntuacion_1' => $validated['value'],
                'puntuacion_2' => 0,
                'puntuacion_3' => 0,
            ]);

            return redirect()->route("{$this->routePrefix}.index")->with('success', __('record_created'));
        } catch (ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al crear registro:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['general' => __('error_creating_record')])->withInput();
        }
    }

    public function show(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        Log::debug('Attempting to show record:', [
            'area_name' => $this->areaName,
            'id' => $id,
            'modelClass' => $this->modelClass,
            'user_id' => $user->id,
        ]);

        // Definir relaciones a cargar, incluyendo 'category' solo para Área A
        $relations = ['recordable.area', 'belonging', 'pais'];
        if ($this->areaName === 'A') {
            $relations[] = 'recordable.category';
        }

        // Buscar el AreaRecord usando recordable_id y recordable_type
        $areaRecord = AreaRecord::with($relations)
            ->where('recordable_id', $id)
            ->where('recordable_type', $this->modelClass)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$areaRecord || !$areaRecord->recordable) {
            $availableIds = AreaRecord::where('recordable_type', $this->modelClass)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->pluck('recordable_id')
                ->toArray();
            Log::error('Record not found:', [
                'area_name' => $this->areaName,
                'id' => $id,
                'available_ids' => $availableIds,
            ]);
            return Inertia::render('Error', [
                'message' => __('Record not found'),
                'status' => 404,
            ]);
        }

        $record = $areaRecord->recordable;

        // Preparar datos para definicion
        $definicion = [
            'id' => $record->id,
            'area_id' => $record->area_id,
            'name' => $record->name,
            'description' => $record->description,
            'init_date' => $record->init_date ? $record->init_date->toDateString() : null,
            'end_date' => $record->end_date ? $record->end_date->toDateString() : null,
            'schedule' => $record->schedule,
            'overtime' => $record->overtime,
            'currently' => $record->currently,
            'details' => $record->details,
            'value' => (int) $areaRecord->value,
            'status' => $areaRecord->status,
            'belonging_id' => $areaRecord->belonging_id,
            'pais_id' => $areaRecord->pais_id,
            'belonging' => $areaRecord->belonging ? $areaRecord->belonging->toArray() : null,
            'belonging_name' => $areaRecord->belonging ? $areaRecord->belonging->name : null,
            'pais' => $areaRecord->pais ? $areaRecord->pais->toArray() : null,
            'area' => $record->area ? $record->area->toArray() : null,
            'area_display_name' => mapArea($this->areaName), // Nombre mapeado
            'category' => $this->areaName === 'A' && $record->category ? [
                'id' => $record->category->id,
                'name' => $record->category->name,
                'value' => (int) $record->category->value,
                'pais_id' => $record->category->pais_id,
            ] : null,
        ];

        Log::debug('Record found:', [
            'area_name' => $this->areaName,
            'id' => $id,
            'record_data' => $definicion,
        ]);

        $props = [
            'area_name' => $this->areaName,
            'area_display_name' => mapArea($this->areaName), // Nombre mapeado
            'area_id' => $record->area_id,
            'definicion' => $definicion,
            'paises' => Paises::all(['id', 'name']),
            'categorias' => $this->areaName === 'A' ? Categoria::all(['id', 'name', 'value']) : [],
            'festivos' => $this->getFestivosNacionales(),
            'belonging_name' => $areaRecord->belonging ? $areaRecord->belonging->name : null,
        ];

        return Inertia::render('Skyfall/AreaDetail', $props);
    }

    public function edit(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        $record = $this->modelClass::with(['area', 'records' => function ($query) {
            $query->with(['belonging', 'pais'])->orderBy('updated_at', 'desc');
        }])
            ->whereNull('deleted_at')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $latestRecord = $record->records->first();

        if ($latestRecord && $latestRecord->status === 2 ) {
            return redirect()->route("{$this->routePrefix}.index")->with('error', __('Cannot edit verified record'));
        }

        return Inertia::render('Skyfall/AreaForm', [
            'paises' => Paises::all(['id', 'name']),
            'categorias' => $this->areaName === 'A' ? Categoria::all(['id', 'name', 'value']) : [],
            'definicion' => array_merge($record->toArray(), [
                'status' => $latestRecord ? $latestRecord->status : null,
                'value' => $latestRecord ? $latestRecord->value : $record->value,
                'belonging_id' => $latestRecord ? $latestRecord->belonging_id : null,
                'pais_id' => $latestRecord ? $latestRecord->pais_id : null,
                'belonging' => $latestRecord ? $latestRecord->belonging : null,
                'belonging_name' => $latestRecord && $latestRecord->belonging ? $latestRecord->belonging->name : null,
                'pais' => $latestRecord ? $latestRecord->pais : null,
            ]),
            'festivos' => $this->getFestivosNacionales(),
            'area_name' => $this->areaName,
            'area_display_name' => mapArea($this->areaName), // Nombre mapeado
            'area_id' => $record->area_id,
            'belonging_name' => $record->area && $record->area->belonging ? $record->area->belonging->name : null,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        try {
            $record = $this->modelClass::whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->findOrFail($id);

            $latestAreaRecord = AreaRecord::where('recordable_id', $record->id)
                ->where('recordable_type', $this->modelClass)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($latestAreaRecord && $latestAreaRecord->status === 2 ) {
                return redirect()->route("{$this->routePrefix}.index")->with('error', __('cannot_edit_verified_record'));
            }

            $validationRules = [
                'area_id' => 'sometimes|exists:areas,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'init_date' => 'sometimes|date|before_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:init_date|before_or_equal:today',
                'currently' => 'sometimes|in:yes,no',
                'belonging_id' => 'sometimes|exists:belongings,id',
                'pais_id' => 'sometimes|exists:paises,id',
                'details' => 'nullable|string|max:1000',
            ];

            if ($this->areaName === 'A') {
                $validationRules['category_id'] = 'required|exists:categorias,id';
                $validationRules['schedule'] = [
                    'integer',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->currently === 'yes' && ($value < 1 || $value > 8)) {
                            $fail(__('validation.between.numeric', ['attribute' => $attribute, 'min' => 1, 'max' => 8]));
                        }
                        if ($request->currently === 'no' && $value !== 0) {
                            $fail(__('validation.in', ['attribute' => $attribute, 'values' => '0']));
                        }
                    },
                ];
                $validationRules['value'] = 'required_if:currently,yes|numeric';
            } else {
                $validationRules['end_date'] = 'required_if:currently,no|nullable|date|after_or_equal:init_date|before_or_equal:today';
                $validationRules['schedule'] = 'required|integer|between:1,8';
                $validationRules['overtime'] = 'required|integer|min:0|max:4';
                $validationRules['value'] = 'required|numeric';
            }

            $validated = $request->validate($validationRules);

            $data = array_merge([
                'area_id' => $record->area_id,
                'name' => $record->name,
                'description' => $record->description,
                'init_date' => $record->init_date->toDateString(),
                'end_date' => $record->end_date?->toDateString(),
                'schedule' => $record->schedule,
                'overtime' => $record->overtime ?? 0,
                'currently' => $record->currently,
                'details' => $record->details,
                'value' => $record->value,
            ], $validated);

            if ($this->areaName === 'A' && isset($data['category_id'])) {
                $category = Categoria::findOrFail($data['category_id']);
                if ($data['currently'] === 'no') {
                    $data['value'] = $category->value;
                    $data['schedule'] = $data['schedule'] ?? 0;
                } else {
                    $data['value'] = $data['value'] ?? $this->calculateValue($data);
                }
            } else {
                $data['value'] = $data['value'] ?? $this->calculateValue($data);
            }

            $areaData = array_diff_key($data, array_flip(['belonging_id', 'pais_id']));
            if ($this->areaName !== 'A') {
                unset($areaData['category_id']);
            }

            $record->update($areaData);

            $areaRecordData = [
                'recordable_id' => $record->id,
                'recordable_type' => $this->modelClass,
                'user_id' => $user->id,
                'belonging_id' => $data['belonging_id'] ?? ($latestAreaRecord ? $latestAreaRecord->belonging_id : null),
                'pais_id' => $data['pais_id'] ?? ($latestAreaRecord ? $latestAreaRecord->pais_id : null),
                'status' => $latestAreaRecord ? $latestAreaRecord->status : 1 ,
                'value' => $data['value'],
                'puntuacion_1' => ($latestAreaRecord && $latestAreaRecord->status === 1 ) ? $data['value'] : 0,
                'puntuacion_2' => ($latestAreaRecord && $latestAreaRecord->status === 2 ) ? $data['value'] : 0,
                'puntuacion_3' => 0,
            ];

            if ($latestAreaRecord) {
                $latestAreaRecord->update($areaRecordData);
            } else {
                AreaRecord::create($areaRecordData);
            }

            return redirect()->route("{$this->routePrefix}.index")->with('success', __('record_updated'));
        } catch (ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al actualizar registro:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['general' => __('error_updating_record')])->withInput();
        }
    }

    protected function calculateValue(array $data)
    {
        // Implementación específica para calcular el valor según las reglas del área
        // Este método debe ser implementado en las clases concretas o en un trait
        return 0; // Placeholder
    }

    public function verify(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        try {
            $record = $this->modelClass::whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->findOrFail($id);

            $areaRecord = AreaRecord::where('recordable_id', $record->id)
                ->where('recordable_type', $this->modelClass)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'desc')
                ->firstOrFail();

            if ($areaRecord->status === 2 ) {
                return redirect()->route("{$this->routePrefix}.index")->with('error', __('Record is already verified'));
            }

            $areaRecord->update([
                'status' => 2 ,
                'puntuacion_2' => $areaRecord->puntuacion_1,
                'puntuacion_1' => 0,
                'puntuacion_3' => 0,
            ]);

            return redirect()->route("{$this->routePrefix}.index")->with('success', __('Record verified'));
        } catch (\Exception $e) {
            Log::error('Error al verificar registro:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['general' => __('Error verifying record')]);
        }
    }

    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        try {
            $record = $this->modelClass::whereNull('deleted_at')
                ->where('user_id', $user->id)
                ->findOrFail($id);

            AreaRecord::where('recordable_id', $record->id)
                ->where('recordable_type', $this->modelClass)
                ->delete();
            $record->delete();

            return redirect()->route("{$this->routePrefix}.index")->with('success', __('Deleted successfully'));
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['general' => __('Error deleting record')]);
        }
    }

    public function getAreaData()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $area = Area::where('name', $this->areaName)->firstOrFail();

        $definiciones = $this->modelClass::with(['area', 'records' => function ($query) {
            $query->with(['belonging', 'pais'])->orderBy('updated_at', 'desc');
        }])
            ->where('area_id', $area->id)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('init_date', 'asc')
            ->get()
            ->map(function ($definicion) {
                $latestRecord = $definicion->records->first();
                if (!$latestRecord) {
            return null; // Excluir definiciones sin AreaRecord
            }
                return [
                    'id' => $definicion->id,
                    'area_id' => $definicion->area_id,
                    'name' => $definicion->name,
                    'description' => $definicion->description,
                    'init_date' => $definicion->init_date,
                    'end_date' => $definicion->end_date,
                    'schedule' => $definicion->schedule,
                    'overtime' => $definicion->overtime,
                    'currently' => $definicion->currently,
                    'value' => $latestRecord ? (int) $latestRecord->value : (int) $definicion->value,
                    'status' => $latestRecord ? $latestRecord->status : null,
                    'belonging_id' => $latestRecord ? $latestRecord->belonging_id : null,
                    'pais_id' => $latestRecord ? $latestRecord->pais_id : null,
                    'details' => $definicion->details,
                    'area' => $definicion->area,
                    'area_display_name' => mapArea($this->areaName), // Nombre mapeado
                    'belonging' => $latestRecord ? $latestRecord->belonging : null,
                    'belonging_name' => $latestRecord && $latestRecord->belonging ? $latestRecord->belonging->name : null,
                    'pais' => $latestRecord ? $latestRecord->pais : null,
                    'category' => $definicion->category ?? null,
                ];
            })->filter()->values();

        $records = AreaRecord::where('recordable_type', $this->modelClass)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->get();

        $totalPropuestos = $records->where('status', 1 )->sum('puntuacion_1');
        $totalVerificados = $records->where('status', 2 )->sum('puntuacion_2');
        $total = $totalPropuestos + $totalVerificados;

        return response()->json([
            'definiciones' => $definiciones,
            'totales' => [
                'propuestos' => (int) $totalPropuestos,
                'verificados' => (int) $totalVerificados,
                'total' => (int) $total,
            ],
            'belonging_name' => $area->belonging ? $area->belonging->name : null,
            'area_name' => $this->areaName,
            'area_display_name' => mapArea($this->areaName), // Nombre mapeado
        ]);
    }
}