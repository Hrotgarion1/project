<?php

namespace App\Policies;

use App\Models\Invitacion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class InvitacionPolicy
{

    use HandlesAuthorization;

    public function aceptar(User $user, Invitacion $invitacion)
    {
        // El usuario solo puede aceptar invitaciones que le hayan sido enviadas
        return $invitacion->invitado_id === $user->id;
    }

    public function salir(User $user, Invitacion $invitacion)
    {
        // El usuario solo puede salir de grupos en los que haya sido invitado
        return $invitacion->invitado_id === $user->id;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('editor') || $user->hasRole('supervisor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invitacion $invitacion): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invitacion $invitacion): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invitacion $invitacion): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invitacion $invitacion): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invitacion $invitacion): bool
    {
        //
    }
}
