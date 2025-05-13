<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Models\Identity;

class IdentityPolicy
{
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
    public function view(User $user, Identity $identity): bool
    {
        // Verifica si el usuario tiene permisos para ver la identidad
        if ($user->hasRole('admin') || $user->id === $identity->user_id) {
        return true;
        }

        return false; // <-- Agrega esto para evitar el error
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
    public function update(User $user, Identity $identity): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Identity $identity): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Identity $identity): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Identity $identity): bool
    {
        //
    }
}
