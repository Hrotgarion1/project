<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Models\Paises;
use App\Models\PasswordResetToken;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use App\Notifications\CustomResetPassword;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    const NUMBER_PER_PAGE = 5;

    public function index(Request $request)
    {
        $query = User::with('roles', 'pais');

        if ($request->search_name) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }
        if ($request->search_email) {
            $query->where('email', 'like', '%' . $request->search_email . '%');
        }
        if ($request->role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }
        if ($request->country) {
            $query->where('pais_id', $request->country);
        }

        $users = $query->paginate(self::NUMBER_PER_PAGE)->withQueryString();
        // Contadores de usuarios por rol
        $roles = Role::all()->pluck('name'); // Obtener todos los roles
        $userCountsByRole = [];
        foreach ($roles as $role) {
            $userCountsByRole[$role] = User::role($role)->count(); // Contar usuarios por rol
        }

        $totalUsers = User::count();

        return inertia('Users/Index', [
            'users' => $users,
            'userCountsByRole' => $userCountsByRole, // Agregar los contadores de usuarios por rol
            'totalUsers' => $totalUsers,
        ]);
    }

    public function create()
    {
        $roles = Role::all();
        $paises = Paises::all();
        return inertia('Users/Create', [
            'roles' => $roles,
            'paises' => $paises->map(fn($pais) => ['id' => $pais->id, 'name' => $pais->nombre]),
        ]);
    }

    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Generar una contraseña temporal
            $temporaryPassword = Str::random(12);
            $data['password'] = bcrypt($temporaryPassword);

            // Crear el usuario
            Log::info('Creando usuario con datos:', $data);
            $user = User::create($data);
            Log::info('Usuario creado:', ['id' => $user->id, 'email' => $user->email]);
            
            if ($request->has('roles')) {
                $user->syncRoles($request->input('roles'));
                Log::info('Roles sincronizados:', ['roles' => $request->input('roles')]);
            }

            // Generar un token de restablecimiento con Jetstream
            $token = Password::createToken($user);
            Log::info('Token de restablecimiento creado:', ['email' => $user->email, 'token' => $token]);

            // Enviar notificación de restablecimiento
            $user->notify(new CustomResetPassword($token));
            Log::info('Notificación de restablecimiento enviada a:', ['email' => $user->email]);

            return redirect()->route('users.index')->with('success', __('User created successfully'));
        } catch (\Exception $e) {
            Log::error('Error al crear usuario:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            return redirect()->back()->withErrors(['error' => __('An error occurred while creating the user')]);
        }
    }

    public function show(User $user)
    {
        $user->load('roles', 'pais');
        return inertia('Users/Show', ['user' => $user]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles', 'pais');
        return inertia('Users/Edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update(UserRequest $request, User $user)
{
    $data = $request->validated();

    // Eliminar la contraseña si está vacía
    if (empty($data['password'])) {
        unset($data['password']);
    }

    // Actualizar los datos del usuario
    $user->update($data);

    // Sincronizar roles si están presentes
    if ($request->has('roles')) {
        $user->syncRoles($request->input('roles'));
    }

    return redirect()->route('users.index')->with('success', __('User updated successfully'));
}

public function regeneratePasswordLink(Request $request, $email)
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            
            // Ejecutar el comando Artisan
            $exitCode = Artisan::call('password:regenerate', ['email' => $email]);
            
            if ($exitCode === 0) {
                Log::info('Enlace de restablecimiento regenerado desde UI:', ['email' => $email]);
                return response()->json([
                    'message' => __('Password reset link regenerated and sent to :email', ['email' => $email]),
                ]);
            }
            
            throw new \Exception(__('Failed to regenerate reset link'));
        } catch (\Exception $e) {
            Log::error('Error al regenerar enlace desde UI:', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => __('Failed to regenerate reset link: :message', ['message' => $e->getMessage()]),
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', __('User deleted successfully'));
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate(['roles' => 'array']);
        $user->syncRoles($request->input('roles', []));
        return redirect()->route('users.index')->with('success', __('Roles updated successfully'));
    }

    public function getRoles()
    {
        return Role::all();
    }

    public function getCountries()
    {
        return Paises::all();
    }

    public function getPreferences(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $preferences = $user->preferences ?? new \App\Models\UserPreference(['user_id' => $user->id]);

        // Asegurar que los colores sean #rrggbb
        $toHex = fn($color) => preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : (
            preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $color, $m) ?
            sprintf('#%02X%02X%02X', $m[1], $m[2], $m[3]) : '#FFD700'
        );

        return response()->json([
            'card_bg_color' => $toHex($preferences->card_bg_color),
            'chart_proposed_color' => $toHex($preferences->chart_proposed_color),
            'chart_verified_color' => $toHex($preferences->chart_verified_color),
            'text_color' => $toHex($preferences->text_color),
            'text_font' => $preferences->text_font,
        ]);
    }

    public function savePreferences(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'card_bg_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'chart_proposed_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'chart_verified_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_font' => ['required', 'in:Figtree,Inter,Roboto,Arial,Poppins,Lato,Montserrat'],
        ]);

        $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json(['message' => 'Preferencias guardadas']);
    }
}
