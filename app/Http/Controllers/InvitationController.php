<?php

namespace App\Http\Controllers;

use App\Models\Invitacion;
use App\Models\Team;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use function App\Helpers\mapRole;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Invitaciones enviadas por el usuario
        $sentInvitations = Invitacion::where('invitador_id', $user->id)
            ->with(['invitado', 'identity'])
            ->get()
            ->map(function ($invitation) {
                $invitation->role_name = mapRole($invitation->role);
                return $invitation;
            });

        // Invitaciones recibidas por el usuario
        $receivedInvitations = Invitacion::where('invitado_id', $user->id)
            ->with(['invitador', 'identity'])
            ->get()
            ->map(function ($invitation) {
                $invitation->role_name = mapRole($invitation->role);
                return $invitation;
            });

        return inertia('Invitations/Index', [
            'sentInvitations' => $sentInvitations,
            'receivedInvitations' => $receivedInvitations,
        ]);
    }

    public function sent($slug)
    {
        $user = auth()->user();
        $allowedTypes = ['tipo A', 'tipo B', 'tipo C'];

        $identity = $user->identities()->where('slug', $slug)->whereIn('type', $allowedTypes)->firstOrFail();

        $sentInvitations = Invitacion::where('invitador_id', $user->id)
            ->where('identity_id', $identity->id)
            ->with(['invitado', 'identity'])
            ->get()
            ->map(function ($invitation) {
                $invitation->role_name = mapRole($invitation->role);
                return $invitation;
            });

        return inertia('Invitations/SentInvitations', [
            'sentInvitations' => $sentInvitations,
            'identity' => $identity,
        ]);
    }

    public function received()
    {
        $user = auth()->user();

        $receivedInvitations = Invitacion::where('invitado_id', $user->id)
            ->with(['invitador', 'identity'])
            ->get()
            ->map(function ($invitation) {
                $invitation->role_name = mapRole($invitation->role);
                return $invitation;
            });

        return inertia('Invitations/ReceivedInvitations', [
            'receivedInvitations' => $receivedInvitations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($slug)
    {
        $user = auth()->user();
        $identity = $user->identities()->where('slug', $slug)->where('status', 'approved')->firstOrFail();

        // Obtener roles derivados según el tipo de la identidad
        $type = $identity->type; // "tipo A", "tipo B", etc.
        $availableRoles = Role::where('name', 'like', "{$type}%")
                            ->whereIn('name', ["{$type}1", "{$type}2"])
                            ->pluck('name')
                            ->values(); // Solo los nombres de los roles

        // Mapear roles a una estructura con type y name
        $mappedRoles = $availableRoles->map(function ($role) {
            return [
                'type' => $role,
                'name' => mapRole($role),
            ];
        })->values();

        return inertia('Invitations/Create', [
            'identity' => $identity,
            'availableRoles' => $mappedRoles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'identity_id' => 'required|exists:identities,id',
            'role' => 'required|exists:roles,name',
        ]);

        $invitador = auth()->user();
        $invitado = User::where('email', $request->email)->first();
        $identity = $invitador->identities()->findOrFail($request->identity_id);

        // Validar que el rol sea coherente con la identidad
        $type = $identity->type;
        if (!in_array($request->role, ["{$type}1", "{$type}2"])) {
            return back()->withErrors(['role' => 'El rol seleccionado no es válido para esta identidad']);
        }

        $invitacion = Invitacion::create([
            'invitador_id' => $invitador->id,
            'invitado_id' => $invitado->id,
            'identity_id' => $identity->id,
            'role' => $request->role,
            'status' => 'pending',
            'token' => Str::random(40),
        ]);

        return inertia('Invitations/Sent', [
            'message' => 'Invitación enviada con éxito',
        ]);
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
    public function update(Request $request, string $id)
    {
        //
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $invitation = Invitacion::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('invitador_id', $user->id)
                      ->orWhere('invitado_id', $user->id);
            })
            ->firstOrFail();

        // Si ya fue aceptada y el usuario es el invitado, remover el rol
        if ($invitation->status === 'approved' && $invitation->invitado_id === $user->id) {
            $invitation->invitado->removeRole($invitation->role);
        }

        $invitation->delete();

        return redirect()->back()->with('message', 'Invitación cancelada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function accept(Request $request, $token)
    {
        $invitacion = Invitacion::where('token', $token)->firstOrFail();

        if ($invitacion->status !== 'pending' || $invitacion->invitado_id !== auth()->id()) {
            abort(403, 'Invitación no válida o ya procesada');
        }

        $invitacion->update(['status' => 'approved']);
        $invitado = $invitacion->invitado;
        $invitado->assignRole($invitacion->role);

        return redirect()->route('invitations.received')->with('message', 'Invitación aceptada con éxito');
    }
}
