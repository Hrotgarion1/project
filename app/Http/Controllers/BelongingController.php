<?php

namespace App\Http\Controllers;

use App\Models\Belonging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class BelongingController extends Controller
{
    public function search(Request $request)
    {
        $countryId = $request->input('country_id');
        $query = $request->input('query', '');

        $cacheKey = "belongings_country_{$countryId}_query_{$query}";

        $belongings = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($countryId, $query) {
            return Belonging::where('country_id', $countryId)
                ->where('name', 'like', "%{$query}%")
                ->take(5)
                ->get(['id', 'name']);
        });

        return response()->json($belongings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('belongings')->where(function ($query) use ($request) {
                    return $query->where('country_id', $request->country_id);
                }),
            ],
            'country_id' => 'required|exists:paises,id',
        ]);

        $belonging = Belonging::create($validated);

        return response()->json($belonging, 201);
    }
}
