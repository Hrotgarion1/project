<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentityType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WebIdentityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', IdentityType::class);

        $types = IdentityType::all();

        return Inertia::render('IdentityTypes/Index', [
            'identityTypes' => $types,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', IdentityType::class);

        return Inertia::render('IdentityTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', IdentityType::class);

        Log::info('Datos recibidos en store:', $request->all());

        $validated = $request->validate([
            'type' => 'required|string|max:255|unique:identity_types,type',
            'terms_and_conditions' => 'required|string',
            'required_documents' => 'required|array|min:1',
            'required_documents.*.name' => 'required|string|max:255',
            'required_documents.*.type' => 'required|in:pdf,image,text',
            'required_documents.*.description' => 'nullable|string|max:500',
            'required_documents.*.sample' => 'nullable|file|mimes:pdf,jpeg,png,txt|max:2048',
        ]);

        $data = $request->only(['type', 'terms_and_conditions', 'required_documents']);
        
        foreach ($data['required_documents'] as $index => $doc) {
            if ($request->hasFile("required_documents.{$index}.sample")) {
                $path = $request->file("required_documents.{$index}.sample")->store('sample-documents', 'public');
                $data['required_documents'][$index]['sample_path'] = $path;
            } else {
                $data['required_documents'][$index]['sample_path'] = null;
            }
        }

        $identityType = IdentityType::create($data);

        // Respuesta para Inertia
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tipo de identidad creado exitosamente',
                'redirect' => route('identity-types.index'),
            ]);
        }

        // Respuesta para solicitudes tradicionales (si es necesario)
        return redirect()->route('identity-types.index')->with('success', 'Tipo de identidad creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IdentityType $identityType)
    {
        $this->authorize('update', $identityType);

        return Inertia::render('IdentityTypes/Edit', [
            'identityType' => $identityType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IdentityType $identityType)
{
    $this->authorize('update', $identityType);

    $validated = $request->validate([
        'type' => 'required|string|max:255|unique:identity_types,type,' . $identityType->id,
        'terms_and_conditions' => 'required|string',
        'required_documents' => 'required|array|min:1',
        'required_documents.*.name' => 'required|string|max:255',
        'required_documents.*.type' => 'required|in:pdf,image,text',
        'required_documents.*.description' => 'nullable|string|max:500',
        'required_documents.*.sample' => 'nullable|file|mimes:pdf,jpeg,png,txt|max:2048',
        'required_documents.*.sample_path' => 'nullable|string',
    ]);

    $data = $request->only(['type', 'terms_and_conditions', 'required_documents']);
    foreach ($data['required_documents'] as $index => $doc) {
        if ($request->hasFile("required_documents.{$index}.sample")) {
            if (isset($identityType->required_documents[$index]['sample_path']) && $identityType->required_documents[$index]['sample_path']) {
                Storage::disk('public')->delete($identityType->required_documents[$index]['sample_path']);
            }
            $path = $request->file("required_documents.{$index}.sample")->store('sample-documents', 'public');
            $data['required_documents'][$index]['sample_path'] = $path;
        } else {
            $data['required_documents'][$index]['sample_path'] = $identityType->required_documents[$index]['sample_path'] ?? null;
        }
    }

    $identityType->update($data);

    // Respuesta para Inertia
    if ($request->wantsJson()) {
        return response()->json([
            'message' => 'Identity type updated successfully',
            'redirect' => route('identity-types.index'),
        ]);
    }

    // Respuesta para solicitudes tradicionales (si es necesario)
    return redirect()->route('identity-types.index')->with('success', 'Identity type updated successfully.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IdentityType $identityType)
    {
        $this->authorize('delete', $identityType);

        foreach ($identityType->required_documents as $doc) {
            if ($doc['sample_path'] ?? false) {
                Storage::disk('public')->delete($doc['sample_path']);
            }
        }
        $identityType->delete();

        return redirect()->route('identity-types.index')->with('success', 'Identity type deleted successfully.');
    }
}
