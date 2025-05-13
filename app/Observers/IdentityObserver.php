<?php

namespace App\Observers;

use App\Models\Identity;
use App\Models\Invitacion;
use App\Models\User;
use App\Http\Controllers\IdentityRequestController;
use App\Models\IdentityActionReason;
use App\Mail\IdentityActionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class IdentityObserver
{

    protected $requestController;

    public function __construct()
    {
        $this->requestController = new IdentityRequestController();
    }
    /**
     * Handle the Identity "created" event.
     */
    public function created(Identity $identity): void
    {
        //
    }

    /**
     * Handle the Identity "updated" event.
     */
    public function updated(Identity $identity): void
    {
        // Verificar si el estado cambió
        if ($identity->wasChanged('status')) {
            $originalStatus = $identity->getOriginal('status');
            $newStatus = $identity->status;

            // Suspensión
            if ($newStatus === 'suspended' && $originalStatus !== 'suspended') {
                $this->handleSuspension($identity);
            }

            // Reactivación: de 'suspended' a 'approved' y suspend_reason_code a null
            if ($newStatus === 'approved' && $originalStatus === 'suspended' && $identity->wasChanged('suspend_reason_code') && is_null($identity->suspend_reason_code)) {
                $this->handleReactivation($identity);
            }
        }
    }

    protected function handleSuspension(Identity $identity)
    {
        $invitations = Invitacion::where('identity_id', $identity->id)->where('status', 'approved')->get();
        $invitedUsers = [];

        // Obtener el motivo de la suspensión
        $reason = IdentityActionReason::where('action_type', 'suspended')
            ->where('code', $identity->suspend_reason_code)
            ->first();

        // Recolectar invitados
        foreach ($invitations as $invitation) {
            $invitado = User::find($invitation->invitado_id);
            if ($invitado && $invitado->email) {
                $invitedUsers[$invitado->id] = ['user' => $invitado, 'role' => $invitation->role];
            } else {
                Log::warning('Invitado sin correo o no encontrado', ['invitado_id' => $invitation->invitado_id]);
            }
        }

        // Enviar notificaciones a los invitados
        foreach ($invitedUsers as $invitadoData) {
            $invitado = $invitadoData['user'];
            $role = $invitadoData['role'];
            try {
                Mail::to($invitado->email)->send(new IdentityActionNotification(
                    $identity,
                    'suspended',
                    $reason,
                    auth()->user() ?? User::find(1),
                    $role,
                    $invitado->name // Pasamos el nombre del invitado
                ));
                Log::info("Notificación enviada a invitado {$invitado->id} ({$invitado->email}) por suspensión de identidad.");
            } catch (\Exception $e) {
                Log::error('Error enviando notificación al invitado: ' . $e->getMessage(), [
                    'invitado_id' => $invitado->id,
                    'identity_id' => $identity->id,
                ]);
            }
        }
    }

    protected function handleReactivation(Identity $identity)
    {
        $invitations = Invitacion::where('identity_id', $identity->id)->where('status', 'approved')->get();
        $invitedUsers = [];

        // No se necesita motivo para reactivación
        $reason = null;

        // Recolectar invitados
        foreach ($invitations as $invitation) {
            $invitado = User::find($invitation->invitado_id);
            if ($invitado && $invitado->email) {
                $invitedUsers[$invitado->id] = ['user' => $invitado, 'role' => $invitation->role];
            } else {
                Log::warning('Invitado sin correo o no encontrado', ['invitado_id' => $invitation->invitado_id]);
            }
        }

        // Enviar notificaciones a los invitados
        foreach ($invitedUsers as $invitadoData) {
            $invitado = $invitadoData['user'];
            $role = $invitadoData['role'];
            try {
                Mail::to($invitado->email)->send(new IdentityActionNotification(
                    $identity,
                    'reactivated',
                    $reason,
                    auth()->user() ?? User::find(1),
                    $role,
                    $invitado->name // Pasamos el nombre del invitado
                ));
                Log::info("Notificación enviada a invitado {$invitado->id} ({$invitado->email}) por reactivación de identidad.");
            } catch (\Exception $e) {
                Log::error('Error enviando notificación al invitado: ' . $e->getMessage(), [
                    'invitado_id' => $invitado->id,
                    'identity_id' => $identity->id,
                ]);
            }
        }
    }

    /**
     * Handle the Identity "deleted" event.
     */
    public function deleted(Identity $identity): void
    {
        $invitations = Invitacion::where('identity_id', $identity->id)->get();
        $invitedUsers = [];

        foreach ($invitations as $invitation) {
            if ($invitation->status === 'approved') {
                $invitado = User::find($invitation->invitado_id);
                if ($invitado) {
                    $otherInvitationsCount = Invitacion::where('invitado_id', $invitado->id)
                        ->where('role', $invitation->role)
                        ->where('status', 'approved')
                        ->where('identity_id', '!=', $identity->id)
                        ->count();

                    if ($otherInvitationsCount === 0) {
                        $invitado->removeRole($invitation->role);
                        Log::info("Rol {$invitation->role} eliminado de usuario {$invitado->id} al no tener más invitaciones.");
                    } else {
                        Log::info("Rol {$invitation->role} mantenido para usuario {$invitado->id} por {$otherInvitationsCount} invitaciones activas.");
                    }

                    $invitedUsers[$invitado->id] = $invitado;
                } else {
                    Log::warning("No se encontró el invitado con ID {$invitation->invitado_id} para la invitación {$invitation->id}.");
                }
            }

            $invitation->delete();
        }

        foreach ($invitedUsers as $invitado) {
            try {
                $reason = IdentityActionReason::where('action_type', 'deleted')
                    ->where('code', 'identity_deleted')
                    ->first();
                Mail::to($invitado->email)->send(new IdentityActionNotification(
                    $identity,
                    'invitation_deleted',
                    $reason ? $reason : null, // Enviamos el objeto completo o null
                    auth()->user() ?? User::find(1),
                    $invitation->role,
                    $invitado->name // Pasamos el nombre del invitado
                ));
                Log::info("Notificación enviada a invitado {$invitado->id} ({$invitado->email}) por eliminación de invitación.");
            } catch (\Exception $e) {
                Log::error('Error enviando notificación al invitado: ' . $e->getMessage());
            }
        }

        try {
            $reason = IdentityActionReason::where('action_type', 'deleted')
                ->where('code', 'identity_deleted')
                ->first();
            Mail::to($identity->user->email)->send(new IdentityActionNotification(
                $identity,
                'invitation_deleted',
                $reason ? $reason : null,
                auth()->user() ?? User::find(1),
                $invitation->role,
                $invitado->name // Pasamos el nombre del invitado
            ));
            Log::info("Notificación enviada al invitador {$identity->user_id} por eliminación de identidad.");
        } catch (\Exception $e) {
            Log::error('Error enviando notificación al invitador: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Identity "restored" event.
     */
    public function restored(Identity $identity): void
    {
        $invitations = Invitacion::withTrashed()
            ->where('identity_id', $identity->id)
            ->whereNotNull('deleted_at')
            ->get();
        $invitedUsers = [];

        foreach ($invitations as $invitation) {
            $invitation->restore();
            Log::info("Invitación restaurada para identity_id: {$identity->id}, invitado_id: {$invitation->invitado_id}");

            if ($invitation->status === 'approved') {
                $invitado = User::find($invitation->invitado_id);
                if ($invitado) {
                    if (!$invitado->hasRole($invitation->role)) {
                        $invitado->assignRole($invitation->role);
                        Log::info("Rol {$invitation->role} reasignado a usuario {$invitado->id} tras restaurar identidad.");
                    }
                    $invitedUsers[$invitado->id] = $invitado;
                }
            }
        }

        foreach ($invitedUsers as $invitado) {
            try {
                $reason = IdentityActionReason::where('action_type', 'restored')
                    ->where('code', 'identity_restored')
                    ->first();
                Mail::to($invitado->email)->send(new IdentityActionNotification(
                    $identity,
                    'invitation_restored',
                    $reason ? $reason : null,
                    auth()->user() ?? User::find(1),
                    $invitation->role,
                    $invitado->name // Pasamos el nombre del invitado
                ));
                Log::info("Notificación enviada a invitado {$invitado->id} ({$invitado->email}) por restauración de invitación.");
            } catch (\Exception $e) {
                Log::error('Error enviando notificación al invitado: ' . $e->getMessage());
            }
        }

        try {
            $reason = IdentityActionReason::where('action_type', 'restored')
                ->where('code', 'identity_restored')
                ->first();
            Mail::to($identity->user->email)->send(new IdentityActionNotification(
                $identity,
                'invitation_restored',
                $reason ? $reason : null,
                auth()->user() ?? User::find(1),
                $invitation->role,
                $invitado->name // Pasamos el nombre del invitado
            ));
            Log::info("Notificación enviada al invitador {$identity->user_id} por restauración de identidad.");
        } catch (\Exception $e) {
            Log::error('Error enviando notificación al invitador: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Identity "force deleted" event.
     */
    public function forceDeleted(Identity $identity): void
    {
        //
    }
}
