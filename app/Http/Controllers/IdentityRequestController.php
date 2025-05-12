<?php

namespace App\Http\Controllers;

use App\Mail\IdentityActionNotification;
use App\Models\Identity;
use App\Models\IdentityActionReason;
use App\Models\IdentityChangeRequest;
use App\Models\IdentitySuspension;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use function App\Helpers\mapRole;
use function App\Helpers\mapRoleToType;

/**
 * Controlador para acciones de administración y edición de identidades.
 * Solo accesible para usuarios con roles 'admin' o 'editor'.
 */

class IdentityRequestController extends Controller
{
    /**
     * Lista las solicitudes de identidades para revisión por admin/editor.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $statusHandled = $request->input('status_handled');
        $statusAll = $request->input('status_all');
        $searchHandled = $request->input('search_handled');
        $searchAll = $request->input('search_all');

        // Consulta para handledIdentities
        $handledIdentitiesQuery = Identity::with(['user:id,name,email', 'handledBy:id,name'])
            ->select('id', 'user_id', 'status', 'type', 'handled_by', 'deleted_at', 'has_updates', 'slug')
            ->where('handled_by', auth()->id())
            ->orderedByStatus();

        if ($searchHandled) {
            $searchHandledLower = strtolower($searchHandled);
            $handledIdentitiesQuery->where(function ($query) use ($searchHandled, $searchHandledLower) {
                $query->whereHas('user', function ($q) use ($searchHandled) {
                    $q->where('name', 'like', "%{$searchHandled}%")
                      ->orWhere('email', 'like', "%{$searchHandled}%");
                })
                ->orWhere('type', 'like', "%{$searchHandled}%");

                $typeFromRole = mapRoleToType($searchHandledLower);
                if ($typeFromRole) {
                    $query->orWhere('type', $typeFromRole);
                }

                $roleKeys = array_map('strtolower', array_keys(config('roles.types', [])));
                $roles = array_filter($roleKeys, fn($role) => str_contains($role, $searchHandledLower));
                Log::info('Búsqueda parcial en roles:', [
                    'search' => $searchHandledLower,
                    'roleKeys' => $roleKeys,
                    'roles' => $roles,
                ]);
                if ($roles) {
                    $types = array_map(fn($role) => mapRoleToType($role), $roles);
                    $query->orWhereIn('type', $types);
                }
            });
        }

        if ($user->hasRole('editor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $handledIdentitiesQuery->whereIn('status', ['in_progress', 'waiting', 'approved']);
        } elseif ($statusHandled === 'deleted' && $isAdmin) {
            $handledIdentitiesQuery->onlyTrashed();
        } elseif ($statusHandled) {
            $handledIdentitiesQuery->where('status', $statusHandled)->whereNull('deleted_at');
        } else {
            $handledIdentitiesQuery->whereNull('deleted_at');
        }

        $handledIdentities = $handledIdentitiesQuery->paginate(3, ['*'], 'handled_page')->through(function ($identity) {
            $identity->role = mapRole($identity->type);
            return $identity;
        });

        // Consulta para allIdentities
        $allIdentitiesQuery = Identity::with(['user:id,name,email', 'handledBy:id,name'])
            ->select('id', 'user_id', 'status', 'type', 'handled_by', 'deleted_at', 'has_updates', 'slug')
            ->orderedByStatus();

        if ($searchAll) {
            $searchAllLower = strtolower($searchAll);
            $allIdentitiesQuery->where(function ($query) use ($searchAll, $searchAllLower) {
                $query->whereHas('user', function ($q) use ($searchAll) {
                    $q->where('name', 'like', "%{$searchAll}%")
                      ->orWhere('email', 'like', "%{$searchAll}%");
                })
                ->orWhere('type', 'like', "%{$searchAll}%")
                ->orWhereHas('handledBy', function ($q) use ($searchAll) {
                    $q->where('name', 'like', "%{$searchAll}%");
                });

                $typeFromRole = mapRoleToType($searchAllLower);
                if ($typeFromRole) {
                    $query->orWhere('type', $typeFromRole);
                }

                $roleKeys = array_map('strtolower', array_keys(config('roles.types', [])));
                $roles = array_filter($roleKeys, fn($role) => str_contains($role, $searchAllLower));
                if ($roles) {
                    $types = array_map(fn($role) => mapRoleToType($role), array_values($roles));
                    $query->orWhereIn('type', $types);
                }
            });
        }

        if ($user->hasRole('editor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $allIdentitiesQuery->where('status', 'pending');
        } elseif ($statusAll === 'deleted' && $isAdmin) {
            $allIdentitiesQuery->onlyTrashed();
        } elseif ($statusAll) {
            $allIdentitiesQuery->where('status', $statusAll)->whereNull('deleted_at');
        } else {
            $allIdentitiesQuery->whereNull('deleted_at');
        }

        $allIdentities = $allIdentitiesQuery->paginate(5, ['*'], 'all_page')->through(function ($identity) {
            $identity->role = mapRole($identity->type);
            return $identity;
        });

        // Contadores para "My Assigned Requests"
        $handledCountsQuery = Identity::where('handled_by', auth()->id());
        if ($user->hasRole('editor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $handledCountsQuery->whereIn('status', ['in_progress', 'waiting', 'approved']);
        }
        $handledCounts = [
            'in_progress' => (clone $handledCountsQuery)->where('status', 'in_progress')->whereNull('deleted_at')->count(),
            'waiting' => (clone $handledCountsQuery)->where('status', 'waiting')->whereNull('deleted_at')->count(),
            'approved' => (clone $handledCountsQuery)->where('status', 'approved')->whereNull('deleted_at')->count(),
        ];

        // Contadores para "All Requests" (solo admin)
        $allCounts = [];
        if ($isAdmin) {
            $allCountsQuery = Identity::query();
            $allCounts = [
                'total' => $allCountsQuery->withTrashed()->count(),
                'pending' => (clone $allCountsQuery)->where('status', 'pending')->whereNull('deleted_at')->count(),
                'in_progress' => (clone $allCountsQuery)->where('status', 'in_progress')->whereNull('deleted_at')->count(),
                'waiting' => (clone $allCountsQuery)->where('status', 'waiting')->whereNull('deleted_at')->count(),
                'approved' => (clone $allCountsQuery)->where('status', 'approved')->whereNull('deleted_at')->count(),
                'rejected' => (clone $allCountsQuery)->where('status', 'rejected')->whereNull('deleted_at')->count(),
                'suspended' => (clone $allCountsQuery)->where('status', 'suspended')->whereNull('deleted_at')->count(),
                'deleted' => (clone $allCountsQuery)->onlyTrashed()->count(),
            ];
        }

        $availableHandlers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'supervisor', 'editor']);
        })->with(['roles' => function ($query) {
            $query->select('name');
        }])->select('id', 'name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
            ];
        });

        Log::info('Consulta SQL para handledIdentities:', [
            'query' => $handledIdentitiesQuery->toSql(),
            'bindings' => $handledIdentitiesQuery->getBindings(),
        ]);

        return Inertia::render('Identities/IdentityPanel', [
            'handledIdentities' => $handledIdentities,
            'allIdentities' => $allIdentities,
            'search_handled' => $searchHandled ?? '',
            'search_all' => $searchAll ?? '',
            'status_handled' => $statusHandled ?? '',
            'status_all' => $statusAll ?? '',
            'availableHandlers' => $availableHandlers,
            'handledCounts' => $handledCounts,
            'allCounts' => $allCounts,
        ]);
    }

    /**
     * Lista las solicitudes que Stuart por admin/editor autenticado.
     */
    public function myRequests(Request $request)
    {
        $user = auth()->user();
        $statusHandled = $request->input('status_handled');
        $searchHandled = $request->input('search_handled');

        if (!$user->hasRole(['admin', 'supervisor', 'editor'])) {
            abort(403, 'No tienes permiso para ver tus solicitudes gestionadas.');
        }

        $handledIdentitiesQuery = Identity::with(['user:id,name,email', 'handledBy:id,name'])
            ->select('id', 'user_id', 'status', 'type', 'handled_by', 'deleted_at', 'has_updates', 'slug')
            ->where('handled_by', $user->id)
            ->orderedByStatus();

        if ($searchHandled) {
            $searchHandledLower = strtolower($searchHandled);
            $handledIdentitiesQuery->where(function ($query) use ($searchHandled, $searchHandledLower) {
                $query->whereHas('user', function ($q) use ($searchHandled) {
                    $q->where('name', 'like', "%{$searchHandled}%")
                      ->orWhere('email', 'like', "%{$searchHandled}%");
                })
                ->orWhere('type', 'like', "%{$searchHandled}%");

                $typeFromRole = mapRoleToType($searchHandledLower);
                if ($typeFromRole) {
                    $query->orWhere('type', $typeFromRole);
                }

                $roleKeys = array_map('strtolower', array_keys(config('roles.types', [])));
                $roles = array_filter($roleKeys, fn($role) => str_contains($role, $searchHandledLower));
                if ($roles) {
                    $types = array_map(fn($role) => mapRoleToType($role), $roles);
                    $query->orWhereIn('type', $types);
                }
            });
        }

        if ($user->hasRole('editor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $handledIdentitiesQuery->whereIn('status', ['in_progress', 'waiting', 'approved']);
        } elseif ($statusHandled === 'deleted' && $user->hasRole('admin')) {
            $handledIdentitiesQuery->onlyTrashed();
        } elseif ($statusHandled) {
            $handledIdentitiesQuery->where('status', $statusHandled)->whereNull('deleted_at');
        } else {
            $handledIdentitiesQuery->whereNull('deleted_at');
        }

        $handledIdentities = $handledIdentitiesQuery->paginate(3, ['*'], 'handled_page')->through(function ($identity) {
            $identity->role = mapRole($identity->type);
            return $identity;
        });

        // Contadores para "My Assigned Requests"
        $handledCountsQuery = Identity::where('handled_by', $user->id);
        if ($user->hasRole('editor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            $handledCountsQuery->whereIn('status', ['in_progress', 'waiting', 'approved']);
        }
        $handledCounts = [
            'in_progress' => (clone $handledCountsQuery)->where('status', 'in_progress')->whereNull('deleted_at')->count(),
            'waiting' => (clone $handledCountsQuery)->where('status', 'waiting')->whereNull('deleted_at')->count(),
            'approved' => (clone $handledCountsQuery)->where('status', 'approved')->whereNull('deleted_at')->count(),
        ];

        $availableHandlers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'supervisor', 'editor']);
        })->with(['roles' => function ($query) {
            $query->select('name');
        }])->select('id', 'name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
            ];
        });

        return Inertia::render('Identities/IdentityPanel', [
            'handledIdentities' => $handledIdentities,
            'allIdentities' => null,
            'search_handled' => $searchHandled ?? '',
            'search_all' => '',
            'status_handled' => $statusHandled ?? '',
            'status_all' => '',
            'availableHandlers' => $availableHandlers,
            'handledCounts' => $handledCounts,
            'allCounts' => []
        ]);
    }

    /**
     * Asigna una solicitud pendiente a un admin/editor para su gestión.
     */
    public function takeRequest(Identity $identity)
    {
        if ($identity->status !== 'pending') {
            return response()->json(['message' => 'Solo se pueden tomar solicitudes pendientes'], 403);
        }

        $identity->update([
            'handled_by' => auth()->user()->id,
            'status' => 'in_progress',
            'taken_at' => now(),
        ]);

        try {
            $this->sendNotification($identity, 'in_progress', 'started', auth()->user());
        } catch (\Exception $e) {
            Log::error('Error enviando notificación: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Solicitud tomada con éxito',
            'identity' => $identity->fresh(['handledBy']),
        ]);
    }

    /**
     * Suspende una identidad aprobada (acción de admin/editor).
     */
    public function suspend(Request $request, Identity $identity)
    {
        if (!auth()->user()->hasRole(['admin', 'supervisor', 'editor'])) {
            abort(403, 'No tienes permiso para suspender identidades.');
        }

        if ($identity->status !== 'approved') {
            abort(400, 'Solo se pueden suspender identidades aprobadas.');
        }

        $validReasons = IdentityActionReason::where('action_type', 'suspended')->pluck('code')->toArray();
        $request->validate(['reason_code' => 'required|in:' . implode(',', $validReasons)]);

        $identity->update([
            'status' => 'suspended',
            'suspend_reason_code' => $request->reason_code,
        ]);

        // Obtener invitador e invitados
        $invitador = $identity->user;
        $invitaciones = $identity->invitations()->where('status', 'approved')->get();

        // Registrar suspensión para el invitador
        IdentitySuspension::create([
            'identity_id' => $identity->id,
            'user_id' => $invitador->id,
            'role_type' => $identity->type,
            'is_inviter' => true,
        ]);

        // Registrar suspensión para los invitados
        foreach ($invitaciones as $invitacion) {
            IdentitySuspension::create([
                'identity_id' => $identity->id,
                'user_id' => $invitacion->invitado_id,
                'role_type' => $invitacion->role,
                'is_inviter' => false,
            ]);
        }

        try {
            $this->sendNotification($identity, 'suspended', $request->reason_code, auth()->user());
        } catch (\Exception $e) {
            Log::error('Error enviando notificación: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Identidad suspendida con éxito',
            'identity' => $identity->fresh(['user', 'handledBy']),
        ]);
    }

    /**
     * Reactiva una identidad suspendida (acción de admin/editor).
     */
    public function reactivate(Request $request, Identity $identity)
    {
        if (!auth()->user()->hasRole(['admin', 'supervisor', 'editor'])) {
            abort(403, 'No tienes permiso para reactivar identidades.');
        }

        if ($identity->status !== 'suspended') {
            abort(400, 'Solo se pueden reactivar identidades suspendidas.');
        }

        $identity->update([
            'status' => 'approved',
            'suspend_reason_code' => null,
        ]);

        IdentitySuspension::where('identity_id', $identity->id)->delete();

        try {
            $this->sendNotification($identity, 'reactivated', null, auth()->user());
        } catch (\Exception $e) {
            Log::error('Error enviando notificación: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Identidad reactivada con éxito',
            'identity' => $identity->fresh(['user', 'handledBy']),
        ]);
    }

    /**
     * Aprueba o rechaza una solicitud de identidad.
     */
    public function updateStatus(Request $request, Identity $identity)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected',
                'email' => 'required_if:status,approved|email',
                'name' => 'required_if:status,approved|string',
                'address' => 'nullable|string',
            ]);

            if (!auth()->user()->hasRole(['admin', 'supervisor', 'editor'])) {
                abort(403, 'No tienes permiso para aprobar o rechazar solicitudes.');
            }

            if (!in_array($identity->status, ['pending', 'in_progress', 'waiting'])) {
                abort(400, 'Solo se pueden aprobar o rechazar solicitudes en estado pendiente, en progreso o en espera.');
            }

            $identity->update([
                'status' => $request->status,
                'handled_by' => auth()->id(),
                'email' => $request->status === 'approved' ? $request->email : $identity->email,
                'name' => $request->status === 'approved' ? $request->name : $identity->name,
                'address' => $request->status === 'approved' ? $request->address : $identity->address,
            ]);

            if ($request->status === 'approved') {
                $user = $identity->user;
                $user->assignRole($identity->type);

                try {
                    $this->sendNotification($identity, 'approved', 'success', auth()->user());
                } catch (\Exception $e) {
                    Log::error('Error enviando notificación: ' . $e->getMessage());
                }
            } elseif ($request->status === 'rejected') {
                try {
                    $this->sendNotification($identity, 'rejected', 'insufficient_docs', auth()->user());
                } catch (\Exception $e) {
                    Log::error('Error enviando notificación: ' . $e->getMessage());
                }
            }

            return response()->json([
                'message' => $request->status === 'approved' ? 'Identidad aprobada y rol asignado' : 'Identidad rechazada',
                'identity' => $identity->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Errores de validación', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error en updateStatus: ' . $e->getMessage(), [
                'request' => $request->all(),
                'identity_id' => $identity->id,
            ]);
            return response()->json(['error' => 'Error interno al procesar la solicitud'], 500);
        }
    }

    /**
     * Muestra los detalles de una solicitud para aprobación o revisión por admin/editor.
     */
    public function show(Identity $identity)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['admin', 'supervisor', 'editor'])) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        if ($user->hasRole('editor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            if ($identity->handled_by !== $user->id || !in_array($identity->status, ['in_progress', 'waiting', 'approved'])) {
                abort(403, 'Los editores solo pueden ver sus propias solicitudes.');
            }
        }

        if ($identity->status === 'in_progress' && !$user->hasRole('admin') && $identity->handled_by !== $user->id) {
            abort(403, 'Solo el editor asignado o un administrador pueden manejar esta solicitud.');
        }

        if ($identity->has_updates) {
            $identity->update(['has_updates' => false]);
        }

        $availableHandlers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'supervisor', 'editor']);
        })->with(['roles' => function ($query) {
            $query->select('name');
        }])->select('id', 'name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
            ];
        });

        $identityData = $identity->load([
            'type' => function ($query) {
                $query->select('id', 'type', 'terms_and_conditions', 'required_documents');
            },
            'user:id,name,email',
            'metas',
            'handledBy:id,name',
            'identityDocuments' => fn($query) => $query->where('active', true)->select('id', 'identity_id', 'name', 'type', 'path', 'active'),
            'change_requests.sender:id,name',
        ]);

        $identityArray = $identityData->toArray();
        $identityArray['role'] = mapRole($identityData->type);

        Log::debug('Identity data after mapping:', ['identityArray' => $identityArray]);

        return Inertia::render('Identities/IdentityApproval', [
            'identity' => $identityArray,
            'suspendReasons' => IdentityActionReason::getReasons('suspended'),
            'deleteReasons' => IdentityActionReason::getReasons('deleted'),
            'availableHandlers' => $availableHandlers,
        ]);
    }

    /**
     * Solicita más documentación a un usuario invitado.
     */
    public function requestMoreDocs(Request $request, Identity $identity)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['editor', 'supervisor', 'admin'])) {
            abort(403, 'No tienes permiso para solicitar más documentos.');
        }

        if (!in_array($identity->status, ['pending', 'in_progress', 'waiting'])) {
            abort(403, 'Esta solicitud no puede ser modificada en este estado.');
        }

        $request->validate([
            'requested_changes' => 'required|string|max:1000',
        ]);

        $changeRequest = $this->createChangeRequest($identity, $request->requested_changes, $user);

        $identity->status = 'waiting';
        $identity->handled_by = $user->id;
        $identity->save();

        try {
            $this->sendNotification($identity, 'waiting', 'more_docs_requested', $user);
        } catch (\Exception $e) {
            Log::error('Error enviando notificación en requestMoreDocs: ' . $e->getMessage());
            return response()->json(['error' => 'Error enviando notificación: ' . $e->getMessage()], 500);
        }

        $identityData = $identity->fresh([
            'user:id,name,email',
            'handledBy:id,name',
            'identityDocuments' => fn($query) => $query->where('active', true),
            'change_requests.sender:id,name',
        ]);
        $identityArray = $identityData->toArray();
        $identityArray['role'] = mapRole($identityData->type);
        Log::debug('Identity data after requestMoreDocs:', ['identityArray' => $identityArray]);

        return response()->json([
            'message' => 'Se ha solicitado más documentos al usuario.',
            'identity' => $identityArray,
        ]);
    }

    /**
     * Crea una solicitud de cambio.
     */
    protected function createChangeRequest($requestable, string $message, $user)
    {
        return IdentityChangeRequest::create([
            'requestable_type' => get_class($requestable),
            'requestable_id' => $requestable->id,
            'message' => $message,
            'sent_at' => now(),
            'sent_by' => $user->id,
        ]);
    }

    /**
     * Restaura una solicitud eliminada (soft delete).
     */
    public function restore($slug)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden restaurar identidades.');
        }

        $identity = Identity::withTrashed()->where('slug', $slug)->firstOrFail();
        $identity->restore();
        $identity->metas()->withTrashed()->restore();
        $identity->update(['status' => 'pending', 'delete_reason_code' => null]);

        Log::info('Solicitud restaurada y marcada como pendiente:', [
            'identity' => $identity->toArray(),
            'documents' => $identity->documents,
        ]);

        try {
            $this->sendNotification($identity, 'restored', null, auth()->user());
        } catch (\Exception $e) {
            Log::error('Error enviando notificación: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Identidad restaurada con éxito']);
    }

    /**
     * Elimina una solicitud de identidad (acción de admin/editor).
     */
    public function destroy(Request $request, Identity $identity)
    {
        if (!auth()->user()->hasRole(['admin','supervisor', 'editor'])) {
            abort(403, 'No tienes permiso para eliminar solicitudes.');
        }

        $validReasons = IdentityActionReason::where('action_type', 'deleted')->pluck('code')->toArray();
        $request->validate(['reason_code' => 'required|in:' . implode(',', $validReasons)]);

        $identity->update(['delete_reason_code' => $request->reason_code]);

        try {
            $this->sendNotification($identity, 'deleted', $request->reason_code, auth()->user());
        } catch (\Exception $e) {
            Log::error('Error enviando notificación: ' . $e->getMessage());
        }

        $identity->metas()->delete();
        $identity->delete();

        return response()->json(['message' => 'Solicitud eliminada con éxito en IdentityRequest']);
    }

    /**
     * Reasigna una solicitud aprobada (acción de admin).
     */
    public function reassign(Request $request, Identity $identity)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Solo los administradores o supervisores pueden reasignar solicitudes.');
        }

        $request->validate([
            'handled_by' => 'required|exists:users,id',
        ]);

        $newHandler = User::where('id', $request->handled_by)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'supervisor', 'editor']);
            })
            ->firstOrFail();

        $identity->update(['handled_by' => $newHandler->id]);

        try {
            $this->sendNotification($identity, 'reassigned', null, auth()->user());
        } catch (\Exception $e) {
            Log::error('Error enviando notificación: ' . $e->getMessage());
        }

        // Cargar todas las relaciones necesarias, incluyendo change_requests
        $identityData = $identity->fresh([
            'user:id,name,email',
            'handledBy:id,name',
            'type' => function ($query) {
                $query->select('id', 'type', 'terms_and_conditions', 'required_documents');
            },
            'metas',
            'identityDocuments' => fn($query) => $query->where('active', true)->select('id', 'identity_id', 'name', 'type', 'path', 'active'),
            'change_requests.sender:id,name',
        ]);

        $identityArray = $identityData->toArray();
        $identityArray['role'] = mapRole($identityData->type);

        return response()->json([
            'message' => 'Solicitud reasignada con éxito',
            'identity' => $identityArray,
        ]);
    }

    /**
     * Reasigna múltiples solicitudes aprobadas (acción de admin).
     */
    public function bulkReassign(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'supervisor'])) {
            abort(403, 'Solo los administradores o supervisores pueden reasignar solicitudes.');
        }

        $request->validate([
            'identity_slugs' => 'required|array',
            'identity_slugs.*' => 'required|string|exists:identities,slug',
            'handled_by' => 'required|exists:users,id',
        ]);

        $newHandler = User::where('id', $request->handled_by)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'supervisor', 'editor']);
            })
            ->firstOrFail();

        $user = auth()->user();
        $query = Identity::whereIn('slug', $request->identity_slugs); // Use slugs

        if (!$user->hasRole('admin')) {
            $query->whereIn('status', ['pending', 'in_progress'])->whereNull('deleted_at');
        }

        $identities = $query->get();

        if ($identities->isEmpty()) {
            return response()->json(['message' => 'No se encontraron solicitudes válidas para reasignar'], 400);
        }

        foreach ($identities as $identity) {
            $identity->update(['handled_by' => $newHandler->id]);
            try {
                $this->sendNotification($identity, 'reassigned', null, auth()->user());
            } catch (\Exception $e) {
                Log::error('Error enviando notificación: ' . $e->getMessage());
            }
        }

        // Recargar datos paginados para la vista
        $handledIdentitiesQuery = Identity::with(['user:id,name,email', 'handledBy:id,name'])
            ->select('id', 'user_id', 'status', 'type', 'handled_by', 'deleted_at', 'has_updates', 'slug') // Add slug
            ->where('handled_by', auth()->user()->id)
            ->orderedByStatus()
            ->whereNull('deleted_at');

        $allIdentitiesQuery = Identity::with(['user:id,name,email', 'handledBy:id,name'])
            ->select('id', 'user_id', 'status', 'type', 'handled_by', 'deleted_at', 'has_updates', 'slug') // Add slug
            ->orderedByStatus()
            ->whereNull('deleted_at');

        $handledIdentities = $handledIdentitiesQuery->paginate(3, ['*'], 'handled_page')->through(function ($identity) {
            $identity->role = mapRole($identity->type);
            return $identity;
        });

        $allIdentities = $allIdentitiesQuery->paginate(5, ['*'], 'all_page')->through(function ($identity) {
            $identity->role = mapRole($identity->type);
            return $identity;
        });

        return response()->json([
            'message' => 'Solicitudes reasignadas con éxito',
            'handledIdentities' => $handledIdentities,
            'allIdentities' => $allIdentities,
        ]);
    }

    /**
     * Envía una notificación por email.
     */
    protected function sendNotification(Identity $identity, string $action, ?string $reasonCode, $admin)
    {
        $reason = null;
        if ($reasonCode) {
            $reason = IdentityActionReason::where('action_type', $action)
                ->where('code', $reasonCode)
                ->firstOrFail();
        }

        Mail::to($identity->user->email)->send(new IdentityActionNotification($identity, $action, $reason, $admin));
    }
}