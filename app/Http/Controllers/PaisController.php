<?php

namespace App\Http\Controllers;

use App\Models\Paises;
use App\Http\Requests\PaisRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PaisController extends Controller
{
    const NUMBER_OF_ITEMS_PER_PAGE = 8;

    public function index()
    {
        $paises = Paises::paginate(self::NUMBER_OF_ITEMS_PER_PAGE);
        return Inertia::render('Admin/Countries/Index', ['paises' => $paises]);
    }

    public function create()
    {
        return Inertia::render('Admin/Countries/Create');
    }

    public function store(PaisRequest $request)
    {
        try {
            $validatedData = $request->validated();
            Paises::create($validatedData);
            return redirect()->route('paises.index')->with('success', __('Country created successfully'));
        } catch (\Exception $e) {
            Log::error('Error creating country:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while creating the country')]);
        }
    }

    public function show(Paises $pais)
    {
        // Si necesitas mostrar detalles individuales, implementa esto
        // Por ejemplo: return Inertia::render('Admin/Countries/Show', ['pais' => $pais]);
        // Si no, puedes dejarlo vacío o redireccionar al index
        return redirect()->route('paises.index');
    }

    public function edit(Paises $pais)
    {
        return Inertia::render('Admin/Countries/Edit', ['pais' => $pais]);
    }

    public function update(PaisRequest $request, Paises $pais)
    {
        try {
            $validatedData = $request->validated();
            $pais->update($validatedData);
            return redirect()->route('paises.index')->with('success', __('Country updated successfully'));
        } catch (\Exception $e) {
            Log::error('Error updating country:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while updating the country')]);
        }
    }

    public function destroy(Paises $pais)
    {
        try {
            $pais->delete();
            return redirect()->route('paises.index')->with('success', __('Country deleted successfully'));
        } catch (\Exception $e) {
            Log::error('Error deleting country:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $pais->id,
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while deleting the country')]);
        }
    }

    public function getCountries()
    {
        return Paises::all();
    }
}
