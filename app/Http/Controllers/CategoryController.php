<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Paises;
use App\Http\Requests\CategoryRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    const NUMBER_OF_ITEMS_PER_PAGE = 8;

    public function index()
    {
        $categories = Categoria::with('pais')->paginate(self::NUMBER_OF_ITEMS_PER_PAGE);
        return Inertia::render('Categories/Index', ['categories' => $categories]);
    }

    public function create()
    {
        $paises = Paises::all();
        return Inertia::render('Categories/Create', ['paises' => $paises]);
    }

    public function store(CategoryRequest $request)
    {
        try {
            $validatedData = $request->validated();
            Categoria::create($validatedData);
            return redirect()->route('categories.index')->with('success', __('Category created successfully'));
        } catch (\Exception $e) {
            Log::error('Error creating category:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while creating the category')]);
        }
    }

    public function show(Categoria $category)
    {
        // Si necesitas mostrar detalles individuales, implementa esto
        // Por ejemplo: return Inertia::render('Admin/Categories/Show', ['category' => $category]);
        // Si no, puedes dejarlo vacío o redireccionar al index
        return redirect()->route('categories.index');
    }

    public function edit(Categoria $category)
    {
        $paises = Paises::all();
        return Inertia::render('Categories/Edit', ['category' => $category, 'paises' => $paises]);
    }

    public function update(CategoryRequest $request, Categoria $category)
    {
        try {
            $validatedData = $request->validated();
            $category->update($validatedData);
            return redirect()->route('categories.index')->with('success', __('Category updated successfully'));
        } catch (\Exception $e) {
            Log::error('Error updating category:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while updating the category')]);
        }
    }

    public function destroy(Categoria $category)
    {
        try {
            $category->delete();
            return redirect()->route('categories.index')->with('success', __('Category deleted successfully'));
        } catch (\Exception $e) {
            Log::error('Error deleting category:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $category->id,
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while deleting the category')]);
        }
    }
}
