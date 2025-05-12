<?php

namespace App\Http\Controllers;

use App\Models\IdentityActionReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IdentityActionReasonController extends Controller
{

    public function __construct()
    {
        // Proteger todas las acciones para que solo los administradores puedan acceder
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('has-role', 'admin')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = (int) $request->input('per_page', 5);

        $reasons = IdentityActionReason::query()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('action_type')
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Admin/IdentityActionReasons', [
            'reasons' => $reasons,
            'search' => $search,
            'perPage' => $perPage,
        ]);
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
        $validated = $request->validate([
            'action_type' => ['required', 'in:suspend,delete'],
            'code' => ['required', 'string', 'max:50', 'unique:identity_action_reasons,code,NULL,id,action_type,' . $request->action_type],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        IdentityActionReason::create($validated);

        return response()->json(['message' => __('Reason created successfully')]);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IdentityActionReason $identityActionReason)
    {
        $validated = $request->validate([
            'action_type' => ['required', 'in:suspend,delete'],
            'code' => ['required', 'string', 'max:50', 'unique:identity_action_reasons,code,' . $identityActionReason->id . ',id,action_type,' . $request->action_type],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $identityActionReason->update($validated);

        return response()->json(['message' => __('Reason updated successfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IdentityActionReason $identityActionReason)
    {
        $identityActionReason->delete();

        return response()->json(['message' => __('Reason deleted successfully')]);
    }
}
