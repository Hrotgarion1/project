<?php

namespace App\Policies;

use App\Models\IdentityType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdentityTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    public function create(User $user)
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, IdentityType $identityType)
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, IdentityType $identityType)
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, IdentityType $identityType)
    {
        return $user->hasRole('admin');
    }
}
