<?php

namespace App\Http\Controllers;

use App\Models\Identity;
use App\Models\IdentityMeta;
use App\Models\IdentityDocument;
use App\Models\IdentityType;
use App\Models\Invitacion;
use App\Models\TermsAcceptance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use function App\Helpers\mapRole;

/**
 * Controlador para acciones de usuarios invitados relacionadas con sus identidades.
 */
class UserIdentitiesController extends Controller
{
    /**
     * Lista las identidades del usuario invitado autenticado.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        $identities = Identity::where('user_id', $user->id)
            ->with(['user:id,name,email', 'handledBy:id,name', 'identityDocuments', 'change_requests.sender:id,name'])
            ->select('id', 'user_id', 'type', 'phone', 'status', 'handled_by', 'email', 'name', 'address')
            ->paginate(5);

        $identities->getCollection()->transform(function ($identity) {
            $identity->has_unseen_requests = $identity->change_requests->some(fn($request) => $request->is_unseen());
            $identity->role_name = mapRole($identity->type);
            return $identity;
        });

        return Inertia::render('UserIdentities/Index', [
            'identities' => $identities,
            'userRole' => $user->hasRole('invitado') ? 'invitado' : 'other',
        ]);
    }

    /**
     * Muestra el formulario para solicitar una nueva identidad.
     */
    public function showForm($type)
    {
        if (!auth()->user()->hasRole('invitado')) {
            return redirect()->route('my-requests.index')->with('error', 'Solo los invitados pueden solicitar identidades.');
        }

        $roleName = mapRole($type); // Obtenemos el nombre mapeado del tipo

        return Inertia::render('Identities/IdentityRequest', [
            'type' => $type,
            'roleName' => $roleName, // Pasamos el nombre del rol como prop
            'roles' => ['tipo A', 'tipo B', 'tipo C', 'tipo D', 'tipo E', 'tipo F', 'tipo G', 'tipo H'],
        ]);
    }

    /**
     * Crea una nueva solicitud de identidad.
     */
    public function requestIdentity(Request $request)
    {
        if (!auth()->check()) {
            Log::warning('Usuario no autenticado en requestIdentity');
            return response()->json(['error' => 'No autorizado'], 403);
        }

        Log::info('Iniciando requestIdentity', [
            'user_id' => auth()->id(),
            'type' => $request->type,
        ]);

        $request->validate([
            'type' => 'required|in:tipo A,tipo B,tipo C,tipo D,tipo E,tipo F,tipo G,tipo H',
            'email' => 'required|email|unique:identities,email|unique:users,email|max:255',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20|regex:/^[\+\(\)0-9\s-]*$/',
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,jpeg,png,txt|max:2048',
            'terms_accepted' => 'required|accepted',
            'meta' => 'nullable|array',
            'meta.*.key' => 'string|max:50',
            'meta.*.value' => 'string|max:255',
        ], [
            'type.in' => 'El tipo de identidad debe ser tipo A, tipo B, tipo C, tipo D, tipo E, tipo F, tipo G o tipo H.',
            'email.unique' => 'Este correo ya está en uso.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'phone.regex' => 'El teléfono solo puede contener números, guiones, +, paréntesis y espacios.',
            'documents.*.mimes' => 'Solo se permiten archivos PDF, JPEG, PNG o TXT.',
        ]);

        // Verificar la aceptación de términos
        $termsAcceptance = TermsAcceptance::where('user_id', auth()->id())
            ->where('type', $request->type)
            ->where('accepted_at', '>=', now()->subMinutes(30))
            ->first();

        if (!$termsAcceptance) {
            Log::warning('No se encontró aceptación de términos válida', [
                'user_id' => auth()->id(),
                'type' => $request->type,
            ]);
            throw ValidationException::withMessages([
                'terms_accepted' => __('You must accept the terms and conditions.'),
            ]);
        }

        $identityType = IdentityType::where('type', $request->type)->firstOrFail();

        $identity = Identity::create([
            'user_id' => auth()->id(),
            'identity_type_id' => $identityType->id,
            'type' => $request->type,
            'email' => $request->email,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'status' => 'pending',
        ]);

        foreach ($request->file('documents') as $name => $file) {
            $path = $file->store('identity_documents', 'public');
            $mimeType = $file->getMimeType();
            $docType = match ($mimeType) {
                'application/pdf' => 'pdf',
                'text/plain' => 'text',
                default => 'image',
            };
            IdentityDocument::create([
                'identity_id' => $identity->id,
                'name' => $name,
                'type' => $docType,
                'path' => $path,
                'is_required' => true,
                'is_uploaded_by_user' => true,
            ]);
        }

        if ($request->has('meta')) {
            foreach ($request->meta as $key => $value) {
                IdentityMeta::create([
                    'identity_id' => $identity->id,
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'type' => gettype($value),
                ]);
            }
        }

        // Eliminar el registro de aceptación
        Log::info('Eliminando aceptación de términos', [
            'acceptance_id' => $termsAcceptance->id,
        ]);
        $termsAcceptance->delete();

        $roleName = mapRole($request->type);
        Log::info('Nueva solicitud de identidad creada', [
            'identity_id' => $identity->id,
            'type' => $request->type,
            'role_name' => $roleName,
        ]);

        return redirect()->route('my-requests.index')->with('success', "Solicitud enviada con éxito para el rol: {$roleName}");
    }

    public function getRequiredDocuments($type)
{
    $identityType = IdentityType::where('type', $type)->firstOrFail();
    $normalizedType = str_replace(' ', '_', strtolower($identityType->type));
    $termsUrl = route('terms_conditions.show', ['type' => $normalizedType]);
    
    return response()->json([
        'type' => $identityType->type,
        'required_documents' => $identityType->required_documents ?? [],
        'terms_url' => $termsUrl,
    ]);
}

    /**
     * Verifica el límite de solicitudes diarias del usuario (máximo 2 por día).
     */
    public function checkLimit(Request $request)
    {
        if (app()->environment('local')) {
            return response()->json(['limitReached' => false]);
        }

        $key = $request->user()->id ?: $request->ip();
        $maxAttempts = 20;
        $decayMinutes = 1440; // 1 día
        $attempts = RateLimiter::attempts('identity-request:' . $key);

        if ($attempts >= $maxAttempts) {
            return response()->json(['limitReached' => true]);
        }

        return response()->json(['limitReached' => false]);
    }

    /**
     * Muestra el formulario para editar una identidad (solo si está en 'pending', 'in_progress' o 'waiting').
     */
    public function edit($slug)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        $identity = Identity::where('slug', $slug)
            ->where('user_id', $user->id)
            ->with([
                'user:id,name,email',
                'handledBy:id,name',
                'identityDocuments' => fn($query) => $query->where('active', true),
                'change_requests.sender:id,name',
            ])
            ->firstOrFail();

        if ($user->hasRole('invitado') && !in_array($identity->status, ['pending', 'in_progress', 'waiting'])) {
            abort(403, 'No tienes permiso para editar esta identidad.');
        }

        $identityType = IdentityType::where('type', $identity->type)->firstOrFail();

        $identity->change_requests()->whereNull('seen_at')->update(['seen_at' => now()]);
        $identityArray = $identity->toArray();
        $identityArray['role_name'] = mapRole($identity->type);

        return Inertia::render('UserIdentities/Edit', [
            'identity' => $identityArray,
            'userRole' => $user->hasRole('invitado') ? 'invitado' : 'other',
            'requiredDocuments' => $identityType->required_documents,
        ]);
    }

    public function update(Request $request, Identity $identity)
    {
        $user = auth()->user();
        if ($user->id !== $identity->user_id || !in_array($identity->status, ['pending', 'in_progress', 'waiting'])) {
            abort(403, 'No tienes permiso para actualizar esta identidad o ya no es editable.');
        }

        $request->validate([
            'email' => 'required|email|max:255|unique:identities,email,' . $identity->id . '|unique:users,email,' . $user->id,
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20|regex:/^[\+\(\)0-9\s-]*$/',
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,jpeg,png,txt|max:2048',
        ]);

        $identity->update([
            'email' => $request->email,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'has_updates' => true,
        ]);

        if ($request->hasFile('documents')) {
            $identityType = IdentityType::where('type', $identity->type)->firstOrFail();
            $requiredDocs = collect($identityType->required_documents)->pluck('name')->all();

            foreach ($request->file('documents') as $name => $file) {
                $existingDoc = IdentityDocument::where('identity_id', $identity->id)
                    ->where('name', $name)
                    ->where('active', true)
                    ->first();

                if ($existingDoc) {
                    $existingDoc->update(['active' => false]);
                    $existingDoc->delete();
                }

                $path = $file->store('identity_documents', 'public');
                $mimeType = $file->getMimeType();
                $docType = match ($mimeType) {
                    'application/pdf' => 'pdf',
                    'text/plain' => 'text',
                    default => 'image',
                };
                IdentityDocument::create([
                    'identity_id' => $identity->id,
                    'name' => $name,
                    'type' => $docType,
                    'path' => $path,
                    'is_required' => in_array($name, $requiredDocs),
                    'is_uploaded_by_user' => true,
                    'active' => true,
                ]);
            }
        }

        return redirect()->route('my-requests.index')->with('message', 'Solicitud actualizada con éxito');
    }

    /**
     * Elimina una solicitud de identidad.
     */
    public function destroy(Identity $identity)
    {
        $user = auth()->user();

        foreach ($identity->identityDocuments as $doc) {
            Storage::disk('public')->delete($doc->path);
            $doc->delete();
        }

        $identity->delete();
        $identity->metas()->delete();

        return redirect()->route('my-requests.index')->with('message', 'Identidad eliminada');
    }

    public function storeDocuments($slug)
    {
        $user = auth()->user();
        $identity = Identity::where('slug', $slug)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($user->hasRole('invitado') && !in_array($identity->status, ['pending', 'in_progress', 'waiting'])) {
            return back()->with('error', 'No puedes añadir documentos en este estado.');
        }

        if (!request()->hasFile('documents')) {
            return back()->withErrors(['documents' => 'No se seleccionaron documentos.']);
        }

        foreach (request()->file('documents') as $name => $file) {
            $path = $file->store('identity_documents', 'public');
            $mimeType = $file->getMimeType();
            $docType = match ($mimeType) {
                'application/pdf' => 'pdf',
                'text/plain' => 'text',
                default => 'image',
            };
            IdentityDocument::create([
                'identity_id' => $identity->id,
                'name' => $name,
                'type' => $docType,
                'path' => $path,
                'is_required' => true,
                'is_uploaded_by_user' => true,
            ]);
        }

        $identity->update(['has_updates' => true]);

        return back()->with('message', 'Documentos añadidos correctamente');
    }

    /**
     * Elimina un documento específico de una solicitud de identidad.
     */
    public function destroyDocument($slug, $document)
    {
        $user = auth()->user();
        $identity = Identity::where('slug', $slug)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($user->hasRole('invitado') && !in_array($identity->status, ['pending', 'in_progress', 'waiting'])) {
            abort(403, 'No puedes eliminar documentos en este estado.');
        }

        $document = IdentityDocument::where('id', $document)
            ->where('identity_id', $identity->id)
            ->firstOrFail();

        Storage::disk('public')->delete($document->path);
        $document->delete();

        $identity->update(['has_updates' => true]);

        return redirect()->back()->with('message', 'Documento eliminado correctamente');
    }

    /**
     * Registra la aceptación de términos para un tipo de identidad.
     */
    public function acceptTerms(Request $request)
    {
        Log::info('Iniciando acceptTerms', [
            'user_id' => auth()->id(),
            'type' => $request->type,
        ]);

        $request->validate([
            'type' => 'required|in:tipo A,tipo B,tipo C,tipo D,tipo E,tipo F,tipo G,tipo H',
        ]);

        $acceptance = TermsAcceptance::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'accepted_at' => now(),
        ]);

        if (!$acceptance) {
            Log::error('Fallo al crear TermsAcceptance', [
                'user_id' => auth()->id(),
                'type' => $request->type,
            ]);
            throw new \Exception('Failed to create terms acceptance');
        }

        Log::info('Términos aceptados', [
            'user_id' => auth()->id(),
            'type' => $request->type,
            'acceptance_id' => $acceptance->id,
        ]);

        return response()->json(['success' => true]);
    }
}
