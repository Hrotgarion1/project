<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SummaryArea;

class SummaryAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SummaryArea::with('areas.records')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        return SummaryArea::create($request->only('name'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return SummaryArea::with(['areas' => fn($query) => $query->whereNull('deleted_at')])
            ->whereNull('deleted_at')
            ->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $summaryArea = SummaryArea::whereNull('deleted_at')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $summaryArea->update(array_filter($validated));

        return response()->json($summaryArea->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        SummaryArea::findOrFail($id)->delete();
        return response()->noContent();
    }
}
